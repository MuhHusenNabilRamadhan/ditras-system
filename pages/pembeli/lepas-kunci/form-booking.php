<?php
// pages/pembeli/lepas-kunci/form-booking.php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Lepas Kunci | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style> h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; } body { font-family: "Montserrat", sans-serif; } </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">

    <?php include '../../../components/sidebar.php'; ?>
    <?php include '../../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1">
        <div class="mb-8">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Rental Service</span>
            <h2 class="text-3xl italic text-gray-800">Rental Lepas Kunci</h2>
        </div>

        <div class="max-w-2xl bg-white border border-gray-100 p-8 shadow-sm">
            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="text-[10px] uppercase tracking-widest text-gray-400 mb-2 block font-semibold">Pilih Kendaraan</label>
                    <select class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm">
                        <option>Toyota Innova Reborn - Rp 500.000/hari</option>
                        <option>Honda Brio - Rp 350.000/hari</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] uppercase tracking-widest text-gray-400 mb-2 block font-semibold">Tanggal Ambil</label>
                        <input type="date" class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] uppercase tracking-widest text-gray-400 mb-2 block font-semibold">Durasi (Hari)</label>
                        <input type="number" min="1" class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none text-sm" placeholder="1">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="bg-black text-white px-8 py-4 text-[10px] uppercase font-bold tracking-widest hover:bg-emerald-600 transition">
                        Konfirmasi Booking
                    </button>
                </div>
            </form>
        </div>
    </main>

    <?php include '../../../components/footer.php'; ?>
</body>
</html>