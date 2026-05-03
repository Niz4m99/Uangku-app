<?php
// process/export_customers.php

// 1. Matikan error reporting sementara agar pesan error tidak merusak file CSV
error_reporting(0);
session_start();

// 2. Bersihkan buffer output dari spasi kosong yang mungkin bocor dari file lain
if (ob_get_length()) {
    ob_clean();
}

require_once '../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// 3. Set Header HTTP untuk memaksa browser mendownload file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Data_Pelanggan_UANGKU_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Buka file output virtual
$output = fopen('php://output', 'w');

// 4. Tambahkan BOM (Byte Order Mark) agar karakter khusus/spasi terbaca rapi di Microsoft Excel
fputs($output, "\xEF\xBB\xBF");

// 5. Tulis Baris Judul Kolom (Header) di CSV
fputcsv($output, array('ID Pelanggan', 'Nama Pelanggan', 'No HP', 'Alamat', 'Status', 'Tanggal Daftar'));

// 6. Ambil data pelanggan dari database
try {
    $stmt = $pdo->query("SELECT id, name, no_hp, alamat, status, created_at FROM pelanggan ORDER BY id DESC");
    
    // Tulis isi datanya baris demi baris
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format ulang tanggal agar lebih rapi (Opsional)
        $row['created_at'] = date('d/m/Y H:i', strtotime($row['created_at']));
        
        fputcsv($output, $row);
    }
} catch (PDOException $e) {
    // Jika query gagal, hentikan eksekusi tanpa merusak output file
    die("Gagal mengambil data pelanggan.");
}

// Tutup output dan hentikan script agar tidak ada tambahan karakter lain
fclose($output);
exit;