<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun - Dashboard BTS & Akses Internet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center">
                <img src="../assets/images/logo_bakti_komdigi.png" alt="Logo BAKTI Kominfo" class="h-20 mx-auto mb-3">
                <h1 class="text-2xl font-bold text-white">Pendaftaran Akun</h1>
                <p class="text-blue-100 text-sm mt-1">Staff & Pengawas</p>
            </div>
            
            <form action="register_process.php" method="POST" class="p-8" onsubmit="return validateForm()">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded-lg mb-6">
                    <p class="text-sm">
                        <strong>Perhatian:</strong> Akun yang didaftarkan akan menunggu persetujuan dari Super Admin. 
                        Hanya email dengan domain <strong>@baktikominfo.id</strong> yang diizinkan.
                    </p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6" role="alert">
                        <p class="font-medium">Pendaftaran Gagal!</p>
                        <p class="text-sm">
                            <?php 
                                $error = $_GET['error'];
                                switch ($error) {
                                    case 'password':
                                        echo 'Password dan konfirmasi password tidak cocok.';
                                        break;
                                    case 'domain':
                                        echo 'Hanya email dengan domain @baktikominfo.id yang diizinkan.';
                                        break;
                                    case 'exists':
                                        echo 'Username atau email sudah terdaftar atau sedang menunggu persetujuan.';
                                        break;
                                    case 'empty':
                                        echo 'Semua field harus diisi.';
                                        break;
                                    default:
                                        echo 'Terjadi kesalahan. Silakan coba lagi.';
                                }
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
                        <p class="font-medium">Pendaftaran Berhasil!</p>
                        <p class="text-sm">Akun Anda telah terdaftar dan menunggu persetujuan dari Super Admin. Anda akan dihubungi via email setelah akun disetujui.</p>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="nama_lengkap">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           id="nama_lengkap" 
                           name="nama_lengkap" 
                           type="text" 
                           placeholder="Contoh: John Doe" 
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="email">
                        Email Resmi <span class="text-red-500">*</span>
                    </label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           id="email" 
                           name="email" 
                           type="email" 
                           placeholder="nama@baktikominfo.id" 
                           pattern=".*@baktikominfo\.id$"
                           title="Harus menggunakan email @baktikominfo.id"
                           required>
                    <p class="text-xs text-gray-500 mt-1">Gunakan email resmi @baktikominfo.id</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="username">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           id="username" 
                           name="username" 
                           type="text" 
                           placeholder="Contoh: john_doe" 
                           pattern="^[a-zA-Z0-9_]{3,20}$"
                           title="Username harus 3-20 karakter, hanya huruf, angka, dan underscore"
                           required>
                    <p class="text-xs text-gray-500 mt-1">3-20 karakter, huruf, angka, dan underscore</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="role">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                            id="role" 
                            name="role" 
                            required>
                        <option value="">Pilih Jabatan</option>
                        <option value="staff">Staff</option>
                        <option value="pengawas">Pengawas</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           id="password" 
                           name="password" 
                           type="password" 
                           placeholder="Minimal 8 karakter" 
                           minlength="8"
                           required>
                    <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="confirm_password">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           id="confirm_password" 
                           name="confirm_password" 
                           type="password" 
                           placeholder="Ulangi password" 
                           minlength="8"
                           required>
                </div>
                
                <button class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all hover:scale-[1.02] mb-4" 
                        type="submit">
                    Daftar Akun
                </button>
                
                <div class="text-center">
                    <a href="login.php" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                        ← Sudah punya akun? Login di sini
                    </a>
                </div>
            </form>
        </div>
        
        <p class="text-center text-gray-600 text-xs mt-6">
            © 2025 BAKTI Kominfo - Dashboard Internal
        </p>
    </div>

    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const email = document.getElementById('email').value;
            const role = document.getElementById('role').value;
            
            if (!email.toLowerCase().endsWith('@baktikominfo.id')) {
                alert('Email harus menggunakan domain @baktikominfo.id');
                return false;
            }
            
            if (!role) {
                alert('Silakan pilih jabatan Anda');
                return false;
            }
            
            if (password !== confirmPassword) {
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>