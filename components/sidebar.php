<?php
// Pastikan session_start() sudah dipanggil di file halaman utama (seperti dashboard.php)
$role = $_SESSION['role'] ?? 'pembeli';
?>
<aside>
    <?php if ($role === 'admin'): ?>
        <a href="dashboard.php">Dashboard Admin</a>
        <?php elseif ($role === 'pembeli'): ?>
        <a href="dashboard.php">Beranda</a>
        <a href="pesan.php">Pesan Tiket</a>
        <?php endif; ?>
</aside>

<aside class="w-64 bg-stone-900 text-white min-h-screen flex flex-col fixed left-0 top-0 bottom-0 z-50 shadow-2xl">
    <div class="p-6 border-b border-stone-800 flex items-center gap-3">
        <svg class="w-8 h-8 text-emerald-500" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 80C30 80 45 60 65 40C75 30 88 35 95 45" stroke="#10b981" stroke-width="8" stroke-linecap="round"/>
        </svg>
        <div class="flex flex-col">
            <span class="text-lg font-black tracking-wider uppercase leading-none">DI<span class="text-emerald-500">TRAS</span></span>
            <span class="text-[7px] font-medium tracking-[0.15em] text-gray-400 uppercase mt-0.5">Premium Travel</span>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1 custom-scrollbar">
        <p class="text-[9px] uppercase tracking-widest text-stone-500 mb-4 px-2">Main Navigation</p>

        <?php if ($role === 'admin') : ?>
            <a href="/DITRAS-SYSTEM/pages/admin/dashboard.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Dashboard Admin</a>
            <a href="/DITRAS-SYSTEM/pages/admin/pesanan.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Pesanan & Invoice</a>
            <a href="/DITRAS-SYSTEM/pages/admin/lacak-gps.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Lacak GPS Real-Time</a>
            <a href="/DITRAS-SYSTEM/pages/admin/kelola-aspirasi.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Kelola Aspirasi Rute</a>
            <p class="text-[9px] uppercase tracking-widest text-stone-500 mt-6 mb-2 px-2">Data Master</p>
            <a href="/DITRAS-SYSTEM/pages/admin/master-mobil.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Master Mobil</a>
            <a href="/DITRAS-SYSTEM/pages/admin/master-supir.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Master Supir</a>
            <a href="/DITRAS-SYSTEM/pages/admin/master-rute.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Master Rute & Tarif</a>
        
        <?php elseif ($role === 'supir') : ?>
            <a href="/DITRAS-SYSTEM/pages/supir/dashboard.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Dashboard Perjalanan</a>
            <a href="/DITRAS-SYSTEM/pages/supir/manifest.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Manifest Penumpang</a>
            <a href="/DITRAS-SYSTEM/pages/supir/konfirmasi.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Konfirmasi COD</a>

        <?php else : ?>
            <a href="/DITRAS-SYSTEM/pages/pembeli/dashboard.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Beranda</a>
            <p class="text-[9px] uppercase tracking-widest text-stone-500 mt-6 mb-2 px-2">Layanan DITRAS</p>
            <a href="/DITRAS-SYSTEM/pages/pembeli/travel/search.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Tiket Travel Reguler</a>
            <a href="/DITRAS-SYSTEM/pages/pembeli/rental-supir/form-sewa.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Sewa Mobil + Supir</a>
            <a href="/DITRAS-SYSTEM/pages/pembeli/lepas-kunci/form-booking.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Rental Lepas Kunci</a>
            <p class="text-[9px] uppercase tracking-widest text-stone-500 mt-6 mb-2 px-2">Fitur Tambahan</p>
            <a href="/DITRAS-SYSTEM/pages/pembeli/lepas-kunci/riwayat.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Riwayat Transaksi</a>
            <a href="/DITRAS-SYSTEM/pages/pembeli/aspirasi/form.php" class="block px-4 py-3 rounded text-xs font-semibold text-stone-300 hover:bg-emerald-600 hover:text-white transition">Usulan Rute Baru</a>
        <?php endif; ?>
    </nav>

    <div class="p-4 border-t border-stone-800">
        <a href="/DITRAS-SYSTEM/pages/auth/logout.php" class="flex items-center justify-center w-full px-4 py-3 bg-red-900/30 text-red-400 hover:bg-red-600 hover:text-white rounded text-[10px] font-bold uppercase tracking-widest transition duration-300">
            Sign Out
        </a>
    </div>
</aside>