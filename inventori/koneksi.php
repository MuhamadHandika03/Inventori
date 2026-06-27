<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "inventori";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(["message" => "Koneksi database ke Laragon gagal: " . mysqli_connect_error()]));
}
?>