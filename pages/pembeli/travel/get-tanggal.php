<?php
// pages/pembeli/travel/get-tanggal.php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/DITRAS-SYSTEM/config/database.php';

header('Content-Type: application/json');

if (isset($_GET['rute'])) {
    $rute = $_GET['rute'];

    try {
        // Ambil data tanggal yang sesuai dengan rute pilihan, pastikan tanggalnya hari ini ke depan (tidak kedaluwarsa)
        $stmt = $pdo->prepare("SELECT DISTINCT tanggal_berangkat FROM jadwal_travel 
                               WHERE rute_perjalanan = ? AND tanggal_berangkat >= CURDATE() 
                               ORDER BY tanggal_berangkat ASC");
        $stmt->execute([$rute]);
        $hasil = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($hasil);
    } catch (Exception $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}