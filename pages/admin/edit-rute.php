<?php
// pages/admin/edit-rute.php
session_start();
require_once '../../config/database.php';

// Proteksi halaman admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$error_msg = '';
$id = $_GET['id'] ?? ''; 

if (empty($id)) {
    header("Location: master-rute.php");
    exit;
}

// 1. Ambil data rute lama menggunakan PDO
try {
    $stmt = $pdo->prepare("SELECT * FROM rute WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        header("Location: master-rute.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}

// 2. Proses Update Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nama_rute = trim($_POST['nama_rute'] ?? '');
    $estimasi_angka = trim($_POST['estimasi_waktu'] ?? '');
    $harga_dasar = trim($_POST['harga_dasar'] ?? '');

    $estimasi_waktu = $estimasi_angka . ' Jam';

    if (!empty($nama_rute) && !empty($estimasi_angka) && !empty($harga_dasar)) {
        try {
            $stmt_update = $pdo->prepare("UPDATE rute SET nama_rute = :nama_rute, estimasi_waktu = :estimasi_waktu, harga_dasar = :harga_dasar WHERE id = :id");
            $stmt_update->execute([
                ':nama_rute' => $nama_rute,
                ':estimasi_waktu' => $estimasi_waktu,
                ':harga_dasar' => $harga_dasar,
                ':id' => $id
            ]);

            header("Location: master-rute.php");
            exit;
        } catch (PDOException $e) {
            $error_msg = "Gagal memperbarui rute: " . $e->getMessage();
        }
    } else {
        $error_msg = "Mohon lengkapi semua data form rute!";
    }
}

$estimasi_angka_lama = 0;
if (!empty($data['estimasi_waktu'])) {
    $estimasi_angka_lama = (float) filter_var($data['estimasi_waktu'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem DITRAS - Edit Trayek Rute</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .serif-title { font-family: 'Playfair Display', serif; }
    </style>
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

        <main class="p-8 max-w-2xl w-full mx-auto flex-1 flex flex-col justify-center">
            <div class="mb-6">
                <p class="text-[11px] font-bold tracking-widest text-emerald-600 uppercase">Data Master › Rute</p>
                <h2 class="serif-title text-3xl italic text-stone-800 mt-1">Ubah Data Trayek</h2>
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
                               value="<?= htmlspecialchars($data['nama_rute']); ?>"
                               class="w-full bg-stone-50 border border-stone-200 rounded-lg px-4 py-3.5 text-sm text-stone-800 font-medium placeholder-stone-400 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>

                    <div>
                        <label for="estimasi_waktu" class="block text-[10px] font-bold tracking-widest text-stone-400 uppercase mb-2">Estimasi Waktu Tempuh /Jam</label>
                        <div class="relative">
                            <input type="number" id="estimasi_waktu" name="estimasi_waktu" required min="0" step="0.1"
                                   value="<?= $estimasi_angka_lama; ?>"
                                   class="w-full bg-stone-50 border border-stone-200 rounded-lg pl-4 pr-14 py-3.5 text-sm font-semibold text-emerald-600 placeholder-stone-400 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                            <span class="absolute right-4 top-3.5 text-sm font-bold text-stone-400">Jam</span>
                        </div>
                    </div>

                    <div>
                        <label for="harga_dasar" class="block text-[10px] font-bold tracking-widest text-stone-400 uppercase mb-2">Tarif Dasar (Dalam Rupiah)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-sm font-bold text-stone-400">Rp</span>
                            <input type="number" id="harga_dasar" name="harga_dasar" required min="0"
                                   value="<?= htmlspecialchars($data['harga_dasar']); ?>"
                                   class="w-full bg-stone-50 border border-stone-200 rounded-lg pl-11 pr-4 py-3.5 text-sm font-semibold text-emerald-600 placeholder-stone-400 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-stone-100">
                        <a href="master-rute.php" class="px-5 py-3 rounded text-xs font-bold tracking-widest uppercase text-stone-400 hover:text-stone-700 transition">Batal</a>
                        <button type="submit" name="update" class="bg-black hover:bg-stone-900 text-white text-xs font-bold tracking-widest uppercase px-6 py-3 rounded shadow transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>