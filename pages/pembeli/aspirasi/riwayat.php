<?php
// pages/pembeli/aspirasi/riwayat.php
session_start();
require_once '../../../config/database.php';

// Proteksi Halaman: Pastikan pembeli sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$riwayat_aspirasi = [];

try {
    // Ambil data aspirasi khusus milik user yang sedang login
    $stmt = $pdo->prepare("SELECT * FROM aspirasi WHERE id_user = ? ORDER BY id DESC");
    $stmt->execute([$id_user]);
    $riwayat_aspirasi = $stmt->fetchAll();
} catch (PDOException $e) {
    // Abaikan error jika tabel belum siap, kita pakai data dummy di bawah untuk preview
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Usulan Rute | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style>
        h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; }
        body { font-family: "Montserrat", sans-serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">

    <?php include '../../../components/sidebar.php'; ?>
    <?php include '../../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1">
        
        <div class="flex justify-between items-end mb-8">
            <div>
                <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Crowdsourcing Panel</span>
                <h2 class="text-3xl italic text-gray-800">Riwayat Usulan Rute</h2>
                <p class="text-gray-400 text-xs mt-1">Pantau status rekomendasi jalur travel yang telah Anda ajukan ke manajemen.</p>
            </div>
            <div>
                <a href="form.php" class="bg-black text-white px-6 py-3 text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-600 transition inline-block">
                    + Usul Rute Baru
                </a>
            </div>
        </div>

        <div class="bg-white border border-gray-100 shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-[10px] uppercase tracking-widest text-gray-500">
                        <th class="py-4 px-6 font-semibold">Rute Yang Diusulkan</th>
                        <th class="py-4 px-6 font-semibold">Alasan & Urgensi</th>
                        <th class="py-4 px-6 font-semibold text-center">Tanggal Kirim</th>
                        <th class="py-4 px-6 font-semibold text-center">Status Review</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    
                    <?php if (count($riwayat_aspirasi) > 0) : ?>
                        <?php foreach ($riwayat_aspirasi as $row) : ?>
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                            <td class="py-4 px-6 font-bold text-emerald-600"><?= htmlspecialchars($row['rute_usulan']) ?></td>
                            <td class="py-4 px-6 text-gray-600 max-w-sm break-words"><?= htmlspecialchars($row['alasan']) ?></td>
                            <td class="py-4 px-6 text-center text-gray-500 text-xs"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                            <td class="py-4 px-6 text-center">
                                <span class="bg-amber-100 text-amber-700 py-1 px-3 rounded-full text-[9px] uppercase tracking-wider font-bold">Ditinjau Admin</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                            <td class="py-4 px-6 font-bold text-emerald-600">Wonosobo - Magelang via Kaliwiro</td>
                            <td class="py-4 px-6 text-gray-600 max-w-sm">Jalur utama sering macet parah saat weekend, butuh jalur alternatif travel eksekutif lewat selatan.</td>
                            <td class="py-4 px-6 text-center text-gray-500 text-xs">06 Jun 2026</td>
                            <td class="py-4 px-6 text-center">
                                <span class="bg-amber-100 text-amber-700 py-1 px-3 rounded-full text-[9px] uppercase tracking-wider font-bold">Ditinjau Admin</span>
                            </td>
                        </tr>
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                            <td class="py-4 px-6 font-bold text-emerald-600">Dieng - Semarang (Direct)</td>
                            <td class="py-4 px-6 text-gray-600 max-w-sm">Banyak turis dari Semarang yang mengeluh harus transit berkali-kali kalau mau ke Dieng.</td>
                            <td class="py-4 px-6 text-center text-gray-500 text-xs">28 Mei 2026</td>
                            <td class="py-4 px-6 text-center">
                                <span class="bg-blue-100 text-blue-700 py-1 px-3 rounded-full text-[9px] uppercase tracking-wider font-bold">Dipertimbangkan</span>
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
        
    </main>

    <?php include '../../../components/footer.php'; ?>

</body>
</html>