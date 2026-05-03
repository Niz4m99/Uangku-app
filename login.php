<?php
// login.php
session_start();
require_once 'config/database.php';

// Jika user sudah login, arahkan langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=dashboard");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Tangkap input username (bukan email)
    $username = $_POST['username']; 
    // 2. Tangkap password asli (Jangan di-md5)
    $password = $_POST['password']; 

    // Cari user berdasarkan username di database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 3. Verifikasi menggunakan password_verify untuk mencocokkan Bcrypt
    if ($user && password_verify($password, $user['password'])) {
        // Simpan data penting ke Session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role']; // Simpan hak akses (Admin/Kasir)
        
        header("Location: index.php?page=dashboard");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UANGKU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <img src="assets/img/logo.png" alt="UANGKU Logo" class="mx-auto h-20 mb-4" onerror="this.style.display='none'">
            <h2 class="text-2xl font-bold text-gray-800">Selamat Datang Di Uangku</h2>
            <p class="text-sm text-gray-500 mt-1">Silakan login ke akun Anda</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-100 text-red-600 p-3 rounded-lg mb-4 text-sm font-medium text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <!-- Input type diubah menjadi text dan name menjadi username -->
                <input type="text" name="username" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b2853] focus:outline-none transition" required placeholder="Masukkan username">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b2853] focus:outline-none transition" required placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-[#0b2853] text-white py-2 rounded-lg font-semibold hover:bg-blue-900 transition duration-200">
                Login
            </button>
        </form>
    </div>
</body>
</html>