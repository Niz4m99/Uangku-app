<?php
// process/crud_income.php
session_start();
require_once '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- 1. TAMBAH PEMASUKAN ---
if ($action == 'create') {
    $jenis        = $_POST['jenis'];
    // Jika Pemasukan Lain, kosongkan (NULL) pelanggan_id
    $pelanggan_id = ($jenis == 'Tagihan Bulanan') ? $_POST['pelanggan_id'] : null;
    $tanggal      = $_POST['tanggal'];
    $jumlah       = $_POST['jumlah'];
    $keterangan   = $_POST['keterangan'];

    $stmt = $pdo->prepare("INSERT INTO pemasukan (jenis, pelanggan_id, tanggal, jumlah, keterangan) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$jenis, $pelanggan_id, $tanggal, $jumlah, $keterangan])) {
        // CATAT KE LOG
        write_log($pdo, "Tambah Pemasukan", "Menambah $jenis: $keterangan senilai Rp " . number_format($jumlah, 0, ',', '.'));
        header("Location: ../index.php?page=income&msg=Data pemasukan berhasil disimpan");
    } else {
        header("Location: ../index.php?page=income&err=Gagal menyimpan data");
    }
    exit;
}

// --- 2. UPDATE PEMASUKAN ---
if ($action == 'update') {
    $id           = $_POST['id'];
    $jenis        = $_POST['jenis'];
    $pelanggan_id = ($jenis == 'Tagihan Bulanan') ? $_POST['pelanggan_id'] : null;
    $tanggal      = $_POST['tanggal'];
    $jumlah       = $_POST['jumlah'];
    $keterangan   = $_POST['keterangan'];

    // Ambil data lama untuk perbandingan di Log
    $stmtOld = $pdo->prepare("SELECT keterangan, jumlah FROM pemasukan WHERE id = ?");
    $stmtOld->execute([$id]);
    $old = $stmtOld->fetch();

    $stmt = $pdo->prepare("UPDATE pemasukan SET jenis = ?, pelanggan_id = ?, tanggal = ?, jumlah = ?, keterangan = ? WHERE id = ?");
    if ($stmt->execute([$jenis, $pelanggan_id, $tanggal, $jumlah, $keterangan, $id])) {
        // CATAT KE LOG
        $detailLog = "Mengubah '" . $old['keterangan'] . "' (Rp " . number_format($old['jumlah'], 0, ',', '.') . ") menjadi '" . $keterangan . "' (Rp " . number_format($jumlah, 0, ',', '.') . ")";
        write_log($pdo, "Update Pemasukan", $detailLog);
        header("Location: ../index.php?page=income&msg=Data berhasil diperbarui");
    } else {
        header("Location: ../index.php?page=income&err=Gagal memperbarui data");
    }
    exit;
}

// --- 3. HAPUS PEMASUKAN (SINGLE) ---
if ($action == 'delete') {
    $id = $_GET['id'];

    // Ambil detail data sebelum dihapus
    $stmtOld = $pdo->prepare("SELECT keterangan, jumlah FROM pemasukan WHERE id = ?");
    $stmtOld->execute([$id]);
    $old = $stmtOld->fetch();

    $stmt = $pdo->prepare("DELETE FROM pemasukan WHERE id = ?");
    if ($stmt->execute([$id])) {
        // CATAT KE LOG
        write_log($pdo, "Hapus Pemasukan", "Menghapus: " . $old['keterangan'] . " senilai Rp " . number_format($old['jumlah'], 0, ',', '.'));
        header("Location: ../index.php?page=income&msg=Data berhasil dihapus");
    } else {
        header("Location: ../index.php?page=income&err=Gagal menghapus data");
    }
    exit;
}

// --- 4. HAPUS MASAL (BULK DELETE) ---
if ($action == 'bulk_delete') {
    if (!empty($_POST['ids'])) {
        $count = count($_POST['ids']);
        $ids = implode(',', array_map('intval', $_POST['ids']));
        
        // Eksekusi hapus masal
        $pdo->query("DELETE FROM pemasukan WHERE id IN ($ids)");
        
        // CATAT KE LOG
        write_log($pdo, "Hapus Masal Pemasukan", "Menghapus sekaligus $count data pemasukan terpilih");
        
        header("Location: ../index.php?page=income&msg=$count data berhasil dihapus");
    } else {
        header("Location: ../index.php?page=income&err=Tidak ada data yang dipilih");
    }
    exit;
}
?>