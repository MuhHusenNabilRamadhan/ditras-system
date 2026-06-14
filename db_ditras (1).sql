-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2026 at 04:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ditras`
--

-- --------------------------------------------------------

--
-- Table structure for table `aspirasi_rute`
--

CREATE TABLE `aspirasi_rute` (
  `id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `rute_usulan` varchar(100) NOT NULL,
  `tanggal_potensial` date NOT NULL,
  `alasan_usulan` text DEFAULT NULL,
  `status_aspirasi` enum('pending','ditindaklanjuti') DEFAULT 'pending',
  `tanggal_kirim` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aspirasi_rute`
--

INSERT INTO `aspirasi_rute` (`id`, `pembeli_id`, `rute_usulan`, `tanggal_potensial`, `alasan_usulan`, `status_aspirasi`, `tanggal_kirim`) VALUES
(1, 12, 'wonosobo - Temanggung', '0026-09-23', 'saya ulang tahun', 'ditindaklanjuti', '2026-06-12 13:31:47'),
(2, 18, 'Surabaya - Bandung', '2026-06-20', 'sayaa pengen liburan kesana', 'pending', '2026-06-14 13:14:35'),
(3, 12, 'Purbalingga - Wonosobo', '2026-06-16', 'pulang kampung', 'ditindaklanjuti', '2026-06-14 13:51:12');

-- --------------------------------------------------------

--
-- Table structure for table `detail_rental`
--

CREATE TABLE `detail_rental` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `mobil_id` int(11) NOT NULL,
  `supir_id` int(11) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_rental` enum('booking','diambil','kembali') NOT NULL DEFAULT 'booking'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_rental`
--

INSERT INTO `detail_rental` (`id`, `transaksi_id`, `mobil_id`, `supir_id`, `tanggal_mulai`, `tanggal_selesai`, `status_rental`) VALUES
(2, 1, 6, 17, '2026-06-10', '2026-06-12', ''),
(3, 2, 6, 17, '2026-06-10', '2026-06-12', 'kembali'),
(4, 3, 7, 17, '2026-06-16', '2026-06-19', 'diambil'),
(5, 4, 4, 16, '2026-06-15', '2026-06-16', 'booking'),
(6, 5, 7, 16, '2026-06-16', '2026-06-17', 'booking'),
(7, 6, 7, 16, '2026-06-16', '2026-06-17', 'booking'),
(8, 7, 7, 16, '2026-06-16', '2026-06-17', '');

-- --------------------------------------------------------

--
-- Table structure for table `detail_travel`
--

CREATE TABLE `detail_travel` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `jadwal_id` int(11) NOT NULL,
  `nama_penumpang` varchar(100) NOT NULL,
  `nomor_hp_penumpang` varchar(15) NOT NULL,
  `jumlah_tiket` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `extend_lepas_kunci`
--

CREATE TABLE `extend_lepas_kunci` (
  `id` int(11) NOT NULL,
  `detail_rental_id` int(11) NOT NULL,
  `durasi_tambahan_hari` int(11) NOT NULL,
  `alasan_urgensi` text NOT NULL,
  `total_biaya_tambahan` int(11) NOT NULL,
  `status_extend` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_keberangkatan`
--

CREATE TABLE `jadwal_keberangkatan` (
  `id` int(11) NOT NULL,
  `rute_id` int(11) NOT NULL,
  `supir_id` int(11) NOT NULL,
  `mobil_id` int(11) NOT NULL,
  `tanggal_berangkat` date NOT NULL,
  `jam_berangkat` time NOT NULL,
  `sisa_kursi` int(11) NOT NULL DEFAULT 14
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_keberangkatan`
--

INSERT INTO `jadwal_keberangkatan` (`id`, `rute_id`, `supir_id`, `mobil_id`, `tanggal_berangkat`, `jam_berangkat`, `sisa_kursi`) VALUES
(2, 5, 9, 1, '2026-06-05', '18:02:00', 0),
(3, 6, 9, 6, '2026-06-07', '02:45:00', 14),
(4, 6, 2, 4, '2026-06-06', '02:50:00', 12),
(5, 9, 13, 1, '2026-06-14', '17:12:00', 14),
(6, 10, 17, 6, '2026-06-15', '16:10:00', 8),
(7, 9, 15, 8, '2026-06-14', '18:56:00', 10),
(8, 10, 19, 5, '2026-06-15', '20:53:00', 14);

-- --------------------------------------------------------

--
-- Table structure for table `mobil`
--

CREATE TABLE `mobil` (
  `id` int(11) NOT NULL,
  `merk` varchar(50) NOT NULL,
  `tahun_kendaraan` year(4) NOT NULL,
  `jumlah_kursi` int(11) NOT NULL,
  `plat_nomor` varchar(15) NOT NULL,
  `status_mobil` enum('tersedia','jalan','maintenance') NOT NULL DEFAULT 'tersedia',
  `status_stnk` enum('Aktif','Mati') DEFAULT 'Aktif',
  `status_pajak` enum('Aktif','Mati') DEFAULT 'Aktif',
  `status_kir` enum('Aktif','Mati','Tidak Ada') DEFAULT 'Tidak Ada',
  `harga_sewa_per_hari` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mobil`
--

INSERT INTO `mobil` (`id`, `merk`, `tahun_kendaraan`, `jumlah_kursi`, `plat_nomor`, `status_mobil`, `status_stnk`, `status_pajak`, `status_kir`, `harga_sewa_per_hari`) VALUES
(1, 'Toyota HiAce Luxury', '2024', 14, 'AA 1234 DF', 'jalan', 'Aktif', 'Aktif', 'Aktif', 800000),
(2, 'Avanza Veloz', '0000', 0, 'AA 5678 ED', 'maintenance', 'Aktif', 'Aktif', 'Tidak Ada', 400000),
(3, 'Innova Zenix', '0000', 0, 'B 9999 DIT', 'maintenance', 'Aktif', 'Aktif', 'Tidak Ada', 650000),
(4, 'HIACE', '0000', 0, 'AB 1 AC', 'tersedia', 'Aktif', 'Aktif', 'Tidak Ada', 1500000),
(5, 'Hiace Premio', '2025', 12, 'AB 25 GH', 'jalan', 'Aktif', 'Aktif', 'Tidak Ada', 500000),
(6, 'Avanza Veloz', '2024', 7, 'AA 5678 EK', 'tersedia', 'Aktif', 'Aktif', 'Aktif', 500000),
(7, 'HIACE', '2013', 14, 'AB 2345 HD', 'tersedia', 'Aktif', 'Aktif', 'Aktif', 1200000),
(8, 'Hiace Premio', '2021', 14, 'AB 7632 GH', 'jalan', 'Aktif', 'Aktif', 'Aktif', 400000);

-- --------------------------------------------------------

--
-- Table structure for table `reservasi`
--

CREATE TABLE `reservasi` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `jadwal_id` int(11) NOT NULL,
  `nama_penumpang` varchar(100) NOT NULL,
  `jumlah_tiket` int(11) NOT NULL,
  `whatsapp_pembeli` varchar(20) NOT NULL,
  `titik_jemput` text NOT NULL,
  `total_bayar` int(11) NOT NULL,
  `status_pembayaran` enum('Belum Bayar','Lunas') DEFAULT 'Belum Bayar',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservasi`
--

INSERT INTO `reservasi` (`id`, `id_user`, `jadwal_id`, `nama_penumpang`, `jumlah_tiket`, `whatsapp_pembeli`, `titik_jemput`, `total_bayar`, `status_pembayaran`, `created_at`) VALUES
(1, 12, 6, 'Nabil Ramadhan', 4, '1234567890', 'Pasar Batur', 600000, 'Lunas', '2026-06-14 05:06:16'),
(2, 12, 6, 'Nabil Ramadhan', 2, '12456095673', 'depan pasar', 300000, 'Lunas', '2026-06-14 07:53:20'),
(3, 12, 7, 'anugrah', 2, '12456095673', 'pasar', 200000, 'Belum Bayar', '2026-06-14 07:57:29'),
(4, 12, 7, 'anugrah', 2, '12456095673', 'pasar', 200000, 'Belum Bayar', '2026-06-14 07:58:00'),
(8, 12, 2, 'Nabil Ramadhan', 12, '12456095673', 'wqe', 2400000, 'Belum Bayar', '2026-06-14 08:10:04'),
(9, 12, 4, 'Nabil Ramadhan', 2, '12456095673', 'qA', 1400000, 'Belum Bayar', '2026-06-14 08:11:45');

-- --------------------------------------------------------

--
-- Table structure for table `review_supir`
--

CREATE TABLE `review_supir` (
  `id` int(11) NOT NULL,
  `supir_id` int(11) NOT NULL,
  `rating_diberikan` decimal(3,2) NOT NULL,
  `komentar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rute`
--

CREATE TABLE `rute` (
  `id` int(11) NOT NULL,
  `nama_rute` varchar(100) NOT NULL,
  `estimasi_waktu` varchar(50) NOT NULL,
  `harga_dasar` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rute`
--

INSERT INTO `rute` (`id`, `nama_rute`, `estimasi_waktu`, `harga_dasar`) VALUES
(5, 'Yogyakarta - Semarang', '4 jam', 200000),
(6, 'Yogyakarta - Surabaya', '6 Jam', 700000),
(8, 'Yogyakarta - Banjarnegara', '5 Jam', 250000),
(9, 'Wonosobo - Dieng', '2 Jam', 100000),
(10, 'Purwokerto - Batur', '3.5 Jam', 150000);

-- --------------------------------------------------------

--
-- Table structure for table `supir_detail`
--

CREATE TABLE `supir_detail` (
  `supir_id` int(11) NOT NULL,
  `status` enum('Standby','On Trip','Travel') DEFAULT 'Standby',
  `kendaraan_bawaan` varchar(100) DEFAULT NULL,
  `tujuan` varchar(255) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 5.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supir_detail`
--

INSERT INTO `supir_detail` (`supir_id`, `status`, `kendaraan_bawaan`, `tujuan`, `rating`) VALUES
(2, 'Travel', NULL, NULL, 5.00),
(9, 'Travel', 'Hilux (AB 2000 AD)', 'Jakarta', 5.00),
(11, 'On Trip', 'Hilux (AB 2000 AD)', 'Jakarta', 5.00),
(13, 'Travel', 'Toyota HiAce Luxury (AA 1234 DF)', 'Wonosobo - Dieng', 5.00),
(15, 'Travel', 'Hiace Premio (AB 7632 GH)', 'Wonosobo - Dieng', 5.00),
(17, 'Standby', 'Avanza Veloz (AA 5678 EK)', 'Purwokerto - Batur', 5.00),
(19, 'Travel', 'Hiace Premio (AB 25 GH)', 'Purwokerto - Batur', 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `tracking_gps`
--

CREATE TABLE `tracking_gps` (
  `id` int(11) NOT NULL,
  `supir_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `jenis_layanan` enum('travel','rental_supir','lepas_kunci') NOT NULL,
  `total_harga` int(11) NOT NULL,
  `status_pembayaran` enum('pending','dikonfirmasi','selesai','ditolak') NOT NULL DEFAULT 'pending',
  `tanggal_transaksi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `pembeli_id`, `jenis_layanan`, `total_harga`, `status_pembayaran`, `tanggal_transaksi`) VALUES
(1, 12, 'rental_supir', 2100000, 'selesai', '2026-06-14 09:54:57'),
(2, 12, 'rental_supir', 2100000, 'selesai', '2026-06-14 09:55:14'),
(3, 12, 'rental_supir', 2800000, 'pending', '2026-06-14 10:05:39'),
(4, 18, 'rental_supir', 700000, 'pending', '2026-06-14 12:58:29'),
(5, 18, 'rental_supir', 700000, 'pending', '2026-06-14 13:01:55'),
(6, 18, 'rental_supir', 700000, 'pending', '2026-06-14 13:02:45'),
(7, 18, 'rental_supir', 700000, 'selesai', '2026-06-14 13:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `nomor_hp` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','supir','pembeli') NOT NULL DEFAULT 'pembeli',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `email`, `nomor_hp`, `password`, `role`, `created_at`) VALUES
(1, 'Abil Admin Utama', NULL, 'admin@ditras.com', '08111111111', 'admin123', 'admin', '2026-06-06 12:39:39'),
(2, 'Joko Supir Travel', 'joko', 'joko@ditras.com', '08222222222', '$2y$10$TcnV5uvtPKSz2ozGenjNpeMuUHcL7Hsborbb2sEpi7FDXCR4smwzS', 'supir', '2026-06-06 12:39:39'),
(3, 'Nabil Pembeli Tiket', NULL, 'nabil@gmail.com', '08333333333', 'pembeli123', 'pembeli', '2026-06-06 12:39:39'),
(4, 'Nabil Ramadhan', NULL, 'ramadhan@gmail.com', '085156372316', '$2y$10$muWWvXKbv.2aY.xORdvJtOcpuzjLg6Oz7RYEJNEDWLgSeJyDYf1vm', 'pembeli', '2026-06-07 14:14:37'),
(9, 'ANUGRAH', 'ANUNG', 'anung@ditras.com', '09876541', '$2y$10$sKqXtZjq6rTere1k7Mqco.4XdkHaCXBQQOg/w5k5qEZnDJWYEEG2G', 'supir', '2026-06-11 16:35:01'),
(10, 'waginem', NULL, 'waginem@ditras.com', '0987654321', '$2y$10$onynXWBy7N8OEajuGKEhDuRlDRiR3uwBldc4pbQ56zMat/nrVjmCi', 'supir', '2026-06-12 03:29:52'),
(11, 'Husen Nabil', NULL, 'Husen@ditras.com', '12345680', 'husen123', 'supir', '2026-06-12 03:55:59'),
(12, 'Anugrah', NULL, 'anugrah@gmail.com', '12346890', '$2y$10$equ15wff1aXH4mB1iZ8xcOpT0n0vDq5Bhg03efdWthYhkk.c.NQBC', 'pembeli', '2026-06-12 11:49:59'),
(13, 'Parma ', NULL, 'parma@ditras.com', '1234567890', 'parma123', 'supir', '2026-06-13 12:40:48'),
(14, 'wahyu', NULL, 'wahyu@ditras.com', '09347945678', 'wahyu123', 'supir', '2026-06-14 03:24:40'),
(15, 'agung', NULL, 'agung@ditras.com', '098765344567', 'agung123', 'supir', '2026-06-14 03:29:53'),
(16, 'Jekiii', NULL, 'jeki@ditras.com', '098765445678', 'jeki123', 'supir', '2026-06-14 03:36:07'),
(17, 'YONOO', NULL, 'yono@ditras.com', '187234670', 'yono123', 'supir', '2026-06-14 03:49:16'),
(18, 'Asroni', NULL, 'asroni@gmail.com', '09812348927', '$2y$10$fo1UdvVz5QifBUqGTsl.M.DtyqFVbUxicS4DLZJtTL5ILdTaGp8CW', 'pembeli', '2026-06-14 12:49:00'),
(19, 'Dhonan', NULL, 'Dhonan@Ditras.com', '098765', '123456', 'supir', '2026-06-14 13:48:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aspirasi_rute`
--
ALTER TABLE `aspirasi_rute`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembeli_id` (`pembeli_id`);

--
-- Indexes for table `detail_rental`
--
ALTER TABLE `detail_rental`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `mobil_id` (`mobil_id`),
  ADD KEY `supir_id` (`supir_id`);

--
-- Indexes for table `detail_travel`
--
ALTER TABLE `detail_travel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `jadwal_id` (`jadwal_id`);

--
-- Indexes for table `extend_lepas_kunci`
--
ALTER TABLE `extend_lepas_kunci`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_rental_id` (`detail_rental_id`);

--
-- Indexes for table `jadwal_keberangkatan`
--
ALTER TABLE `jadwal_keberangkatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rute_id` (`rute_id`),
  ADD KEY `supir_id` (`supir_id`),
  ADD KEY `mobil_id` (`mobil_id`);

--
-- Indexes for table `mobil`
--
ALTER TABLE `mobil`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plat_nomor` (`plat_nomor`);

--
-- Indexes for table `reservasi`
--
ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `jadwal_id` (`jadwal_id`);

--
-- Indexes for table `review_supir`
--
ALTER TABLE `review_supir`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rute`
--
ALTER TABLE `rute`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supir_detail`
--
ALTER TABLE `supir_detail`
  ADD PRIMARY KEY (`supir_id`);

--
-- Indexes for table `tracking_gps`
--
ALTER TABLE `tracking_gps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supir_id` (`supir_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembeli_id` (`pembeli_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nomor_hp` (`nomor_hp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aspirasi_rute`
--
ALTER TABLE `aspirasi_rute`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `detail_rental`
--
ALTER TABLE `detail_rental`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `detail_travel`
--
ALTER TABLE `detail_travel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `extend_lepas_kunci`
--
ALTER TABLE `extend_lepas_kunci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_keberangkatan`
--
ALTER TABLE `jadwal_keberangkatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mobil`
--
ALTER TABLE `mobil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reservasi`
--
ALTER TABLE `reservasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `review_supir`
--
ALTER TABLE `review_supir`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rute`
--
ALTER TABLE `rute`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tracking_gps`
--
ALTER TABLE `tracking_gps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aspirasi_rute`
--
ALTER TABLE `aspirasi_rute`
  ADD CONSTRAINT `aspirasi_rute_ibfk_1` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `detail_rental`
--
ALTER TABLE `detail_rental`
  ADD CONSTRAINT `detail_rental_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_rental_ibfk_2` FOREIGN KEY (`mobil_id`) REFERENCES `mobil` (`id`),
  ADD CONSTRAINT `detail_rental_ibfk_3` FOREIGN KEY (`supir_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `detail_travel`
--
ALTER TABLE `detail_travel`
  ADD CONSTRAINT `detail_travel_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_travel_ibfk_2` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_keberangkatan` (`id`);

--
-- Constraints for table `extend_lepas_kunci`
--
ALTER TABLE `extend_lepas_kunci`
  ADD CONSTRAINT `extend_lepas_kunci_ibfk_1` FOREIGN KEY (`detail_rental_id`) REFERENCES `detail_rental` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal_keberangkatan`
--
ALTER TABLE `jadwal_keberangkatan`
  ADD CONSTRAINT `jadwal_keberangkatan_ibfk_1` FOREIGN KEY (`rute_id`) REFERENCES `rute` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_keberangkatan_ibfk_2` FOREIGN KEY (`supir_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `jadwal_keberangkatan_ibfk_3` FOREIGN KEY (`mobil_id`) REFERENCES `mobil` (`id`);

--
-- Constraints for table `reservasi`
--
ALTER TABLE `reservasi`
  ADD CONSTRAINT `reservasi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservasi_ibfk_2` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_keberangkatan` (`id`);

--
-- Constraints for table `supir_detail`
--
ALTER TABLE `supir_detail`
  ADD CONSTRAINT `supir_detail_ibfk_1` FOREIGN KEY (`supir_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tracking_gps`
--
ALTER TABLE `tracking_gps`
  ADD CONSTRAINT `tracking_gps_ibfk_1` FOREIGN KEY (`supir_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
