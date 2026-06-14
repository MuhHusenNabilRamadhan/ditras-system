<?php
// api/cek-ketersediaan.php
header('Content-Type: application/json');

// Sambungkan ke database Anda
require_once '../config/database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $layanan = $_POST['layanan'] ?? '';

    // Contoh query (sesuaikan dengan tabel database Anda)
    // $stmt = $conn->prepare("SELECT * FROM armada WHERE tanggal = ? AND status = 'tersedia'");
    // $stmt->execute([$tanggal]);
    
    // Simulasi respons dari database
    echo json_encode([
        'status' => 'success',
        'message' => "Data untuk $layanan pada $tanggal ditemukan.",
        'nama' => $nama
    ]);
}
?>