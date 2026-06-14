<?php
session_start();
require_once '../../config/database.php';

// Pastikan parameter id dikirim lewat URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Gunakan prepared statement PDO untuk menghapus data rute secara aman
        $stmt = $pdo->prepare("DELETE FROM rute WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // Setelah berhasil menghapus, otomatis dialihkan kembali ke halaman master rute
        header("Location: master-rute.php");
        exit();
    } catch (PDOException $e) {
        // Tampilkan pesan error jika proses gagal akibat relasi foreign key atau masalah DB lainnya
        die("Gagal menghapus data rute: " . $e->getMessage());
    }
} else {
    // Jika tidak ada ID yang valid di URL, langsung kembalikan ke master rute
    header("Location: master-rute.php");
    exit();
}
?>