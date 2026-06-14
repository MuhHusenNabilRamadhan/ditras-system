<?php
// pages/pembeli/rental-supir/proses-sewa.php

// Pastikan session sudah berjalan untuk mengambil ID pembeli yang login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. HUBUNGKAN KONEKSI DATABASE SESUAI STRUKTUR LAPTOPMU
// Menggunakan file database yang sama dengan form-sewa.php ($pdo)
require_once '../../../config/database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. PROTEKSI SESSION (Disamakan dengan form-sewa.php yaitu 'id_user' dan 'pembeli')
    if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
        echo "<script>alert('Silahkan login terlebih dahulu!'); window.location.href='../../auth/login.php';</script>";
        exit();
    }
    
    $pembeli_id = $_SESSION['id_user']; 
    $jenis_layanan = 'rental_supir';
    
    // 3. AMBIL DATA DARI INPUT FORM (Disesuaikan dengan atribut name di form-sewa.php)
    $tanggal_mulai = $_POST['tanggal_berangkat'] ?? null; // Menangkap name="tanggal_berangkat"
    $durasi = isset($_POST['durasi']) ? (int)$_POST['durasi'] : 1; // Menangkap name="durasi"
    $mobil_id = $_POST['id_mobil'] ?? null; // Menangkap name="id_mobil"
    $supir_id = $_POST['id_supir'] ?? null; // Menangkap name="id_supir"
    
    // Perhitungan Tanggal Selesai berdasarkan durasi hari
    if ($tanggal_mulai) {
        $tanggal_selesai = date('Y-m-d', strtotime($tanggal_mulai . " + $durasi days"));
    } else {
        echo "<script>alert('Tanggal mulai sewa tidak boleh kosong!'); window.history.back();</script>";
        exit();
    }

    // 4. KALKULASI TOTAL HARGA REALTIME (Disamakan dengan harga hardcode form-sewa.php)
    // Mobil: Rp 500.000/hari, Supir: Rp 200.000/hari
    $harga_mobil_per_hari = 500000;
    $harga_supir_per_hari = !empty($supir_id) ? 200000 : 0;
    
    $total_harga = ($harga_mobil_per_hari + $harga_supir_per_hari) * $durasi;

    try {
        // Mulai database transaction menggunakan variabel $pdo bawaan database.php
        $pdo->beginTransaction();

        // STEP 1: Insert ke tabel parent 'transaksi'
        $sql_transaksi = "INSERT INTO transaksi (pembeli_id, jenis_layanan, total_harga, status_pembayaran, tanggal_transaksi) 
                          VALUES (:pembeli_id, :jenis_layanan, :total_harga, 'pending', NOW())";
        
        $stmt_transaksi = $pdo->prepare($sql_transaksi);
        $stmt_transaksi->execute([
            ':pembeli_id'      => $pembeli_id,
            ':jenis_layanan'    => $jenis_layanan,
            ':total_harga'      => $total_harga
        ]);

        // Ambil ID transaksi yang barusan terbuat secara otomatis (AUTO_INCREMENT)
        $transaksi_id = $pdo->lastInsertId();

        // STEP 2: Insert ke tabel child 'detail_rental' memakai transaksi_id yang valid
        $sql_rental = "INSERT INTO detail_rental (transaksi_id, mobil_id, supir_id, tanggal_mulai, tanggal_selesai, status_rental) 
                       VALUES (:transaksi_id, :mobil_id, :supir_id, :tanggal_mulai, :tanggal_selesai, 'booking')";
        
        $stmt_rental = $pdo->prepare($sql_rental);
        $stmt_rental->execute([
            ':transaksi_id'    => $transaksi_id,
            ':mobil_id'        => $mobil_id,
            ':supir_id'        => !empty($supir_id) ? $supir_id : null, // Set NULL jika supir tidak diisi
            ':tanggal_mulai'   => $tanggal_mulai,
            ':tanggal_selesai' => $tanggal_selesai
        ]);

        // Jika kedua proses insert berhasil tanpa error, commit ke database
        $pdo->commit();

        // =========================================================================
        // PERBAIKAN REDIRECT PATH: Diarahkan ke folder lepas-kunci tempat riwayat berada
        // =========================================================================
        echo "<script>alert('Pemesanan berhasil disimpan! Silahkan cek riwayat transaksi.'); window.location.href='../lepas-kunci/riwayat.php';</script>";
        exit();

    } catch (PDOException $e) {
        // Batalkan semua perubahan jika salah satu query gagal agar data tidak korup
        $pdo->rollBack();
        echo "Gagal menyimpan data sewa: " . $e->getMessage();
    }
} else {
    // Jika diakses langsung tanpa POST method
    header("Location: form-sewa.php");
    exit();
}
?>