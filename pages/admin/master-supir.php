<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Proteksi agar hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "pages/auth/login.php");
    exit;
}

$success_msg = "";
$error_msg = "";

// 1. PROSES TAMBAH SUPIR BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_supir'])) {
    $nama = $_POST['nama'];
    $nomor_hp = $_POST['nomor_hp'];
    $plain_password = $_POST['password'];
    $password_hashed = password_hash($plain_password, PASSWORD_DEFAULT); 
    $email = trim($_POST['email']); 

    try {
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        
        if ($check_stmt->rowCount() > 0) {
            $error_msg = "Email sudah digunakan oleh akun lain!";
        } else {
            $insert_user = $pdo->prepare("INSERT INTO users (nama, nomor_hp, password, email, role) VALUES (?, ?, ?, ?, 'supir')");
            $insert_user->execute([$nama, $nomor_hp, $plain_password, $email]); 
            
            $success_msg = "Akun Supir baru berhasil ditambahkan!";
        }
    } catch (PDOException $e) {
        $error_msg = "Gagal menambah supir: " . $e->getMessage();
    }
}

// 2. PROSES UPDATE DATA SUPIR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_supir'])) {
    $id_supir = $_POST['id_supir'];
    $status = $_POST['status'];
    $email_baru = trim($_POST['email']);
    
    $kendaraan = ($status === 'Standby') ? null : $_POST['kendaraan_bawaan'];
    $tujuan = ($status === 'Standby') ? null : $_POST['tujuan'];

    try {
        $pdo->beginTransaction();

        $update_user_stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ? AND role = 'supir'");
        $update_user_stmt->execute([$email_baru, $id_supir]);

        $check_stmt = $pdo->prepare("SELECT supir_id FROM supir_detail WHERE supir_id = ?");
        $check_stmt->execute([$id_supir]);
        
        if ($check_stmt->rowCount() > 0) {
            $update_stmt = $pdo->prepare("UPDATE supir_detail SET status = ?, kendaraan_bawaan = ?, tujuan = ? WHERE supir_id = ?");
            $update_stmt->execute([$status, $kendaraan, $tujuan, $id_supir]);
        } else {
            $insert_stmt = $pdo->prepare("INSERT INTO supir_detail (supir_id, status, kendaraan_bawaan, tujuan) VALUES (?, ?, ?, ?)");
            $insert_stmt->execute([$id_supir, $status, $kendaraan, $tujuan]);
        }

        $pdo->commit();
        $success_msg = "Data status & email login supir berhasil diperbarui!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Gagal memperbarui data: " . $e->getMessage();
    }
}

// 3. PROSES HAPUS SUPIR
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    try {
        $pdo->beginTransaction();
        
        $delete_detail = $pdo->prepare("DELETE FROM supir_detail WHERE supir_id = ?");
        $delete_detail->execute([$delete_id]);
        
        $delete_user = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'supir'");
        $delete_user->execute([$delete_id]);
        
        $pdo->commit();
        $success_msg = "Akun dan data supir berhasil dihapus permanen!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Gagal menghapus data: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Supir | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>body { font-family: 'Montserrat', sans-serif; }</style>
</head>
<body class="bg-[#faf9f6] text-stone-900">

    <?php include '../../components/sidebar.php'; ?>

    <main class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <p class="text-[12px] uppercase tracking-[0.2em] text-emerald-600 font-bold mb-1">Data Master</p>
                <h2 class="text-3xl font-bold tracking-tight">Kelola Akun & Data Supir</h2>
            </div>
            <button type="button" onclick="openAddModal()" class="bg-[#009663] text-white px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-emerald-700 transition shadow-sm">
                + Tambah Supir Baru
            </button>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium rounded-xl">
                ✅ <?= $success_msg ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 text-sm font-medium rounded-xl">
                ❌ <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">Nama Supir</th>
                            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">Email Akun</th>
                            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">Password</th> 
                            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">Nomor HP</th>
                            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">Rating</th>
                            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">Keterangan Tugas</th>
                            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-gray-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php
                        $query = "SELECT u.id, u.nama, u.email, u.password, u.nomor_hp, sd.status, sd.kendaraan_bawaan, sd.tujuan,
                                  (SELECT IFNULL(AVG(rating_diberikan), 5.00) FROM review_supir WHERE supir_id = u.id) as avg_rating
                                  FROM users u 
                                  LEFT JOIN supir_detail sd ON u.id = sd.supir_id 
                                  WHERE u.role = 'supir'";
                        
                        try {
                            $stmt = $pdo->query($query);
                            
                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch()):
                                    $status_supir = $row['status'] ?? 'Standby';
                                    
                                    $keterangan = '<span class="text-gray-400 italic text-xs">Menunggu penugasan</span>';
                                    if ($status_supir === 'On Trip' || $status_supir === 'Travel') {
                                        $unit = htmlspecialchars($row['kendaraan_bawaan'] ?? 'Belum ditentukan');
                                        $arah = htmlspecialchars($row['tujuan'] ?? 'Belum ditentukan');
                                        $keterangan = "<div class='text-xs space-y-0.5'>
                                                        <span class='block font-bold text-stone-700'>🚘 $unit</span>
                                                        <span class='block text-stone-500'>📍 Ke: $arah</span>
                                                       </div>";
                                    }

                                    $badgeClass = 'bg-gray-100 text-gray-600';
                                    if ($status_supir === 'Standby') { 
                                        $badgeClass = 'bg-emerald-100 text-emerald-700'; 
                                    } elseif ($status_supir === 'On Trip') { 
                                        $badgeClass = 'bg-blue-100 text-blue-700'; 
                                    } elseif ($status_supir === 'Travel') { 
                                        $badgeClass = 'bg-purple-100 text-purple-700'; 
                                    }
                        ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-4 font-bold text-stone-800"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="p-4 text-sm text-stone-600 font-medium"><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                                    
                                    <td class="p-4">
                                        <div class="password-container flex items-center gap-2 font-mono text-sm">
                                            <span class="masked-password text-gray-400 tracking-widest" data-real-password="<?= htmlspecialchars($row['password'] ?? '123456') ?>" data-visible="false">••••••••</span>
                                            <button type="button" class="btn-toggle-table-pass text-gray-400 hover:text-emerald-600 transition focus:outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    
                                    <td class="p-4 text-sm text-stone-600"><?= htmlspecialchars($row['nomor_hp']) ?></td>
                                    <td class="p-4 text-sm font-bold text-yellow-500">⭐ <?= number_format($row['avg_rating'], 2) ?></td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 text-[10px] font-bold rounded-md uppercase tracking-wider <?= $badgeClass ?>">
                                            <?= htmlspecialchars($status_supir) ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <?= $keterangan ?>
                                    </td>
                                    <td class="p-4 text-center space-x-2">
                                        <button type="button" 
                                                onclick="openEditModal('<?= $row['id'] ?>', '<?= htmlspecialchars($row['nama']) ?>', '<?= $status_supir ?>', '<?= htmlspecialchars($row['kendaraan_bawaan'] ?? '') ?>', '<?= htmlspecialchars($row['tujuan'] ?? '') ?>', '<?= htmlspecialchars($row['email'] ?? '') ?>')" 
                                                class="bg-stone-100 text-stone-700 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-600 hover:text-white transition shadow-sm">
                                            Manage Status
                                        </button>
                                        <button type="button" onclick="confirmDelete('<?= $row['id'] ?>', '<?= htmlspecialchars($row['nama']) ?>')" class="text-red-500 text-xs font-bold hover:underline bg-transparent border-none cursor-pointer">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            } else {
                                echo "<tr><td colspan='8' class='p-8 text-center text-sm text-gray-500'>Belum ada data supir.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='8' class='p-8 text-center text-sm text-red-500 font-bold bg-red-50'>ERROR DATABASE: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="addSupirModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-bold text-stone-800 tracking-tight">Tambah Akun Supir</h3>
                <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-stone-600 text-2xl font-semibold focus:outline-none">&times;</button>
            </div>
            
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" required placeholder="Nama lengkap supir" class="w-full bg-white border border-gray-200 text-stone-800 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Email Login Supir</label>
                    <input type="email" name="email" required placeholder="Contoh: supir@ditras.com" class="w-full bg-white border border-gray-200 text-stone-800 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nomor HP / WhatsApp</label>
                    <input type="text" name="nomor_hp" required placeholder="Contoh: 0812345678" class="w-full bg-white border border-gray-200 text-stone-800 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-sm">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="modal_password_input" name="password" required placeholder="Password akun supir" class="w-full bg-white border border-gray-200 text-stone-800 pl-4 pr-12 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-sm">
                        <button type="button" id="btn_toggle_modal_pass" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-stone-600 transition focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pt-3 flex space-x-3">
                    <button type="button" onclick="closeAddModal()" class="w-1/3 bg-gray-100 text-gray-600 font-bold py-3 rounded-xl text-xs uppercase tracking-wider hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" name="tambah_supir" class="w-2/3 bg-emerald-600 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-wider hover:bg-emerald-700 transition shadow-sm">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-bold text-stone-800 tracking-tight">Manage Status Supir</h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-stone-600 text-2xl font-semibold focus:outline-none">&times;</button>
            </div>
            
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="id_supir" id="modal_id_supir">

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Supir</label>
                    <input type="text" id="modal_nama_supir" class="w-full bg-gray-50 border border-gray-200 text-stone-700 px-4 py-2.5 rounded-xl text-sm font-medium focus:outline-none cursor-not-allowed" readonly>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Email Login Supir</label>
                    <input type="email" name="email" id="modal_email_supir" required placeholder="Masukkan email login supir" class="w-full bg-white border border-gray-200 text-stone-800 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-sm font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Status Operasional</label>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-xl transition">
                            <input type="radio" name="status" id="status_standby" value="Standby" class="peer hidden" onchange="toggleFormInputs()">
                            <label for="status_standby" class="block w-full text-center p-3 rounded-xl border border-gray-200 bg-white text-sm font-medium text-gray-600 cursor-pointer peer-checked:bg-emerald-100 peer-checked:border-emerald-200 peer-checked:text-emerald-800 peer-checked:font-bold hover:bg-gray-50 transition shadow-sm">
                                Standby
                            </label>
                        </div>
                        <div class="rounded-xl transition">
                            <input type="radio" name="status" id="status_ontrip" value="On Trip" class="peer hidden" onchange="toggleFormInputs()">
                            <label for="status_ontrip" class="block w-full text-center p-3 rounded-xl border border-gray-200 bg-white text-sm font-medium text-gray-600 cursor-pointer peer-checked:bg-blue-100 peer-checked:border-blue-200 peer-checked:text-blue-800 peer-checked:font-bold hover:bg-gray-50 transition shadow-sm">
                                On Trip
                            </label>
                        </div>
                        <div class="rounded-xl transition">
                            <input type="radio" name="status" id="status_travel" value="Travel" class="peer hidden" onchange="toggleFormInputs()">
                            <label for="status_travel" class="block w-full text-center p-3 rounded-xl border border-gray-200 bg-white text-sm font-medium text-gray-600 cursor-pointer peer-checked:bg-purple-100 peer-checked:border-purple-200 peer-checked:text-purple-800 peer-checked:font-bold hover:bg-gray-50 transition shadow-sm">
                                Travel
                            </label>
                        </div>
                    </div>
                </div>

                <div id="wrapper_penugasan" class="space-y-4 hidden opacity-0 transition-opacity duration-300">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Unit Kendaraan / Mobil</label>
                        <input type="text" name="kendaraan_bawaan" id="modal_kendaraan" placeholder="Contoh: Avanza Veloz (B 1234 ABC)" class="w-full bg-white border border-gray-200 text-stone-800 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kota / Alamat Tujuan</label>
                        <input type="text" name="tujuan" id="modal_tujuan" placeholder="Contoh: Bandung (Via Tol Cileunyi)" class="w-full bg-white border border-gray-200 text-stone-800 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-sm">
                    </div>
                </div>

                <div class="pt-3 flex space-x-3">
                    <button type="button" onclick="closeEditModal()" class="w-1/3 bg-gray-100 text-gray-600 font-bold py-3 rounded-xl text-xs uppercase tracking-wider hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" name="update_supir" class="w-2/3 bg-emerald-600 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-wider hover:bg-emerald-700 transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // TOGGLE HIDDEN PASSWORD PADA MODAL TAMBAH
        document.getElementById('btn_toggle_modal_pass').addEventListener('click', function() {
            const passwordInput = document.getElementById('modal_password_input');
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            this.classList.toggle('text-emerald-600', isPassword);
            this.classList.toggle('text-gray-400', !isPassword);
        });

        // TOGGLE INTIP PASSWORD PADA TABEL
        document.querySelectorAll('.btn-toggle-table-pass').forEach(button => {
            button.addEventListener('click', function () {
                const container = this.closest('.password-container');
                const passwordSpan = container.querySelector('.masked-password');
                const realPassword = passwordSpan.getAttribute('data-real-password');
                const isVisible = passwordSpan.getAttribute('data-visible') === 'true';

                if (isVisible) {
                    passwordSpan.textContent = '••••••••';
                    passwordSpan.classList.add('tracking-widest', 'text-gray-400');
                    passwordSpan.classList.remove('font-medium', 'text-stone-700');
                    passwordSpan.setAttribute('data-visible', 'false');
                    this.classList.remove('text-emerald-600');
                    this.classList.add('text-gray-400');
                } else {
                    passwordSpan.textContent = realPassword;
                    passwordSpan.classList.remove('tracking-widest', 'text-gray-400');
                    passwordSpan.classList.add('font-medium', 'text-stone-700');
                    passwordSpan.setAttribute('data-visible', 'true');
                    this.classList.remove('text-gray-400');
                    this.classList.add('text-emerald-600');
                }
            });
        });

        // MODAL 1: TAMBAH SUPIR
        const addModal = document.getElementById('addSupirModal');
        const addModalContent = addModal.querySelector('.transform');

        function openAddModal() {
            addModal.classList.remove('hidden');
            setTimeout(() => {
                addModal.classList.remove('opacity-0');
                addModalContent.classList.remove('scale-95');
                addModalContent.classList.add('scale-100');
            }, 20);
        }

        function closeAddModal() {
            addModal.classList.add('opacity-0');
            addModalContent.classList.remove('scale-100');
            addModalContent.classList.add('scale-95');
            setTimeout(() => { addModal.classList.add('hidden'); }, 300);
        }

        // MODAL 2: EDIT STATUS
        const editModal = document.getElementById('editModal');
        const editModalContent = editModal.querySelector('.transform');

        function openEditModal(id, nama, status, kendaraan, tujuan, email) {
            document.getElementById('modal_id_supir').value = id;
            document.getElementById('modal_nama_supir').value = nama;
            document.getElementById('modal_email_supir').value = email; 
            
            const statusRadio = document.querySelector(`input[name="status"][value="${status}"]`);
            if (statusRadio) {
                statusRadio.checked = true;
            } else {
                document.getElementById('status_standby').checked = true;
            }

            document.getElementById('modal_kendaraan').value = kendaraan;
            document.getElementById('modal_tujuan').value = tujuan;

            toggleFormInputs(false);

            editModal.classList.remove('hidden');
            setTimeout(() => {
                editModal.classList.remove('opacity-0');
                editModalContent.classList.remove('scale-95');
                editModalContent.classList.add('scale-100');
            }, 20);
        }

        function closeEditModal() {
            editModal.classList.add('opacity-0');
            editModalContent.classList.remove('scale-100');
            editModalContent.classList.add('scale-95');
            setTimeout(() => { editModal.classList.add('hidden'); }, 300);
        }

        function toggleFormInputs(withAnimation = true) {
            const checkedRadio = document.querySelector('input[name="status"]:checked');
            const statusPilihan = checkedRadio ? checkedRadio.value : 'Standby';
            const wrapperPenugasan = document.getElementById('wrapper_penugasan');
            
            if (statusPilihan === 'On Trip' || statusPilihan === 'Travel') {
                wrapperPenugasan.classList.remove('hidden');
                if (withAnimation) {
                    setTimeout(() => { wrapperPenugasan.classList.remove('opacity-0'); }, 50);
                } else {
                    wrapperPenugasan.classList.remove('opacity-0');
                }
            } else {
                if (withAnimation) {
                    wrapperPenugasan.classList.add('opacity-0');
                    setTimeout(() => { wrapperPenugasan.classList.add('hidden'); }, 300);
                } else {
                    wrapperPenugasan.classList.add('opacity-0');
                    wrapperPenugasan.classList.add('hidden');
                }
                document.getElementById('modal_kendaraan').value = '';
                document.getElementById('modal_tujuan').value = '';
            }
        }

        // JAVASCRIPT KONFIRMASI HAPUS
        function confirmDelete(id, nama) {
            if (confirm(`Apakah kamu yakin ingin menghapus supir "${nama}" secara permanen?`)) {
                window.location.href = `master-supir.php?delete_id=${id}`;
            }
        }

        window.onclick = function(event) {
            if (event.target == editModal) closeEditModal();
            if (event.target == addModal) closeAddModal();
        }
    </script>
</body>
</html>