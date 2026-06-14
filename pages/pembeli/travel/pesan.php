<?php
// pages/pembeli/travel/pesan.php
session_start();

// Panggil database memakai path relatif mundur 3 kali
require_once '../../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// Ambil parameter id_jadwal dari URL
$id_jadwal = $_GET['id_jadwal'] ?? null;

if (!$id_jadwal) {
    echo "<script>alert('Pilih jadwal keberangkatan terlebih dahulu melalui halaman jadwal!'); window.location.href='search.php';</script>";
    exit;
}

try {
    // Query disesuaikan menggunakan r.harga_dasar dari struktur phpMyAdmin kamu
    $stmt = $pdo->prepare("
        SELECT 
            jk.id AS id_jadwal,
            jk.tanggal_berangkat,
            jk.jam_berangkat,
            jk.sisa_kursi,
            r.nama_rute,
            r.harga_dasar,
            m.merk,
            m.plat_nomor,
            u.nama AS nama_supir,
            u.nomor_hp AS hp_supir
        FROM jadwal_keberangkatan jk
        INNER JOIN rute r ON jk.rute_id = r.id
        INNER JOIN mobil m ON jk.mobil_id = m.id
        INNER JOIN users u ON jk.supir_id = u.id
        WHERE jk.id = ?
    ");
    $stmt->execute([$id_jadwal]);
    $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$jadwal) {
        die("Jadwal perjalanan tidak ditemukan atau sudah tidak aktif.");
    }

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesan Tiket Travel | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style> h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; } body { font-family: "Montserrat", sans-serif; } </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">
    <?php 
    include '../../../components/sidebar.php'; 
    include '../../../components/header.php'; 
    ?>
    
    <main class="ml-64 p-8 flex-1">
        <div class="mb-10">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Booking Form</span>
            <h2 class="text-4xl italic text-gray-800">Form Pemesanan Tiket</h2>
            <p class="text-gray-400 mt-2 text-sm">Silakan isi nama penumpang, jumlah tiket, dan lokasi penjemputan Anda.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-5xl">
            <div class="lg:col-span-2 bg-white p-8 border border-gray-100 shadow-sm">
                <form action="proses-pesan.php" method="POST" class="space-y-6">
                    <input type="hidden" name="id_jadwal" value="<?= $jadwal['id_jadwal'] ?>">

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-400 mb-2 font-semibold">Nama Penumpang Utama</label>
                        <input type="text" name="nama_penumpang" class="w-full p-3 bg-[#faf9f6] border border-gray-200 focus:outline-none focus:border-emerald-600 text-sm" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-400 mb-2 font-semibold">Jumlah Tiket Yang Dibeli</label>
                            <input type="number" name="jumlah_tiket" min="1" max="<?= $jadwal['sisa_kursi'] ?>" class="w-full p-3 bg-[#faf9f6] border border-gray-200 focus:outline-none focus:border-emerald-600 text-sm" placeholder="Maksimal <?= $jadwal['sisa_kursi'] ?> tiket" required>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-400 mb-2 font-semibold">Nomor WhatsApp Anda</label>
                            <input type="text" name="whatsapp_pembeli" class="w-full p-3 bg-[#faf9f6] border border-gray-200 focus:outline-none focus:border-emerald-600 text-sm" placeholder="Contoh: 0812345678" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-400 mb-2 font-semibold">Titik / Lokasi Penjemputan</label>
                        <textarea name="titik_jemput" rows="3" class="w-full p-3 bg-[#faf9f6] border border-gray-200 focus:outline-none focus:border-emerald-600 text-sm" placeholder="Tuliskan alamat penjemputan detail" required></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-emerald-700 text-white p-3 uppercase tracking-widest text-xs font-bold hover:bg-emerald-800 transition">
                            Proses Pemesanan & Hubungi Driver
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 border border-gray-100 shadow-sm h-fit space-y-4">
                <h3 class="text-xl font-bold border-b pb-3 text-gray-800">Ringkasan Perjalanan</h3>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Rute Lintasan</p>
                    <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($jadwal['nama_rute']) ?></p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Waktu Keberangkatan</p>
                    <p class="text-sm font-semibold text-gray-700"><?= date('d M Y', strtotime($jadwal['tanggal_berangkat'])) ?> - <?= htmlspecialchars($jadwal['jam_berangkat']) ?> WIB</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Armada Unit</p>
                    <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($jadwal['merk']) ?> [<?= htmlspecialchars($jadwal['plat_nomor']) ?>]</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Harga Per Kursi</p>
                    <p class="text-sm font-bold text-emerald-700">Rp <?= number_format($jadwal['harga_dasar'], 0, ',', '.') ?></p>
                </div>
                <div class="pt-2 border-t">
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Sisa Kursi Tersedia</p>
                    <span class="px-2 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded block w-fit mt-1">
                        <?= $jadwal['sisa_kursi'] ?> Kursi Kosong
                    </span>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../../components/footer.php'; ?>
</body>
</html>