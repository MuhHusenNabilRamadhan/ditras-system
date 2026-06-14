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
$host     = getenv('DB_HOST') ?: "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com"; // Default ke TiDB
$dbname   = getenv('DB_NAME') ?: "db_ditras";
$username = getenv('DB_USER') ?: "8cyG8Tbmd4S7t6a.root";
$password = getenv('DB_PASS') ?: "jxioXlbn5IbbLbjU"; 
$port     = getenv('DB_PORT') ?: "4000";

try {
    // Konfigurasi dasar PDO
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // Jika berjalan di lingkungan produksi (Vercel/TiDB Cloud)
    if (getenv('VERCEL_URL') || $port == "4000") {
        // PERBAIKAN FINAL: Gunakan angka 1014 (ekuivalen dengan MYSQL_ATTR_SSL_MODE)
        // dan angka 1 (ekuivalen dengan SSL_MODE_REQUIRED) agar lolos dari error Undefined Constant di Vercel
        $options[1014] = 1; 
        
        // Mematikan verifikasi cert lokal demi kecocokan handshake container serverless Vercel
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; 
    }

    // Membuat koneksi PDO dengan Port dinamis dan charset yang aman
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, $options);

} catch(PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>