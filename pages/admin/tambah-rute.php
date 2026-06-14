<?php
session_start();
require_once '../../config/database.php';

$error_msg = '';

// Proses Simpan Data Rute Trayek Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_rute = trim($_POST['nama_rute'] ?? '');
    $estimasi_angka = trim($_POST['estimasi_waktu'] ?? '');
    $harga_dasar = trim($_POST['harga_dasar'] ?? '');

    // Gabungkan angka yang diinput admin dengan string " Jam"
    $estimasi_waktu = $estimasi_angka . ' Jam';

    if (!empty($nama_rute) && !empty($estimasi_angka) && !empty($harga_dasar)) {
        try {
            // Menggunakan query aman prepared statements PDO
            $stmt = $pdo->prepare("INSERT INTO rute (nama_rute, estimasi_waktu, harga_dasar) VALUES (:nama_rute, :estimasi_waktu, :harga_dasar)");
            $stmt->execute([
                ':nama_rute' => $nama_rute,
                ':estimasi_waktu' => $estimasi_waktu,
                ':harga_dasar' => $harga_dasar
            ]);

            // Sukses, langsung kembalikan ke halaman master rute
            header("Location: master-rute.php");
            exit();
        } catch (PDOException $e) {
            $error_msg = "Gagal menyimpan rute baru: " . $e->getMessage();
        }
    } else {
        $error_msg = "Mohon isi semua kelengkapan data rute terlebih dahulu!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem DITRAS - Tambah Trayek Rute</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-[#fcfbfa] text-stone-800 min-h-screen flex">

    <aside class="w-64 bg-[#1c1412] text-stone-400 flex flex-col justify-between min-h-screen sticky top-0">
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
                <a href="aspirasi.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-stone-900 transition">Kelola Aspirasi Rute</a>
                
                <p class="px-3 text-[10px] font-bold tracking-widest text-stone-600 uppercase mt-6 mb-2">Data Master</p>
                <a href="master-mobil.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-stone-900 transition">Master Mobil</a>
                <a href="master-supir.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-stone-900 transition">Master Supir</a>
                <a href="master-rute.php" class="flex items-center px-3 py-2.5 text-sm rounded-lg bg-stone-900 text-white font-medium transition">Master Rute & Tarif</a>
            </nav>
        </div>
        <div class="p-4">
            <a href="logout.php" class="flex items-center justify-center w-full px-3 py-2.5 text-sm font-semibold text-red-400 bg-red-950/20 hover:bg-red-950/40 border border-red-900/30 rounded-lg transition">SIGN OUT</a>
        </div>
    </aside>

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

        <main class="p-8 max-w-2xl w-full mx-auto flex-1 flex flex-col justify-center">
            <div class="mb-6">
                <p class="text-[11px] font-bold tracking-widest text-emerald-600 uppercase">Data Master › Rute</p>
                <h2 class="serif-title text-3xl italic text-stone-800 mt-1">Tambah Trayek Baru</h2>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="mb-4 p-4 text-xs font-semibold tracking-wide text-red-700 bg-red-50 border border-red-200 rounded-lg">
                    <?= htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl border border-stone-200 shadow-sm p-8">
                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label for="nama_rute" class="block text-[10px] font-bold tracking-widest text-stone-400 uppercase mb-2">Rute Perjalanan</label>
                        <input type="text" id="nama_rute" name="nama_rute" required
                               placeholder="Contoh: Wonosobo (Dieng) — Yogyakarta (YIA)" 
                               class="w-full bg-stone-50 border border-stone-200 rounded-lg px-4 py-3.5 text-sm text-stone-800 font-medium placeholder-stone-400 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>

                    <div>
                        <label for="estimasi_waktu" class="block text-[10px] font-bold tracking-widest text-stone-400 uppercase mb-2">Estimasi Waktu Tempuh /Jam</label>
                        <div class="relative">
                            <input type="number" id="estimasi_waktu" name="estimasi_waktu" required min="0" step="0.1"
                                   placeholder="3.5" 
                                   class="w-full bg-stone-50 border border-stone-200 rounded-lg pl-4 pr-14 py-3.5 text-sm font-semibold text-emerald-600 placeholder-stone-400 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                            <span class="absolute right-4 top-3.5 text-sm font-bold text-stone-400">Jam</span>
                        </div>
                        <p class="text-[10px] text-stone-400 mt-1.5">*Gunakan titik untuk desimal, contoh: 3.5</p>
                    </div>

                    <div>
                        <label for="harga_dasar" class="block text-[10px] font-bold tracking-widest text-stone-400 uppercase mb-2">Tarif Dasar (Dalam Rupiah)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-sm font-bold text-stone-400">Rp</span>
                            <input type="number" id="harga_dasar" name="harga_dasar" required min="0"
                                   placeholder="150000" 
                                   class="w-full bg-stone-50 border border-stone-200 rounded-lg pl-11 pr-4 py-3.5 text-sm font-semibold text-emerald-600 placeholder-stone-400 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-stone-100">
                        <a href="master-rute.php" class="px-5 py-3 rounded text-xs font-bold tracking-widest uppercase text-stone-400 hover:text-stone-700 transition">Batal</a>
                        <button type="submit" class="bg-black hover:bg-stone-900 text-white text-xs font-bold tracking-widest uppercase px-6 py-3 rounded shadow transition">Simpan Rute</button>
                    </div>
                </form>
            </div>
        </main>

        <footer class="px-8 py-4 bg-white border-t border-stone-100 text-center md:text-left md:flex md:justify-between text-[10px] font-semibold text-stone-400 uppercase tracking-wider">
            <span>© 2026 Dieng Trans Sejahtera (DITRAS). Hak Cipta Dilindungi.</span>
            <span class="mt-1 md:mt-0">Project Kelompok Web Development</span>
        </footer>
    </div>

</body>
</html>