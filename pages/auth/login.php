<?php
// pages/auth/login.php
session_start();
require_once '../../config/database.php';

$error = '';

if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Ambil data user berdasarkan email menggunakan PDO
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Cek password (Bisa hash dari register, atau teks polos dari dummy data awal)
        $password_match = false;
        
        if (password_verify($password, $user['password'])) {
            $password_match = true;
        } elseif ($password === $user['password']) {
            // Fallback untuk akun dummy admin/supir yang dibuat di awal
            $password_match = true;
        }

        if ($password_match) {
            // Set Session
            $_SESSION['id_user']  = $user['id'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['role']     = $user['role'];

            // Arahkan ke dashboard masing-masing sesuai role
            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] == 'supir') {
                header("Location: ../supir/dashboard.php");
            } else {
                header("Location: ../pembeli/dashboard.php");
            }
            exit;
        } else {
            $error = 'Password yang Anda masukkan salah.';
        }
    } else {
        $error = 'Email tidak terdaftar dalam sistem DITRAS.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In | DITRAS Premium</title>
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

        <div class="text-center mb-10">
            <span class="text-[9px] uppercase tracking-[0.5em] text-emerald-600 font-bold block mb-2">Gate Access</span>
            <h2 class="text-4xl italic text-gray-800">Welcome Back</h2>
            <p class="text-gray-400 text-[10px] uppercase tracking-widest mt-2">Silakan masuk ke akun DITRAS Anda</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="bg-red-50 text-red-700 p-4 mb-6 text-[10px] uppercase tracking-wider border-l-2 border-red-500">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div class="group">
                <label class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-500 transition">Alamat Email</label>
                <input 
                    type="email" 
                    name="email" 
                    required 
                    class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm placeholder-gray-300" 
                    placeholder="nama@email.com"
                />
            </div>

            <div class="group">
                <label class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-500 transition">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm placeholder-gray-300" 
                    placeholder="••••••••"
                />
            </div>

            <div class="pt-4">
                <button 
                    type="submit" 
                    name="login" 
                    class="w-full bg-black text-white py-4 text-[10px] uppercase font-bold tracking-widest hover:bg-emerald-600 transition duration-300"
                >
                    Sign In Account
                </button>
            </div>
        </form>

        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <p class="text-gray-400 text-[10px] uppercase tracking-widest">
                Belum punya akun? 
                <a href="register.php" class="text-emerald-600 font-bold hover:underline ml-1">Daftar Di Sini</a>
            </p>
            <a href="../../index.php" class="inline-block mt-4 text-[9px] uppercase tracking-wider text-gray-400 hover:text-black underline">
                ← Kembali ke Beranda Utama
            </a>
        </div>
    </div>

</body>
</html>