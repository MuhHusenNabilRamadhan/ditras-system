<?php
// pages/auth/logout.php
session_start(); // Mulai session untuk bisa menangkap session yang aktif

// Bersihkan semua variabel session
session_unset();

// Hancurkan session dari server
session_destroy();

// Arahkan kembali ke halaman index utama (di luar folder pages)
header("Location: ../../index.php");
exit;
?>