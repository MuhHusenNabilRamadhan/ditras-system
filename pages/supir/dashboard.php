<?php
// pages/supir/dashboard.php
session_start();
require_once '../../config/database.php';

// Memastikan hanya role supir yang bisa akses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'supir') {
    header("Location: ../auth/login.php");
    exit;
}

// Mengambil nama supir secara dinamis dari session
$nama_supir = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : (isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Supir');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supir | DITRAS Travel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 bg-[#1a1a1a] text-gray-300 flex flex-col justify-between p-5 shrink-0">
        <div>
            <div class="mb-10 px-2">
                <div class="flex items-center gap-2 text-white font-bold text-xl tracking-wider">
                    <span class="text-[#00a86b]"><i class="fa-solid fa-route"></i></span> DITRAS
                </div>
                <div class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold mt-0.5">Premium Travel</div>
            </div>

            <div class="text-[11px] text-gray-600 font-bold uppercase tracking-wider mb-3 px-2">Main Navigation</div>
            <nav class="space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#262626] text-white font-medium transition-all">
                    <i class="fa-solid fa-table-columns text-sm text-[#00a86b]"></i> Dashboard Perjalanan
                </a>
                <a href="manifest.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-[#222] hover:text-white transition-all text-gray-400">
                    <i class="fa-solid fa-clipboard-list text-sm"></i> Manifest Penumpang
                </a>
                <a href="konfirmasi-cod.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-[#222] hover:text-white transition-all text-gray-400">
                    <i class="fa-solid fa-wallet text-sm"></i> Konfirmasi COD
                </a>
            </nav>
        </div>

        <div class="border-t border-neutral-800 pt-4">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#00a86b] flex items-center justify-center text-white font-bold text-sm">
                        <?= strtoupper(substr(trim($nama_supir), 0, 1)); ?>
                    </div>
                    <div class="text-xs">
                        <p class="text-white font-medium truncate w-32"><?= htmlspecialchars($nama_supir); ?></p>
                        <p class="text-gray-500 text-[10px]">Driver DITRAS</p>
                    </div>
                </div>
                <a href="../auth/logout.php" class="text-gray-500 hover:text-red-400 p-1 transition-colors">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="flex-1 p-10 overflow-y-auto">
        <div class="mb-10 border-b border-gray-200 pb-6">
            <h1 class="text-3xl font-bold text-gray-800">Halo, <?= htmlspecialchars($nama_supir); ?>!</h1>
            <p class="text-sm text-gray-500 mt-1">Selamat datang kembali di <span class="font-bold text-[#00a86b]">Sistem DITRAS Travel</span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="manifest.php" class="group bg-white p-6 rounded-xl shadow-xs border border-gray-100 hover:border-[#00a86b] transition-all hover:-translate-y-1 block">
                <div class="p-3 bg-emerald-50 text-[#00a86b] rounded-lg w-12 mb-4 group-hover:bg-[#00a86b] group-hover:text-white transition-colors">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-[#00a86b]">Lihat Manifest</h3>
                <p class="text-xs text-gray-400 mt-1.5">Cek daftar penumpang dan rute penjemputan.</p>
            </a>

            <a href="update-lokasi.php" class="group bg-black p-6 rounded-xl shadow-xs hover:bg-neutral-900 transition-all hover:-translate-y-1 block">
                <div class="p-3 bg-neutral-800 text-white rounded-lg w-12 mb-4 group-hover:text-[#00a86b]">
                    <i class="fa-solid fa-location-crosshairs text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Update Lokasi</h3>
                <p class="text-xs text-neutral-400 mt-1.5">Update status perjalanan (Rest Area, Tol, dll).</p>
            </a>

            <a href="konfirmasi-cod.php" class="group bg-white p-6 rounded-xl shadow-xs border border-gray-100 hover:border-[#00a86b] transition-all hover:-translate-y-1 block">
                <div class="p-3 bg-emerald-50 text-[#00a86b] rounded-lg w-12 mb-4 group-hover:bg-[#00a86b] group-hover:text-white transition-colors">
                    <i class="fa-solid fa-hand-holding-dollar text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-[#00a86b]">Konfirmasi COD</h3>
                <p class="text-xs text-gray-400 mt-1.5">Validasi pembayaran tunai dari penumpang.</p>
            </a>
        </div>
    </main>
</body>
</html>