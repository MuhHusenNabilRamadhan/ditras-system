<?php
session_start();
require_once '../../config/database.php';

if (isset($_POST['simpan'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_supir']);
    $email  = mysqli_real_escape_string($conn, $_POST['email_supir']); // Menambahkan input email
    $telp   = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $pass   = mysqli_real_escape_string($conn, $_POST['password_supir']); // Menambahkan input password (Tanpa username)
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $status = mysqli_real_escape_string($conn, $_POST['status_supir']);

    // Query INSERT disesuaikan dengan kolom di database (menambahkan email_supir dan password)
    // Pastikan nama kolom di database sesuai (misal: email_supir, password)
    $query = "INSERT INTO supir (nama_supir, email_supir, no_telp, password, rating, status_supir) 
              VALUES ('$nama', '$email', '$telp', '$pass', '$rating', '$status')";

    if (mysqli_query($conn, $query)) {
        header("Location: master-supir.php");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Supir | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#faf9f6] p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-xl border border-stone-100 shadow-sm">
        <h2 class="text-2xl font-bold mb-6 text-stone-800">Tambah Pengemudi Baru</h2>
        
        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase mb-1">Nama Pengemudi</label>
                <input type="text" name="nama_supir" required class="w-full p-3 border rounded-lg" placeholder="Nama lengkap supir">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase mb-1">Email Login Supir</label>
                <input type="email" name="email_supir" required class="w-full p-3 border rounded-lg" placeholder="contoh: supir@ditras.com">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase mb-1">Nomor Telepon / WA</label>
                <input type="text" name="no_telp" placeholder="Contoh: 0812345678" required class="w-full p-3 border rounded-lg">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase mb-1">Password Akun</label>
                <input type="password" name="password_supir" placeholder="Masukkan password akun supir" required class="w-full p-3 border rounded-lg">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase mb-1">Rating Awal</label>
                <input type="number" step="0.01" name="rating" value="5.00" required class="w-full p-3 border rounded-lg">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase mb-1">Status Supir</label>
                <select name="status_supir" class="w-full p-3 border rounded-lg bg-white">
                    <option value="idle">IDLE (TERSEDIA)</option>
                    <option value="jalan">JALAN (BERTUGAS)</option>
                    <option value="off">OFF</option>
                </select>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" name="simpan" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg text-xs font-bold uppercase transition-colors">
                    Simpan
                </button>
                <a href="master-supir.php" class="bg-stone-100 hover:bg-stone-200 text-stone-600 px-6 py-3 rounded-lg text-xs font-bold uppercase text-center transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</body>
</html>