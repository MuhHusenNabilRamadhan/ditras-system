<?php
// pages/admin/pesanan.php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// ==========================================
// 1. LOGIKA KEMBALI KE GARASI (SET_SELESAI)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'set_selesai' && isset($_GET['id_jadwal'])) {
    $id_jadwal = $_GET['id_jadwal'];
    try {
        $pdo->beginTransaction();
        
        // Ambil data mobil_id dan supir_id berdasarkan jadwal keberangkatan
        $stmtJadwal = $pdo->prepare("SELECT mobil_id, supir_id FROM jadwal_keberangkatan WHERE id = ?");
        $stmtJadwal->execute([$id_jadwal]);
        $jd = $stmtJadwal->fetch(PDO::FETCH_ASSOC);
        
        if ($jd) {
            // A. Update status mobil kembali 'tersedia' secara real-time
            if (!empty($jd['mobil_id'])) {
                $stmtMobil = $pdo->prepare("UPDATE mobil SET status_mobil = 'tersedia' WHERE id = ?");
                $stmtMobil->execute([$jd['mobil_id']]);
            }
            
            // B. PERBAIKAN: Mengarah ke tabel supir_detail, kolom status, menggunakan nilai 'Standby' sesuai ENUM database
            if (!empty($jd['supir_id'])) {
                $stmtSupir = $pdo->prepare("UPDATE supir_detail SET status = 'Standby' WHERE supir_id = ?");
                $stmtSupir->execute([$jd['supir_id']]);
            }
        }
        
        $pdo->commit();
        header("Location: pesanan.php?status=success");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal memproses kembali ke garasi: " . $e->getMessage());
    }
}

// ==========================================
// LOGIKA TAMBAHAN: KEMBALI KE GARASI KHUSUS RENTAL + SUPIR
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'set_selesai_rental' && isset($_GET['id_rental'])) {
    $id_rental = $_GET['id_rental'];
    try {
        $pdo->beginTransaction();
        
        // Ambil data supir_id dan mobil_id dari detail_rental
        $stmtRent = $pdo->prepare("SELECT supir_id, mobil_id FROM detail_rental WHERE id = ?");
        $stmtRent->execute([$id_rental]);
        $rentData = $stmtRent->fetch(PDO::FETCH_ASSOC);
        
        if ($rentData) {
            // Ubah status rental menjadi selesai total agar hilang dari list jalan / berubah jadi Standby
            $stmtUpdateRental = $pdo->prepare("UPDATE detail_rental SET status_rental = 'selesai_total' WHERE id = ?");
            $stmtUpdateRental->execute([$id_rental]);

            if (!empty($rentData['mobil_id'])) {
                $stmtMobil = $pdo->prepare("UPDATE mobil SET status_mobil = 'tersedia' WHERE id = ?");
                $stmtMobil->execute([$rentData['mobil_id']]);
            }
            if (!empty($rentData['supir_id'])) {
                $stmtSupir = $pdo->prepare("UPDATE supir_detail SET status = 'Standby' WHERE supir_id = ?");
                $stmtSupir->execute([$rentData['supir_id']]);
            }
        }
        
        $pdo->commit();
        header("Location: pesanan.php?status=success");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal memproses kembali ke garasi rental: " . $e->getMessage());
    }
}

// ==========================================
// 2. QUERY MENAMPILKAN DATA BERBASIS RUTE/LAYANAN (GROUP BY)
// ==========================================
$pesanan_data = [];
try {
    // Query mengelompokkan reservasi travel berdasarkan jadwal keberangkatan agar efisien
    $query = "
        SELECT 
            r.nama_rute AS rute_lintasan,
            'Travel' AS jenis_layanan,
            u.nama AS nama_supir,
            CONCAT(m.merk, ' [', m.plat_nomor, ']') AS nama_armada,
            SUM(res.total_bayar) AS uang_didapat,
            -- Jika ada satu saja penumpang yang 'Belum Bayar', maka status travel masih 'MENUNGGU COD'
            IF(COUNT(CASE WHEN res.status_pembayaran = 'Belum Bayar' THEN 1 END) > 0, 'MENUNGGU COD', 'LUNAS / SIAP GARASI') AS status_pembayaran,
            jk.id AS id_jadwal,
            m.status_mobil,
            MIN(res.id) AS id_res_invoice, -- Mengambil salah satu ID reservasi untuk keperluan link invoice
            NULL AS id_rental_sp -- Pengenal tambahan untuk membedakan dengan baris rental supir
        FROM reservasi res
        INNER JOIN jadwal_keberangkatan jk ON res.jadwal_id = jk.id
        INNER JOIN rute r ON jk.rute_id = r.id
        LEFT JOIN mobil m ON jk.mobil_id = m.id
        LEFT JOIN users u ON jk.supir_id = u.id
        GROUP BY jk.id

        UNION ALL

        SELECT 
            'Rental Lepas Kunci (Innova)' AS rute_lintasan,
            'Lepas Kunci' AS jenis_layanan,
            '-' AS nama_supir,
            'Toyota Innova Reborn (AA 1234 XY)' AS nama_armada,
            1000000 AS uang_didapat,
            'MENUNGGU COD' AS status_pembayaran,
            NULL AS id_jadwal,
            'jalan' AS status_mobil,
            1 AS id_res_invoice,
            NULL AS id_rental_sp

        UNION ALL

        -- TAMBAHAN QUERY: MENAMPILKAN RENTAL DENGAN SUPIR (CHARTER)
        SELECT 
            CONCAT('Charter Rental (', m.merk, ')') AS rute_lintasan,
            'Dengan Supir' AS jenis_layanan,
            u.nama AS nama_supir,
            CONCAT(m.merk, ' [', m.plat_nomor, ']') AS nama_armada,
            t.total_harga AS uang_didapat,
            -- Mengikuti status pembayaran dari tabel transaksi utama ('selesai' berarti lunas)
            IF(t.status_pembayaran = 'selesai', 'LUNAS / SIAP GARASI', 'MENUNGGU COD') AS status_pembayaran,
            NULL AS id_jadwal,
            -- Jika supir mengklik selesai di supir, status_rental berubah jadi 'kembali'
            IF(dr.status_rental = 'kembali', 'kembali', 'jalan') AS status_mobil,
            t.id AS id_res_invoice,
            dr.id AS id_rental_sp
        FROM detail_rental dr
        INNER JOIN transaksi t ON dr.transaksi_id = t.id
        INNER JOIN mobil m ON dr.mobil_id = m.id
        INNER JOIN users u ON dr.supir_id = u.id
        WHERE dr.supir_id IS NOT NULL AND dr.status_rental != 'selesai_total'
            
        ORDER BY rute_lintasan DESC
    ";
    $pesanan_data = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal memuat data laporan pesanan: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan | DITRAS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,600;1,400&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        h1, h2, .serif { font-family: "Cormorant Garamond", serif; } 
        body { font-family: "Plus Jakarta Sans", sans-serif; } 
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">

    <?php include '../../components/sidebar.php'; ?>
    <?php include '../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Transaction Center</span>
                <h2 class="text-4xl italic text-gray-800">Laporan Finansial & Garasi</h2>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> Status armada dan driver berhasil dikembalikan ke Garasi (Standby)!
            </div>
        <?php endif; ?>

        <div class="bg-white border border-gray-100 shadow-xs rounded-xl overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-[11px] uppercase tracking-wider text-gray-500 font-bold">
                        <th class="py-4 px-6">Rute Lintasan / Layanan</th>
                        <th class="py-4 px-6">Jenis</th>
                        <th class="py-4 px-6">Penanggung Jawab (Supir)</th>
                        <th class="py-4 px-6">Unit Armada</th>
                        <th class="py-4 px-6 text-right">Total Pendapatan</th>
                        <th class="py-4 px-6 text-center">Status COD</th>
                        <th class="py-4 px-6 text-center">Kontrol Garasi / Dokumen</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($pesanan_data)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400 font-medium italic">Belum ada riwayat transaksi perjalanan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pesanan_data as $row): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="py-4 px-6 font-bold text-gray-800"><?= htmlspecialchars($row['rute_lintasan']) ?></td>
                                <td class="py-4 px-6">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium <?= $row['jenis_layanan'] === 'Travel' ? 'bg-blue-50 text-blue-700' : ($row['jenis_layanan'] === 'Dengan Supir' ? 'bg-indigo-50 text-indigo-700' : 'bg-purple-50 text-purple-700') ?>">
                                        <?= $row['jenis_layanan'] ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-medium text-gray-700"><?= htmlspecialchars($row['nama_supir'] ?? '-') ?></td>
                                <td class="py-4 px-6 text-gray-500 text-xs font-semibold"><?= htmlspecialchars($row['nama_armada'] ?? '-') ?></td>
                                <td class="py-4 px-6 text-right font-bold text-gray-950">Rp <?= number_format($row['uang_didapat'], 0, ',', '.') ?></td>
                                <td class="py-4 px-6 text-center">
                                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full <?= ($row['status_pembayaran'] === 'LUNAS / SIAP GARASI') ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                        <?= $row['status_pembayaran'] ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center flex items-center justify-center gap-2">
                                    
                                    <?php if ($row['jenis_layanan'] === 'Dengan Supir'): ?>
                                        <?php if ($row['status_pembayaran'] === 'MENUNGGU COD' && $row['status_mobil'] !== 'kembali'): ?>
                                            <button type="button" onclick="alert('Tidak dapat mengembalikan ke garasi. Menunggu konfirmasi tugas selesai dari driver!')" class="text-[11px] bg-gray-300 text-gray-500 font-bold py-1 px-3 rounded shadow-xs cursor-not-allowed flex items-center gap-1">
                                                <i class="fa-solid fa-lock"></i> Menunggu Driver
                                            </button>
                                        <?php else: ?>
                                            <a href="pesanan.php?action=set_selesai_rental&id_rental=<?= $row['id_rental_sp'] ?>" class="text-[11px] bg-neutral-900 hover:bg-emerald-600 text-white font-bold py-1 px-3 rounded shadow-xs transition-all">
                                                <i class="fa-solid fa-warehouse mr-1"></i> Kembali Ke Garasi
                                            </a>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <?php if ($row['status_mobil'] === 'jalan' || !empty($row['id_jadwal'])): ?>
                                            <?php if ($row['status_pembayaran'] === 'MENUNGGU COD' && $row['jenis_layanan'] !== 'Lepas Kunci'): ?>
                                                <button type="button" onclick="alert('Tidak dapat mengembalikan ke garasi. Menunggu konfirmasi pembayaran COD dari supir!')" class="text-[11px] bg-gray-300 text-gray-500 font-bold py-1 px-3 rounded shadow-xs cursor-not-allowed flex items-center gap-1">
                                                    <i class="fa-solid fa-lock"></i> Menunggu Driver
                                                </button>
                                            <?php else: ?>
                                                <a href="pesanan.php?action=set_selesai&id_jadwal=<?= $row['id_jadwal'] ?>" class="text-[11px] bg-neutral-900 hover:bg-emerald-600 text-white font-bold py-1 px-3 rounded shadow-xs transition-all">
                                                    <i class="fa-solid fa-warehouse mr-1"></i> Kembali Ke Garasi
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-xs text-emerald-600 font-bold italic"><i class="fa-solid fa-circle-check text-emerald-500"></i> Standby</span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <a href="../invoice/utama.php?id=<?= $row['id_res_invoice'] ?>" class="text-[11px] border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium py-1 px-2 rounded transition flex items-center gap-1">
                                        <i class="fa-solid fa-file-invoice text-gray-400"></i> Invoice
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>
</body>
</html>