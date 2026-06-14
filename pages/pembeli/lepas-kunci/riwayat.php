<?php
// pages/pembeli/lepas-kunci/riwayat.php
session_start();
require_once '../../../config/database.php';

// Proteksi Halaman Pembeli
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$id_pembeli = $_SESSION['id_user'];

try {
    // =========================================================================
    // QUERY DINAMIS: MENGAMBIL SEMUA LAYANAN (TRAVEL, LEPAS KUNCI, RENTAL SUPIR)
    // =========================================================================
    // Query ini menggabungkan data transaksi milik user beserta detail unit mobil jika ada
    $query_riwayat = "
        SELECT 
            t.id AS id_transaksi,
            t.jenis_layanan,
            t.total_harga,
            t.status_pembayaran,
            t.tanggal_transaksi,
            -- Subquery / Conditional untuk mengambil nama armada atau rute sebagai deskripsi
            CASE 
                WHEN t.jenis_layanan = 'travel' THEN 'Tiket Travel Reguler Terjadwal'
                ELSE (
                    SELECT m.merk FROM detail_rental dr 
                    JOIN mobil m ON dr.mobil_id = m.id 
                    WHERE dr.transaksi_id = t.id LIMIT 1
                )
            END AS info_layanan
        FROM transaksi t
        WHERE t.pembeli_id = :pembeli_id
        ORDER BY t.id DESC
    ";
    
    $stmt = $pdo->prepare($query_riwayat);
    $stmt->execute(['pembeli_id' => $id_pembeli]);
    $daftar_riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Gagal memuat riwayat transaksi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi Pelanggan | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style> 
        h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; } 
        body { font-family: "Montserrat", sans-serif; } 
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">

    <?php include '../../../components/sidebar.php'; ?>
    <?php include '../../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1">
        <div class="mb-8">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Customer History</span>
            <h2 class="text-3xl italic text-gray-800">Riwayat Seluruh Transaksi</h2>
            <p class="text-gray-400 text-xs mt-1">Daftar semua manifest pemesanan travel reguler dan carter armada unit DITRAS Anda.</p>
        </div>

        <div class="bg-white border border-gray-100 shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-[10px] uppercase tracking-widest text-gray-500">
                        <th class="py-4 px-6 font-semibold">ID Booking</th>
                        <th class="py-4 px-6 font-semibold">Jenis Layanan</th>
                        <th class="py-4 px-6 font-semibold">Detail / Unit</th>
                        <th class="py-4 px-6 font-semibold">Total Tagihan</th>
                        <th class="py-4 px-6 font-semibold text-center">Status</th>
                        <th class="py-4 px-6 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($daftar_riwayat)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400 italic bg-white">
                                Anda belum memiliki riwayat transaksi apa pun saat ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_riwayat as $row): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="py-4 px-6 font-bold text-gray-800">#TRX-<?= $row['id_transaksi'] ?></td>
                                
                                <td class="py-4 px-6">
                                    <?php if ($row['jenis_layanan'] === 'travel'): ?>
                                        <span class="bg-blue-50 text-blue-700 py-0.5 px-2 rounded font-medium text-[10px] uppercase tracking-wider">Travel Reguler</span>
                                    <?php elseif ($row['jenis_layanan'] === 'lepas_kunci'): ?>
                                        <span class="bg-purple-50 text-purple-700 py-0.5 px-2 rounded font-medium text-[10px] uppercase tracking-wider">Lepas Kunci</span>
                                    <?php else: ?>
                                        <span class="bg-amber-50 text-amber-700 py-0.5 px-2 rounded font-medium text-[10px] uppercase tracking-wider">Rental + Supir</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="py-4 px-6 text-gray-600 font-medium">
                                    <?= htmlspecialchars($row['info_layanan'] ?? 'Unit DITRAS') ?>
                                </td>
                                
                                <td class="py-4 px-6 font-bold text-slate-700">
                                    Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                </td>
                                
                                <td class="py-4 px-6 text-center">
                                    <?php if ($row['status_pembayaran'] === 'selesai' || $row['status_pembayaran'] === 'lunas'): ?>
                                        <span class="bg-emerald-100 text-emerald-700 py-1 px-3 rounded-full text-[9px] uppercase tracking-wider font-bold">Selesai</span>
                                    <?php elseif ($row['status_pembayaran'] === 'pending' || $row['status_pembayaran'] === 'dikonfirmasi'): ?>
                                        <span class="bg-amber-100 text-amber-700 py-1 px-3 rounded-full text-[9px] uppercase tracking-wider font-bold">Pending</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-700 py-1 px-3 rounded-full text-[9px] uppercase tracking-wider font-bold">Denda / Batal</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="py-4 px-6 text-right">
                                    <a href="../../../invoice/utama.php?id=<?= $row['id_transaksi'] ?>" class="text-emerald-600 font-bold text-[10px] uppercase underline hover:text-emerald-800 transition">
                                        Invoice
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../../../components/footer.php'; ?>
</body>
</html>