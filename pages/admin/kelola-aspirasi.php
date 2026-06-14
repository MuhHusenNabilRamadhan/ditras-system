<?php
// pages/admin/kelola-aspirasi.php
session_start();
require_once '../../config/database.php'; // Menggunakan $pdo

$error_msg = '';
$success_msg = '';

// Logika ketika admin menekan tombol "Tindak Lanjuti"
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'tindak') {
        try {
            // 1. Ambil data aspirasi dan email pembeli terlebih dahulu (JOIN ke tabel users)
            $stmt_info = $pdo->prepare("SELECT ar.rute_usulan, u.email, u.nama 
                                        FROM aspirasi_rute ar 
                                        LEFT JOIN users u ON ar.pembeli_id = u.id 
                                        WHERE ar.id = ?");
            $stmt_info->execute([$id]);
            $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

            if ($info) {
                // 2. Update status di database menjadi 'ditindaklanjuti'
                $stmt_update = $pdo->prepare("UPDATE aspirasi_rute SET status_aspirasi = 'ditindaklanjuti' WHERE id = ?");
                $stmt_update->execute([$id]);

                // 3. Logika Kirim Email Notifikasi ke Pengusul
                $email_tujuan = $info['email'] ?? 'pelanggan@ditras.com'; 
                $nama_pembeli = $info['nama'] ?? 'Pelanggan DITRAS';
                $rute = $info['rute_usulan'];

                $subjek = "Update Usulan Rute Baru - DITRAS SYSTEM";
                $pesan = "Halo $nama_pembeli,\n\nTerima kasih telah mengirimkan aspirasi rute baru: $rute.\n\nKami menginformasikan bahwa usulan Anda saat ini telah DITINDAKLANJUTI oleh Tim Admin Utama DITRAS. Tim kami sedang melakukan survei kelayakan armada.\n\nSalam,\nAdmin DITRAS";
                $headers = "From: admin@ditras-system.com";

                // Mengirim email (Jika di localhost, log-nya akan terekam di xampp/mailoutput)
                @mail($email_tujuan, $subjek, $pesan, $headers);

                $success_msg = "Aspirasi berhasil ditindaklanjuti! Notifikasi email telah dikirimkan ke pelanggan.";
            }
        } catch (PDOException $e) {
            $error_msg = "Gagal memproses tindakan: " . $e->getMessage();
        }
    }
}

// Ambil data aspirasi terbaru untuk ditampilkan di tabel
$data_aspirasi = [];
try {
    $query = "SELECT aspirasi_rute.*, users.nama AS nama_pembeli 
              FROM aspirasi_rute 
              LEFT JOIN users ON aspirasi_rute.pembeli_id = users.id 
              ORDER BY aspirasi_rute.id DESC";
    $stmt = $pdo->query($query);
    $data_aspirasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Gagal memuat data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem DITRAS - Kelola Aspirasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-[#fcfbfa] text-stone-800 min-h-screen flex">

    <aside class="w-64 bg-[#1c1412] text-stone-400 flex flex-col justify-between min-h-screen fixed top-0 left-0 z-20">
        <div>
            <div class="p-6 border-b border-stone-800 flex items-center gap-3">
                <div class="text-emerald-500 font-bold text-xl tracking-wider">DI<span class="text-white">TRAS</span></div>
                <div class="text-[10px] text-stone-500 tracking-widest uppercase font-semibold leading-none">Premium<br>Travel</div>
            </div>
            <nav class="p-4 space-y-1">
                <p class="px-3 text-[10px] font-bold tracking-widest text-stone-600 uppercase mb-2">Main Navigation</p>
                <a href="dashboard.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-stone-900 transition">Dashboard Admin</a>
                <a href="pesanan.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-stone-900 transition">Pesanan & Invoice</a>
                <a href="lacak-gps.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-stone-900 transition">Lacak GPS Real-Time</a>
                <a href="kelola-aspirasi.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg bg-stone-900 text-white font-medium transition">Kelola Aspirasi Rute</a>
                
                <p class="px-3 text-[10px] font-bold tracking-widest text-stone-600 uppercase mt-6 mb-2">Data Master</p>
                <a href="master-mobil.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-stone-900 transition">Master Mobil</a>
                <a href="master-supir.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-stone-900 transition">Master Supir</a>
                <a href="master-rute.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-stone-900 transition">Master Rute & Tarif</a>
            </nav>
        </div>
        <div class="p-4">
            <a href="logout.php" class="flex items-center justify-center w-full px-3 py-2.5 text-sm font-semibold text-red-400 bg-red-950/20 hover:bg-red-950/40 border border-red-900/30 rounded-lg transition">SIGN OUT</a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 pl-64">
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

        <main class="p-8 w-full mx-auto flex-1">
            <div class="mb-6">
                <p class="text-[11px] font-bold tracking-widest text-emerald-600 uppercase">Main Navigation</p>
                <h2 class="serif-title text-3xl italic text-stone-800 mt-1">Kelola Aspirasi Rute</h2>
            </div>

            <?php if (!empty($success_msg)): ?>
                <div class="mb-4 p-4 text-xs font-semibold tracking-wide text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg">
                    🎉 <?= htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="mb-4 p-4 text-xs font-semibold tracking-wide text-red-700 bg-red-50 border border-red-200 rounded-lg">
                    ⚠️ <?= htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-stone-900 text-white text-[10px] uppercase tracking-wider font-bold">
                            <th class="p-4 text-center w-12">No</th>
                            <th class="p-4 w-44">Nama Pengusul</th>
                            <th class="p-4 w-52">Rute Usulan</th>
                            <th class="p-4 w-32 text-center">Tgl Potensial</th>
                            <th class="p-4">Alasan & Urgensi Usulan</th>
                            <th class="p-4 text-center w-28">Status Admin</th>
                            <th class="p-4 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-xs text-stone-600">
                        <?php 
                        $no = 1;
                        if (!empty($data_aspirasi)) :
                            foreach ($data_aspirasi as $row) : 
                                // Corak warna badge status internal admin
                                $badge_color = ($row['status_aspirasi'] === 'ditindaklanjuti') 
                                    ? "bg-emerald-50 text-emerald-700 border-emerald-200" 
                                    : "bg-amber-50 text-amber-700 border-amber-200";
                        ?>
                            <tr class="hover:bg-stone-50/50 transition">
                                <td class="p-4 text-center font-bold text-stone-400"><?= $no++; ?></td>
                                <td class="p-4 font-bold text-stone-800">
                                    <?= htmlspecialchars($row['nama_pembeli'] ?? 'User ID #'.$row['pembeli_id']); ?>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 bg-stone-100 border border-stone-200 text-stone-800 font-semibold rounded">
                                        <?= htmlspecialchars($row['rute_usulan']); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center whitespace-nowrap text-stone-500">
                                    <?= date('d M Y', strtotime($row['tanggal_potensial'])); ?>
                                </td>
                                <td class="p-4 leading-relaxed text-stone-500">
                                    <?= nl2br(htmlspecialchars($row['alasan_usulan'])); ?>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider rounded-full border <?= $badge_color; ?>">
                                        <?= htmlspecialchars($row['status_aspirasi']); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <?php if ($row['status_aspirasi'] === 'pending') : ?>
                                        <a href="kelola-aspirasi.php?action=tindak&id=<?= $row['id']; ?>" class="inline-block bg-emerald-600 text-white px-3 py-1.5 rounded font-bold text-[9px] uppercase tracking-wider hover:bg-emerald-700 transition">
                                            Tindak Lanjuti
                                        </a>
                                    <?php else : ?>
                                        <span class="text-stone-400 font-medium italic text-[10px]">Email Terkirim</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        else : 
                        ?>
                            <tr>
                                <td colspan="7" class="p-12 text-center text-stone-400 font-medium italic bg-stone-50/30">
                                    Belum ada kiriman aspirasi rute baru dari pelanggan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <footer class="px-8 py-4 bg-white border-t border-stone-100 text-center md:text-left md:flex md:justify-between text-[10px] font-semibold text-stone-400 uppercase tracking-wider">
            <span>© 2026 Dieng Trans Sejahtera (DITRAS). Hak Cipta Dilindungi.</span>
            <span class="mt-1 md:mt-0">Project Kelompok Web Development</span>
        </footer>
    </div>

</body>
</html>