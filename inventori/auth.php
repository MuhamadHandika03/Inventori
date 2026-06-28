<?php
// CORS: same-origin only
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    log_error("auth: invalid method $method");
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Username & password wajib diisi"]);
    exit;
}

// Rate limiting
session_start();
$rate_file = sys_get_temp_dir() . "/login_ratelimit_" . md5($_SERVER['REMOTE_ADDR']) . ".lock";
$max_attempts = 5;
$lockout_minutes = 15;

if (file_exists($rate_file)) {
    $data = json_decode(file_get_contents($rate_file), true);
    if ($data && $data['attempts'] >= $max_attempts) {
        $elapsed = time() - $data['since'];
        if ($elapsed < $lockout_minutes * 60) {
            $remaining = ceil(($lockout_minutes * 60 - $elapsed) / 60);
            log_error("auth: rate-limited $username from {$_SERVER['REMOTE_ADDR']}");
            http_response_code(429);
            echo json_encode(["status" => "error", "message" => "Terlalu banyak percobaan. Coba lagi $remaining menit lagi."]);
            exit;
        } else {
            @unlink($rate_file);
        }
    }
}

// PDO prepared stmt
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = :username LIMIT 1");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    // Increment rate limit
    $data = file_exists($rate_file) ? json_decode(file_get_contents($rate_file), true) : ['attempts' => 0, 'since' => time()];
    $data['attempts']++;
    if ($data['attempts'] === 1) $data['since'] = time();
    file_put_contents($rate_file, json_encode($data), LOCK_EX);
    log_error("auth: failed login $username from {$_SERVER['REMOTE_ADDR']} (attempt {$data['attempts']})");
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Username atau Password salah!"]);
    exit;
}

// Login success — clear rate limit
@unlink($rate_file);

// Server-side session
session_regenerate_id(true);
$_SESSION['user_id']    = $user['id'];
$_SESSION['username']   = $user['username'];
$_SESSION['role']       = $user['role'];
$_SESSION['logged_in']  = true;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

log_error("auth: login success $username");
echo json_encode([
    "status" => "success",
    "message" => "Autentikasi Berhasil",
    "user" => [
        "username" => $user['username'],
        "role"     => $user['role'],
    ],
    "csrf_token" => $_SESSION['csrf_token'],
]);
