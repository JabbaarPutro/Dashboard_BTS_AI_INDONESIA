# Struktur Folder Dashboard BTS AI Internal

Dokumen ini menjelaskan struktur folder yang telah diorganisir untuk kemudahan maintenance dan pengembangan.

## 📁 Struktur Lengkap

```
Dashboard_BTS_AI_INTERNAL/
│
├── 📄 index.php                  # Landing page utama
├── 📄 composer.json              # Dependencies management
├── 📄 README.md                  # Dokumentasi utama
├── 📄 STRUKTUR_FOLDER.md         # Dokumentasi struktur folder (file ini)
│
├── 📂 auth/                      # Folder Autentikasi & User Management
│   ├── login.php                # Halaman login
│   ├── signin.php               # Proses autentikasi login
│   ├── register.php             # Halaman registrasi
│   ├── register_process.php     # Proses registrasi user baru
│   ├── signup_process.php       # Proses signup alternatif
│   ├── logout.php               # Proses logout & destroy session
│   ├── create_user.php          # Form create user manual (admin)
│   └── buat_hash.php            # Utility untuk generate password hash
│
├── 📂 dashboard/                 # Folder Dashboard & Data Management
│   ├── dashboard.php            # Dashboard utama dengan peta interaktif
│   ├── statistics.php           # Halaman statistik & analytics
│   ├── manage_data.php          # CRUD data BTS & Internet
│   ├── kelola_data.php          # Kelola data (legacy - deprecated)
│   ├── manage_users.php         # Manajemen pengguna sistem
│   ├── add_data.php             # API endpoint tambah data
│   ├── import_data.php          # Import data dari Excel/CSV
│   ├── export_data.php          # Export data ke Excel
│   ├── download_template.php    # Download template import Excel
│   └── toggle_user_status.php   # Toggle status aktif/nonaktif user
│
├── 📂 includes/                  # Folder Include Files & Helpers
│   ├── sidebar.php              # Sidebar navigation component
│   ├── check_session.php        # Session validation & access control
│   ├── security_helper.php      # Security helper functions
│   └── access_denied.php        # Access denied page
│
├── 📂 api/                       # Folder API Endpoints
│   └── api.php                  # REST API untuk data BTS & Internet
│
├── 📂 assets/                    # Folder Static Assets
│   ├── 📂 css/                  # Stylesheets
│   │   └── style.css           # Main stylesheet
│   │
│   ├── 📂 js/                   # JavaScript Files
│   │   ├── main.js             # Main JavaScript logic
│   │   ├── bts.js              # BTS map & data logic
│   │   ├── internet.js         # Internet map & data logic
│   │   └── statistics.js       # Statistics & charts logic
│   │
│   └── 📂 data/                 # Data Files
│       └── IndonesiaProvinsi.geojson  # GeoJSON Indonesia provinces
│
├── 📂 config/                    # Folder Configuration
│   └── db_bts_aksesinternet_dashboard_internal.sql  # Database schema & initial data
│
└── 📂 vendor/                    # Composer Dependencies (auto-generated)
    └── ...                       # Third-party libraries


## 🔗 Navigasi Antar Folder

### Dari Dashboard ke:
- **Auth**: `../auth/login.php`, `../auth/logout.php`
- **Includes**: `../includes/sidebar.php`, `../includes/check_session.php`
- **API**: `../api/api.php`
- **Assets**: `../assets/css/style.css`, `../assets/js/main.js`

### Dari Auth ke:
- **Dashboard**: `../dashboard/dashboard.php`
- **Includes**: `../includes/check_session.php`
- **Assets**: `../assets/css/style.css`
- **Index**: `../index.php`

### Dari Includes ke:
- **Dashboard**: `../dashboard/dashboard.php`
- **Auth**: `../auth/login.php`, `../auth/logout.php`

## 📝 Catatan Penting

1. **Semua file PHP di folder `dashboard/`** harus include:
   - `../includes/check_session.php` untuk validasi session
   - `../includes/sidebar.php` untuk navigation

2. **Semua file PHP di folder `auth/`** yang memerlukan session harus include:
   - `../includes/check_session.php`

3. **Path relatif untuk assets**:
   - CSS: `../assets/css/style.css`
   - JS: `../assets/js/[filename].js`
   - Data: `../assets/data/[filename]`

4. **API Calls dari JavaScript**:
   - Gunakan path: `../api/api.php?type=[type]`

## 🔄 Perubahan dari Struktur Lama

### Before (Root Folder):
```
Dashboard_BTS_AI_INTERNAL/
├── login.php
├── dashboard.php
├── api.php
├── style.css
├── main.js
└── ...semua file tercampur
```

### After (Organized):
```
Dashboard_BTS_AI_INTERNAL/
├── auth/          # File autentikasi
├── dashboard/     # File dashboard
├── includes/      # File include
├── api/          # File API
├── assets/       # Static files
└── config/       # Configuration
```

## ✅ Keuntungan Struktur Baru

1. **Mudah di-maintain** - File terorganisir berdasarkan fungsi
2. **Scalable** - Mudah menambah fitur baru tanpa konflik
3. **Secure** - Separation of concerns untuk security
4. **Clean** - Root directory lebih bersih dan profesional
5. **Team-friendly** - Tim developer mudah memahami struktur

## 🚀 Tips Development

- Selalu gunakan path relatif (`../`) untuk referensi antar folder
- Jangan hardcode absolute path
- Gunakan `check_session.php` di setiap halaman yang memerlukan autentikasi
- Test semua link setelah perubahan struktur

## 📞 Kontak

Jika ada pertanyaan tentang struktur folder, silakan hubungi tim development.

---
**Update Terakhir:** Desember 2025
**Versi:** 2.0 (Reorganized Structure)
