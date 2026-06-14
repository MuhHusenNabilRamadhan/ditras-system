<?php
// pages/pembeli/travel/search.php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../../auth/login.php");
    exit;
}

try {
    // MEMPERBAIKI KUERI: Menambahkan JOIN ke tabel users untuk mengambil nama supir
    $query_jadwal = $pdo->query("
        SELECT 
            jk.id,
            jk.jam_berangkat,
            jk.tanggal_berangkat,
            jk.sisa_kursi,
            r.nama_rute,
            m.merk,
            m.plat_nomor,
            u.nama AS nama_supir
        FROM jadwal_keberangkatan jk
        JOIN rute r ON jk.rute_id = r.id
        JOIN mobil m ON jk.mobil_id = m.id
        JOIN users u ON jk.supir_id = u.id
        ORDER BY jk.tanggal_berangkat ASC, jk.jam_berangkat ASC
    ");
    $daftar_jadwal = $query_jadwal->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $daftar_jadwal = [];
    $error_db = "Gagal mengambil info perjalanan: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Jadwal Perjalanan | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; }
        body { font-family: "Montserrat", sans-serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 min-h-screen flex">

    <?php include '../../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <main class="p-8 max-w-7xl w-full mx-auto flex-1">
            <div class="mb-10">
                <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Schedules</span>
                <h2 class="text-4xl italic text-gray-800">Jadwal Keberangkatan</h2>
                <p class="text-gray-400 mt-2 text-sm">Pilih jadwal perjalanan Anda. Kami menampilkan supir yang bertugas demi kenyamanan Anda.</p>
            </div>

            <?php if (isset($error_db)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-xs font-mono"><?= $error_db; ?></div>
            <?php endif; ?>

            <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 bg-[#faf9f6] text-gray-400 font-semibold text-xs uppercase tracking-wider">
                                <th class="p-5">Armada & Supir</th>
                                <th class="p-5">Rute Lintasan & Aksi</th>
                                <th class="p-5">Tanggal & Waktu</th>
                                <th class="p-5">Status Tiket</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-50">
                            <?php if (!empty($daftar_jadwal)): ?>
                                <?php foreach ($daftar_jadwal as $jadwal): ?>
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="p-5">
                                            <div class="font-bold text-gray-700"><?= htmlspecialchars($jadwal['merk']); ?></div>
                                            <div class="text-[10px] text-gray-400 font-mono tracking-wide mt-0.5"><?= htmlspecialchars($jadwal['plat_nomor']); ?></div>
                                            <div class="mt-2 text-[11px] text-emerald-700 font-semibold flex items-center gap-1.5">
                                                <i class="fa-solid fa-user-tie"></i> Supir: <?= htmlspecialchars($jadwal['nama_supir']); ?>
                                            </div>
                                        </td>
                                        
                                        <td class="p-5">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-gray-600 font-medium"><?= htmlspecialchars($jadwal['nama_rute']); ?></span>
                                                
                                                <?php if ($jadwal['sisa_kursi'] > 0): ?>
                                                    <a href="pesan.php?id_jadwal=<?= $jadwal['id']; ?>" class="inline-block bg-black hover:bg-stone-900 text-white text-[10px] font-bold tracking-widest uppercase px-4 py-2 rounded transition shadow-sm whitespace-nowrap">
                                                        Pesan Tiket
                                                    </a>
                                                <?php else: ?>
                                                    <button disabled class="bg-gray-100 text-gray-400 text-[10px] font-bold tracking-widest uppercase px-4 py-2 rounded cursor-not-allowed whitespace-nowrap">
                                                        Full Booked
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        
                                        <td class="p-5">
                                            <div class="text-xs font-medium"><?= date('d M Y', strtotime($jadwal['tanggal_berangkat'])); ?></div>
                                            <div class="font-mono font-bold text-stone-700 text-xs mt-0.5"><?= date('H:i', strtotime($jadwal['jam_berangkat'])); ?> WIB</div>
                                        </td>
                                        
                                        <td class="p-5">
                                            <?php if ($jadwal['sisa_kursi'] <= 0): ?>
                                                <span class="text-[10px] font-bold bg-red-50 text-red-600 px-2.5 py-1 uppercase tracking-wide rounded-md">Penuh</span>
                                            <?php else: ?>
                                                <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2.5 py-1 uppercase tracking-wide rounded-md">
                                                    Tersedia <?= $jadwal['sisa_kursi']; ?> Kursi
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="p-10 text-center text-sm text-gray-400 italic">Belum ada jadwal keberangkatan armada aktif yang di-publish oleh admin untuk saat ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>