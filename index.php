<?php
// index.php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<?php include 'layouts/header.php'; ?>
 
<div class="flex w-full h-full relative">
    
    <!-- Sidebar Desktop & Mobile -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full sm:relative sm:translate-x-0 transition duration-200 ease-in-out z-40 flex flex-col w-64 bg-[#0b2853] text-white h-full shadow-xl sm:shadow-none">
        <div class="p-4 flex items-center justify-between border-b border-blue-800">
            <div class="flex items-center space-x-3">
                <img src="assets/img/logo.png" alt="Logo" class="h-8 bg-white rounded-full p-1" onerror="this.style.display='none'">
                <span class="font-bold text-lg">UANGKU</span>
            </div>
            <!-- Tombol Close (Hanya muncul di mobile) -->
            <button id="closeMenuBtn" class="sm:hidden text-white hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <!-- Menu Navigation -->
        <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
            <!-- Dashboard -->
            <a href="?page=dashboard" class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $page == 'dashboard' ? 'bg-blue-600' : 'hover:bg-white/10' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- Pelanggan -->
            <a href="?page=customers" class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $page == 'customers' ? 'bg-blue-600' : 'hover:bg-white/10' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="font-medium">Pelanggan</span>
            </a>

            <!-- Pemasukan -->
            <a href="?page=income" class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $page == 'income' ? 'bg-blue-600' : 'hover:bg-white/10' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">Pemasukan</span>
            </a>

            <!-- Pengeluaran -->
            <a href="?page=expenses" class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $page == 'expenses' ? 'bg-blue-600' : 'hover:bg-white/10' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                <span class="font-medium">Pengeluaran</span>
            </a>

            <!-- Laporan -->
            <a href="?page=reports" class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $page == 'reports' ? 'bg-blue-600' : 'hover:bg-white/10' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="font-medium">Laporan</span>
            </a>

            <!-- Log Aktivitas (BARU) -->
            <a href="?page=logs" class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $page == 'logs' ? 'bg-blue-600' : 'hover:bg-white/10' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">Log Aktivitas</span>
            </a>

            <!-- Pengaturan -->
            <a href="?page=settings" class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $page == 'settings' ? 'bg-blue-600' : 'hover:bg-white/10' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="font-medium">Pengaturan</span>
            </a>
        </nav>

        <!-- Logout -->
        <div class="p-4 border-t border-white/10">
            <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="font-medium">Logout</span>
            </a>
        </div>
    </aside>

    <!-- Overlay untuk Mobile (Gelap saat menu terbuka) -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden sm:hidden"></div>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 bg-gray-50 h-full">
        <!-- Topbar Mobile & Profile -->
        <header class="bg-white shadow-sm border-b px-4 py-3 flex justify-between items-center z-10">
            <!-- Tombol Hamburger Mobile -->
            <button id="openMenuBtn" class="sm:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div class="flex-1"></div>
            <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-700">Admin ▼</span>
                <img src="https://ui-avatars.com/api/?name=Admin&background=0b2853&color=fff" alt="Admin" class="h-8 w-8 rounded-full">
            </div>
        </header>

        <!-- Dynamic Content -->
        <main class="flex-1 overflow-y-auto relative">
            <?php
            $file_path = "pages/{$page}.php";
            if (file_exists($file_path)) {
                include $file_path;
            } else {
                echo "<div class='p-6 flex flex-col items-center justify-center h-full text-gray-500'>
                        <svg class='w-16 h-16 text-gray-300 mb-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                        <h2 class='text-xl font-bold'>Halaman tidak ditemukan.</h2>
                        <p class='text-sm mt-2'>Mungkin sedang dalam perbaikan atau belum dibuat.</p>
                      </div>";
            }
            ?>
        </main>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<script>
    // Logika Mobile Sidebar Navbar
    const sidebar = document.getElementById('sidebar');
    const openBtn = document.getElementById('openMenuBtn');
    const closeBtn = document.getElementById('closeMenuBtn');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleMenu() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    openBtn.addEventListener('click', toggleMenu);
    closeBtn.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', toggleMenu);
</script>

</body>
</html>