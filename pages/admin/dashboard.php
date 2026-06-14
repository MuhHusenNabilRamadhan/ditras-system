<?php
// pages/admin/dashboard.php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Menghubungkan variabel $conn bawaan kode lama dengan variabel $pdo dari database.php
$conn = $pdo; 

// Proteksi Halaman Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    // ====================================================================
    // 1. HITUNG REAL-TIME TOTAL PENDAPATAN & TRANSAKSI SELESAI
    // ====================================================================
    $query_stats = "
        SELECT 
            SUM(CASE WHEN status_pembayaran = 'selesai' THEN total_harga ELSE 0 END) AS total_pendapatan,
            COUNT(CASE WHEN status_pembayaran = 'selesai' THEN 1 END) AS total_transaksi_selesai
        FROM transaksi
    ";
    $stats = $conn->query($query_stats)->fetch(PDO::FETCH_ASSOC);
    $total_revenue = $stats['total_pendapatan'] ?? 0;
    $total_orders = $stats['total_transaksi_selesai'] ?? 0;

    // ====================================================================
    // 2. HITUNG STATUS ARMADA AKTIF VS STANDBY VS BENGKEL
    // ====================================================================
    $query_armada = "
        SELECT 
            COUNT(CASE WHEN status_mobil = 'jalan' THEN 1 END) AS aktif,
            COUNT(CASE WHEN status_mobil = 'tersedia' THEN 1 END) AS standby,
            COUNT(CASE WHEN status_mobil = 'bengkel' THEN 1 END) AS di_bengkel
        FROM mobil
    ";
    $armada_status = $conn->query($query_armada)->fetch(PDO::FETCH_ASSOC);
    $armada_aktif = $armada_status['aktif'] ?? 0;
    $armada_standby = $armada_status['standby'] ?? 0;
    $armada_bengkel = $armada_status['di_bengkel'] ?? 0;
    $total_armada = $armada_aktif + $armada_standby + $armada_bengkel;
    
    $persen_aktif = $total_armada > 0 ? round(($armada_aktif / $total_armada) * 100) : 0;

    // AMBIL LIST DETAIL MOBIL YANG STATUSNYA BENGKEL SECARA REAL-TIME
    $query_detail_bengkel = "
        SELECT merk, plat_nomor 
        FROM mobil 
        WHERE status_mobil = 'bengkel'
        ORDER BY id DESC
    ";
    $mobil_bengkel_list = $conn->query($query_detail_bengkel)->fetchAll(PDO::FETCH_ASSOC);

    // ====================================================================
    // 3. DATA RUTE, SUPIR, DAN KETERSEDIAAN KURSI
    // ====================================================================
    $query_rute_kursi = "
        SELECT 
            sd.tujuan AS rute_lintasan,
            u.nama AS nama_supir,
            m.merk AS armada,
            m.jumlah_kursi AS total_kursi
        FROM supir_detail sd
        JOIN users u ON sd.supir_id = u.id
        LEFT JOIN mobil m ON sd.kendaraan_bawaan = m.merk
        WHERE sd.status = 'Travel' AND sd.tujuan IS NOT NULL
        ORDER BY sd.tujuan ASC
    ";
    $data_rute_kursi = $conn->query($query_rute_kursi)->fetchAll(PDO::FETCH_ASSOC);

    // ====================================================================
    // 4. DATA GRAFIK OMSET LAYANAN 7 HARI TERAKHIR
    // ====================================================================
    $labels_omset = [];
    $data_omset = [];
    
    $kolom_tanggal = 'tanggal'; 
    try {
        $check_column = $conn->query("SHOW COLUMNS FROM transaksi LIKE 'tanggal'")->fetch();
        if (!$check_column) {
            $check_alt = $conn->query("SHOW COLUMNS FROM transaksi LIKE 'tanggal_transaksi'")->fetch();
            if ($check_alt) {
                $kolom_tanggal = 'tanggal_transaksi';
            }
        }
    } catch (Exception $e) {
        $kolom_tanggal = 'tanggal';
    }

    for ($i = 6; $i >= 0; $i--) {
        $tgl = date('Y-m-d', strtotime("-$i days"));
        $labels_omset[] = date('d M', strtotime($tgl));
        
        try {
            $q_omset_tgl = "
                SELECT COALESCE(SUM(total_harga), 0) AS omset_harian 
                FROM transaksi 
                WHERE DATE($kolom_tanggal) = :tgl AND status_pembayaran = 'selesai'
            ";
            $stmt_omset = $conn->prepare($q_omset_tgl);
            $stmt_omset->execute(['tgl' => $tgl]);
            $res_omset = $stmt_omset->fetch(PDO::FETCH_ASSOC);
            $data_omset[] = (int)$res_omset['omset_harian'];
        } catch (PDOException $e) {
            $data_omset[] = 0; 
        }
    }

} catch (PDOException $e) {
    die("Gagal memuat visualisasi data dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Kendali Admin | DITRAS</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              brand: {
                lightbg: '#faf9f6',
                darkbg: '#121212',
                cardlight: '#ffffff',
                carddark: '#1c1c1e',
                accent: '#059669', 
                blue: '#3b82f6'
              }
            },
            fontFamily: {
              serif: ['Cormorant Garamond', 'serif'],
              sans: ['Montserrat', 'sans-serif'],
            }
          }
        }
      }
    </script>
    
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    
    <style>
      h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; }
      body { font-family: "Montserrat", sans-serif; }
      .fade-in { animation: fadeIn 0.6s ease-out forwards; }
      @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      .slide-in-right { animation: slideIn 1.2s cubic-bezier(0.19, 1, 0.22, 1) forwards; }
      @keyframes slideIn { from { opacity: 0; transform: translateX(60px); } to { opacity: 1; transform: translateX(0); } }
    </style>
</head>
<body class="bg-brand-lightbg text-stone-900 min-h-screen flex flex-col dark:bg-brand-darkbg dark:text-gray-100 transition-colors duration-300">

    <?php include '../../components/sidebar.php'; ?>
    <?php include '../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1 fade-in">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <p class="text-[12px] uppercase tracking-[0.2em] text-brand-accent font-bold">Monitor Operasional DITRAS</p>
                <h2 class="text-3xl italic font-semibold">Melayani 25 jam sek 1 jam nggo umbah-umbah</h2>
            </div>
            
            <button id="theme-toggle" class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 dark:bg-brand-carddark dark:border-zinc-800 text-brand-accent transition-all duration-300">
                <svg id="theme-icon-sun" class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <svg id="theme-icon-moon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- TOTAL PENDAPATAN REAL-TIME -->
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Total Pendapatan</p>
                <h3 class="text-3xl font-bold tracking-tight mb-2" id="count-revenue">Rp 0</h3>
                <span class="text-xs text-brand-accent bg-emerald-50 px-2 py-1 rounded-md dark:bg-emerald-950/50">Dinamis ↑ <span class="text-gray-400 font-normal">Sistem Live Terkoneksi</span></span>
            </div>
            
            <!-- TOTAL TRANSAKSI SELESAI -->
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Total Transaksi Selesai</p>
                <h3 class="text-3xl font-bold tracking-tight mb-2" id="count-orders">0</h3>
                <span class="text-xs text-brand-accent bg-emerald-50 px-2 py-1 rounded-md dark:bg-emerald-950/50">100% Valid ↑ <span class="text-gray-400 font-normal">Dari Garasi Utama</span></span>
            </div>
            
            <!-- PERSENTASE ARMADA AKTIF -->
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800 flex items-center justify-between">
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Efisiensi Bulanan</p>
                    <h3 class="text-2xl font-bold mb-1"><?= $persen_aktif ?>% Armada Aktif</h3>
                    <p class="text-xs text-gray-400">Jalan: <?= $armada_aktif ?> | Standby: <?= $armada_standby ?></p>
                </div>
                <div class="w-24 h-24 relative">
                    <canvas id="chartSemiPie"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-lg font-semibold font-serif italic">Grafik Omset Layanan (Real-Time)</h4>
                    <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded dark:bg-emerald-950">Titik Bergerak Aktif</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="chartGaris"></canvas>
                </div>
            </div>

            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <h4 class="text-lg font-semibold font-serif italic mb-4">Volume Pesanan Mingguan</h4>
                <div class="h-64 relative">
                    <canvas id="chartBatang"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- KARTU INFORMASI RUTE & KETERSEDIAAN KURSI -->
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col h-full dark:bg-brand-carddark dark:border-zinc-800">
                <div class="mb-4">
                    <h4 class="text-lg font-semibold font-serif italic text-slate-800 dark:text-gray-100">Status Rute & Kursi Terjadwal</h4>
                    <p class="text-xs text-gray-400">Daftar armada travel aktif beserta kapasitas kursi bawaan.</p>
                </div>
                
                <div class="flex-1 overflow-y-auto max-h-[220px] pr-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-zinc-800 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                <th class="pb-3">Rute / Lintasan</th>
                                <th class="pb-3">Penanggung Jawab</th>
                                <th class="pb-3 text-center">Kursi</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-gray-50 dark:divide-zinc-800/50">
                            <?php if (empty($data_rute_kursi)): ?>
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-gray-400 italic">Tidak ada rute travel yang berjalan hari ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data_rute_kursi as $rute): ?>
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                        <td class="py-3 font-semibold text-slate-700 dark:text-gray-200">
                                            <?= htmlspecialchars($rute['rute_lintasan']) ?>
                                            <span class="block text-[10px] font-normal text-gray-400 mt-0.5">
                                                <?= htmlspecialchars($rute['armada'] ?? 'Belum Set Armada') ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400 align-middle"><?= htmlspecialchars($rute['nama_supir']) ?></td>
                                        <td class="py-3 text-center align-middle">
                                            <span class="inline-block px-2 py-1 font-bold text-emerald-700 bg-emerald-50 rounded-md dark:bg-emerald-950/50 dark:text-emerald-400">
                                                <?= intval($rute['total_kursi'] ?? 0) ?> Sits
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PENYERAPAN DANA PEMASARAN -->
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800 slide-in-right">
                <h4 class="text-lg font-semibold font-serif italic mb-4">Penyerapan Dana Pemasaran</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs mb-1"><span>Iklan Google Digital Travel</span><span class="font-bold">69%</span></div>
                        <div class="w-full bg-gray-100 h-2 rounded-full dark:bg-zinc-700"><div class="bg-brand-accent h-full rounded-full transition-all duration-1000 ease-out" id="bar-dana-1" style="width: 0%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1"><span>Promosi Media Sosial</span><span class="font-bold">78%</span></div>
                        <div class="w-full bg-gray-100 h-2 rounded-full dark:bg-zinc-700"><div class="bg-brand-blue h-full rounded-full transition-all duration-1000 ease-out" id="bar-dana-2" style="width: 0%"></div></div>
                    </div>
                </div>
            </div>

            <!-- STATUS LOGISTIK SISTEM (REAL-TIME DARI MASTER MOBIL STATUS BENGKEL) -->
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800 flex flex-col justify-between slide-in-right">
                <div>
                    <span class="text-[10px] text-red-500 font-bold uppercase tracking-wider block mb-2">Status Logistik Sistem</span>
                    
                    <?php if ($armada_bengkel > 0): ?>
                        <div class="flex items-start gap-3 bg-red-50 p-3 rounded-xl dark:bg-red-950/30">
                            <div class="p-2 bg-red-500 text-white rounded-lg shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-red-700 dark:text-red-400">Armada Butuh Perawatan (<?= $armada_bengkel ?> Unit)</p>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 max-h-[110px] overflow-y-auto space-y-0.5 pr-1">
                                    <?php foreach ($mobil_bengkel_list as $mb): ?>
                                        <span class="block">• <?= htmlspecialchars($mb['merk']) ?> <b class="text-stone-700 dark:text-stone-300">[<?= htmlspecialchars($mb['plat_nomor']) ?>]</b> sedang perbaikan.</span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-3 bg-emerald-50 p-3 rounded-xl dark:bg-emerald-950/30">
                            <div class="p-2 bg-emerald-500 text-white rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400">Semua Armada Prima</p>
                                <p class="text-[10px] text-gray-400">100% unit siap jalan & tidak ada kendaraan di bengkel hari ini.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <a href="../admin/master-mobil.php" class="w-full text-center bg-brand-blue text-white text-[10px] uppercase font-bold tracking-widest py-3 rounded-xl mt-4 hover:bg-blue-600 transition block">
                    Lihat Seluruh Log Perawatan
                </a>
            </div>
            
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        // 1. ANIMASI ANGKA COUNTER
        function animateCounter(id, target, isCurrency = false) {
            let current = 0;
            const duration = 1200;
            const stepTime = 20;
            const steps = duration / stepTime;
            const increment = target / steps;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                document.getElementById(id).innerText = isCurrency 
                    ? 'Rp ' + Math.floor(current).toLocaleString('id-ID') 
                    : Math.floor(current).toLocaleString('id-ID');
            }, stepTime);
        }

        animateCounter('count-revenue', <?= $total_revenue ?>, true);
        animateCounter('count-orders', <?= $total_orders ?>, false);

        setTimeout(() => {
            document.getElementById('bar-dana-1').style.width = '69%';
            document.getElementById('bar-dana-2').style.width = '78%';
        }, 300);

        // 2. DIAGRAM GARIS OMSET REAL-TIME 7 HARI TERAKHIR
        const targetLabelsGaris = <?= json_encode($labels_omset) ?>;
        const targetDataGaris   = <?= json_encode($data_omset) ?>;

        const ctxGaris = document.getElementById('chartGaris').getContext('2d');
        const gradienGaris = ctxGaris.createLinearGradient(0, 0, 0, 250);
        gradienGaris.addColorStop(0, 'rgba(5, 150, 105, 0.4)');
        gradienGaris.addColorStop(1, 'rgba(5, 150, 105, 0)');

        const chartGaris = new Chart(ctxGaris, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Omset Harian',
                    data: [],
                    borderColor: '#059669',
                    borderWidth: 4,
                    fill: true,
                    backgroundColor: gradienGaris,
                    tension: 0.3,
                    pointRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#059669',
                    pointBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                animation: { duration: 450, easing: 'easeOutQuad' },
                scales: {
                    y: { 
                        min: 0, 
                        suggestedMax: 5000000, 
                        grid: { display: false }, 
                        ticks: { font: { size: 10 }, callback: function(value) { return 'Rp ' + value.toLocaleString('id-ID'); } } 
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        let dataIndex = 0;
        function renderLineProgressive() {
            if (dataIndex < targetDataGaris.length) {
                chartGaris.data.labels.push(targetLabelsGaris[dataIndex]);
                chartGaris.data.datasets[0].data.push(targetDataGaris[dataIndex]);
                chartGaris.update();
                dataIndex++;
                setTimeout(renderLineProgressive, 250);
            }
        }
        setTimeout(renderLineProgressive, 500);

        // 3. DIAGRAM BATANG VOLUME MINGGUAN
        const chartBatang = new Chart(document.getElementById('chartBatang'), {
            type: 'bar',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [
                    { label: 'Travel', data: [45, 35, 55, 30, 70, 85, 90], backgroundColor: '#059669', borderRadius: 6 },
                    { label: 'Rental', data: [20, 25, 30, 45, 50, 65, 60], backgroundColor: '#3b82f6', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, labels: { font: { size: 10 } } } },
                scales: {
                    y: { max: 100, grid: { display: false }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        // 4. DIAGRAM EFISIENSI SEMI PIE (MENYERAP DATA BENGKEL)
        new Chart(document.getElementById('chartSemiPie'), {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Standby', 'Bengkel'],
                datasets: [{ 
                    data: [<?= $armada_aktif ?>, <?= $armada_standby ?>, <?= $armada_bengkel ?>], 
                    backgroundColor: ['#059669', '#3b82f6', '#ef4444'], 
                    borderWidth: 0 
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                rotation: -90, circumference: 180, cutout: '75%',
                animation: { animateScale: true, duration: 2000, easing: 'easeOutBack' }
            }
        });

        // TEMA SWITCHER LOGIC
        const themeToggle = document.getElementById('theme-toggle');
        const themeIconSun = document.getElementById('theme-icon-sun');
        const themeIconMoon = document.getElementById('theme-icon-moon');
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.classList.add(currentTheme);
        if (currentTheme === 'dark') {
            themeIconSun.classList.add('hidden');
            themeIconMoon.classList.remove('hidden');
        }
        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            themeIconSun.classList.toggle('hidden');
            themeIconMoon.classList.toggle('hidden');
            let theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
            setTimeout(() => { location.reload(); }, 150);
        });
    </script>
</body>
</html>