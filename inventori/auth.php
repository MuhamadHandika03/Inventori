<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Membaca data JSON payload dari API Client
    $input = json_decode(file_get_contents('php://input'), true);
    $username = mysqli_real_escape_string($conn, $input['username'] ?? '');
    $password = md5($input['password'] ?? ''); 

    $query = "SELECT id, username, role FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        echo json_encode([
            "status" => "success",
            "message" => "Autentikasi Berhasil",
            "user" => [
                "username" => $user['username'],
                "role" => $user['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Username atau Password salah!"]);
    }
}
?>