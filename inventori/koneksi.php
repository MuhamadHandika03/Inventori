<?php
/**
 * PDO-based db connection + shared helpers.
 * Reads DB_HOST, DB_USER, DB_PASS, DB_NAME from env.
 */
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'inventori';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    log_error("DB connection failed: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(["message" => "Koneksi database gagal"]));
}

function log_error(string $msg): void {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) @mkdir($log_dir, 0755, true);
    $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;
    file_put_contents($log_dir . '/error.log', $line, FILE_APPEND | LOCK_EX);
}
