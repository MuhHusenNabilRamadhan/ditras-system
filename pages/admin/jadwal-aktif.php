<?php
// pages/admin/jadwal-aktif.php
session_start();
require_once '../../config/database.php';

// Cek autentikasi dan role admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Ambil parameter id_rute dari URL
$id_rute = $_GET['id_rute'] ?? null;

if (!$id_rute) {
    header("Location: master-rute.php");
    exit;
}

try {
    // 1. Ambil data rute berdasarkan id
    $stmt = $pdo->prepare("SELECT * FROM rute WHERE id = ?");
    $stmt->execute([$id_rute]);
    $rute = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rute) {
        $stmt = $pdo->prepare("SELECT * FROM tabel_rute WHERE id = ?");
        $stmt->execute([$id_rute]);
        $rute = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$rute) {
            die("Data rute tidak ditemukan di database!");
        }
    }

    // 2. Ambil data mobil yang status_mobil = 'tersedia'
    $mobils = $pdo->query("SELECT id, merk, plat_nomor FROM mobil WHERE status_mobil = 'tersedia'")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Ambil data supir REALTIME langsung dari tabel 'users' yang role-nya supir
    // Kita LEFT JOIN ke supir_detail biar kalau akun baru belum ada barisnya di supir_detail, tetap muncul.
    // Jika statusnya NULL (belum di-set), otomatis kita anggap 'Standby'
    $supirs = $pdo->query("
        SELECT 
            u.id AS supir_id, 
            u.nama AS nama_supir,
            IFNULL(sd.status, 'Standby') AS status
        FROM users u
        LEFT JOIN supir_detail sd ON u.id = sd.supir_id
        WHERE LOWER(u.role) = 'supir' 
          AND (LOWER(sd.status) = 'standby' OR sd.status IS NULL)
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobil_id = $_POST['mobil_id']; // Berisi ID Mobil
    $supir_id = $_POST['supir_id']; // Berisi ID Supir
    $tanggal_berangkat = $_POST['tanggal_berangkat'];
    $jam_berangkat = $_POST['jam_berangkat'];
    $sisa_kursi = 14; 

    try {
        $pdo->beginTransaction();

        // 1. Ambil info plat nomor & merk mobil untuk mengisi data penugasan supir
        $stmtMobil = $pdo->prepare("SELECT merk, plat_nomor FROM mobil WHERE id = ?");
        $stmtMobil->execute([$mobil_id]);
        $dataMobil = $stmtMobil->fetch(PDO::FETCH_ASSOC);
        $kendaraan_bawaan = $dataMobil ? $dataMobil['merk'] . " (" . $dataMobil['plat_nomor'] . ")" : "Mobil ID " . $mobil_id;

        // 2. Tentukan tujuan berdasarkan nama rute yang sedang aktif
        $tujuan_trip = $rute['nama_rute'] ?? 'Tujuan Belum Ditentukan';

        // 3. Insert ke tabel jadwal keberangkatan
        $insert = $pdo->prepare("INSERT INTO jadwal_keberangkatan (rute_id, supir_id, mobil_id, tanggal_berangkat, jam_berangkat, sisa_kursi) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$id_rute, $supir_id, $mobil_id, $tanggal_berangkat, $jam_berangkat, $sisa_kursi]);

        // 4. Update status mobil menjadi 'jalan'
        $update_mobil = $pdo->prepare("UPDATE mobil SET status_mobil = 'jalan' WHERE id = ?");
        $update_mobil->execute([$mobil_id]);

        // 5. Update status supir menjadi 'Travel' 
        // Jika supir baru belum ada di supir_detail, otomatis INSERT. Jika sudah ada, tinggal UPDATE.
        $update_supir = $pdo->prepare("
            INSERT INTO supir_detail (supir_id, status, kendaraan_bawaan, tujuan) 
            VALUES (?, 'Travel', ?, ?)
            ON DUPLICATE KEY UPDATE 
                status = 'Travel', 
                kendaraan_bawaan = VALUES(kendaraan_bawaan), 
                tujuan = VALUES(tujuan)
        ");
        $update_supir->execute([$supir_id, $kendaraan_bawaan, $tujuan_trip]);

        $pdo->commit();

        echo "<script>alert('Jadwal berhasil ditambahkan! Mobil otomatis [jalan] dan Supir otomatis [Travel] membawa armada baru.'); window.location.href='master-rute.php';</script>";
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Gagal mempublish jadwal: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem DITRAS - Set Jadwal Perjalanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-[#fcfbfa] text-stone-800 min-h-screen flex flex-col md:flex-row">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-stone-200 h-20 flex items-center justify-between px-8 sticky top-0 z-10 flex-shrink-0">
            <h1 class="serif-title text-2xl italic text-stone-800">Sistem DITRAS</h1>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-stone-800">Abil Admin Utama</p>
                    <p class="text-[10px] font-bold tracking-wider text-emerald-600 uppercase">Admin</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center font-bold text-emerald-700">A</div>
            </div>
        </header>

        <main class="p-8 max-w-2xl w-full mx-auto flex-1 my-4">
            <div class="mb-6">
                <p class="text-[11px] font-bold tracking-widest text-emerald-600 uppercase">Manajemen Operasional</p>
                <h2 class="serif-title text-3xl italic text-stone-800 mt-1">Set Jadwal Perjalanan</h2>
                <p class="text-xs text-stone-500 mt-1">Membuat trayek aktif pada rute: <strong class="text-stone-700"><?= htmlspecialchars($rute['nama_rute'] ?? 'Rute Tidak Diketahui'); ?></strong></p>
            </div>

            <?php if (isset($error_msg)): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 text-xs font-semibold p-4 rounded-lg mb-4"><?= $error_msg; ?></div>
            <?php endif; ?>

            <div class="bg-white rounded-xl border border-stone-200 shadow-sm p-8 mb-8">
                <form action="" method="POST" class="space-y-6">
                    
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Tanggal Keberangkatan</label>
                        <input type="date" name="tanggal_berangkat" class="w-full p-3.5 border border-stone-200 rounded-lg bg-stone-50 text-sm focus:outline-none focus:border-emerald-600 focus:bg-white transition" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Armada Mobil</label>
                        <select name="mobil_id" class="w-full p-3.5 border border-stone-200 rounded-lg bg-stone-50 text-sm focus:outline-none focus:border-emerald-600 focus:bg-white transition cursor-pointer" required>
                            <option value="" disabled selected>-- Pilih Armada Unit --</option>
                            <?php if (!empty($mobils)): ?>
                                <?php foreach ($mobils as $mobil): ?>
                                    <option value="<?= $mobil['id']; ?>">
                                        <?= htmlspecialchars($mobil['merk']); ?> [<?= htmlspecialchars($mobil['plat_nomor']); ?>]
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Tidak ada armada dengan status 'tersedia' saat ini</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Driver Supir</label>
                        <select name="supir_id" class="w-full p-3.5 border border-stone-200 rounded-lg bg-stone-50 text-sm focus:outline-none focus:border-emerald-600 focus:bg-white transition cursor-pointer" required>
                            <option value="" disabled selected>-- Pilih Nama Driver --</option>
                            <?php if (!empty($supirs)): ?>
                                <?php foreach ($supirs as $supir): ?>
                                    <option value="<?= $supir['supir_id']; ?>">
                                        <?= htmlspecialchars($supir['nama_supir']); ?> (Status: <?= htmlspecialchars($supir['status']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Tidak ada supir dengan status 'Standby' saat ini</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Jam Keberangkatan</label>
                        <input type="time" name="jam_berangkat" class="w-full p-3.5 border border-stone-200 rounded-lg bg-stone-50 text-sm focus:outline-none focus:border-emerald-600 focus:bg-white transition" required>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                        <a href="master-rute.php" class="px-5 py-3 rounded text-xs font-bold tracking-widest uppercase text-stone-400 hover:text-stone-700 transition">Batal</a>
                        <button type="submit" class="bg-black hover:bg-stone-900 text-white text-xs font-bold tracking-widest uppercase px-6 py-3 rounded shadow transition">Publish Jadwal Aktif</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>