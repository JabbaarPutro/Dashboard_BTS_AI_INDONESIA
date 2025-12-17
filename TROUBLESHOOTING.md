# Troubleshooting Guide - Dashboard BTS AI Internal

## 🔧 Solusi Error Yang Sudah Diperbaiki

### Error 404 - File Not Found

#### 1. Logo tidak ditemukan (logo_bakti_komdigi.png)
**Error:** `Failed to load resource: logo_bakti_komdigi.png - 404 (Not Found)`

**Solusi:**
- Logo dipindahkan ke `assets/images/logo_bakti_komdigi.png`
- Semua referensi diupdate:
  - Dashboard: `../assets/images/logo_bakti_komdigi.png`
  - Auth pages: `../assets/images/logo_bakti_komdigi.png`
  - Index: `assets/images/logo_bakti_komdigi.png`

#### 2. JavaScript files tidak ditemukan (bts.js, main.js, etc)
**Error:** `Failed to load resource: bts.js - 404 (Not Found)`

**Solusi:**
- File JS dipindahkan ke `assets/js/`
- Path diupdate di dashboard.php:
  ```html
  <script src="../assets/js/bts.js?v=<?php echo time(); ?>"></script>
  <script src="../assets/js/internet.js?v=<?php echo time(); ?>"></script>
  <script src="../assets/js/main.js?v=<?php echo time(); ?>"></script>
  ```
- Cache busting parameter (`?v=<?php echo time(); ?>`) ditambahkan untuk mencegah caching issue

## 🚀 Cara Mengakses Aplikasi Setelah Fix

1. **Clear browser cache** atau gunakan Ctrl+Shift+R (hard refresh)
2. **Akses URL:**
   - Landing: `http://localhost/Dashboard_BTS_AI_INTERNAL/index.php`
   - Login: `http://localhost/Dashboard_BTS_AI_INTERNAL/auth/login.php`
   - Dashboard: `http://localhost/Dashboard_BTS_AI_INTERNAL/dashboard/dashboard.php`

## 📁 Struktur File Assets

```
assets/
├── css/
│   └── style.css
├── js/
│   ├── main.js
│   ├── bts.js
│   ├── internet.js
│   └── statistics.js
├── data/
│   └── IndonesiaProvinsi.geojson
└── images/
    └── logo_bakti_komdigi.png
```

## 🔍 Cara Debugging Error

### 1. Check File Exists
```powershell
# Verifikasi file ada
Test-Path "assets\images\logo_bakti_komdigi.png"
Test-Path "assets\js\bts.js"
```

### 2. Check Browser Console
- Tekan F12 untuk membuka DevTools
- Lihat tab Console untuk error messages
- Lihat tab Network untuk failed requests

### 3. Clear Cache
```
Ctrl + Shift + Delete (Clear browsing data)
atau
Ctrl + Shift + R (Hard reload)
```

## ⚠️ Common Issues

### Issue: Script masih 404 setelah fix
**Penyebab:** Browser cache
**Solusi:** 
1. Clear browser cache
2. Hard refresh (Ctrl+Shift+R)
3. Gunakan Private/Incognito mode

### Issue: Logo tidak muncul
**Penyebab:** Path relatif salah
**Solusi:** 
- Dari folder `dashboard/`: gunakan `../assets/images/logo_bakti_komdigi.png`
- Dari folder `auth/`: gunakan `../assets/images/logo_bakti_komdigi.png`
- Dari root: gunakan `assets/images/logo_bakti_komdigi.png`

### Issue: API tidak response
**Penyebab:** Path API salah di JavaScript
**Solusi:** 
- Pastikan API calls menggunakan: `../api/api.php?type=[type]`
- Check file `api/api.php` exists

## 🔐 Security Files Added

### 1. Root .htaccess
- Proteksi vendor folder
- Proteksi config folder (SQL files)
- Security headers
- Directory listing disabled

### 2. Assets .htaccess
- CORS enabled untuk static assets
- Proper MIME types
- Cache control untuk performance

## 📝 Files Modified in Fix

1. ✅ `dashboard/dashboard.php` - Updated paths & cache busting
2. ✅ `dashboard/statistics.php` - Updated script path
3. ✅ `dashboard/manage_data.php` - Updated logo path
4. ✅ `auth/login.php` - Updated logo path
5. ✅ `auth/register.php` - Updated logo path
6. ✅ `index.php` - Updated logo path
7. ✅ `assets/.htaccess` - Created for asset management
8. ✅ `.htaccess` - Security configuration

## 🎯 Testing Checklist

Setelah fix, test hal berikut:

- [ ] Index page loads without errors
- [ ] Login page loads with logo
- [ ] Dashboard loads with map
- [ ] BTS tab shows data correctly
- [ ] Internet tab shows data correctly
- [ ] Statistics page loads with charts
- [ ] No 404 errors in console
- [ ] Logo displays correctly on all pages

## 💡 Tips

1. **Selalu gunakan relative paths** sesuai struktur folder
2. **Clear cache** setelah perubahan file
3. **Check console** untuk error messages
4. **Gunakan cache busting** untuk file yang sering berubah
5. **Backup** sebelum melakukan perubahan besar

## 📞 Support

Jika masih ada masalah, check:
1. File permissions (755 untuk folder, 644 untuk files)
2. Apache/PHP error logs
3. Browser console errors
4. Network tab untuk failed requests

---
**Last Updated:** Desember 17, 2025
**Version:** 2.0
