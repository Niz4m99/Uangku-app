<!-- pages/settings.php -->
<?php
// ==========================================
// PROSES UBAH USERNAME
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_username') {
    $new_username = trim($_POST['new_username']);
    $password = $_POST['password'];
    $user_id = $_SESSION['user_id'];

    // Ambil password saat ini dari database untuk validasi keamanan
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Cek apakah username baru sudah dipakai orang lain
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmtCheck->execute([$new_username, $user_id]);
        
        if ($stmtCheck->rowCount() > 0) {
            echo "<script>window.location.href='index.php?page=settings&err=Username sudah dipakai, pilih yang lain.';</script>"; exit;
        } else {
            $stmtUpdate = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            if ($stmtUpdate->execute([$new_username, $user_id])) {
                $_SESSION['username'] = $new_username; // Perbarui session langsung
                if (function_exists('write_log')) write_log($pdo, "Ubah Username", "Mengubah username menjadi: " . $new_username);
                echo "<script>window.location.href='index.php?page=settings&msg=Username berhasil diperbarui!';</script>"; exit;
            } else {
                echo "<script>window.location.href='index.php?page=settings&err=Gagal menyimpan ke database.';</script>"; exit;
            }
        }
    } else {
        echo "<script>window.location.href='index.php?page=settings&err=Password saat ini salah!';</script>"; exit;
    }
}

// ==========================================
// PROSES UBAH PASSWORD
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    // Pastikan password baru dan konfirmasi cocok
    if ($new_password !== $confirm_password) {
        echo "<script>window.location.href='index.php?page=settings&err=Konfirmasi password baru tidak cocok!';</script>"; exit;
    }

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($current_password, $user['password'])) {
        // Enkripsi password baru dengan sistem Bcrypt tingkat tinggi
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        $stmtUpdate = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmtUpdate->execute([$hashed_password, $user_id])) {
            if (function_exists('write_log')) write_log($pdo, "Ubah Password", "User memperbarui password keamanan.");
            echo "<script>window.location.href='index.php?page=settings&msg=Password berhasil diperbarui dengan aman!';</script>"; exit;
        } else {
            echo "<script>window.location.href='index.php?page=settings&err=Gagal memperbarui password.';</script>"; exit;
        }
    } else {
        echo "<script>window.location.href='index.php?page=settings&err=Password lama salah!';</script>"; exit;
    }
}

// Ambil data user yang sedang login
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();
?>

<div class="p-4 sm:p-6 space-y-6">
    <div class="flex items-center justify-between border-b pb-4">
        <h1 class="text-2xl font-bold text-gray-800">Pengaturan Akun</h1>
    </div>

    <!-- Alert Notifikasi Berhasil / Gagal -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
            <span class="block sm:inline font-medium">✅ <?= htmlspecialchars($_GET['msg']) ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
            <span class="block sm:inline font-medium">❌ <?= htmlspecialchars($_GET['err']) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
        
        <!-- FORM UBAH USERNAME -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">👤</span> Ubah Username
            </h3>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_username">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username Saat Ini</label>
                    <input type="text" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-500 cursor-not-allowed" readonly>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username Baru</label>
                    <input type="text" name="new_username" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b2853] focus:outline-none" required placeholder="Masukkan username baru...">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini (Wajib)</label>
                    <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b2853] focus:outline-none" required placeholder="Ketik password untuk konfirmasi">
                </div>
                
                <button type="submit" class="bg-[#0b2853] text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-900 transition">
                    Simpan Username
                </button>
            </form>
        </div>

        <!-- FORM UBAH PASSWORD -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                🔒 Ubah Password
            </h2>
            <!-- PERUBAHAN PENTING: action dikosongkan agar diproses di file ini juga -->
            <form action="" method="POST">
                <input type="hidden" name="action" value="update_password">
                
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Password Saat Ini (Wajib)</label>
                    <input type="password" name="current_password" placeholder="Masukkan password lama..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#0b2853] focus:outline-none" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="new_password" placeholder="Masukkan password baru..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#0b2853] focus:outline-none" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" placeholder="Ulangi password baru..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#0b2853] focus:outline-none" required>
                </div>
                
                <button type="submit" class="w-full sm:w-auto bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition shadow-sm">
                    Simpan Password
                </button>
            </form>
        </div>

    </div>
</div>