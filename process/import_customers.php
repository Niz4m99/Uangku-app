<?php
// process/import_customers.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../index.php");
    exit;
}

if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] == 0) {
    $fileName = $_FILES['file_excel']['name'];
    $fileTmpName = $_FILES['file_excel']['tmp_name'];
    
    // Pastikan ekstensi file adalah csv
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    if (strtolower($ext) != 'csv') {
        header("Location: ../index.php?page=customers&err=Format file harus .csv!");
        exit;
    }

    // Buka file untuk dibaca
    if (($handle = fopen($fileTmpName, "r")) !== FALSE) {
        // Lewati baris pertama (Header: Nama Lengkap, Alamat, dll)
        fgetcsv($handle, 1000, ",");

        $stmt = $pdo->prepare("INSERT INTO pelanggan (name, alamat, no_hp, paket, status) VALUES (?, ?, ?, ?, ?)");
        
        $sukses = 0;
        // Baca data per baris
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Validasi: Pastikan kolom nama tidak kosong
            if (!empty($data[0])) {
                $name   = trim($data[0]);
                $alamat = trim($data[1] ?? '');
                $no_hp  = trim($data[2] ?? '');
                $paket  = trim($data[3] ?? '10 Mbps'); // Default 10 Mbps jika kosong
                $status = trim($data[4] ?? 'Aktif');   // Default Aktif jika kosong
                
                $stmt->execute([$name, $alamat, $no_hp, $paket, $status]);
                $sukses++;
            }
        }
        fclose($handle);
        header("Location: ../index.php?page=customers&msg=Berhasil meng-import $sukses pelanggan baru.");
        exit;
    }
} else {
    header("Location: ../index.php?page=customers&err=Gagal mengunggah file.");
    exit;
}
?>