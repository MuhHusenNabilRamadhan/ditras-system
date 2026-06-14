<?php
// pages/pembeli/dashboard.php
session_start();

// Karena file ini berada langsung di dalam folder pembeli/, 
// kita hanya perlu naik 2 tingkat untuk kembali ke root DITRAS-SYSTEM/
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layanan | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style> 
        h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; } 
        body { font-family: "Montserrat", sans-serif; } 
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">
    
    <?php 
    // Mengarahkan komponen include naik 2 tingkat ke root folder
    include '../../components/sidebar.php'; 
    include '../../components/header.php'; 
    ?>
    
    <main class="ml-64 p-8 flex-1">
        <div class="mb-10">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Travel Service Hub</span>
            <h2 class="text-4xl italic text-gray-800">Layanan Travel</h2>
            <p class="text-gray-400 mt-2 text-sm">Silakan cari jadwal operasional armada travel kami untuk melakukan pemesanan.</p>
        </div>

        <div class="max-w-2xl">
            <a href="travel/search.php" class="block bg-white p-8 border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-500/30 transition-all duration-300 group">
                <h3 class="text-2xl font-serif italic mb-2 group-hover:text-emerald-700 transition duration-300">Cari Jadwal Perjalanan</h3>
                <p class="text-xs text-gray-400 leading-relaxed">Cek daftar rute aktif, jam keberangkatan real-time, ketersediaan sisa kursi armada, dan lakukan reservasi tiket langsung dari jadwal yang Anda pilih.</p>
            </a>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>
</body>
</html>