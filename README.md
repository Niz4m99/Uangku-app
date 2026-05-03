#  UANGKU - Aplikasi Manajemen Keuangan & WiFi

UANGKU adalah aplikasi berbasis web yang dirancang untuk mempermudah manajemen keuangan, pencatatan pelanggan, dan pemantauan aktivitas bisnis. Dibangun menggunakan PHP murni (PDO), MySQL, dan Tailwind CSS.

##  Fitur Utama
- **Dashboard Interaktif:** Ringkasan statistik pelanggan, pemasukan, dan pengeluaran.
- **Manajemen Pelanggan:** Tambah, edit, hapus, dan pantau status pelanggan.
- **Pemasukan & Pengeluaran:** Pencatatan arus kas yang rapi.
- **Log Aktivitas:** Memantau siapa melakukan apa beserta IP Address-nya.
- **Manajemen User (Multi-Role):** Sistem hak akses untuk Admin dan Kasir.
- **Export Data:** Unduh data pelanggan dalam format CSV.

## Project structure
Visualisasi susunan file dan direktori proyek UANGKU:

uangku/
├── config/             # Konfigurasi inti (database.php, auth.php)
├── assets/             # File statis (CSS, JS, Gambar)
│   ├── css/            # Style tambahan (style.css)
│   ├── js/             # Logika frontend (app.js)
│   └── img/            # Logo dan aset visual (logo.png)
├── layouts/            # Komponen UI tetap (Header, Topbar, Footer)
├── pages/              # Halaman konten (Dashboard, Customers, Reports, Settings)
├── process/            # Logika Backend (Auth, CRUD Pelanggan/Keuangan)
├── database/           # Backup database SQL (uangku.sql)
├── index.php           # File utama sebagai routing sistem
├── login.php           # Halaman autentikasi masuk
├── logout.php          # Proses keluar sistem
└── .htaccess           # Konfigurasi server Apache
    
##  Cara Install di Localhost
1. Clone atau download repository ini ke dalam folder `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka **phpMyAdmin**, buat database baru dengan nama `uangku_db`.
3. Import file **`uangku.sql`** ke dalam database tersebut.
4. Buka file `config/database.php` dan sesuaikan koneksi databasenya.
5. Akses aplikasi melalui browser (`http://localhost/uangku`).

##  Akses Login Bawaan
- **Username:** `admin`
- **Password:** `password123`

##  Credit
Dikembangkan oleh **[niz4m](https://niz4m.domcloud.dev)**.

