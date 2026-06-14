<?php
// pages/admin/master-rute.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

try {
    $stmt = $pdo->query("SELECT * FROM rute ORDER BY id DESC");
    $data_rute = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error data rute: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem DITRAS - Master Rute</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } .serif-title { font-family: 'Playfair Display', serif; } </style>
</head>
<body class="bg-[#fcfbfa] text-stone-800 min-h-screen flex">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-stone-200 h-20 flex items-center justify-between px-8 sticky top-0 z-10">
            <h1 class="serif-title text-2xl italic text-stone-800">Sistem DITRAS</h1>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-stone-800">Abil Admin Utama</p>
                    <p class="text-[10px] font-bold tracking-wider text-emerald-600 uppercase">Admin</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center font-bold text-emerald-700">A</div>
            </div>
        </header>

        <main class="p-8 max-w-7xl w-full mx-auto flex-1">
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold tracking-widest text-emerald-600 uppercase">Data Master</p>
                    <h2 class="serif-title text-3xl italic text-stone-800 mt-1">Rute &amp; Tarif</h2>
                </div>
                <a href="tambah-rute.php" class="bg-black hover:bg-stone-900 text-white text-xs font-bold tracking-widest uppercase px-5 py-3 rounded shadow transition leading-none">+ Tambah Trayek Rute</a>
            </div>

            <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 text-[10px] font-bold tracking-widest text-stone-400 uppercase">
                                <th class="px-6 py-4">Titik Keberangkatan → Tujuan</th>
                                <th class="px-6 py-4">Estimasi Waktu</th>
                                <th class="px-6 py-4">Tarif Dasar</th>
                                <th class="px-6 py-4 text-right">Aksi Manajemen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <?php if (empty($data_rute)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-stone-400">Belum ada rute trayek perjalanan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data_rute as $row): ?>
                                <tr class="hover:bg-stone-50/50 transition">
                                    <td class="px-6 py-5 font-bold text-stone-800 text-sm">
                                        <?= htmlspecialchars($row['nama_rute']); ?>
                                    </td>
                                    <td class="px-6 py-5 text-sm text-stone-500">
                                        <?= htmlspecialchars($row['estimasi_waktu'] ?? '3.5 Jam'); ?>
                                    </td>
                                    <td class="px-6 py-5 text-sm font-semibold text-emerald-600">
                                        Rp <?= number_format($row['harga_dasar'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-5 text-right text-[11px] font-bold tracking-wide space-x-2">
                                        <a href="jadwal-aktif.php?id_rute=<?= $row['id']; ?>" class="bg-emerald-600 text-white hover:bg-emerald-700 px-3 py-1.5 rounded transition shadow-sm">+ JADWALKAN</a>
                                        <a href="edit-rute.php?id=<?= $row['id']; ?>" class="text-stone-600 hover:text-stone-900 transition">EDIT</a>
                                        <a href="hapus-rute.php?id=<?= $row['id']; ?>" class="text-red-500 hover:text-red-700 transition" onclick="return confirm('Yakin hapus rute ini?');">HAPUS</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>