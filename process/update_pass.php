<?php
// process/update_pass.php
session_start();
require_once '../config/database.php'; // Path kembali ke luar folder process/

// Keamanan: Cek apakah user benar-benar login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];
    
    // Hash password inputan user untuk dicocokkan dengan database (karena di DB pakai MD5)
    $current_password_input = md5($_POST['current_password']);

    // 1. Ambil data password user saat ini dari DB
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // 2. VERIFIKASI KEAMANAN UTAMA: Cek apakah password lama yang dimasukkan BENAR
    if ($user['password'] !== $current_password_input) {
        header("Location: ../index.php?page=settings&err=Gagal! Password saat ini salah.");
        exit;
    }

    // --- LOGIKA UPDATE EMAIL ---
    if ($action === 'update_email') {
        $new_email = trim($_POST['new_email']);

        // Pastikan email baru belum dipakai user lain
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$new_email, $user_id]);
        if ($check->rowCount() > 0) {
            header("Location: ../index.php?page=settings&err=Gagal! Email tersebut sudah terdaftar.");
            exit;
        }

        // Update ke database
        $update = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        if ($update->execute([$new_email, $user_id])) {
            header("Location: ../index.php?page=settings&msg=Berhasil! Email akun telah diperbarui.");
        } else {
            header("Location: ../index.php?page=settings&err=Terjadi kesalahan sistem.");
        }
        exit;
    }

    // --- LOGIKA UPDATE PASSWORD ---
    if ($action === 'update_password') {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Pastikan password baru dan konfirmasi sama
        if ($new_password !== $confirm_password) {
            header("Location: ../index.php?page=settings&err=Gagal! Konfirmasi password baru tidak cocok.");
            exit;
        }

        // Update ke database dengan MD5
        $new_password_hashed = md5($new_password);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($update->execute([$new_password_hashed, $user_id])) {
            header("Location: ../index.php?page=settings&msg=Berhasil! Password telah diganti.");
        } else {
            header("Location: ../index.php?page=settings&err=Terjadi kesalahan sistem.");
        }
        exit;
    }
} else {
    // Jika ada yang iseng akses file ini tanpa POST, tendang balik ke settings
    header("Location: ../index.php?page=settings");
    exit;
}
?>