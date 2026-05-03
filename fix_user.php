<?php
require_once 'config/database.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Hapus user lama dan masukkan yang baru dengan hash yang benar
mysqli_query($conn, "DELETE FROM users WHERE username = '$username'");

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
$stmt->bind_param("ss", $username, $hashed_password);

if ($stmt->execute()) {
    echo "User '$username' berhasil diperbarui! Password Anda sekarang: $password <br>";
    echo "Silakan hapus file ini demi keamanan, lalu coba login kembali.";
} else {
    echo "Gagal memperbarui user: " . $conn->error;
}
?>