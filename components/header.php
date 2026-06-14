<?php
// Mengambil nama user dari session jika ada, kalau tidak ada default ke 'User'
$nama_user = $_SESSION['nama_user'] ?? 'User';
$display_role = strtoupper($_SESSION['role'] ?? 'PEMBELI');
?>
<header class="ml-64 bg-white border-b border-gray-100 h-20 px-8 flex items-center justify-between sticky top-0 z-40 shadow-sm">
    <div class="flex items-center gap-2">
        <h1 class="text-2xl font-semibold tracking-wide text-gray-800 serif">Sistem DITRAS</h1>
    </div>
    
    <div class="flex items-center gap-4">
        <div class="text-right">
            <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($nama_user) ?></p>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600"><?= $display_role ?></p>
        </div>
        <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 font-bold text-sm shadow-inner">
            <?= strtoupper(substr($nama_user, 0, 1)) ?>
        </div>
    </div>
</header>