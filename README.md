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
uangku/
│
├── index.php              
├── login.php             
├── logout.php           
├── .htaccess              
│
├── config/
│   ├── database.php      
│   └── auth.php           
│
├── assets/
│   ├── css/
│   │   └── style.css      
│   ├── js/
│   │   └── app.js         
│   └── img/
│       └── logo.png
│
├── layouts/
│   ├── header.php        
│   ├── topbar.php        
│   └── footer.php        
│
├── pages/
│   ├── dashboard.php      
│   ├── customers.php
│   ├── income.php
│   ├── expenses.php
│   ├── reports.php
│   └── settings.php     
│
├── process/
│   ├── auth_process.php  
│   ├── update_pass.php    
│   ├── crud_customer.php
│   ├── crud_income.php
│   └── crud_expense.php
│
└── database/
    └── uangku.sql
    
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

