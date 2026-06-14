<?php
// api/index.php

// Ambil URL yang sedang diakses user
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// 1. Jika mengakses API cek ketersediaan
if ($path === '/api/cek-ketersediaan') {
    require __DIR__ . '/cek-ketersediaan.php';
    exit;
}

// 2. Jika mengakses halaman di dalam folder pages (Contoh: /pages/auth/login)
if (strpos($path, '/pages/') === 0) {
    // Cari file asli di luar folder api (naik 1 tingkat)
    $file_target = __DIR__ . '/..' . $path;
    
    // Jika user lupa nulis .php di URL, kita bantu tambahkan otomatis
    if (!file_exists($file_target) && file_exists($file_target . '.php')) {
        $file_target .= '.php';
    }

    if (file_exists($file_target)) {
        require $file_target;
    } else {
        http_response_code(404);
        echo "404 - Halaman '" . htmlspecialchars($path) . "' Tidak Ditemukan.";
    }
    exit;
}

// 3. Jika mengakses halaman utama (/)
if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

// 4. Jika file statis (css, js, gambar) tidak sengaja lewat sini, biarkan lolos
return false;