<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DITRAS | Dieng Trans Sejahtera</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

    <link
      href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600&display=swap"
      rel="stylesheet"
    />

    <style>
      h1,
      h2,
      h3,
      .serif {
        font-family: "Cormorant Garamond", serif;
      }
      body {
        font-family: "Montserrat", sans-serif;
        scroll-behavior: smooth;
      }

      /* Animasi Garis Bawah Navbar */
      .nav-link::after {
        content: "";
        display: block;
        width: 0;
        height: 1px;
        background: #10b981;
        transition: width 0.3s;
      }
      .nav-link:hover::after {
        width: 100%;
      }

      /* Custom Scrollbar */
      ::-webkit-scrollbar {
        width: 8px;
      }
      ::-webkit-scrollbar-track {
        background: #faf9f6;
      }
      ::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
      }
      ::-webkit-scrollbar-thumb:hover {
        background: #10b981;
      }
    </style>
  </head>
  <body class="bg-[#faf9f6] text-gray-900 overflow-x-hidden">
    
    <nav
      class="flex justify-between items-center px-6 md:px-16 py-6 bg-white border-b border-gray-100 sticky top-0 z-[100]"
    >
      <div class="flex items-center gap-3 cursor-pointer" onclick="window.location.href = 'index.php'">
        <svg class="w-9 h-9 text-emerald-500" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 75L40 30L58 55L70 40L90 75H15Z" fill="url(#gradientDieng)" opacity="0.15"/>
            <path d="M10 80C30 80 45 60 65 40C75 30 88 35 95 45" stroke="url(#gradientRoute)" stroke-width="6" stroke-linecap="round"/>
            <path d="M82 25L95 45L72 48" stroke="#10B981" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="65" cy="40" r="4" fill="#EF4444"/>
            <defs>
                <linearGradient id="gradientDieng" x1="15" y1="75" x2="90" y2="30" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#10b981"/>
                    <stop offset="100%" stop-color="#06b6d4"/>
                </linearGradient>
                <linearGradient id="gradientRoute" x1="10" y1="80" x2="95" y2="45" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#1e3a8a"/>
                    <stop offset="50%" stop-color="#3b82f6"/>
                    <stop offset="100%" stop-color="#10b981"/>
                </linearGradient>
            </defs>
        </svg>
        <div class="flex flex-col">
            <span class="text-xl font-black tracking-wider text-gray-800 uppercase leading-none">DI<span class="text-emerald-500">TRAS</span></span>
            <span class="text-[8px] font-medium tracking-[0.15em] text-gray-400 uppercase mt-0.5">Dieng Trans Sejahtera</span>
        </div>
      </div>

      <div
        class="hidden md:flex space-x-10 text-[10px] uppercase tracking-[0.3em] font-semibold text-gray-500 items-center"
      >
        <a href="#services" class="nav-link hover:text-emerald-600 transition">Layanan</a>
        <a href="#about" class="nav-link hover:text-emerald-600 transition">Tentang Kami</a>
        <a href="#booking" class="nav-link hover:text-emerald-600 transition">Cek Ketersediaan</a>
        <a href="pages/auth/login.php" class="nav-link hover:text-emerald-600 transition font-bold text-emerald-500">Sign In</a>
      </div>

      <button
        onclick="scrollToSection('booking')"
        class="bg-black text-white px-6 py-2 text-[10px] uppercase font-bold hover:bg-emerald-600 transition duration-300"
      >
        Pesan Sekarang
      </button>
    </nav>

    <header
      class="relative h-[85vh] flex items-center justify-center overflow-hidden"
    >
      <img
        src="https://asset.kompas.com/crops/nZ4d9g3IT3_BkpGyL91Hax_BJy0=/0x0:1800x1200/750x500/data/photo/2022/07/30/62e53356296a4.jpg"
        alt="Dieng Mountain Road"
        class="absolute inset-0 w-full h-full object-cover brightness-[0.4]"
        data-aos="zoom-out"
        data-aos-duration="3000"
      />

      <div
        class="relative text-center text-white px-4"
        data-aos="fade-up"
        data-aos-delay="500"
      >
        <span class="text-[10px] uppercase tracking-[0.6em] mb-4 block"
          >Wonosobo • Dieng • Yogyakarta • Semarang • Purwokerto</span
        >
        <h1 class="text-6xl md:text-7xl mb-6 italic leading-none">
          DIENG TRANS SEJAHTERA
        </h1>
        <p
          class="text-xs md:text-sm uppercase tracking-[0.4em] font-light mb-10 max-w-xl mx-auto leading-relaxed"
        >
          Menghadirkan harmoni kenyamanan berkendara dan keamanan logistik ke setiap perjalanan Anda.
        </p>
        <a
          href="#services"
          class="inline-block border border-white px-10 py-4 text-[10px] uppercase tracking-widest hover:bg-white hover:text-black transition duration-500"
        >
          Lihat Layanan Utama
        </a>
      </div>
    </header>

    <section id="services" class="py-24 px-6 md:px-20 max-w-7xl mx-auto text-center">
        <span class="text-emerald-600 font-bold uppercase tracking-widest text-[10px]" data-aos="fade-up">Layanan Kami</span>
        <h2 class="text-5xl mt-2 mb-16 italic" data-aos="fade-up" data-aos-delay="100">3 Pilar Kenyamanan DITRAS</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
            <div class="bg-white p-8 border border-gray-100 shadow-sm" data-aos="fade-up" data-aos-delay="200">
                <div class="text-3xl mb-4 text-emerald-500">01</div>
                <h3 class="text-2xl font-bold mb-3 italic">Tiket Travel Reguler</h3>
                <p class="text-gray-500 text-xs leading-relaxed">Perjalanan terjadwal antar kota dengan armada prima berkapasitas maksimal 14 kursi demi kenyamanan ekstra.</p>
            </div>
            <div class="bg-white p-8 border border-gray-100 shadow-sm" data-aos="fade-up" data-aos-delay="300">
                <div class="text-3xl mb-4 text-emerald-500">02</div>
                <h3 class="text-2xl font-bold mb-3 italic">Sewa Mobil + Supir</h3>
                <p class="text-gray-500 text-xs leading-relaxed">Nikmati perjalanan eksklusif dengan kebebasan memilih supir profesional dan jenis armada sesuai preferensi Anda.</p>
            </div>
            <div class="bg-white p-8 border border-gray-100 shadow-sm" data-aos="fade-up" data-aos-delay="400">
                <div class="text-3xl mb-4 text-emerald-500">03</div>
                <h3 class="text-2xl font-bold mb-3 italic">Rental Lepas Kunci</h3>
                <p class="text-gray-500 text-xs leading-relaxed">Kendali penuh ada di tangan Anda. Sistem sewa mandiri yang fleksibel dengan fitur perpanjangan darurat (Extend).</p>
            </div>
        </div>
    </section>

    <section
      id="about"
      class="py-24 px-6 md:px-20 max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-16 border-t border-gray-500/10"
    >
      <div class="w-full md:w-1/2" data-aos="fade-right">
        <div class="relative">
          <img
            src="https://www.asuransiku.id/support/images/upload-photos/article/artikel3-bisnis-travel-besar.png"
            class="shadow-2xl grayscale hover:grayscale-0 transition duration-1000 w-full h-[450px] object-cover"
          />
          <div
            class="absolute -bottom-6 -right-6 bg-emerald-600 w-32 h-32 -z-10 hidden md:block"
          ></div>
        </div>
      </div>
      <div
        class="w-full md:w-1/2 text-center md:text-left"
        data-aos="fade-left"
      >
        <span
          class="text-emerald-600 font-bold uppercase tracking-widest text-[10px]"
          >Tentang Kami</span
        >
        <h2 class="text-5xl mt-4 mb-6 italic">Filosofi Perjalanan Kami</h2>
        <p class="text-gray-500 leading-relaxed mb-8 text-sm">
          Kami percaya bahwa mobilitas bukan sekadar berpindah tempat, melainkan tentang rasa aman selama di perjalanan. Dengan armada modern yang dirawat berkala, penjadwalan supir yang transparan, serta sistem pelacakan digital terintegrasi, kini DITRAS menyajikan standar baru dalam industri transportasi.
        </p>
        <button
          onclick="scrollToSection('booking')"
          class="text-[10px] uppercase font-bold tracking-widest border-b-2 border-black pb-1 hover:text-emerald-600 hover:border-emerald-600 transition"
        >
          Mulai Booking Sekarang
        </button>
      </div>
    </section>

    <section id="booking" class="py-24 bg-[#111] text-white">
      <div class="max-w-4xl mx-auto px-6 text-center">
        <div data-aos="fade-down">
          <span
            class="text-emerald-400 font-bold uppercase tracking-widest text-[10px] mb-4 block"
            >Rencanakan Perjalanan</span
          >
          <h2 class="text-5xl mb-12 italic">Experience The Comfort</h2>

          <form
            id="reservationForm"
            class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left"
          >
            <div class="group">
              <label
                class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-400 transition"
                >Nama Lengkap Anda</label
              >
              <input
                type="text"
                id="resName"
                required
                class="w-full bg-transparent border-b border-gray-800 py-3 focus:border-emerald-400 outline-none transition placeholder-gray-800"
                placeholder="Contoh: Muh Husen Nabil"
              />
            </div>
            <div class="group">
              <label
                class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-400 transition"
                >Tanggal Perjalanan</label
              >
              <input
                type="date"
                id="resDate"
                required
                class="w-full bg-transparent border-b border-gray-800 py-3 focus:border-emerald-400 outline-none transition text-gray-400"
              />
            </div>
            
            <div class="md:col-span-2 group">
              <label
                class="text-[10px] uppercase tracking-widest text-gray-500 mb-2 block group-focus-within:text-emerald-400 transition"
                >Pilih Layanan Utama</label
              >
              <select 
                required
                class="w-full bg-transparent border-b border-gray-800 py-3 focus:border-emerald-400 outline-none transition text-gray-400"
              >
                <option value="travel" class="bg-neutral-900 text-white">Tiket Travel Reguler (HiAce 14 Kursi)</option>
                <option value="rental_supir" class="bg-neutral-900 text-white">Sewa Mobil + Supir Pilihan</option>
                <option value="lepas_kunci" class="bg-neutral-900 text-white">Rental Mobil Mandiri (Lepas Kunci)</option>
              </select>
            </div>

            <div class="md:col-span-2">
              <button
                type="submit"
                id="submitBtn"
                class="mt-8 w-full bg-emerald-600 px-12 py-5 text-[10px] uppercase font-bold tracking-widest hover:bg-emerald-500 transition flex items-center justify-center gap-3 group"
              >
                <span id="btnText">Cek Ketersediaan Kursi & Armada</span>
                <div
                  id="loader"
                  class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"
                ></div>
              </button>
            </div>
          </form>

          <div
            id="successMessage"
            class="hidden mt-8 p-10 bg-stone-900 border border-emerald-900/50"
          >
            <div class="text-emerald-400 text-4xl mb-4 italic">Armada Siap!</div>
            <p
              class="text-xs text-gray-400 uppercase tracking-widest leading-loose"
            >
              Halo <span id="displayName" class="text-white font-bold"></span>, Rute & Layanan yang Anda cari tersedia di database kami. 
              Silakan lakukan <a href="pages/auth/login.php" class="text-emerald-400 font-bold underline">Sign In</a> atau mendaftar terlebih dahulu untuk mengunci pesanan dan memilih nomor kursi/supir.
            </p>
            <button
              onclick="location.reload()"
              class="mt-6 text-[9px] uppercase tracking-tighter text-gray-500 hover:text-white underline"
            >
              Cek Tanggal Lain
            </button>
          </div>
        </div>
      </div>
    </section>

    <footer class="bg-white py-16 border-t border-gray-100 text-center">
      <div class="max-w-7xl mx-auto px-6">
        <h3 class="text-xl font-bold tracking-[0.3em] uppercase mb-4">
          DITRAS
        </h3>
        <p class="text-gray-400 text-[9px] uppercase tracking-widest mb-8">
          © 2026 Project Kelompok Web Development - Dieng Trans Sejahtera
        </p>
        <div class="flex justify-center space-x-8 text-xs font-medium text-gray-400">
          <a href="#" class="hover:text-emerald-600 transition">Instagram</a>
          <a href="#" class="hover:text-emerald-600 transition">Facebook</a>
          <a href="#" class="hover:text-emerald-600 transition">WhatsApp Admin</a>
        </div>
      </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
      // 1. Inisialisasi Animasi AOS
      AOS.init({
        duration: 1200,
        once: true,
        offset: 100,
      });

      // 2. Fungsi Smooth Scroll
      function scrollToSection(id) {
        const element = document.getElementById(id);
        if (element) {
          element.scrollIntoView({ behavior: "smooth" });
        }
      }

      // 3. Logika Form Cek Simulasi Ketersediaan
      const form = document.getElementById("reservationForm");
      const submitBtn = document.getElementById("submitBtn");
      const loader = document.getElementById("loader");
      const btnText = document.getElementById("btnText");
      const successMsg = document.getElementById("successMessage");
      const displayName = document.getElementById("displayName");

      form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Ubah UI menjadi loading
        btnText.innerText = "Memeriksa Database MySQL...";
        loader.classList.remove("hidden");
        submitBtn.disabled = true;
        submitBtn.classList.add("opacity-50", "cursor-not-allowed");

        // Simulasi pencarian data ke server (2 Detik)
        setTimeout(() => {
          const nameInput = document.getElementById("resName").value;

          // Sembunyikan form, tampilkan pesan sukses simulasi
          form.classList.add("hidden");
          successMsg.classList.remove("hidden");
          displayName.innerText = nameInput;
        }, 2000);
      });
    </script>
  </body>
</html>