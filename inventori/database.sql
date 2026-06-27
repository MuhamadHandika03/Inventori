CREATE DATABASE IF NOT EXISTS db_inventori;
USE db_inventori;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL
);

CREATE TABLE IF NOT EXISTS barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    price INT NOT NULL DEFAULT 0
);

USE db_inventori;

CREATE TABLE IF NOT EXISTS transaksi_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barang_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    jenis_transaksi ENUM('masuk', 'keluar') NOT NULL,
    jumlah INT NOT NULL,
    keterangan TEXT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE CASCADE
);

INSERT INTO users (username, password, role) VALUES 
('admin', MD5('admin123'), 'admin'),
('staff', MD5('staff123'), 'staff')
ON DUPLICATE KEY UPDATE username=username;