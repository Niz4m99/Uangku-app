<?php
// process/crud_customer.php
session_start();
require_once '../config/database.php';

// Pastikan user sudah login dan mengakses via POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../index.php");
    exit;
}

$action = $_POST['action'] ?? '';

// 1. TAMBAH PELANGGAN (CREATE)
if ($action == 'create') {
    $name   = $_POST['name'];
    $alamat = $_POST['alamat'];
    $no_hp  = $_POST['no_hp'] ?? '';
    $paket  = $_POST['paket'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("INSERT INTO pelanggan (name, alamat, no_hp, paket, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $alamat, $no_hp, $paket, $status]);
        
        // Kembalikan ke halaman pelanggan setelah berhasil
        header("Location: ../index.php?page=customers");
        exit;
    } catch (PDOException $e) {
        die("Error Database: " . $e->getMessage());
    }
}

// 2. UBAH PAKET / DATA PELANGGAN (UPDATE)
if ($action == 'update') {
    $id     = $_POST['id'];
    $name   = $_POST['name'];
    $alamat = $_POST['alamat'];
    $paket  = $_POST['paket'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE pelanggan SET name = ?, alamat = ?, paket = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $alamat, $paket, $status, $id]);
        
        header("Location: ../index.php?page=customers");
        exit;
    } catch (PDOException $e) {
        die("Error Database: " . $e->getMessage());
    }
}

// 3. HAPUS PELANGGAN (DELETE)
if ($action == 'delete') {
    $id = $_POST['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM pelanggan WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: ../index.php?page=customers");
        exit;
    } catch (PDOException $e) {
        die("Error Database: " . $e->getMessage());
    }
}
?>