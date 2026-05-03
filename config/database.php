<?php
// config/database.php

$host = "localhost";
$user = "root"; 
$pass = "";  
$db   = "uangku_db";

// ==========================================
// --- SISTEM ANTI-TAMPER (PROTEKSI COPYRIGHT) ---
// ==========================================
$footer_path = __DIR__ . '/../layouts/footer.php';

if (file_exists($footer_path)) {
    $footer_content = file_get_contents($footer_path);
    
  
    if (strpos($footer_content, 'niz4m.domcloud.dev') === false || strpos($footer_content, 'niz4m') === false) {
        die("
            <div style='text-align:center; margin-top:100px; font-family:sans-serif;'>
                <h1 style='color:red;'>⚠️ FATAL ERROR ⚠️</h1>
                <p><strong>Pelanggaran Hak Cipta Terdeteksi!</strong></p>
                <p>Sistem mendeteksi penghapusan atau perubahan pada credit developer.</p>
                <p>Aplikasi telah dikunci secara otomatis. Silakan kembalikan credit asli kepada <a href='https://niz4m.domcloud.dev'>niz4m</a> untuk memulihkan sistem.</p>
            </div>
        ");
    }
} else {
    // Jika file footer.php sengaja dihapus seluruhnya
    die("<h1 style='color:red; text-align:center; margin-top:100px; font-family:sans-serif;'>FATAL ERROR: File inti sistem hilang!</h1>");
}
// ==========================================

// Jika lolos Anti-Tamper, lanjutkan koneksi Database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
function write_log($pdo, $aktivitas, $detail = null) {
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, aktivitas, detail, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $aktivitas, $detail, $ip]);
}
?>