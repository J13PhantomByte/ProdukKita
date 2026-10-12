<?php
declare(strict_types=1);

// Sesuaikan dengan konfigurasi MySQL lokal. Untuk produksi, gunakan environment variables.
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'db_produk';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    // Jangan tampilkan detail koneksi/database kepada pengguna.
    error_log('Database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Layanan sedang bermasalah. Silakan coba lagi nanti.');
}
