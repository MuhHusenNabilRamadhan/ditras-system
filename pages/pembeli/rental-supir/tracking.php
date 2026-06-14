<?php
// pages/pembeli/rental-supir/tracking.php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../../auth/login.php");
    exit;
}

$id_detail = $_GET['id_detail'] ?? null;
$durasi = isset($_GET['durasi']) ? intval($_GET['durasi']) : 1;

if (!$id_detail) {
    die("ID Detail Rental tidak valid!");
}

try {
    // Mengambil data rental dan join ke users untuk tahu nama supirnya
    $stmt = $pdo->prepare("
        SELECT dr.*, u.nama AS nama_supir 
        FROM detail_rental dr
        JOIN users u ON dr.supir_id = u.id 
        WHERE dr.id = ?
    ");
    $stmt->execute([$id_detail]);
    $sewa = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $sewa = null;
}

if (!$sewa) {
    die("Data pesanan tidak ditemukan.");
}

// BACKEND FALLBACK HARGA: Tarif Mobil + Supir dikali Durasi
$tarif_mobil = 500000;
$tarif_supir = 200000;
$total_harga = ($tarif_mobil + $tarif_supir) * $durasi;

// JIKA SUPIR SUDAH KONFIRMASI MANIFEST (Misal status berubah dari 'booking' menjadi 'diambil')
if ($sewa['status_rental'] === 'diambil') {
    header("Location: ../../supir/konfirmasi-cod.php?id_detail=" . $id_detail);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Konfirmasi Supir | DITRAS</title>
    <meta http-equiv="refresh" content="4">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;1,400&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet" />
    <style>
      .serif { font-family: "Cormorant Garamond", serif; }
      body { font-family: "Montserrat", sans-serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-stone-900 min-h-screen flex flex-col justify-center items-center p-6">

    <div class="max-w-md w-full bg-white border border-stone-200 p-8 text-center shadow-sm rounded-sm">
        <div class="inline-block w-12 h-12 border-2 border-stone-200 border-t-emerald-600 rounded-full animate-spin mb-6"></div>
        
        <span class="text-[9px] uppercase tracking-[0.25em] text-emerald-600 font-bold block mb-2">Request Sent Successfully</span>
        <h2 class="text-3xl serif italic text-stone-800 mb-4">Menunggu Konfirmasi</h2>
        
        <p class="text-sm text-stone-500 leading-relaxed mb-6">
            Sistem sedang meneruskan data pengajuan rental ke manifest kerja Mitra Supir: <br>
            <strong class="text-stone-800"><?= htmlspecialchars($sewa['nama_supir']) ?></strong>.
        </p>

        <div class="bg-stone-50 p-4 border border-stone-100 text-left rounded-sm space-y-2 mb-6">
            <div class="flex justify-between text-xs">
                <span class="text-stone-400">Mulai Perjalanan</span>
                <span class="font-semibold text-stone-700"><?= htmlspecialchars($sewa['tanggal_mulai']) ?></span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-stone-400">Selesai Perjalanan</span>
                <span class="font-semibold text-stone-700"><?= htmlspecialchars($sewa['tanggal_selesai']) ?></span>
            </div>
            <div class="flex justify-between text-xs border-t border-stone-200 pt-2">
                <span class="text-stone-400 font-bold">Estimasi Tagihan</span>
                <span class="font-bold text-emerald-600 font-mono">Rp <?= number_format($total_harga, 0, ',', '.') ?></span>
            </div>
        </div>

        <p class="text-[10px] text-stone-400 italic animate-pulse">
            Halaman ini akan otomatis beralih setelah disetujui supir via manifest...
        </p>
    </div>

</body>
</html>