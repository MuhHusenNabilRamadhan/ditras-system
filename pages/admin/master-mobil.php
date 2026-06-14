<?php
session_start();
require_once '../../config/database.php';

// Proteksi halaman admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "pages/auth/login.php");
    exit;
}

$error_msg = "";
$success_msg = "";

// ==========================================
// 1. PROSES HAPUS DATA (DELETE)
// ==========================================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM mobil WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Armada berhasil dihapus!";
        header("Location: master-mobil.php");
        exit;
    } catch (PDOException $e) {
        $error_msg = "Gagal menghapus armada: " . $e->getMessage();
    }
}

// ==========================================
// 2. PROSES TAMBAH / EDIT DATA (CREATE & UPDATE)
// ==========================================
if (isset($_POST['simpan_mobil'])) {
    $id = $_POST['id_mobil'] ?? '';
    $plat = strtoupper(trim($_POST['plat_nomor']));
    $merk = trim($_POST['merk']);
    $tahun = $_POST['tahun_kendaraan'];
    $kursi = $_POST['jumlah_kursi'];
    $harga = $_POST['harga_sewa_per_hari'];
    $status = $_POST['status_mobil'];
    $status_stnk = $_POST['status_stnk'];
    $status_pajak = $_POST['status_pajak'];
    $status_kir = $_POST['status_kir'];

    try {
        if (empty($id)) {
            // INSERT (Tambah Baru)
            $stmt = $pdo->prepare("INSERT INTO mobil (plat_nomor, merk, tahun_kendaraan, jumlah_kursi, harga_sewa_per_hari, status_mobil, status_stnk, status_pajak, status_kir) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$plat, $merk, $tahun, $kursi, $harga, $status, $status_stnk, $status_pajak, $status_kir]);
            $_SESSION['success'] = "Armada baru berhasil ditambahkan!";
        } else {
            // UPDATE (Edit Eksisting)
            $stmt = $pdo->prepare("UPDATE mobil SET plat_nomor = ?, merk = ?, tahun_kendaraan = ?, jumlah_kursi = ?, harga_sewa_per_hari = ?, status_mobil = ?, status_stnk = ?, status_pajak = ?, status_kir = ? WHERE id = ?");
            $stmt->execute([$plat, $merk, $tahun, $kursi, $harga, $status, $status_stnk, $status_pajak, $status_kir, $id]);
            $_SESSION['success'] = "Data armada berhasil diperbarui!";
        }
        header("Location: master-mobil.php");
        exit;
    } catch (PDOException $e) {
        $error_msg = "Gagal memproses data: " . $e->getMessage();
    }
}

// Ambil session flash message jika ada
if (isset($_SESSION['success'])) {
    $success_msg = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ==========================================
// 3. READ DATA (TAMPILKAN KE TABEL)
// ==========================================
$stmt = $pdo->query("SELECT * FROM mobil ORDER BY id DESC");
$all_mobil = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Armada | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #009663; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#faf9f6] text-stone-900 antialiased">

    <?php include '../../components/sidebar.php'; ?>

    <main class="ml-64 p-8 min-h-screen">
        <div class="flex justify-between items-center mb-8">
            <div>
                <p class="text-[11px] uppercase tracking-[0.3em] text-[#009663] font-extrabold mb-1">Data Master</p>
                <h1 class="text-3xl font-bold tracking-tight text-stone-800">Kelola Armada</h1>
            </div>
            <button onclick="openModal()" class="bg-[#009663] text-white font-bold px-6 py-3.5 rounded-2xl text-xs uppercase tracking-widest hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-200 transition-all duration-300 flex items-center gap-2">
                <span>+</span> Tambah Mobil Baru
            </button>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-700 text-xs font-semibold rounded-2xl flex items-center">⚠️ <span class="ml-2"><?= $error_msg ?></span></div>
        <?php endif; ?>
        <?php if (!empty($success_msg)): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-semibold rounded-2xl flex items-center">✅ <span class="ml-2"><?= $success_msg ?></span></div>
        <?php endif; ?>

        <div class="bg-white rounded-[32px] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="py-5 px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Plat Nomor</th>
                            <th class="py-5 px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Merek & Tipe</th>
                            <th class="py-5 px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Tahun</th>
                            <th class="py-5 px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Kursi</th>
                            <th class="py-5 px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Harga Sewa</th>
                            <th class="py-5 px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Status Unit</th>
                            <th class="py-5 px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kelengkapan Dokumen</th>
                            <th class="py-5 px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($all_mobil)): ?>
                            <tr>
                                <td colspan="8" class="py-10 text-center text-sm font-medium text-gray-400">Belum ada armada terdaftar.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_mobil as $row): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-6 font-bold text-sm text-stone-800"><?= htmlspecialchars($row['plat_nomor']) ?></td>
                                    <td class="py-4 px-6 font-medium text-sm text-gray-600"><?= htmlspecialchars($row['merk']) ?></td>
                                    <td class="py-4 px-6 text-sm font-bold text-stone-700 text-center"><?= $row['tahun_kendaraan'] ?></td>
                                    <td class="py-4 px-6 text-sm font-bold text-emerald-700 text-center bg-emerald-50/30 rounded-lg"><?= $row['jumlah_kursi'] ?> set</td>
                                    <td class="py-4 px-6 font-bold text-sm text-stone-800">Rp <?= number_format($row['harga_sewa_per_hari'], 0, ',', '.') ?></td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($row['status_mobil'] === 'tersedia'): ?>
                                            <span class="px-3 py-1 text-[10px] font-bold tracking-wider rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">TERSEDIA</span>
                                        <?php elseif ($row['status_mobil'] === 'jalan'): ?>
                                            <span class="px-3 py-1 text-[10px] font-bold tracking-wider rounded-full bg-blue-50 text-blue-700 border border-blue-200">JALAN</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 text-[10px] font-bold tracking-wider rounded-full bg-orange-50 text-orange-700 border border-orange-200">BENGKEL</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-1.5 text-[9px] font-bold">
                                            <span class="px-2 py-0.5 rounded border <?= $row['status_stnk'] === 'Aktif' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200' ?>">STNK: <?= strtoupper($row['status_stnk']) ?></span>
                                            <span class="px-2 py-0.5 rounded border <?= $row['status_pajak'] === 'Aktif' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200' ?>">PJK: <?= strtoupper($row['status_pajak']) ?></span>
                                            <span class="px-2 py-0.5 rounded border <?= $row['status_kir'] === 'Aktif' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200' ?>">KIR: <?= $row['status_kir'] === 'Aktif' ? 'AKTIF' : 'N/A' ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center text-xs font-bold">
                                        <div class="flex justify-center items-center gap-3">
                                            <button onclick="editMobil(<?= htmlspecialchars(json_encode($row)) ?>)" class="text-emerald-600 hover:text-emerald-800 transition-colors">Edit</button>
                                            <a href="master-mobil.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus armada <?= $row['plat_nomor'] ?>?')" class="text-red-500 hover:text-red-700 transition-colors">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="mobilModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden flex justify-center items-center p-4 transition-all duration-300">
        <div class="w-full max-w-xl bg-white p-8 rounded-[32px] border border-gray-100 shadow-2xl relative max-h-[90vh] overflow-y-auto transform scale-95 opacity-0 transition-all duration-300" id="modalContainer">
            
            <button onclick="closeModal()" class="absolute top-6 right-6 text-gray-400 hover:text-stone-700 font-bold text-lg">&times;</button>
            
            <div class="mb-6">
                <p class="text-[10px] uppercase tracking-[0.3em] text-[#009663] font-extrabold mb-1">Fleet Management</p>
                <h2 id="modalTitle" class="text-2xl font-bold tracking-tight text-stone-800">Registrasi Unit Baru</h2>
            </div>

            <form action="" method="POST" class="space-y-6">
                <input type="hidden" name="id_mobil" id="id_mobil">

                <div class="space-y-4">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] flex items-center">
                        <span class="w-6 h-[1px] bg-gray-200 mr-2"></span> Spesifikasi Utama
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-stone-600 ml-1">Plat Nomor</label>
                            <input type="text" name="plat_nomor" id="plat_nomor" required placeholder="Contoh: AB 1234 CD" class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-emerald-500 text-stone-800 px-4 py-3 rounded-xl text-sm transition-all duration-300 outline-none shadow-inner uppercase font-bold">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-stone-600 ml-1">Tahun Kendaraan</label>
                            <input type="number" name="tahun_kendaraan" id="tahun_kendaraan" min="2010" max="<?= date('Y') + 1 ?>" required placeholder="2024" class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-emerald-500 text-stone-800 px-4 py-3 rounded-xl text-sm transition-all duration-300 outline-none shadow-inner font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold text-stone-600 ml-1">Merek & Tipe Unit</label>
                            <input type="text" name="merk" id="merk" required placeholder="Contoh: Toyota Hiace" class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-emerald-500 text-stone-800 px-4 py-3 rounded-xl text-sm transition-all duration-300 outline-none shadow-inner font-medium">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-stone-600 ml-1">Jumlah Kursi</label>
                            <input type="number" name="jumlah_kursi" id="jumlah_kursi" min="1" max="60" required placeholder="14" class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-emerald-500 text-stone-800 px-4 py-3 rounded-xl text-sm transition-all duration-300 outline-none shadow-inner font-bold text-emerald-700">
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] flex items-center">
                        <span class="w-6 h-[1px] bg-gray-200 mr-2"></span> Status & Operasional
                    </h3>
                    
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-stone-600 ml-1">Harga Sewa / Hari (Rp)</label>
                        <input type="number" name="harga_sewa_per_hari" id="harga_sewa_per_hari" required placeholder="450000" class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-emerald-500 text-stone-800 px-4 py-3 rounded-xl text-sm transition-all duration-300 outline-none shadow-inner font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-stone-600 ml-1">Status Awal Unit</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="relative">
                                <input type="radio" name="status_mobil" id="status_tersedia" value="tersedia" checked class="peer hidden">
                                <div class="cursor-pointer text-center py-2.5 rounded-xl border-2 border-gray-100 bg-white text-[10px] font-bold text-gray-400 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 transition-all duration-300">TERSEDIA</div>
                            </label>
                            <label class="relative">
                                <input type="radio" name="status_mobil" id="status_jalan" value="jalan" class="peer hidden">
                                <div class="cursor-pointer text-center py-2.5 rounded-xl border-2 border-gray-100 bg-white text-[10px] font-bold text-gray-400 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition-all duration-300">JALAN</div>
                            </label>
                            <label class="relative">
                                <input type="radio" name="status_mobil" id="status_maintenance" value="maintenance" class="peer hidden">
                                <div class="cursor-pointer text-center py-2.5 rounded-xl border-2 border-gray-100 bg-white text-[10px] font-bold text-gray-400 peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-700 transition-all duration-300">BENGKEL</div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] flex items-center">
                        <span class="w-6 h-[1px] bg-gray-200 mr-2"></span> Kelengkapan Dokumen
                    </h3>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label class="block text-[9px] font-extrabold text-stone-500 text-center">STATUS STNK</label>
                            <div class="flex flex-col gap-1.5">
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_stnk" id="stnk_aktif" value="Aktif" checked class="peer hidden">
                                    <div class="py-2 rounded-lg text-center text-[10px] font-bold bg-gray-50 border border-transparent text-gray-400 peer-checked:bg-emerald-500 peer-checked:text-white transition-all">AKTIF</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_stnk" id="stnk_mati" value="Mati" class="peer hidden">
                                    <div class="py-2 rounded-lg text-center text-[10px] font-bold bg-gray-50 border border-transparent text-gray-400 peer-checked:bg-red-500 peer-checked:text-white transition-all">MATI</div>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[9px] font-extrabold text-stone-500 text-center">PAJAK TAHUNAN</label>
                            <div class="flex flex-col gap-1.5">
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_pajak" id="pajak_aktif" value="Aktif" checked class="peer hidden">
                                    <div class="py-2 rounded-lg text-center text-[10px] font-bold bg-gray-50 border border-transparent text-gray-400 peer-checked:bg-emerald-500 peer-checked:text-white transition-all">AKTIF</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_pajak" id="pajak_mati" value="Mati" class="peer hidden">
                                    <div class="py-2 rounded-lg text-center text-[10px] font-bold bg-gray-50 border border-transparent text-gray-400 peer-checked:bg-red-500 peer-checked:text-white transition-all">MATI</div>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[9px] font-extrabold text-stone-500 text-center">IZIN KIR</label>
                            <div class="flex flex-col gap-1.5">
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_kir" id="kir_aktif" value="Aktif" checked class="peer hidden">
                                    <div class="py-2 rounded-lg text-center text-[10px] font-bold bg-gray-50 border border-transparent text-gray-400 peer-checked:bg-emerald-500 peer-checked:text-white transition-all">AKTIF</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_kir" id="kir_tidak" value="Tidak Ada" class="peer hidden">
                                    <div class="py-2 rounded-lg text-center text-[10px] font-bold bg-gray-50 border border-transparent text-gray-400 peer-checked:bg-gray-500 peer-checked:text-white transition-all">N/A</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" name="simpan_mobil" class="flex-1 bg-[#009663] text-white font-bold py-3.5 rounded-xl text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all duration-300">
                        Simpan Data
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 bg-stone-100 text-stone-500 font-bold py-3.5 rounded-xl text-xs uppercase tracking-widest hover:bg-stone-200 transition-all duration-300">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('mobilModal');
        const container = document.getElementById('modalContainer');

        function openModal() {
            // Reset form ke mode Tambah Data bawaan
            document.getElementById('id_mobil').value = '';
            document.getElementById('modalTitle').innerText = 'Registrasi Unit Baru';
            document.getElementById('plat_nomor').value = '';
            document.getElementById('merk').value = '';
            document.getElementById('tahun_kendaraan').value = '';
            document.getElementById('jumlah_kursi').value = '';
            document.getElementById('harga_sewa_per_hari').value = '';
            document.getElementById('status_tersedia').checked = true;
            document.getElementById('stnk_aktif').checked = true;
            document.getElementById('pajak_aktif').checked = true;
            document.getElementById('kir_aktif').checked = true;

            // Efek Animasi Muncul
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 20);
        }

        function closeModal() {
            // Efek Animasi Menutup
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 250);
        }

        function editMobil(data) {
            openModal();
            // Set data terpilih ke form input modal
            document.getElementById('id_mobil').value = data.id;
            document.getElementById('modalTitle').innerText = 'Perbarui Data Armada';
            document.getElementById('plat_nomor').value = data.plat_nomor;
            document.getElementById('merk').value = data.merk;
            document.getElementById('tahun_kendaraan').value = data.tahun_kendaraan;
            document.getElementById('jumlah_kursi').value = data.jumlah_kursi;
            document.getElementById('harga_sewa_per_hari').value = data.harga_sewa_per_hari;

            // Set Radio button status mobil
            if(data.status_mobil === 'tersedia') document.getElementById('status_tersedia').checked = true;
            if(data.status_mobil === 'jalan') document.getElementById('status_jalan').checked = true;
            if(data.status_mobil === 'maintenance') document.getElementById('status_maintenance').checked = true;

            // Set Radio button kelengkapan dokumen
            document.getElementById(data.status_stnk === 'Aktif' ? 'stnk_aktif' : 'stnk_mati').checked = true;
            document.getElementById(data.status_pajak === 'Aktif' ? 'pajak_aktif' : 'pajak_mati').checked = true;
            document.getElementById(data.status_kir === 'Aktif' ? 'kir_aktif' : 'kir_tidak').checked = true;
        }

        // Tutup modal jika user klik area luar/backdrop gray
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    </script>
</body>
</html>