<?php
session_start();

// Ambil nama supir dari session
$nama_supir = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Supir';

// Tangkap data status dan keterangan dari form sebelumnya agar tidak hilang
$status_perjalanan = isset($_POST['status_perjalanan']) ? $_POST['status_perjalanan'] : '';
$keterangan_lokasi = isset($_POST['keterangan_lokasi']) ? $_POST['keterangan_lokasi'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DITRAS - Bagikan Koordinat GPS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
        }
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
                <a href="../auth/logout.php" class="text-gray-500 hover:text-red-400 p-1 transition-colors" title="Log Out">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="flex-1 p-10 flex flex-col items-center justify-center">
        
        <div class="max-w-md w-full bg-white rounded-2xl border border-gray-100 p-8 text-center shadow-xs">
            <div class="w-24 h-24 bg-emerald-50 text-[#00a86b] rounded-full flex items-center justify-center mx-auto mb-6 text-4xl animate-bounce">
                <i class="fa-solid fa-location-dot"></i>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-2">Membagikan Koordinat GPS</h2>
            <p class="text-sm text-gray-400 mb-6 leading-relaxed">Sistem akan mencatat lokasi presisi Anda saat tombol di bawah ini ditekan.</p>

            <div class="bg-gray-50 rounded-xl p-4 text-left text-xs text-gray-500 space-y-1 mb-6 border border-gray-100">
                <p><strong>Status:</strong> <?= htmlspecialchars($status_perjalanan); ?></p>
                <p><strong>Keterangan:</strong> <?= htmlspecialchars($keterangan_lokasi); ?></p>
            </div>

            <form action="dashboard.php?status=update_success" method="POST">
                <input type="hidden" name="latitude" id="latitude" value="">
                <input type="hidden" name="longitude" id="longitude" value="">
                <input type="hidden" name="status_perjalanan" value="<?= htmlspecialchars($status_perjalanan); ?>">
                <input type="hidden" name="keterangan_lokasi" value="<?= htmlspecialchars($keterangan_lokasi); ?>">

                <button type="submit" class="w-full py-3 bg-black hover:bg-neutral-800 text-white font-bold text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-satellite-dish text-emerald-400"></i> Bagikan Koordinat GPS
                </button>
            </form>

            <a href="update-lokasi.php" class="inline-block mt-4 text-xs font-semibold text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ubah status
            </a>
        </div>

    </main>

    <script>
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
            });
        }
    </script>

</body>
</html>