<?php
// pages/supir/konfirmasi-cod.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_supir = $_SESSION['id_user'];
$nama_supir = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Supir';

// Logika update status pembayaran jika tombol konfirmasi ditekan
if (isset($_GET['action']) && $_GET['action'] == 'bayar' && isset($_GET['id_res'])) {
    $id_res = $_GET['id_res'];
    $jenis_bayar = $_GET['type'] ?? 'travel';

    if ($jenis_bayar === 'rental') {
        try {
            $pdo->beginTransaction();
            
            // 1. Ambil data supir_id dan mobil_id terlebih dahulu dari detail_rental sebelum diubah
            $stmtGetInfo = $pdo->prepare("SELECT transaksi_id, supir_id, mobil_id FROM detail_rental WHERE id = ?");
            $stmtGetInfo->execute([$id_res]);
            $rentalData = $stmtGetInfo->fetch(PDO::FETCH_ASSOC);

            if ($rentalData) {
                $transaksi_id = $rentalData['transaksi_id'];
                $supir_id = $rentalData['supir_id'];
                $mobil_id = $rentalData['mobil_id'];

                // 2. Update status_rental di tabel detail_rental menjadi 'kembali'
                $stmtUpdateRental = $pdo->prepare("UPDATE detail_rental SET status_rental = 'kembali' WHERE id = ?");
                $stmtUpdateRental->execute([$id_res]);

                // 3. Update status pembayaran di tabel transaksi induk menjadi 'selesai'
                $stmtUpdateTx = $pdo->prepare("UPDATE transaksi SET status_pembayaran = 'selesai' WHERE id = ?");
                $stmtUpdateTx->execute([$transaksi_id]);

                // 4. SESUAI DATABASE: Kembalikan status supir ke Standby di tabel supir_detail
                if (!empty($supir_id)) {
                    $stmtUpdateSupir = $pdo->prepare("UPDATE supir_detail SET status = 'Standby' WHERE supir_id = ?");
                    $stmtUpdateSupir->execute([$supir_id]);
                }

                // 5. SESUAI DATABASE: Kembalikan status mobil ke tersedia di tabel mobil
                if (!empty($mobil_id)) {
                    $stmtUpdateMobil = $pdo->prepare("UPDATE mobil SET status_mobil = 'tersedia' WHERE id = ?");
                    $stmtUpdateMobil->execute([$mobil_id]);
                }
            }

            $pdo->commit();
            
            // REDIRECT LANGSUNG KE PESANAN.PHP ADMIN SETELAH SELESAI
            echo "<script>alert('Tugas rental berhasil diselesaikan! Mengalihkan ke halaman pesanan admin...'); window.location.href='../admin/pesanan.php';</script>";
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error Pelunasan Rental: " . $e->getMessage());
        }
    } else {
        // Logika bawaan kamu untuk Travel Reguler
        try {
            $pdo->beginTransaction();
            
            $stmtUpdate = $pdo->prepare("UPDATE reservasi SET status_pembayaran = 'Lunas' WHERE id = ?");
            $stmtUpdate->execute([$id_res]);
            
            // Mengubah status supir travel kembali ke Standby di tabel supir_detail jika perjalanan selesai
            $stmtUpdateSupirTravel = $pdo->prepare("UPDATE supir_detail SET status = 'Standby' WHERE supir_id = ?");
            $stmtUpdateSupirTravel->execute([$id_supir]);
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error Pelunasan Travel: " . $e->getMessage());
        }
    }
    
    header("Location: konfirmasi-cod.php");
    exit;
}

try {
    // Ambil jadwal aktif supir (Travel Reguler)
    $stmtJadwal = $pdo->prepare("
        SELECT jk.id AS id_jadwal, jk.tanggal_berangkat, jk.jam_berangkat, r.nama_rute, m.merk, m.plat_nomor
        FROM jadwal_keberangkatan jk
        INNER JOIN rute r ON jk.rute_id = r.id
        INNER JOIN mobil m ON jk.mobil_id = m.id
        WHERE jk.supir_id = ? AND jk.tanggal_berangkat >= CURDATE()
        ORDER BY jk.tanggal_berangkat ASC, jk.jam_berangkat ASC LIMIT 1
    ");
    $stmtJadwal->execute([$id_supir]);
    $jadwal = $stmtJadwal->fetch(PDO::FETCH_ASSOC);

    $penumpang = [];
    $total_uang_masuk = 0;
    $total_uang_pending = 0;
    $total_penumpang = 0;

    // 1. Ambil & Hitung Data Travel Reguler
    if ($jadwal) {
        $stmtPenumpang = $pdo->prepare("
            SELECT res.id AS id_reservasi, res.nama_penumpang, res.jumlah_tiket, res.total_bayar, res.status_pembayaran, res.titik_jemput
            FROM reservasi res
            WHERE res.jadwal_id = ?
            ORDER BY res.id DESC
        ");
        $stmtPenumpang->execute([$jadwal['id_jadwal']]);
        $penumpang = $stmtPenumpang->fetchAll(PDO::FETCH_ASSOC);

        foreach ($penumpang as $p) {
            $total_penumpang += $p['jumlah_tiket'];
            if ($p['status_pembayaran'] === 'Lunas') {
                $total_uang_masuk += $p['total_bayar'];
            } else {
                $total_uang_pending += $p['total_bayar'];
            }
        }
    }

    // 2. MODIFIKASI: Ambil Data Rental Supir (Baik yang 'diambil' maupun yang sudah 'kembali' di hari ini agar status lunasnya tetap keliatan di tabel)
    $stmtRental = $pdo->prepare("
        SELECT dr.id AS id_rental, u.nama AS nama_penyewa, t.total_harga, t.status_pembayaran, m.merk, m.plat_nomor, dr.status_rental
        FROM detail_rental dr
        JOIN transaksi t ON dr.transaksi_id = t.id
        JOIN users u ON t.pembeli_id = u.id
        JOIN mobil m ON dr.mobil_id = m.id
        WHERE dr.supir_id = ? AND (dr.status_rental = 'diambil' OR (dr.status_rental = 'kembali' AND DATE(t.tanggal_transaksi) = CURDATE()))
    ");
    $stmtRental->execute([$id_supir]);
    $listRental = $stmtRental->fetchAll(PDO::FETCH_ASSOC);

    foreach ($listRental as $r) {
        if ($r['status_pembayaran'] === 'selesai' || $r['status_pembayaran'] === 'Lunas') {
            $total_uang_masuk += $r['total_harga'];
        } else {
            $total_uang_pending += $r['total_harga'];
        }
    }

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi COD & Pendapatan | DITRAS Driver</title>
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
                <a href="manifest.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-[#222] hover:text-white transition-all text-gray-400">
                    <i class="fa-solid fa-clipboard-list text-sm"></i> Manifest Penumpang
                </a>
                <a href="konfirmasi-cod.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#262626] text-white font-medium transition-all">
                    <i class="fa-solid fa-wallet text-sm text-[#00a86b]"></i> Konfirmasi COD
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
                <a href="../auth/logout.php" class="text-gray-500 hover:text-red-400 p-1 transition-colors">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <main class="p-10 max-w-7xl w-full mx-auto flex-1">
            
            <div class="mb-10 border-b border-gray-200 pb-6">
                <h2 class="text-3xl font-bold text-gray-800">Keuangan & Konfirmasi COD</h2>
                <p class="text-sm text-gray-500 mt-1">Ringkasan total perolehan dana tunai dari manifest jalan Anda.</p>
            </div>

            <?php if (!$jadwal && empty($listRental)): ?>
                <div class="bg-amber-50 border border-amber-200 p-4 text-amber-700 rounded-xl text-sm font-medium">
                    Tidak ada perjalanan aktif maupun tugas rental saat ini.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-xs">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Penumpang Terbawa</p>
                        <p class="text-3xl font-extrabold text-gray-800 mt-2"><?= $total_penumpang ?> <span class="text-sm font-medium text-gray-500">Jiwa (Travel)</span></p>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-xs border-l-4 border-emerald-500">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Uang Didapat (Lunas)</p>
                        <p class="text-3xl font-extrabold text-emerald-600 mt-2">Rp <?= number_format($total_uang_masuk, 0, ',', '.') ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-xs border-l-4 border-amber-500">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Sisa Tagihan Belum COD</p>
                        <p class="text-3xl font-extrabold text-amber-600 mt-2">Rp <?= number_format($total_uang_pending, 0, ',', '.') ?></p>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 shadow-xs rounded-xl overflow-hidden">
                    <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800 text-base">Rincian Penagihan Tugas Aktif</h3>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase text-gray-500 font-bold">
                                <th class="p-4">Nama Penumpang / Tagihan</th>
                                <th class="p-4 text-center">Layanan & Keterangan</th>
                                <th class="p-4 text-right">Tagihan</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($penumpang as $p): ?>
                                <tr class="border-b border-gray-50 text-sm hover:bg-gray-50">
                                    <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($p['nama_penumpang']) ?></td>
                                    <td class="p-4 text-center text-xs text-blue-600 font-medium">[Travel] <?= $p['jumlah_tiket'] ?> Kursi</td>
                                    <td class="p-4 text-right font-bold">Rp <?= number_format($p['total_bayar'], 0, ',', '.') ?></td>
                                    <td class="p-4 text-center">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full <?= $p['status_pembayaran'] === 'Lunas' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                            <?= $p['status_pembayaran'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($p['status_pembayaran'] !== 'Lunas'): ?>
                                            <a href="konfirmasi-cod.php?action=bayar&id_res=<?= $p['id_reservasi'] ?>&type=travel" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1 px-3 rounded-lg transition-all">
                                                Konfirmasi Masuk Dana
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs"><i class="fa-solid fa-check-double text-emerald-500"></i> Sudah Masuk Lap.</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php foreach ($listRental as $r): ?>
                                <tr class="border-b border-gray-50 text-sm bg-purple-50/30 hover:bg-purple-50">
                                    <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($r['nama_penyewa']) ?></td>
                                    <td class="p-4 text-center text-xs text-purple-700 font-medium">[Rental] Charter Unit <?= htmlspecialchars($r['merk']) ?></td>
                                    <td class="p-4 text-right font-bold text-emerald-700">Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                                    
                                    <td class="p-4 text-center">
                                        <?php if ($r['status_pembayaran'] === 'selesai'): ?>
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">
                                                Lunas
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700">
                                                Dalam Tugas
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="p-4 text-center">
                                        <?php if ($r['status_pembayaran'] !== 'selesai'): ?>
                                            <a href="konfirmasi-cod.php?action=bayar&id_res=<?= $r['id_rental'] ?>&type=rental" onclick="return confirm('Selesaikan tugas charter rental unit dan konfirmasi penerimaan dana?')" class="text-xs bg-stone-800 hover:bg-stone-950 text-white font-bold py-1 px-3 rounded-lg transition-all">
                                                Selesaikan Tugas Rental
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs"><i class="fa-solid fa-check-double text-emerald-500"></i> Sudah Masuk Lap.</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>