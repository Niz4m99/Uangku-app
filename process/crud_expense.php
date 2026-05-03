<?php
// process/crud_expense.php
session_start();
require_once '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- 1. TAMBAH PENGELUARAN ---
if ($action == 'create') {
    $tanggal    = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $jumlah     = $_POST['jumlah'];

    $stmt = $pdo->prepare("INSERT INTO pengeluaran (tanggal, keterangan, jumlah) VALUES (?, ?, ?)");
    if ($stmt->execute([$tanggal, $keterangan, $jumlah])) {
        // CATAT KE LOG
        write_log($pdo, "Tambah Pengeluaran", "Menambah pengeluaran: $keterangan senilai Rp " . number_format($jumlah, 0, ',', '.'));
        header("Location: ../index.php?page=expenses&msg=Data pengeluaran berhasil disimpan");
    } else {
        header("Location: ../index.php?page=expenses&err=Gagal menyimpan data");
    }
    exit;
}

// --- 2. UPDATE PENGELUARAN ---
if ($action == 'update') {
    $id         = $_POST['id'];
    $tanggal    = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $jumlah     = $_POST['jumlah'];

    // Ambil data lama untuk perbandingan di Log
    $stmtOld = $pdo->prepare("SELECT keterangan, jumlah FROM pengeluaran WHERE id = ?");
    $stmtOld->execute([$id]);
    $old = $stmtOld->fetch();

    $stmt = $pdo->prepare("UPDATE pengeluaran SET tanggal = ?, keterangan = ?, jumlah = ? WHERE id = ?");
    if ($stmt->execute([$tanggal, $keterangan, $jumlah, $id])) {
        // CATAT KE LOG
        $detailLog = "Mengubah '" . $old['keterangan'] . "' (Rp " . number_format($old['jumlah'], 0, ',', '.') . ") menjadi '" . $keterangan . "' (Rp " . number_format($jumlah, 0, ',', '.') . ")";
        write_log($pdo, "Update Pengeluaran", $detailLog);
        
        header("Location: ../index.php?page=expenses&msg=Data berhasil diperbarui");
    } else {
        header("Location: ../index.php?page=expenses&err=Gagal memperbarui data");
    }
    exit;
}

// --- 3. HAPUS PENGELUARAN (SINGLE) ---
if ($action == 'delete') {
    $id = $_GET['id'];

    // Ambil detail data sebelum dihapus
    $stmtOld = $pdo->prepare("SELECT keterangan, jumlah FROM pengeluaran WHERE id = ?");
    $stmtOld->execute([$id]);
    $old = $stmtOld->fetch();

    $stmt = $pdo->prepare("DELETE FROM pengeluaran WHERE id = ?");
    if ($stmt->execute([$id])) {
        // CATAT KE LOG
        write_log($pdo, "Hapus Pengeluaran", "Menghapus: " . $old['keterangan'] . " senilai Rp " . number_format($old['jumlah'], 0, ',', '.'));
        header("Location: ../index.php?page=expenses&msg=Data berhasil dihapus");
    } else {
        header("Location: ../index.php?page=expenses&err=Gagal menghapus data");
    }
    exit;
}

// --- 4. HAPUS MASAL (BULK DELETE) ---
if ($action == 'bulk_delete') {
    if (!empty($_POST['ids'])) {
        $count = count($_POST['ids']);
        $ids = implode(',', array_map('intval', $_POST['ids']));
        
        // Eksekusi hapus
        $pdo->query("DELETE FROM pengeluaran WHERE id IN ($ids)");
        
        // CATAT KE LOG
        write_log($pdo, "Hapus Masal Pengeluaran", "Menghapus sekaligus $count data pengeluaran terpilih");
        
        header("Location: ../index.php?page=expenses&msg=$count data berhasil dihapus");
    } else {
        header("Location: ../index.php?page=expenses&err=Tidak ada data yang dipilih");
    }
    exit;
}