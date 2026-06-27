<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$action = isset($_GET['action']) ? $_GET['action'] : null;

switch($method) {
    case 'GET':
        // Jika minta data riwayat transaksi
        if ($action === 'riwayat') {
            $query = "SELECT t.*, b.name as nama_barang, b.code as kode_barang 
                      FROM transaksi_stok t 
                      JOIN barang b ON t.barang_id = b.id 
                      ORDER BY t.id DESC";
            $result = mysqli_query($conn, $query);
            $data = [];
            while($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
        } else {
            // Mengambil data barang seperti biasa
            $query = "SELECT * FROM barang ORDER BY id DESC";
            $result = mysqli_query($conn, $query);
            $data = [];
            while($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        // FITUR BARU: Proses Transaksi Stok Masuk / Keluar
        if ($action === 'transaksi') {
            $barang_id = intval($input['barang_id']);
            $username = mysqli_real_escape_string($conn, $input['username']);
            $jenis = mysqli_real_escape_string($conn, $input['jenis_transaksi']); // 'masuk' atau 'keluar'
            $jumlah = intval($input['jumlah']);
            $keterangan = mysqli_real_escape_string($conn, $input['keterangan'] ?? '');

            // 1. Cek stok sekarang terlebih dahulu
            $cek_barang = mysqli_query($conn, "SELECT stock FROM barang WHERE id = $barang_id");
            if (mysqli_num_rows($cek_barang) == 0) {
                http_response_code(444);
                echo json_encode(["message" => "Barang tidak ditemukan"]);
                break;
            }
            $row_barang = mysqli_fetch_assoc($cek_barang);
            $stok_sekarang = $row_barang['stock'];

            // 2. Hitung perubahan stok
            if ($jenis === 'masuk') {
                $stok_baru = $stok_sekarang + $jumlah;
            } else {
                $stok_baru = $stok_sekarang - $jumlah;
                if ($stok_baru < 0) {
                    http_response_code(400);
                    echo json_encode(["message" => "Stok tidak mencukupi untuk pengeluaran ini!"]);
                    break;
                }
            }

            // 3. Mulai Query Update tabel barang dan Insert tabel transaksi
            $update_query = "UPDATE barang SET stock = $stok_baru WHERE id = $barang_id";
            $insert_query = "INSERT INTO transaksi_stok (barang_id, username, jenis_transaksi, jumlah, keterangan) 
                             VALUES ($barang_id, '$username', '$jenis', $jumlah, '$keterangan')";

            if (mysqli_query($conn, $update_query) && mysqli_query($conn, $insert_query)) {
                echo json_encode(["message" => "Transaksi stok berhasil dicatat"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Gagal memproses transaksi"]);
            }
            break;
        }

        // POST biasa: Tambah master barang (Hanya Admin)
        $code = mysqli_real_escape_string($conn, $input['code']);
        $name = mysqli_real_escape_string($conn, $input['name']);
        $stock = intval($input['stock']);
        $unit = mysqli_real_escape_string($conn, $input['unit']);
        $price = intval($input['price']);

        $query = "INSERT INTO barang (code, name, stock, unit, price) VALUES ('$code', '$name', $stock, '$unit', $price)";
        if (mysqli_query($conn, $query)) {
            echo json_encode(["message" => "Barang berhasil ditambahkan"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Gagal menambah barang"]);
        }
        break;

    case 'PUT':
        if (!$id) { echo json_encode(["message" => "ID Kosong"]); break; }
        $input = json_decode(file_get_contents('php://input'), true);
        $code = mysqli_real_escape_string($conn, $input['code']);
        $name = mysqli_real_escape_string($conn, $input['name']);
        $stock = intval($input['stock']);
        $unit = mysqli_real_escape_string($conn, $input['unit']);
        $price = intval($input['price']);

        $query = "UPDATE barang SET code='$code', name='$name', stock=$stock, unit='$unit', price=$price WHERE id=$id";
        if (mysqli_query($conn, $query)) {
            echo json_encode(["message" => "Barang berhasil diperbarui"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Gagal memperbarui barang"]);
        }
        break;

    case 'DELETE':
        if (!$id) { echo json_encode(["message" => "ID Kosong"]); break; }
        $query = "DELETE FROM barang WHERE id=$id";
        if (mysqli_query($conn, $query)) {
            echo json_encode(["message" => "Barang berhasil dihapus"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Gagal menghapus barang"]);
        }
        break;
}
?>