<?php
// pages/admin/lacak-gps.php
session_start();
require_once '../../config/database.php'; 

// Proteksi Halaman Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak GPS Real-Time | Sistem DITRAS</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
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
                accent: '#059669', // Emerald premium DITRAS
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    
    <style>
      h1, h2, h3, h4, .serif { font-family: "Cormorant Garamond", serif; }
      body { font-family: "Montserrat", sans-serif; }
      
      /* Animasi Transisi Konten Utama */
      .fade-in { animation: fadeIn 0.6s ease-out forwards; }
      @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-brand-lightbg text-stone-900 min-h-screen flex flex-col dark:bg-brand-darkbg dark:text-gray-100 transition-colors duration-300">

    <?php include '../../components/sidebar.php'; ?>
    <?php include '../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1 fade-in">
        
        <div class="mb-6">
            <p class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold dark:text-emerald-500">Fleet Monitoring</p>
            <h2 class="text-3xl italic font-semibold text-stone-800 dark:text-gray-100">Lacak GPS Real-Time</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white p-6 rounded-2xl border border-stone-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                    <h3 class="text-xs uppercase tracking-wider font-bold text-stone-400 mb-4 dark:text-zinc-500">Armada Dalam Perjalanan</h3>
                    <div class="space-y-3" id="fleet-list">
                        </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-stone-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                    <h3 class="text-sm font-bold text-stone-800 mb-3 flex items-center gap-2 dark:text-gray-200">
                        <svg class="w-4 h-4 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Perbarui Posisi Armada Terpilih
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-bold text-stone-400 uppercase block mb-1 dark:text-zinc-500">Nama Tempat / Status Jalan</label>
                            <input type="text" id="input-status" placeholder="Contoh: Rest Area KM 57 / Jalur Utama Dieng" class="w-full text-xs p-3 border border-stone-200 rounded-xl focus:outline-none focus:border-brand-accent transition dark:bg-zinc-900 dark:border-zinc-700 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-stone-400 uppercase block mb-1 dark:text-zinc-500">Link Share Google Maps / Koordinat</label>
                            <input type="text" id="input-link" placeholder="Paste link maps supir atau koordinat lat,long" class="w-full text-xs p-3 border border-stone-200 rounded-xl focus:outline-none focus:border-brand-accent transition dark:bg-zinc-900 dark:border-zinc-700 dark:text-gray-100">
                        </div>
                        <button onclick="simpanPerubahanLokasi()" class="w-full bg-brand-accent text-white text-[11px] uppercase font-bold tracking-wider py-3 rounded-xl hover:bg-emerald-700 transition shadow-sm">
                            Terapkan Lokasi Terbaru
                        </button>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-8 flex flex-col gap-4">
                
                <div class="bg-zinc-900 rounded-3xl overflow-hidden shadow-md relative min-h-[480px] flex flex-col border dark:border-zinc-800">
                    <div id="map-placeholder" class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center bg-[radial-gradient(#2c2c2c_1px,transparent_1px)] [background-size:16px_16px]">
                        <div class="w-12 h-12 rounded-full bg-zinc-800 flex items-center justify-center text-brand-accent mb-4 border border-zinc-700">
                            <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h4 class="serif italic text-xl text-white font-medium mb-2">Sistem Pemetaan Satelit</h4>
                        <p class="text-zinc-400 text-xs max-w-sm">Pilih salah satu unit armada di sebelah kiri untuk melihat peta jalur dan posisi koordinat live.</p>
                    </div>
                    
                    <iframe id="live-map-iframe" class="w-full flex-1 border-0 hidden" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

                <div id="status-card" class="bg-white p-5 rounded-2xl border border-stone-100 shadow-sm hidden items-center justify-between dark:bg-brand-carddark dark:border-zinc-800">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-stone-400 font-bold mb-0.5 dark:text-zinc-500">Lokasi Terkini Armada</p>
                        <h4 id="status-judul-unit" class="text-lg font-bold text-stone-800 mb-1 dark:text-gray-200">Nama Armada</h4>
                        <p class="text-xs text-stone-600 flex items-center gap-1 dark:text-zinc-400">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            Posisi Terpantau: <span id="status-lokasi-teks" class="font-semibold text-brand-accent">Menunggu data...</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold bg-stone-100 text-stone-600 px-3 py-1.5 rounded-lg border border-stone-200 dark:bg-zinc-900 dark:text-zinc-400 dark:border-zinc-700">
                            Modul Taktis Aktif
                        </span>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        // Simulasi Array Sementara
        let dataArmada = [
            {
                id: 1,
                nopol: "AA 1234 XY",
                jenis: "Innova Reborn",
                supir: "Supir Pak Budi",
                lokasi: "JALUR DIENG - JALUR UTAMA",
                maps_query: "Dieng, Wonosobo Regency, Central Java"
            },
            {
                id: 2,
                nopol: "B 9999 ZZ",
                jenis: "HiAce Commuter",
                supir: "Supir Pak Joko",
                lokasi: "REST AREA KM 57",
                maps_query: "-6.371903, 107.329241"
            }
        ];

        let idArmadaAktif = null;

        function renderFleetList() {
            const listContainer = document.getElementById('fleet-list');
            listContainer.innerHTML = '';

            dataArmada.forEach(item => {
                const isActive = item.id === idArmadaAktif;
                const cardClass = isActive 
                    ? 'border-2 border-emerald-500 bg-emerald-50/40 p-4 rounded-xl cursor-pointer transition-all dark:bg-emerald-950/20'
                    : 'border border-stone-100 hover:border-stone-300 p-4 rounded-xl cursor-pointer transition-all dark:border-zinc-800 dark:hover:border-zinc-700';
                
                listContainer.innerHTML += `
                    <div class="${cardClass}" onclick="pilihArmada(${item.id})">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-bold text-stone-800 dark:text-gray-200">${item.nopol} <span class="font-normal text-stone-500 dark:text-zinc-400">(${item.jenis})</span></h4>
                                <p class="text-[11px] text-stone-400 mt-0.5 dark:text-zinc-500">${item.supir}</p>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-t border-dashed border-stone-200 dark:border-zinc-700 flex items-center gap-1.5 text-[10px] text-emerald-600 font-bold uppercase tracking-wider dark:text-emerald-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            ${item.lokasi}
                        </div>
                    </div>
                `;
            });
        }

        function pilihArmada(id) {
            idArmadaAktif = id;
            renderFleetList();

            const unit = dataArmada.find(x => x.id === id);
            
            document.getElementById('input-status').value = unit.lokasi;
            document.getElementById('input-link').value = unit.maps_query;

            updateTampilanPeta(unit);
        }

        function dapatkanEmbedUrl(inputString) {
            if(!inputString) return "https://maps.google.com/maps?q=Dieng&output=embed";
            let query = encodeURIComponent(inputString);
            return `https://maps.google.com/maps?q=${query}&t=&z=14&ie=UTF8&iwloc=&output=embed`;
        }

        function updateTampilanPeta(unit) {
            const placeholder = document.getElementById('map-placeholder');
            const iframe = document.getElementById('live-map-iframe');
            const statusCard = document.getElementById('status-card');

            placeholder.classList.add('hidden');
            iframe.classList.remove('hidden');
            statusCard.classList.remove('hidden');
            statusCard.classList.add('flex');

            iframe.src = dapatkanEmbedUrl(unit.maps_query);

            document.getElementById('status-judul-unit').innerText = `${unit.nopol} (${unit.jenis})`;
            document.getElementById('status-lokasi-teks').innerText = unit.lokasi;
        }

        function simpanPerubahanLokasi() {
            if (!idArmadaAktif) {
                alert("Silakan pilih armada terlebih dahulu di panel kiri, Bro!");
                return;
            }

            const lokasiBaru = document.getElementById('input-status').value.trim();
            const linkBaru = document.getElementById('input-link').value.trim();

            if (!lokasiBaru || !linkBaru) {
                alert("Semua field wajib diisi!");
                return;
            }

            const index = dataArmada.findIndex(x => x.id === idArmadaAktif);
            dataArmada[index].lokasi = lokasiBaru.toUpperCase();
            dataArmada[index].maps_query = linkBaru;

            renderFleetList();
            updateTampilanPeta(dataArmada[index]);
            console.log("Data sukses diperbarui.");
        }

        // Jalankan list saat halaman terbuka
        renderFleetList();
    </script>
</body>
</html>