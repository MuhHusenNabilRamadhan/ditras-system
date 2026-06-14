<?php
session_start();
require_once '../../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: master-mobil.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM mobil WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (isset($_POST['update'])) {
    $sql = "UPDATE mobil SET plat_nomor = ?, merk = ?, harga_sewa_per_hari = ?, status_mobil = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['plat_nomor'], $_POST['merk'], $_POST['harga_sewa_per_hari'], $_POST['status_mobil'], $id]);
    header("Location: master-mobil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Armada | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-900/50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-lg p-8 rounded-3xl shadow-2xl">
        <h2 class="text-2xl font-bold mb-6 text-stone-800">Ubah Data & Armada</h2>
        
        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-[10px] font-bold text-stone-400 uppercase mb-1">Plat Nomor</label>
                <input type="text" name="plat_nomor" value="<?= htmlspecialchars($data['plat_nomor']); ?>" required class="w-full p-3 bg-stone-50 border border-stone-100 rounded-xl font-bold uppercase">
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-stone-400 uppercase mb-1">Merek & Tipe</label>
                <input type="text" name="merk" value="<?= htmlspecialchars($data['merk']); ?>" required class="w-full p-3 bg-stone-50 border border-stone-100 rounded-xl">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-stone-400 uppercase mb-1">Harga Sewa / Hari</label>
                <input type="number" name="harga_sewa_per_hari" value="<?= htmlspecialchars($data['harga_sewa_per_hari']); ?>" required class="w-full p-3 bg-stone-50 border border-stone-100 rounded-xl">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-stone-400 uppercase mb-2">Status Operasional</label>
                <div class="grid grid-cols-3 gap-2">
                    <?php 
                    $statuses = ['tersedia' => 'TERSEDIA', 'jalan' => 'JALAN', 'maintenance' => 'MAINTENANCE'];
                    foreach ($statuses as $val => $label): 
                        $isChecked = ($data['status_mobil'] == $val) ? 'checked' : '';
                    ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="status_mobil" value="<?= $val ?>" <?= $isChecked ?> class="peer hidden">
                            <div class="text-center p-3 rounded-xl border border-stone-200 text-[10px] font-bold text-stone-400 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 transition-all">
                                <?= $label ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" name="update" class="flex-1 bg-emerald-600 text-white py-3 rounded-xl text-xs font-bold uppercase hover:bg-emerald-700">Update</button>
                <a href="master-mobil.php" class="px-6 bg-stone-100 text-stone-600 py-3 rounded-xl text-xs font-bold uppercase hover:bg-stone-200">Batal</a>
            </div>
        </form>
    </div>

</body>
</html>