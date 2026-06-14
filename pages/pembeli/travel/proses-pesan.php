<?php
// pages/pembeli/rental-supir/proses-pesan.php
session_start();
require_once '../../../config/database.php';

// Pastikan pembeli sudah login sebelum memproses pesanan
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data ID Pembeli dari Session user yang aktif
    $pembeli_id      = $_SESSION['id_user'];
    
    // Menangkap data lemparan dari Form input dengan proteksi string kosong / undifined
    $mobil_id        = (!empty($_POST['id_mobil'])) ? intval($_POST['id_mobil']) : null;
    $supir_id        = (!empty($_POST['id_supir'])) ? intval($_POST['id_supir']) : null;
    $tanggal_mulai   = (!empty($_POST['tanggal_berangkat'])) ? trim($_POST['tanggal_berangkat']) : null;
    $durasi          = (!empty($_POST['durasi'])) ? intval($_POST['durasi']) : 1;
    
    // Validasi ketat dan spesifik agar tahu parameter mana yang kosong saat form dikirim
    if (!$mobil_id) {
        die("Gagal memproses! Anda belum memilih armada kendaraan (Atribut name='id_mobil' kosong atau tidak cocok).");
    }
    if (!$supir_id) {
        die("Gagal memproses! Anda belum memilih driver/supir mitra (Atribut name='id_supir' kosong atau tidak cocok).");
    }
    if (!$tanggal_mulai) {
        die("Gagal memproses! Tanggal keberangkatan belum ditentukan (Atribut name='tanggal_berangkat' kosong atau tidak cocok).");
    }
    
    // 1. Hitung tanggal_selesai secara otomatis berdasarkan durasi hari sewa
    $date = new DateTime($tanggal_mulai);
    $date->modify('+' . ($durasi - 1) . ' days');
    $tanggal_selesai = $date->format('Y-m-d');
    
    // 2. Kalkulasi tarif total (Contoh standar: Mobil 500rb + Supir 200rb = 700rb per hari)
    $tarif_mobil = 500000; 
    $tarif_supir = 200000; 
    $total_tagihan = ($tarif_mobil + $tarif_supir) * $durasi;

    try {
        // Mulai database transaction untuk menjaga integritas relasi tabel
        $pdo->beginTransaction();

        // 3. INSERT KE TABEL INDUK (transaksi) 
        $stmt_induk = $pdo->prepare("
            INSERT INTO transaksi (pembeli_id, jenis_layanan, total_harga, status_pembayaran, tanggal_transaksi) 
            VALUES (?, 'rental_supir', ?, 'pending', NOW())
        ");
        $stmt_induk->execute([$pembeli_id, $total_tagihan]);
        
        // Ambil ID Transaksi yang baru saja terbuat untuk dipakai oleh tabel detail_rental
        $transaksi_id = $pdo->lastInsertId();

        // 4. INSERT KE TABEL ANAK (detail_rental)
        $status_rental = 'booking'; 
        $stmt_detail = $pdo->prepare("
            INSERT INTO detail_rental (transaksi_id, mobil_id, supir_id, tanggal_mulai, tanggal_selesai, status_rental) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt_detail->execute([$transaksi_id, $mobil_id, $supir_id, $tanggal_mulai, $tanggal_selesai, $status_rental]);
        
        // Mengambil ID detail_rental untuk parameter tracking di halaman pembeli
        $id_detail = $pdo->lastInsertId();

        // Kunci semua perubahan data ke dalam database jika semua query berhasil
        $pdo->commit();

        // Alihkan pembeli langsung ke halaman tracking status order secara real-time
        header("Location: tracking.php?id_detail=" . $id_detail . "&durasi=" . $durasi);
        exit;

    } catch (PDOException $e) {
        // Jika salah satu query di atas gagal, batalkan semua agar database tidak berantakan
        $pdo->rollBack();
        die("Gagal memproses pesanan rental ke database: " . $e->getMessage());
    }
} else {
    // Jika file diakses langsung tanpa melalui form POST, kembalikan ke form-sewa
    header("Location: form-sewa.php");
    exit;
}