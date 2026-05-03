<?php
// process/download_template.php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}

// Header agar didownload sebagai file CSV
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=Template_Import_Pelanggan.csv");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen('php://output', 'w');

// Tambahkan karakter BOM agar MS Excel membaca dengan rapi
fputs($output, "\xEF\xBB\xBF");

// Tulis Header Kolom (Baris Pertama yang wajib ada)
fputcsv($output, ['Nama Lengkap', 'Alamat', 'Nomor HP', 'Paket', 'Status']);

// Tulis Baris Contoh (Boleh dihapus saat diisi nanti)
fputcsv($output, ['Contoh', 'jakarta RT 01/RW 02', '081234567890', '10 Mbps', 'Aktif']);

fclose($output);
exit;
?>