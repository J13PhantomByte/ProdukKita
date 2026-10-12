CREATE DATABASE IF NOT EXISTS db_produk
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_produk;

CREATE TABLE IF NOT EXISTS products (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  barang ENUM('Makanan','Minuman','Dessert','Lainnya') NOT NULL DEFAULT 'Makanan',
  harga INT UNSIGNED NOT NULL,
  stok INT UNSIGNED NOT NULL DEFAULT 0,
  gambar VARCHAR(500) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_products_nama (nama),
  INDEX idx_products_barang (barang)
) ENGINE=InnoDB;

INSERT INTO products (nama, barang, harga, stok, gambar) VALUES
('Es Kopi Susu','Minuman',15000,10,'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=800&q=80'),
('Burger Beef','Makanan',25000,5,'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80'),
('Strawberry Cake','Dessert',20000,3,'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80'),
('French Fries','Makanan',12000,8,'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=800&q=80');
