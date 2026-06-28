<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'koneksi.php';

session_start();
if (empty($_SESSION['logged_in'])) {
    log_error("api: unauthorized from {$_SERVER['REMOTE_ADDR']}");
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}
$role     = $_SESSION['role'];
$username = $_SESSION['username'];

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id'])     ? intval($_GET['id'])     : null;
$action = isset($_GET['action']) ? $_GET['action']         : null;

if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        log_error("api: CSRF mismatch $username ($method)");
        http_response_code(403);
        echo json_encode(["message" => "CSRF token invalid"]);
        exit;
    }
}

function autoCode($conn, $kategori_id) {
    $stmt = $conn->prepare("SELECT code_prefix FROM kategori WHERE id = :id");
    $stmt->execute([':id' => $kategori_id]);
    $kat = $stmt->fetch();
    if (!$kat) return null;
    $prefix = $kat['code_prefix'];

    $stmt2 = $conn->prepare("SELECT code FROM barang WHERE code LIKE :pre ORDER BY id DESC LIMIT 1");
    $stmt2->execute([':pre' => $prefix . '-%']);
    $last = $stmt2->fetch();

    if ($last) {
        $parts = explode('-', $last['code']);
        $num = intval(end($parts)) + 1;
    } else {
        $num = 1;
    }
    return $prefix . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
}

function has_deleted($conn) {
    $stmt = $conn->prepare("SELECT b.*, k.name AS kategori_name, k.code_prefix 
        FROM barang b JOIN kategori k ON b.kategori_id = k.id 
        WHERE b.deleted_at IS NOT NULL ORDER BY b.id DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

switch ($method) {
    // ───── GET ─────
    case 'GET':
        // Kategori list
        if ($action === 'kategori') {
            $stmt = $conn->query("SELECT * FROM kategori ORDER BY name");
            echo json_encode($stmt->fetchAll());
            break;
        }
        // Deleted items (trash)
        if ($action === 'trash') {
            echo json_encode(has_deleted($conn));
            break;
        }
        // Riwayat
        if ($action === 'riwayat') {
            $stmt = $conn->prepare("
                SELECT t.*, b.name AS nama_barang, b.code AS kode_barang
                FROM transaksi_stok t
                JOIN barang b ON t.barang_id = b.id
                ORDER BY t.id DESC
            ");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;
        }
        // Active barang only
        $stmt = $conn->query("SELECT b.*, k.name AS kategori_name, k.code_prefix 
            FROM barang b JOIN kategori k ON b.kategori_id = k.id 
            WHERE b.deleted_at IS NULL ORDER BY b.id DESC");
        echo json_encode($stmt->fetchAll());
        break;

    // ───── POST ─────
    case 'POST':
        // Create kategori (admin only)
        if ($action === 'kategori') {
            if ($role !== 'admin') {
                http_response_code(403);
                echo json_encode(["message" => "Hanya admin"]);
                break;
            }
            $name   = trim($input['name'] ?? '');
            $prefix = strtoupper(trim($input['code_prefix'] ?? ''));
            if (!$name || !$prefix) {
                http_response_code(400);
                echo json_encode(["message" => "Nama & prefix wajib"]);
                break;
            }
            try {
                $stmt = $conn->prepare("INSERT INTO kategori (name, code_prefix) VALUES (:name, :prefix)");
                $stmt->execute([':name' => $name, ':prefix' => $prefix]);
                echo json_encode(["message" => "Kategori ditambahkan", "id" => $conn->lastInsertId()]);
            } catch (PDOException $e) {
                http_response_code(409);
                echo json_encode(["message" => "Kategori/prefix sudah ada"]);
            }
            break;
        }

        // Transaksi
        if ($action === 'transaksi') {
            $barang_id   = intval($input['barang_id'] ?? 0);
            $jenis       = $input['jenis_transaksi'] ?? '';
            $jumlah      = intval($input['jumlah'] ?? 0);
            $keterangan  = trim($input['keterangan'] ?? '');

            if (!in_array($jenis, ['masuk', 'keluar'])) {
                http_response_code(400);
                echo json_encode(["message" => "Jenis transaksi tidak valid"]);
                break;
            }
            $stmt = $conn->prepare("SELECT stock FROM barang WHERE id = :id AND deleted_at IS NULL");
            $stmt->execute([':id' => $barang_id]);
            $barang = $stmt->fetch();
            if (!$barang) {
                http_response_code(404);
                echo json_encode(["message" => "Barang tidak ditemukan"]);
                break;
            }
            $stok_baru = $jenis === 'masuk' ? $barang['stock'] + $jumlah : $barang['stock'] - $jumlah;
            if ($stok_baru < 0) {
                http_response_code(400);
                echo json_encode(["message" => "Stok tidak mencukupi"]);
                break;
            }
            $conn->beginTransaction();
            try {
                $conn->prepare("UPDATE barang SET stock = :stock WHERE id = :id")
                    ->execute([':stock' => $stok_baru, ':id' => $barang_id]);
                $conn->prepare("INSERT INTO transaksi_stok (barang_id, username, jenis_transaksi, jumlah, keterangan)
                    VALUES (:barang_id, :username, :jenis, :jumlah, :keterangan)")
                    ->execute([':barang_id'=>$barang_id,':username'=>$username,':jenis'=>$jenis,':jumlah'=>$jumlah,':keterangan'=>$keterangan]);
                $conn->commit();
                log_error("transaksi: $jenis $jumlah $barang_id by $username");
                echo json_encode(["message" => "Transaksi stok berhasil dicatat"]);
            } catch (Exception $e) {
                $conn->rollBack();
                log_error("transaksi failed: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(["message" => "Gagal memproses transaksi"]);
            }
            break;
        }

        // Create barang — Admin only, auto-code
        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Hanya admin"]);
            break;
        }
        $kategori_id = intval($input['kategori_id'] ?? 0);
        $name  = trim($input['name'] ?? '');
        $stock = intval($input['stock'] ?? 0);
        $unit  = trim($input['unit'] ?? '');
        $price = intval($input['price'] ?? 0);

        if (!$kategori_id || !$name) {
            http_response_code(400);
            echo json_encode(["message" => "Kategori & nama wajib"]);
            break;
        }
        $code = autoCode($conn, $kategori_id);
        if (!$code) {
            http_response_code(400);
            echo json_encode(["message" => "Kategori tidak valid"]);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO barang (kategori_id, code, name, stock, unit, price) 
            VALUES (:kategori_id, :code, :name, :stock, :unit, :price)");
        $stmt->execute([':kategori_id'=>$kategori_id,':code'=>$code,':name'=>$name,':stock'=>$stock,':unit'=>$unit,':price'=>$price]);
        log_error("barang: create $code ($name) by $username");
        echo json_encode(["message" => "Barang berhasil ditambahkan", "code" => $code]);
        break;

    // ───── PUT ─────
    case 'PUT':
        // Restore soft-deleted
        if ($action === 'restore') {
            if ($role !== 'admin') {
                http_response_code(403);
                echo json_encode(["message" => "Hanya admin"]);
                break;
            }
            if (!$id) { echo json_encode(["message" => "ID Kosong"]); break; }
            $stmt = $conn->prepare("UPDATE barang SET deleted_at = NULL WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount()) {
                log_error("barang: restore id=$id by $username");
                echo json_encode(["message" => "Barang dipulihkan"]);
            } else {
                echo json_encode(["message" => "Barang tidak ditemukan"]);
            }
            break;
        }

        // Update barang — Admin only
        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Hanya admin"]);
            break;
        }
        if (!$id) { echo json_encode(["message" => "ID Kosong"]); break; }

        $kategori_id = intval($input['kategori_id'] ?? 0);
        $name  = trim($input['name'] ?? '');
        $stock = intval($input['stock'] ?? 0);
        $unit  = trim($input['unit'] ?? '');
        $price = intval($input['price'] ?? 0);

        // Only regenerate code if kategori changed
        $old = $conn->prepare("SELECT code, kategori_id FROM barang WHERE id = :id");
        $old->execute([':id' => $id]);
        $oldData = $old->fetch();
        $code = $oldData['code'];
        if ($kategori_id && $kategori_id != ($oldData['kategori_id'] ?? 0)) {
            $newCode = autoCode($conn, $kategori_id);
            if ($newCode) $code = $newCode;
        }
        $kategori_id = $kategori_id ?: ($oldData['kategori_id'] ?? 1);

        $stmt = $conn->prepare("UPDATE barang SET kategori_id=:kategori_id, code=:code, name=:name, stock=:stock, unit=:unit, price=:price WHERE id=:id");
        $stmt->execute([':kategori_id'=>$kategori_id,':code'=>$code,':name'=>$name,':stock'=>$stock,':unit'=>$unit,':price'=>$price,':id'=>$id]);
        if ($stmt->rowCount()) {
            log_error("barang: update id=$id by $username");
            echo json_encode(["message" => "Barang diperbarui", "code" => $code]);
        } else {
            echo json_encode(["message" => "Barang tidak ditemukan"]);
        }
        break;

    // ───── DELETE (soft delete) ─────
    case 'DELETE':
        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Hanya admin"]);
            break;
        }
        // Permanent delete if action=force
        if ($action === 'force') {
            if (!$id) { echo json_encode(["message" => "ID Kosong"]); break; }
            $stmt = $conn->prepare("DELETE FROM barang WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount()) {
                log_error("barang: forcedelete id=$id by $username");
                echo json_encode(["message" => "Barang dihapus permanen"]);
            } else {
                echo json_encode(["message" => "Barang tidak ditemukan"]);
            }
            break;
        }
        // Default: soft delete
        if (!$id) { echo json_encode(["message" => "ID Kosong"]); break; }
        $stmt = $conn->prepare("UPDATE barang SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount()) {
            log_error("barang: softdelete id=$id by $username");
            echo json_encode(["message" => "Barang dipindahkan ke tempat sampah"]);
        } else {
            echo json_encode(["message" => "Barang tidak ditemukan"]);
        }
        break;
}
