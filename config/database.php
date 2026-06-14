<?php
// config/database.php

// --- TAMBAHKAN BARIS INI UNTUK MEMPERBAIKI FONT & NOT FOUND ---
// Sesuaikan 'ditras' dengan nama folder project kamu di dalam htdocs
define('BASE_URL', 'http://localhost/ditras/');
// ---------------------------------------------------------------

$host     = "localhost";
$dbname   = "db_ditras";
$username = "root";
$password = "";

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Setting error mode ke exception untuk mempermudah debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Default fetch mode diatur ke Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Hentikan eksekusi jika database gagal terhubung
    die("Koneksi Database PDO Gagal: " . $e->getMessage());
}
?>