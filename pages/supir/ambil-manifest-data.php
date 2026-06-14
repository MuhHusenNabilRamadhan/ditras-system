<?php
// pages/supir/ambil-manifest-data.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    exit;
}

$id_supir = $_SESSION['id_user'];
$output = '';

try {
    // 1. CEK DATA TRAVEL REGULER YANG AKTIF
    // Data hanya muncul jika tanggal berangkat adalah HARI INI ke depan
    $stmtJadwal = $pdo->prepare("
        SELECT jk.id AS id_jadwal, jk.tanggal_berangkat, jk.jam_berangkat, r.nama_rute, m.merk, m.plat_nomor
        FROM jadwal_keberangkatan jk
        INNER JOIN rute r ON jk.rute_id = r.id
        INNER JOIN mobil m ON jk.mobil_id = m.id
        WHERE jk.supir_id = ? AND jk.tanggal_berangkat >= CURDATE()
        ORDER BY jk.tanggal_berangkat ASC, jk.jam_berangkat ASC LIMIT 1
    ");
    $stmtJadwal->execute([$id_supir]);
    $jadwal = $stmtJadwal->fetch(PDO::FETCH_ASSOC);

    // Render Data Travel Reguler jika ada
    if ($jadwal) {
        $stmtPenumpang = $pdo->prepare("
            SELECT res.id AS id_reservasi, res.nama_penumpang, res.jumlah_tiket, res.total_bayar, res.status_pembayaran, res.titik_jemput
            FROM reservasi res
            WHERE res.jadwal_id = ?
            ORDER BY res.id DESC
        ");
        $stmtPenumpang->execute([$jadwal['id_jadwal']]);
        $penumpang = $stmtPenumpang->fetchAll(PDO::FETCH_ASSOC);

        $output .= '
        <div class="mb-4 text-xs font-bold uppercase tracking-wider text-stone-400"><i class="fa-solid fa-bus-simple mr-1"></i> Tugas Travel Reguler</div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-white p-6 border border-gray-100 shadow-xs mb-6 rounded-xl">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Rute Lintasan</p>
                <p class="text-base font-bold text-emerald-700">'.htmlspecialchars($jadwal['nama_rute']).'</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Unit Armada</p>
                <p class="text-base font-bold text-gray-800">'.htmlspecialchars($jadwal['merk']).' <span class="text-xs font-mono font-bold bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">'.htmlspecialchars($jadwal['plat_nomor']).'</span></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Jadwal Keberangkatan</p>
                <p class="text-base font-bold text-gray-800">'.date('d M Y', strtotime($jadwal['tanggal_berangkat'])).' — '.htmlspecialchars($jadwal['jam_berangkat']).' WIB</p>
            </div>
        </div>

        <div class="bg-white border border-gray-100 shadow-xs rounded-xl overflow-hidden mb-10">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-bold">
                        <th class="p-4">Nama Penumpang</th>
                        <th class="p-4 text-center">Jumlah Tiket</th>
                        <th class="p-4">Titik Penjemputan</th>
                        <th class="p-4 text-right">Total Tagihan</th>
                        <th class="p-4 text-center">Status Manifest / Aksi</th>
                    </tr>
                </thead>
                <tbody>';
                
        if (empty($penumpang)) {
            $output .= '
                    <tr>
                        <td colspan="5" class="p-8 text-center text-sm text-gray-400 font-medium">
                            <i class="fa-solid fa-folder-open block text-2xl text-gray-300 mb-2"></i> Belum ada reservasi masuk.
                        </td>
                    </tr>';
        } else {
            foreach ($penumpang as $p) {
                $output .= '
                    <tr class="border-b border-gray-50 text-sm hover:bg-gray-50 transition-colors">
                        <td class="p-4 font-semibold text-gray-800">'.htmlspecialchars($p['nama_penumpang']).'</td>
                        <td class="p-4 text-center font-semibold text-neutral-600">'.$p['jumlah_tiket'].' Kursi</td>
                        <td class="p-4 text-gray-500 text-xs max-w-xs truncate" title="'.htmlspecialchars($p['titik_jemput']).'">'.htmlspecialchars($p['titik_jemput']).'</td>
                        <td class="p-4 text-right font-bold text-gray-900">Rp '.number_format($p['total_bayar'], 0, ',', '.').'</td>
                        <td class="p-4 text-center">';
                if ($p['status_pembayaran'] === 'Belum Bayar') {
                    $output .= '
                            <a href="konfirmasi-cod.php?id='.$p['id_reservasi'].'" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-sm transition-all">
                                Konfirmasi Pembayaran
                            </a>';
                } else {
                    $output .= '
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">
                                <i class="fa-solid fa-circle-check text-[10px]"></i> Lunas (COD Sukses)
                            </span>';
                }
                $output .= '</td></tr>';
            }
        }
        $output .= '</tbody></table></div>';
    }

    // 2. CEK REQUEST DATA RENTAL MOBIL YANG MASUK (Status: 'booking')
    $stmtRental = $pdo->prepare("
        SELECT dr.id, dr.tanggal_mulai, dr.tanggal_selesai, u.nama AS nama_pembeli, t.total_harga, m.merk, m.plat_nomor
        FROM detail_rental dr
        JOIN transaksi t ON dr.transaksi_id = t.id
        JOIN users u ON t.pembeli_id = u.id
        JOIN mobil m ON dr.mobil_id = m.id
        WHERE dr.supir_id = ? AND dr.status_rental = 'booking'
    ");
    $stmtRental->execute([$id_supir]);
    $rentals = $stmtRental->fetchAll(PDO::FETCH_ASSOC);

    if (count($rentals) > 0) {
        $output .= '
        <div class="mb-4 text-xs font-bold uppercase tracking-wider text-stone-400"><i class="fa-solid fa-car mr-1"></i> Request Order Rental Mobil</div>';
        foreach ($rentals as $row) {
            $output .= '
            <div class="p-6 mb-4 bg-white border border-amber-200 rounded-xl flex flex-col md:flex-row justify-between items-start md:items-center shadow-xs">
                <div>
                    <span class="text-[10px] uppercase tracking-wider bg-amber-100 text-amber-800 px-2.5 py-1 font-bold rounded-full"><i class="fa-solid fa-bell animate-bounce mr-1"></i> Order Rental Baru</span>
                    <h4 class="font-bold text-lg text-stone-800 mt-2">'.htmlspecialchars($row['nama_pembeli']).'</h4>
                    <p class="text-xs text-stone-500 mt-0.5">Unit Mobil: '.htmlspecialchars($row['merk']).' ('.htmlspecialchars($row['plat_nomor']).')</p>
                    <p class="text-xs text-stone-500">Durasi Kerja: '.date('d M Y', strtotime($row['tanggal_mulai'])).' s/d '.date('d M Y', strtotime($row['tanggal_selesai'])).'</p>
                    <p class="text-sm font-bold text-emerald-600 mt-2">Pendapatan: Rp '.number_format($row['total_harga'], 0, ',', '.').'</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <form method="POST" action="manifest.php">
                        <input type="hidden" name="id_detail" value="'.$row['id'].'">
                        <button type="submit" name="aksi_rental" value="terima" class="bg-stone-950 text-white text-xs px-5 py-2.5 hover:bg-stone-800 tracking-wider font-bold uppercase transition-all rounded-lg shadow-md">
                            Terima Tugas Rental
                        </button>
                    </form>
                </div>
            </div>';
        }
    }

    // Jika dua-duanya kosong (Travel kosong & Rental kosong)
    if (!$jadwal && count($rentals) === 0) {
        $output = '
        <div class="bg-amber-50 border border-amber-200 p-5 text-amber-700 rounded-xl text-sm font-medium shadow-xs">
            <i class="fa-solid fa-circle-info mr-1.5 text-base"></i> Anda saat ini dalam status <strong>Standby di Garasi</strong>. Belum ada jadwal travel aktif atau request rental mobil baru yang ditugaskan kepada Anda.
        </div>';
    }

    echo $output;

} catch (Exception $e) {
    echo '<div class="text-red-600 text-xs">Error sinkronisasi: ' . htmlspecialchars($e->getMessage()) . '</div>';
}