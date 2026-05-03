# 💰 UANGKU - Manajemen Keuangan & WiFi System

UANGKU adalah aplikasi manajemen keuangan berbasis web yang dirancang untuk mempermudah pencatatan pelanggan, pemantauan arus kas (pemasukan/pengeluaran), dan pengelolaan operasional bisnis secara real-time. Dibangun menggunakan PHP murni (PDO) untuk performa ringan dan Tailwind CSS untuk antarmuka modern yang responsif.

## 📂 Struktur Aplikasi

Memahami susunan file dan direktori dalam proyek ini:
```text
uangku/
├── assets/             # File statis (Logo, Icon, Gambar)
│   └── img/
├── config/             # Konfigurasi inti (database.php)
├── database/           # Backup database (uangku.sql)
├── layouts/            # Komponen UI tetap (Header, Footer, Sidebar)
├── pages/              # Halaman konten (Dashboard, Pelanggan, Settings)
├── process/            # Logika Backend (Auth, Export CSV, CRUD)
├── index.php           # File utama (Routing system)
├── login.php           # Halaman autentikasi masuk
└── logout.php          # Proses penghapusan session
✨ Fitur Unggulan
📊 Dashboard Interaktif: Ringkasan statistik pelanggan dan grafik keuangan.

👥 Manajemen Pelanggan: Pengelolaan data pelanggan lengkap dengan status langganan.

💸 Arus Kas (Cashflow): Pencatatan otomatis setiap transaksi masuk dan keluar.

🕵️ Log Aktivitas (Audit Trail): Rekam jejak aktivitas user untuk keamanan sistem.

🔒 Keamanan Tinggi: Proteksi SQL Injection (PDO) dan enkripsi password Bcrypt.

📥 Export Data: Fitur unduh data pelanggan langsung ke format CSV/Excel.

📱 Responsive Design: Tampilan optimal di berbagai perangkat (Mobile & Desktop).

🚀 Panduan Instalasi
1. Penggunaan di Localhost (XAMPP/Laragon)
Download repository ini dan letakkan di folder htdocs atau www.

Buka phpMyAdmin, buat database baru dengan nama uangku_db.

Import file database/uangku.sql ke dalam database tersebut.

Sesuaikan kredensial database di file config/database.php.

Akses melalui browser di: http://localhost/uangku.

2. Penggunaan di Hosting (cPanel)
Upload semua file aplikasi ke folder public_html.

Buat Database, User, dan Password melalui menu MySQL® Databases di cPanel.

Hubungkan User ke Database dengan mencentang ALL PRIVILEGES.

Import file uangku.sql melalui phpMyAdmin di hosting Anda.

Perbarui file config/database.php sesuai dengan detail database hosting Anda.

🔑 Akses Login Bawaan
Username: admin
Password: admin

Developed by niz4m
