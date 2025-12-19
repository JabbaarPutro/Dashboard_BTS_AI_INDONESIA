# Dashboard BTS Akses Internet Internal

Dashboard untuk monitoring dan manajemen data akses internet BTS (Base Transceiver Station) dengan visualisasi geografis Indonesia.

## 📋 Deskripsi

Aplikasi web berbasis PHP untuk mengelola dan memvisualisasikan data akses internet BTS di seluruh Indonesia. Dilengkapi dengan fitur peta interaktif, statistik real-time, dan sistem manajemen pengguna.

## ✨ Fitur Utama

- 🗺️ **Visualisasi Peta Interaktif** - Menampilkan data BTS berdasarkan provinsi di Indonesia menggunakan GeoJSON
- 📊 **Dashboard Statistik** - Analisis data dan statistik akses internet BTS
- 👥 **Manajemen Pengguna** - Sistem autentikasi dan otorisasi pengguna
- 📁 **Manajemen Data** - CRUD (Create, Read, Update, Delete) data BTS
- 📤 **Import/Export Data** - Import data dari template dan export ke Excel
- 🔒 **Keamanan** - Hash password dan session management yang aman

## 🛠️ Teknologi yang Digunakan

### Backend

- **PHP** - Server-side scripting
- **MySQL** - Database management
- **Composer** - Dependency management

### Frontend

- **HTML5/CSS3** - Markup dan styling
- **JavaScript** - Client-side interactivity
- **GeoJSON** - Data geografis Indonesia

### Library & Dependencies

- **PhpSpreadsheet** - Export/import Excel files
- **HTMLPurifier** - Security sanitization
- **ZipStream-PHP** - File compression
- **MathPHP** - Mathematical operations

## 📦 Instalasi

### Prerequisites

Pastikan Anda sudah menginstal:

- PHP >= 7.4
- MySQL/MariaDB
- Apache/Nginx Web Server
- Composer

### Langkah Instalasi

1. **Clone Repository**

   ```bash
   git clone https://github.com/username/Dashboard_BTS_AI_INTERNAL.git
   cd Dashboard_BTS_AI_INTERNAL
   ```
2. **Install Dependencies**

   ```bash
   composer install
   ```
3. **Setup Database**

   - Buat database baru di MySQL:
     ```sql
     CREATE DATABASE db_bts_aksesinternet_dashboard_internal;
     ```
   - Import file SQL:
     ```bash
     mysql -u username -p db_bts_aksesinternet_dashboard_internal < config/db_bts_aksesinternet_dashboard_internal.sql
     ```
4. **Konfigurasi Database**

   - Edit file koneksi database (biasanya di `api.php` atau file konfigurasi lainnya)
   - Sesuaikan kredensial database:
     ```php
     $host = "localhost";
     $user = "your_username";
     $pass = "your_password";
     $db = "db_bts_aksesinternet_dashboard_internal";
     ```
5. **Set Permissions**

   ```bash
   chmod -R 755 .
   ```
6. **Jalankan Aplikasi**

   - Akses melalui web browser: `http://localhost/Dashboard_BTS_AI_INTERNAL`
   - Atau jika menggunakan XAMPP: `http://localhost:8080/Dashboard_BTS_AI_INTERNAL`

## 📖 Penggunaan

### Login

1. Akses halaman utama di `/index.php` atau langsung ke halaman login `/auth/login.php`
2. Masukkan kredensial yang telah terdaftar
3. Untuk registrasi pengguna baru, gunakan `/auth/register.php`

### Dashboard

- **Dashboard Utama** (`/dashboard/dashboard.php`) - Overview data dan statistik
- **Statistik** (`/dashboard/statistics.php`) - Analisis detail data BTS

### Manajemen Data

- **Kelola Data** (`/dashboard/manage_data.php`) - CRUD data BTS
- **Tambah Data** (`/dashboard/add_data.php`) - Menambah data BTS baru
- **Import Data** (`/dashboard/import_data.php`) - Import dari Excel/CSV
- **Export Data** (`/dashboard/export_data.php`) - Export ke Excel

### Manajemen Pengguna (Admin)

- **Manage Users** (`/dashboard/manage_users.php`) - Kelola pengguna sistem
- **Create User** (`/auth/create_user.php`) - Buat pengguna baru
- **Toggle User Status** - Aktifkan/nonaktifkan pengguna

## 🗂️ Struktur File

```
Dashboard_BTS_AI_INTERNAL/
├── index.php                     # Halaman landing page
├── composer.json                 # Composer dependencies
├── README.md                     # Dokumentasi proyek
├── auth/                         # Folder autentikasi
│   ├── login.php                # Halaman login
│   ├── signin.php               # Proses login
│   ├── register.php             # Halaman registrasi
│   ├── register_process.php     # Proses registrasi
│   ├── signup_process.php       # Proses signup
│   ├── logout.php               # Proses logout
│   ├── create_user.php          # Create user baru
│   └── buat_hash.php            # Hash password utility
├── dashboard/                    # Folder dashboard & data management
│   ├── dashboard.php            # Dashboard utama
│   ├── statistics.php           # Halaman statistik
│   ├── manage_data.php          # Manajemen data BTS
│   ├── kelola_data.php          # Kelola data (legacy)
│   ├── manage_users.php         # Manajemen pengguna
│   ├── add_data.php             # Tambah data
│   ├── import_data.php          # Import dari Excel
│   ├── export_data.php          # Export ke Excel
│   ├── download_template.php    # Download template Excel
│   └── toggle_user_status.php   # Toggle status user
├── includes/                     # Folder file include
│   ├── sidebar.php              # Sidebar navigation
│   ├── check_session.php        # Session management
│   ├── security_helper.php      # Helper keamanan
│   └── access_denied.php        # Access denied page
├── api/                         # Folder API
│   └── api.php                  # API endpoints
├── assets/                      # Folder assets
│   ├── css/                     # CSS files
│   │   └── style.css           # Styling utama
│   ├── js/                      # JavaScript files
│   │   ├── main.js             # JavaScript utama
│   │   ├── bts.js              # Logic BTS
│   │   ├── internet.js         # Logic internet
│   │   └── statistics.js       # Logic statistik
│   └── data/                    # Data files
│       └── IndonesiaProvinsi.geojson  # Data geografis
├── config/                      # Folder konfigurasi
│   └── db_bts_aksesinternet_dashboard_internal.sql  # Database schema
└── vendor/                      # Dependencies folder (Composer)
```

## 🔐 Keamanan

Aplikasi ini mengimplementasikan beberapa fitur keamanan:

- Session management yang aman
- Protection terhadap SQL injection
- XSS protection menggunakan HTMLPurifier
- Access control dan authorization

## 📊 Database Schema

Database menggunakan struktur yang terdapat pada file `db_bts_aksesinternet_dashboard_internal.sql`. Import file ini untuk mendapatkan struktur tabel yang diperlukan.


## 🙏 Acknowledgments

- Data geografis Indonesia dari [sumber GeoJSON]
- PHPSpreadsheet untuk export Excel
- Dan semua kontributor open-source library yang digunakan

---

⭐ Jangan lupa untuk memberikan star jika proyek ini bermanfaat!
