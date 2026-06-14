<?php
// config/database.php

// BASE_URL otomatis menyesuaikan apakah di Vercel atau di Localhost
if (getenv('VERCEL_URL')) {
    define('BASE_URL', 'https://' . getenv('VERCEL_URL') . '/');
} else {
    define('BASE_URL', 'http://localhost/ditras/');
}

// Mengambil kredensial dari Environment Variables
$host     = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com");
$dbname   = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? "db_ditras");
$username = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? "8cyG8Tbmd4S7t6a.root");
$password = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? "jxioXlbn5IbbLbjU");
$port     = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? "4000");

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // Jika berjalan di Vercel atau terhubung ke TiDB Cloud (Port 4000)
    if ($port == "4000" || getenv('VERCEL_URL')) {
        
        // 1. Cari jalur sertifikat CA bawaan sistem Linux di server Vercel
        $ca_paths = [
            '/etc/pki/tls/certs/ca-bundle.crt',  // Standar Amazon Linux / RedHat (Vercel Runtime standard)
            '/etc/ssl/certs/ca-certificates.crt', // Standar Ubuntu / Debian
            '/etc/ssl/cert.pem'
        ];
        
        $found_ca = '';
        foreach ($ca_paths as $path) {
            if (file_exists($path)) {
                $found_ca = $path;
                break;
            }
        }

        // 2. Jika sertifikat sistem ditemukan, pasang ke driver menggunakan nilai integer agar anti-error
        if (!empty($found_ca)) {
            // 1012 adalah nilai integer dari PDO::MYSQL_ATTR_SSL_CA
            $options[1012] = $found_ca; 
        }

        // 1014 adalah nilai integer dari PDO::MYSQL_ATTR_SSL_MODE
        // 1 melambangkan MYSQL_ATTR_SSL_MODE_REQUIRED
        $options[1014] = 1; 
        
        // Matikan verifikasi cert lokal agar tidak bentrok dengan sertifikat kolektif TiDB Cloud
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    // Eksekusi koneksi PDO
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, $options);

} catch(PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>