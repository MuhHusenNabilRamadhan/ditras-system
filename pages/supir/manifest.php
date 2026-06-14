<?php
// pages/supir/manifest.php (Tetap Utuh & Aman)
session_start();
require_once '../../config/database.php';

// Pastikan supir sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_supir = $_SESSION['id_user'];
$nama_supir = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : (isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Supir');

// Logika POST untuk menerima tugas rental mobil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_rental']) && $_POST['aksi_rental'] === 'terima') {
    $id_detail = intval($_POST['id_detail']);
    try {
        $stmt_update = $pdo->prepare("UPDATE detail_rental SET status_rental = 'diambil' WHERE id = ? AND supir_id = ?");
        $stmt_update->execute([$id_detail, $id_supir]);
        header("Location: manifest.php");
        exit;
    } catch (PDOException $e) {
        die("Gagal mengonfirmasi rental: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifest Penumpang | DITRAS Driver</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: "Plus Jakarta Sans", sans-serif; background-color: #f8f9fa; } </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 min-h-screen flex">
    
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
                    <i class="fa-solid fa-table-columns text-sm"></i> Dashboard Perjalanan
                </a>
                <a href="manifest.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#262626] text-white font-medium transition-all">
                    <i class="fa-solid fa-clipboard-list text-sm text-[#00a86b]"></i> Manifest Penumpang
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
                <a href="../auth/logout.php" class="text-gray-500 hover:text-red-400 p-1 transition-colors" title="Keluar Sistem">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <main class="p-10 max-w-7xl w-full mx-auto flex-1">
            
            <div class="mb-10 border-b border-gray-200 pb-6">
                <h2 class="text-3xl font-bold text-gray-800">Manifest Penumpang & Rental</h2>
                <p class="text-sm text-gray-500 mt-1">Sistem Manifes Perjalanan Driver: <span class="font-bold text-[#00a86b]"><?= htmlspecialchars($nama_supir); ?></span></p>
            </div>

            <div id="manifest-realtime-container">
                <div class="p-8 text-center text-sm text-gray-400 italic">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Sinkronisasi database...
                </div>
            </div>

        </main>
    </div>

    <script>
    function updateManifest() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'ambil-manifest-data.php', true);
        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('manifest-realtime-container').innerHTML = this.responseText;
            }
        };
        xhr.send();
    }

    updateManifest();
    setInterval(updateManifest, 3000);
    </script>
</body>
</html>