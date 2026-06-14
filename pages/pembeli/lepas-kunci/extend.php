<?php
// pages/pembeli/lepas-kunci/extend.php
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
    <title>Extend Sewa | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style> h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; } body { font-family: "Montserrat", sans-serif; } </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">

    <?php include '../../../components/sidebar.php'; ?>
    <?php include '../../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1">
        <div class="mb-8">
            <span class="text-[10px] uppercase tracking-[0.2em] text-red-600 font-bold block mb-1">Management Service</span>
            <h2 class="text-3xl italic text-gray-800">Extend Waktu Sewa</h2>
            <p class="text-xs text-gray-500 mt-2">Gunakan formulir ini jika Anda membutuhkan tambahan waktu pemakaian unit.</p>
        </div>

        <div class="max-w-xl bg-white border border-gray-100 p-8 shadow-sm">
            <form action="../../../invoice/extend.php" method="GET" class="space-y-6">
                <div>
                    <label class="text-[10px] uppercase tracking-widest text-gray-400 mb-2 block font-semibold">Pilih Transaksi Aktif</label>
                    <select name="id" class="w-full bg-transparent border-b border-gray-200 py-2 outline-none text-sm">
                        <option value="EXT-1102">TRX-7821 | Innova Reborn</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-widest text-gray-400 mb-2 block font-semibold">Tambahan Durasi (Jam)</label>
                    <input type="number" class="w-full bg-transparent border-b border-gray-200 py-2 outline-none text-sm" placeholder="Contoh: 3">
                </div>
                <button type="submit" class="bg-red-700 text-white px-8 py-4 text-[10px] uppercase font-bold tracking-widest hover:bg-red-800 transition">
                    Ajukan Extend & Bayar
                </button>
            </form>
        </div>
    </main>
    <?php include '../../../components/footer.php'; ?>
</body>
</html>