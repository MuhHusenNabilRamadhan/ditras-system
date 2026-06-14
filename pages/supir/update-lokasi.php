<?php
session_start();

// Ambil nama supir dari session untuk menyapa di sidebar
$nama_supir = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Supir';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DITRAS - Update Lokasi Perjalanan</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
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
                <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-[#222] hover:text-white transition-all text-gray-400">
                    <i class="fa-solid fa-table-columns text-sm"></i> Dashboard
                </a>
                <a href="manifest.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-[#222] hover:text-white transition-all text-gray-400">
                    <i class="fa-solid fa-clipboard-list text-sm"></i> Manifest Penumpang
                </a>
                <a href="konfirmasi.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-[#222] hover:text-white transition-all text-gray-400">
                    <i class="fa-solid fa-wallet text-sm"></i> Konfirmasi COD
                </a>
            </nav>
        </div>
        <div class="border-t border-neutral-800 pt-4">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#00a86b] flex items-center justify-center text-white font-bold text-sm">
                        <?= strtoupper(substr($nama_supir, 0, 1)); ?>
                    </div>
                    <div class="text-xs">
                        <p class="text-white font-medium truncate w-32"><?= $nama_supir; ?></p>
                        <p class="text-gray-500 text-[10px]">Driver Armada</p>
                    </div>
                </div>
                <a href="../auth/logout.php" class="text-gray-500 hover:text-red-400 p-1 transition-colors">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Update Lokasi Armada</h1>
            <p class="text-sm text-gray-500 mt-1">Laporkan rute atau lokasi terkini koordinat armada Anda ke sistem admin.</p>
        </header>

        <div class="max-w-2xl bg-white rounded-xl border border-gray-100 p-8">
            <form action="kirim-gps.php" method="POST" class="space-y-6">
                
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Status Perjalanan</label>
                    <select name="status_perjalanan" class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-gray-50 focus:bg-white focus:border-[#00a86b] focus:outline-none transition-all">
                        <option value="Proses Penjemputan">🚗 Proses Penjemputan Penumpang</option>
                        <option value="Masuk Tol">🛣️ Masuk Jalur Tol</option>
                        <option value="Rest Area">☕ Istirahat / Rest Area</option>
                        <option value="Macet Total">⚠️ Hambatan / Macet Total</option>
                        <option value="Sampai Tujuan">🏁 Sudah Sampai di Lokasi Tujuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Detail Lokasi / Keterangan</label>
                    <input type="text" name="keterangan_lokasi" placeholder="Contoh: Rest Area KM 429 atau Macet di Secang" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm text-gray-800 bg-gray-50 focus:bg-white focus:border-[#00a86b] focus:outline-none transition-all">
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-6 py-3 bg-black hover:bg-neutral-800 text-white text-sm font-bold rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-paper-plane text-xs text-emerald-400"></i> Kirim Laporan Lokasi
                    </button>
                    <a href="dashboard.php" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-lg transition-colors">Batal</a>
                </div>

            </form>
        </div>
    </main>

</body>
</html>