<?php
// pages/auth/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bersihkan semua variabel session
session_unset();

// Hancurkan session dari server
session_destroy();

// PERBAIKAN: Mengarahkan secara absolut langsung ke halaman root '/' agar tidak terkena 404 di Vercel
header("Location: /");
exit;
?>