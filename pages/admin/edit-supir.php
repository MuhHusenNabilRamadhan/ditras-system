<?php
session_start();
require_once '../../config/database.php';

// Ambil ID supir dari URL / parameter modal
$id = $_GET['id'] ?? null;
if (!$id) { 
    header("Location: master-supir.php"); 
    exit; 
}

// Ambil data supir gabungan dengan tabel user untuk dapet email loginnya
// (Menyesuaikan dengan relasi id_user di sistem login kamu)
$stmt = $pdo->prepare("
    SELECT s.*, u.email 
    FROM supir_detail s
    LEFT JOIN user u ON s.id_user = u.id_user 
    WHERE s.id_supir = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    die("Data supir tidak ditemukan!");
}

// Proses Update Data
if (isset($_POST['update'])) {
    $status = $_POST['status'];
    $kendaraan = $_POST['kendaraan_bawaan'];
    $tujuan = $_POST['tujuan'];
    $email = $_POST['email'];

    try {
        $pdo->beginTransaction();

        // 1. Update data akun login (Email) di tabel user
        $stmtUser = $pdo->prepare("UPDATE user SET email = ? WHERE id_user = ?");
        $stmtUser->execute([$email, $data['id_user']]);

        // 2. Update data operasional di tabel supir_detail
        $stmtSupir = $pdo->prepare("
            UPDATE supir_detail 
            SET status = ?, kendaraan_bawaan = ?, tujuan = ? 
            WHERE id_supir = ?
        ");
        $stmtSupir->execute([$status, $kendaraan, $tujuan, $id]);

        $pdo->commit();
        header("Location: master-supir.php");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal memperbarui data supir: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manage Status Supir | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>body { font-family: 'Montserrat', sans-serif; }</style>
</head>
<body class="bg-stone-900/50 min-h-screen flex items-center justify-center p-4 animate-fade-in">

    <div class="bg-white w-full max-w-lg p-8 rounded-[32px] shadow-2xl border border-stone-100">
        <div class="mb-6">
            <p class="text-[10px] uppercase tracking-[0.2em] text-[#009663] font-bold mb-1">Driver Management</p>
            <h2 class="text-2xl font-bold tracking-tight text-stone-800">Manage Status & Akun</h2>
        </div>
        
        <form action="" method="POST" class="space-y-5">
            
            <div>
                <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1.5">Email Akun Login</label>
                <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? ''); ?>" required placeholder="contoh: joko@ditras.com" class="w-full p-3.5 bg-stone-50 border border-transparent focus:bg-white focus:border-emerald-500 rounded-xl font-medium outline-none transition shadow-inner">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1.5">Kendaraan Bawaan</label>
                    <input type="text" name="kendaraan_bawaan" value="<?= htmlspecialchars($data['kendaraan_bawaan'] ?? ''); ?>" placeholder="Contoh: Hilace (AB 2000 AD)" class="w-full p-3.5 bg-stone-50 border border-transparent focus:bg-white focus:border-emerald-500 rounded-xl text-sm outline-none transition shadow-inner">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1.5">Kota Tujuan</label>
                    <input type="text" name="tujuan" value="<?= htmlspecialchars($data['tujuan'] ?? ''); ?>" placeholder="Contoh: Jakarta" class="w-full p-3.5 bg-stone-50 border border-transparent focus:bg-white focus:border-emerald-500 rounded-xl text-sm outline-none transition shadow-inner">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2.5">Status Driver Saat Ini</label>
                <div class="grid grid-cols-3 gap-2.5">
                    <?php 
                    $statuses = [
                        'Standby' => ['label' => 'STANDBY', 'color' => 'peer-checked:bg-emerald-600 peer-checked:border-emerald-600'],
                        'On Trip' => ['label' => 'ON TRIP', 'color' => 'peer-checked:bg-blue-600 peer-checked:border-blue-600'],
                        'Travel'  => ['label' => 'TRAVEL', 'color' => 'peer-checked:bg-purple-600 peer-checked:border-purple-600']
                    ];
                    foreach ($statuses as $val => $meta): 
                        $isChecked = (trim($data['status']) === $val) ? 'checked' : '';
                    ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="<?= $val ?>" <?= $isChecked ?> class="peer hidden">
                            <div class="text-center py-3 rounded-xl border border-stone-200 bg-white text-[10px] font-bold text-stone-400 <?= $meta['color'] ?> peer-checked:text-white transition-all duration-200 uppercase tracking-wider">
                                <?= $meta['label'] ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" name="update" class="flex-1 bg-[#009663] text-white py-3.5 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-emerald-700 transition shadow-sm">
                    Simpan Perubahan
                </button>
                <a href="master-supir.php" class="px-6 bg-stone-100 text-stone-500 py-3.5 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-stone-200 transition text-center flex items-center justify-center">
                    Batal
                </a>
            </div>
        </form>
    </div>

</body>
</html>