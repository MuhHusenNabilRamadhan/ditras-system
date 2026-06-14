<?php
// pages/pembeli/rental-supir/form-sewa.php
session_start();

// Jalur include sesuai dengan struktur folder laptopmu
require_once '../../../config/database.php';

// Proteksi Halaman Pembeli/User
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../../auth/login.php");
    exit;
}

$nama_user = $_SESSION['nama_user'] ?? 'Pelanggan';

// =========================================================================
// QUERY ADAPTASI DARI JADWAL-AKTIF (MENGGUNAKAN STRUKTUR TABEL MOBIL & USERS ADMIN)
// =========================================================================
try {
    // Mengambil data mobil menggunakan struktur tabel admin (id, merk, plat_nomor, status_mobil)
    $query_mobil = $pdo->query("SELECT id, merk, plat_nomor FROM mobil WHERE LOWER(status_mobil) = 'tersedia' ORDER BY merk ASC");
    $daftar_mobil = $query_mobil->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $daftar_mobil = [];
}

try {
    // Mengambil data supir dari tabel users & supir_detail sesuai logika realtime admin
    $query_supir = $pdo->query("
        SELECT 
            u.id AS supir_id, 
            u.nama AS nama_supir,
            IFNULL(sd.status, 'Standby') AS status
        FROM users u
        LEFT JOIN supir_detail sd ON u.id = sd.supir_id
        WHERE LOWER(u.role) = 'supir' 
          AND (LOWER(sd.status) = 'standby' OR sd.status IS NULL)
        ORDER BY u.nama ASC
    ");
    $daftar_supir = $query_supir->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $daftar_supir = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Mobil + Supir | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
      h1, h2, h3, h4, .serif { font-family: "Cormorant Garamond", serif; }
      body { font-family: "Montserrat", sans-serif; }
      .input-premium {
          background-color: #faf9f6;
          border: 1px solid #e5e7eb;
          transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }
      .input-premium:focus {
          border-color: #059669;
          background-color: #ffffff;
          box-shadow: 0 4px 12px rgba(5, 150, 105, 0.05);
      }
    </style>
</head>
<body class="bg-[#faf9f6] text-stone-900 min-h-screen flex flex-col">

    <?php include '../../../components/sidebar.php'; ?>
    <?php include '../../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1">
        
        <div class="mb-8">
            <span class="text-[10px] uppercase tracking-[0.25em] text-emerald-600 font-bold block mb-1">Chauffeur Service</span>
            <h2 class="text-4xl italic text-gray-800">Rental Mobil &amp; Supir Mitra</h2>
            <p class="text-gray-400 mt-1 text-sm">Pilih unit armada unggulan dan supir profesional kami untuk perjalanan Anda.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <div class="lg:col-span-2 bg-white p-8 border border-gray-100 shadow-sm rounded-sm">
                <form action="proses-sewa.php" method="POST" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-2">Tanggal Mulai Sewa</label>
                            <input type="date" name="tanggal_berangkat" class="w-full p-3.5 input-premium text-sm outline-none" required>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-2">Durasi Pemakaian</label>
                            <div class="relative flex items-center">
                                <input type="number" name="durasi" id="durasi" min="1" value="1" class="w-full p-3.5 input-premium text-sm outline-none pr-12" required>
                                <span class="absolute right-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Hari</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-2">Pilih Armada Mobil (Ready &amp; Standby)</label>
                        <select name="id_mobil" id="id_mobil" class="w-full p-3.5 input-premium text-sm outline-none appearance-none cursor-pointer" required>
                            <option value="" disabled selected>-- Cari Kendaraan Berstatus Standby --</option>
                            <?php if (!empty($daftar_mobil)): ?>
                                <?php foreach ($daftar_mobil as $mobil): ?>
                                    <option value="<?= $mobil['id'] ?>" data-harga="500000">
                                        <?= htmlspecialchars($mobil['merk']) ?> [<?= htmlspecialchars($mobil['plat_nomor']) ?>] — Rp 500.000 / Hari
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Maaf, seluruh armada admin saat ini sedang jalan / terpakai.</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-2">Pilih Supir Pengemudi (Tersedia)</label>
                        <select name="id_supir" id="id_supir" class="w-full p-3.5 input-premium text-sm outline-none appearance-none cursor-pointer" required>
                            <option value="" disabled selected>-- Pilih Supir Siap Tugas --</option>
                            <?php if (!empty($daftar_supir)): ?>
                                <?php foreach ($daftar_supir as $supir): ?>
                                    <option value="<?= $supir['supir_id'] ?>" data-harga="200000">
                                        <?= htmlspecialchars($supir['nama_supir']) ?> (Status: <?= htmlspecialchars($supir['status']) ?>) — Rp 200.000 / Hari
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Maaf, saat ini seluruh mitra supir kami sedang bertugas.</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full md:w-auto bg-stone-900 text-white text-[10px] uppercase tracking-[0.2em] font-bold px-8 py-4 hover:bg-emerald-700 transition duration-300 shadow-sm">
                            Kunci Pemesanan &amp; Lanjut COD
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-stone-900 text-white p-8 rounded-none border border-stone-800 flex flex-col justify-between min-h-[380px] shadow-xl">
                <div>
                    <span class="text-[9px] text-emerald-400 font-bold uppercase tracking-[0.2em] block mb-2">✦ Billing Information</span>
                    <h3 class="text-2xl font-serif italic text-stone-100 mb-4">Metode Pembayaran COD (Tunai)</h3>
                    <p class="text-xs text-stone-400 leading-relaxed mb-4">
                        Sistem rental DITRAS menggunakan opsi pembayaran **Cash on Delivery**. Pembayaran sah dilakukan langsung melalui supir pendamping saat armada sampai di lokasi penjemputan Anda.
                    </p>
                    <div class="text-[11px] bg-stone-800/50 p-3 border-l-2 border-emerald-500 text-stone-300 italic">
                        "Supir akan memperbarui koordinat penjemputan serta rute waktu nyata sesaat setelah order divalidasi supir."
                    </div>
                </div>
                
                <div class="mt-8 border-t border-stone-800 pt-6">
                    <p class="text-[10px] uppercase tracking-wider text-stone-400 font-semibold mb-1">Total Estimasi Tagihan</p>
                    <div class="text-4xl font-bold tracking-tight text-emerald-400 font-mono" id="display-total">Rp 0</div>
                    <p class="text-[9px] text-stone-500 mt-2">*Tarif akumulatif bersih (belum termasuk biaya bahan bakar/tol perjalanan).</p>
                </div>
            </div>

        </div>
    </main>

    <?php include '../../../components/footer.php'; ?>

    <script>
        const selectMobil = document.getElementById('id_mobil');
        const selectSupir = document.getElementById('id_supir');
        const inputDurasi = document.getElementById('durasi');
        const displayTotal = document.getElementById('display-total');

        function hitungTotalSewa() {
            let tarifMobil = 0;
            let tarifSupir = 0;
            let totalHari = parseInt(inputDurasi.value) || 1;

            if (selectMobil.selectedIndex > 0) {
                tarifMobil = parseInt(selectMobil.options[selectMobil.selectedIndex].getAttribute('data-harga')) || 0;
            }
            if (selectSupir.selectedIndex > 0) {
                tarifSupir = parseInt(selectSupir.options[selectSupir.selectedIndex].getAttribute('data-harga')) || 0;
            }

            let totalBiaya = (tarifMobil + tarifSupir) * totalHari;
            
            // Format Rupiah Live Indonesia (.toLocaleString)
            displayTotal.innerText = 'Rp ' + totalBiaya.toLocaleString('id-ID');
        }

        selectMobil.addEventListener('change', hitungTotalSewa);
        selectSupir.addEventListener('change', hitungTotalSewa);
        inputDurasi.addEventListener('input', hitungTotalSewa);
    </script>
</body>
</html>