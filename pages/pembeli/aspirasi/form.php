<?php
// pages/pembeli/aspirasi/form.php
session_start();
require_once '../../../config/database.php';

// Proteksi Halaman: Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$success_msg = '';
$error_msg = '';

// Proses Simpan Aspirasi Rute Baru
if (isset($_POST['kirim_aspirasi'])) {
    $pembeli_id       = $_SESSION['id_user']; // ID pembeli diambil dari session
    $rute_usulan      = trim($_POST['rute_usulan']);
    $alasan_usulan    = trim($_POST['alasan_usulan']);
    $tanggal_potensial = $_POST['tanggal_potensial']; // Ambil input tanggal potensial

    if (!empty($rute_usulan) && !empty($alasan_usulan) && !empty($tanggal_potensial)) {
        try {
            // Query disesuaikan dengan nama kolom tabel database kamu
            $stmt = $pdo->prepare("INSERT INTO aspirasi_rute (pembeli_id, rute_usulan, tanggal_potensial, alasan_usulan, status_aspirasi) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$pembeli_id, $rute_usulan, $tanggal_potensial, $alasan_usulan]);
            
            $success_msg = "Terima kasih! Usulan rute Anda telah berhasil dikirim ke tim manajemen DITRAS.";
        } catch (PDOException $e) {
            $error_msg = "Gagal menyimpan ke database: " . $e->getMessage();
        }
    } else {
        $error_msg = "Harap isi semua kolom form usulan rute.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usulkan Rute Baru | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style>
        h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; }
        body { font-family: "Montserrat", sans-serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">

    <?php include '../../../components/sidebar.php'; ?>
    <?php include '../../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1">
        
        <div class="mb-8">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Crowdsourcing Feature</span>
            <h2 class="text-3xl italic text-gray-800">Usulan Rute Baru</h2>
            <p class="text-gray-400 text-xs mt-1">Punya ide rute travel eksklusif? Tuliskan aspirasi Anda di bawah ini.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 bg-white border border-gray-100 p-8 shadow-sm relative">
                <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>

                <?php if (!empty($success_msg)) : ?>
                    <div class="bg-emerald-50 text-emerald-800 p-4 mb-6 text-xs border-l-2 border-emerald-500 tracking-wide">
                        ✨ <?= $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)) : ?>
                    <div class="bg-amber-50 text-amber-800 p-4 mb-6 text-xs border-l-2 border-amber-500 tracking-wide">
                        ⚠️ <?= $error_msg; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label class="text-[10px] uppercase tracking-widest text-gray-400 mb-2 block font-semibold">Nama Pengusul</label>
                        <input 
                            type="text" 
                            disabled 
                            value="<?= htmlspecialchars($_SESSION['nama'] ?? 'Pembeli DITRAS'); ?>" 
                            class="w-full bg-gray-50 border-b border-gray-200 py-2 text-sm text-gray-500 cursor-not-allowed outline-none"
                        />
                    </div>

                    <div class="group">
                        <label class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-500 transition">Rute yang Diusulkan</label>
                        <input 
                            type="text" 
                            name="rute_usulan" 
                            required 
                            placeholder="Contoh: Wonosobo - Magelang via Kaliwiro"
                            class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm placeholder-gray-300 font-medium"
                        />
                    </div>

                    <div class="group">
                        <label class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-500 transition">Tanggal Potensial Rute Digunakan</label>
                        <input 
                            type="date" 
                            name="tanggal_potensial" 
                            required 
                            class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm text-gray-600 font-medium"
                        />
                    </div>

                    <div class="group">
                        <label class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-500 transition">Alasan & Urgensi Rute</label>
                        <textarea 
                            name="alasan_usulan" 
                            rows="4" 
                            required 
                            placeholder="Ceritakan mengapa rute ini penting atau seberapa sering Anda akan melewati jalur ini..."
                            class="w-full bg-transparent border-b border-gray-200 py-2 focus:border-emerald-500 outline-none transition text-sm placeholder-gray-300 resize-none"
                        ></textarea>
                    </div>

                    <div class="pt-4">
                        <button 
                            type="submit" 
                            name="kirim_aspirasi" 
                            class="bg-black text-white px-8 py-4 text-[10px] uppercase font-bold tracking-widest hover:bg-emerald-600 transition duration-300"
                        >
                            Kirim Usulan Rute
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-stone-900 text-white p-8 shadow-md flex flex-col justify-between relative overflow-hidden h-fit">
                <div class="absolute inset-0 opacity-5 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px]"></div>
                
                <div class="z-10">
                    <span class="text-[9px] uppercase tracking-[0.3em] text-emerald-400 font-bold block mb-4">💡 Info Sistem</span>
                    <h3 class="text-2xl italic font-serif text-stone-200 mb-4 leading-snug">Bagaimana usulan Anda diproses?</h3>
                    <p class="text-stone-400 text-xs leading-relaxed mb-4">
                        Every single route choice matter! Aspirasi rute Anda akan langsung tampil secara real-time di halaman manajemen admin utama.
                    </p>
                </div>
                
                <div class="border-t border-stone-800 pt-6 mt-8 z-10 text-[9px] uppercase tracking-wider text-stone-500">
                    DITRAS Premium Travel System v1.0
                </div>
            </div>

        </div>

    </main>

    <?php include '../../../components/footer.php'; ?>

</body>
</html>