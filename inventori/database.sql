CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL
);

CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code_prefix VARCHAR(5) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO kategori (id, name, code_prefix) VALUES
(1, 'Monitor', 'MTR'),
(2, 'Keyboard', 'KEY'),
(3, 'Mouse', 'MOU'),
(4, 'Printer', 'PRT'),
(5, 'Laptop', 'LPT'),
(6, 'Aksesoris', 'AKS'),
(7, 'Makanan', 'MKN'),
(8, 'Minuman', 'MNM'),
(9, 'ATK', 'ATK'),
(10, 'Elektronik', 'ELK');

CREATE TABLE IF NOT EXISTS barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    price INT NOT NULL DEFAULT 0,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id)
);

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
