<?php
// config/database.php

// BASE_URL otomatis menyesuaikan apakah di Vercel atau di Localhost
if (getenv('VERCEL_URL')) {
    define('BASE_URL', 'https://' . getenv('VERCEL_URL') . '/');
} else {
    define('BASE_URL', 'http://localhost/ditras/');
}

// Mengambil kredensial dari Environment Variables dengan Fallback nilai langsung
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

    // JIKA DI VERCEL / PORT TIDE CLOUD (4000)
    if ($port == "4000" || getenv('VERCEL_URL')) {
        // Mencari file CA Bundle valid di server Linux Vercel
        $ca_paths = [
            '/etc/pki/tls/certs/ca-bundle.crt',   // Amazon Linux (Standar Vercel)
            '/etc/ssl/certs/ca-certificates.crt',  // Debian/Ubuntu
            '/etc/ssl/cert.pem'
        ];
        
        $found_ca = '';
        foreach ($ca_paths as $path) {
            if (file_exists($path)) {
                $found_ca = $path;
                break;
            }
        }

        // SOLUSI MUTLAK: Menggunakan skema URI DSN dengan opsi SSL ketat di dalam string
        if (!empty($found_ca)) {
            $dsn = "mysql:uri=mysql://$username:$password@$host:$port/$dbname?sslca=$found_ca&sslmode=verify-ca";
        } else {
            $dsn = "mysql:uri=mysql://$username:$password@$host:$port/$dbname?sslmode=required";
        }
        
        // Buat koneksi langsung menggunakan skema URI DSN
        $pdo = new PDO($dsn, null, null, $options);

    } else {
        // Jalur standar jika Anda testing di Localhost offline (tanpa SSL)
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, $options);
    }

} catch(PDOException $e) {
    // Jika skema URI mengalami keterbatasan di versi pdo tertentu, gunakan fallback DSN murni
    try {
        $fallback_dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        if (isset($found_ca) && !empty($found_ca)) {
            $fallback_dsn .= ";sslca=" . $found_ca;
        } else {
            $fallback_dsn .= ";ssl-mode=required";
        }
        $pdo = new PDO($fallback_dsn, $username, $password, $options);
    } catch (PDOException $ex) {
        die("Koneksi Database Gagal Sempurna: " . $ex->getMessage());
    }
}
?>