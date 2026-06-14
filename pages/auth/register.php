<?php
// pages/auth/register.php
session_start();
require_once '../../config/database.php';

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $password = $_POST['password'];

    // Validasi No HP (hanya angka)
    if (!preg_match("/^[0-9]+$/", $nomor_hp)) {
        $error = "Nomor HP hanya boleh berisi angka.";
    } else {
        // Cek apakah Email atau No HP sudah terdaftar
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR nomor_hp = ?");
        $stmt_check->execute([$email, $nomor_hp]);
        
        if ($stmt_check->rowCount() > 0) {
            $error = "Email atau Nomor HP sudah terdaftar di sistem DITRAS.";
        } else {
            // Enkripsi Password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert User Baru (Otomatis role 'pembeli')
            $stmt_insert = $pdo->prepare("INSERT INTO users (nama, email, nomor_hp, password, role) VALUES (?, ?, ?, ?, 'pembeli')");
            
            try {
                $stmt_insert->execute([$nama, $email, $nomor_hp, $hashed_password]);
                $success = "Registrasi berhasil! Silakan Sign In untuk memesan tiket atau armada.";
            } catch (PDOException $e) {
                $error = "Terjadi kesalahan sistem: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Akun | DITRAS Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet" />
    <style>
        h2, .serif { font-family: "Cormorant Garamond", serif; }
        body { font-family: "Montserrat", sans-serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 md:p-12 border border-gray-100 shadow-xl max-w-md w-full relative">
        <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>

        <div class="text-center mb-8">
            <span class="text-[9px] uppercase tracking-[0.5em] text-emerald-600 font-bold block mb-2">Join DITRAS</span>
            <h2 class="text-4xl italic text-gray-800">Registrasi</h2>
            <p class="text-gray-400 text-[10px] uppercase tracking-widest mt-2">Daftar untuk menikmati perjalanan premium</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="bg-red-50 text-red-700 p-4 mb-6 text-[10px] uppercase tracking-wider border-l-2 border-red-500">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="bg-emerald-50 text-emerald-700 p-4 mb-6 text-[10px] uppercase tracking-wider border-l-2 border-emerald-500">
                <?= $success; ?>
            </div>
        <?php else: ?>

        <form action="" method="POST" class="space-y-5">
            <div class="group">
                <label class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-500 transition">Nama Lengkap</label>
                <input type="text" name="nama" required class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm placeholder-gray-300" placeholder="Sesuai KTP" />
            </div>

            <div class="group">
                <label class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-500 transition">Nomor Handphone (Aktif WA)</label>
                <input type="tel" name="nomor_hp" required class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm placeholder-gray-300" placeholder="081234567890" />
            </div>

            <div class="group">
                <label class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-500 transition">Alamat Email</label>
                <input type="email" name="email" required class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm placeholder-gray-300" placeholder="nama@email.com" />
            </div>

            <div class="group">
                <label class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-500 transition">Password Baru</label>
                <input type="password" name="password" required class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm placeholder-gray-300" placeholder="••••••••" />
            </div>

            <div class="pt-4">
                <button type="submit" name="register" class="w-full bg-black text-white py-4 text-[10px] uppercase font-bold tracking-widest hover:bg-emerald-600 transition duration-300">
                    Buat Akun Sekarang
                </button>
            </div>
        </form>

        <?php endif; ?>

        <div class="mt-6 text-center border-t border-gray-100 pt-6">
            <p class="text-gray-400 text-[10px] uppercase tracking-widest">
                Sudah punya akun? 
                <a href="login.php" class="text-emerald-600 font-bold hover:underline ml-1">Sign In</a>
            </p>
        </div>
    </div>

</body>
</html>