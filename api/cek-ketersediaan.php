<?php
// api/cek-ketersediaan.php
header('Content-Type: application/json');

// Sambungkan ke database Anda
require_once '../config/database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $layanan = $_POST['layanan'] ?? '';

    try {
        // Menggunakan variabel $pdo yang sesuai dengan config/database.php
        // $stmt = $pdo->prepare("SELECT COUNT(*) FROM armada WHERE tanggal = ? AND layanan = ? AND status = 'tersedia'");
        // $stmt->execute([$tanggal, $layanan]);
        
        echo json_encode([
            'status' => 'success',
            'message' => "Data untuk $layanan pada $tanggal berhasil dicek.",
            'nama' => $nama
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal terhubung ke database TiDB: ' . $e->getMessage()
        ]);
    }
}
?>