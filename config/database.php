<?php
// config/database.php

$host     = getenv('DB_HOST') ?: "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com";
$dbname   = getenv('DB_NAME') ?: "db_ditras";
$username = getenv('DB_USER') ?: "8cyG8Tbmd4S7t6a.root";
$password = getenv('DB_PASS') ?: "jxioXlbn5IbbLbjU"; 
$port     = getenv('DB_PORT') ?: "4000";

try {
    // Tambahkan opsi SSL Required ke dalam array
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // 1014 adalah nilai internal untuk PDO::MYSQL_ATTR_SSL_MODE
        // 1 adalah nilai untuk MYSQL_ATTR_SSL_MODE_REQUIRED
        1014 => 1, 
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, 
    ];

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);

} catch(PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>