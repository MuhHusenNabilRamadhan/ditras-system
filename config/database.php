<?php
// config/database.php

// BASE_URL otomatis menyesuaikan apakah di Vercel atau di Localhost
if (getenv('VERCEL_URL')) {
    define('BASE_URL', 'https://' . getenv('VERCEL_URL') . '/');
} else {
    // Sesuaikan dengan local kamu jika sedang testing offline
    define('BASE_URL', 'http://localhost/ditras/');
}

// Mengambil kredensial dari Environment Variables (Vercel / Local .env)
$host     = getenv('DB_HOST') ?: "localhost";
$dbname   = getenv('DB_NAME') ?: "db_ditras";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: "";
$port     = getenv('DB_PORT') ?: "3306";

try {
    // Konfigurasi khusus untuk TiDB (Wajib SSL di Cloud)
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // Jika berjalan di Vercel (Production), aktifkan SSL untuk TiDB
    if (getenv('VERCEL_URL')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; 
        // Catatan: TiDB Cloud secara default mengizinkan koneksi TLS tanpa verifikasi cert lokal pada driver modern.
    }

    // Membuat koneksi PDO dengan Port dinamis
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password, $options);

} catch(PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>