<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard BTS & Akses Internet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center">
              <img src="../assets/images/logo_bakti_komdigi.png" alt="Logo BAKTI Kominfo" class="h-20 mx-auto mb-3">
                <h1 class="text-2xl font-bold text-white">Dashboard Login</h1>
                <p class="text-blue-100 text-sm mt-1">BTS & Akses Internet</p>
            </div>
            
            <form action="signin.php" method="POST" class="p-8">
                <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6" role="alert">
                    <p class="font-medium">Login Gagal!</p>
                    <p class="text-sm">
                        <?php 
                            $error = $_GET['error'];
                            switch ($error) {
                                case 'invalid':
                                    echo 'Username atau password salah.';
                                    break;
                                case 'inactive':
                                    echo 'Akun Anda telah dinonaktifkan. Hubungi administrator.';
                                    break;
                                case 'pending':
                                    echo 'Akun Anda masih menunggu persetujuan dari Super Admin.';
                                    break;
                                case 'empty':
                                    echo 'Username dan password harus diisi.';
                                    break;
                                case 'system':
                                    echo 'Terjadi kesalahan sistem. Silakan coba lagi.';
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
                        <p class="font-medium">Akun Berhasil Dibuat!</p>
                        <p class="text-sm">Silakan login dengan akun yang telah dibuat.</p>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['logout'])): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg mb-6" role="alert">
                        <p class="text-sm">Anda telah berhasil logout.</p>
                    </div>
                <?php endif; ?>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="username">
                        Username
                    </label>
                    <div class="relative">
                        <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                               id="username" 
                               name="username" 
                               type="text" 
                               placeholder="Masukkan username" 
                               required>
                        <svg class="absolute right-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">
                        Password
                    </label>
                    <div class="relative">
                        <input class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                               id="password" 
                               name="password" 
                               type="password" 
                               placeholder="Masukkan password" 
                               required>
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                            <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eye-slash-icon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 .847 0 1.67.129 2.46.364m-6.08 6.08a3 3 0 104.243 4.243m-4.243-4.243L8.125 8.125m1.06-1.06L15 15.875M3 3l18 18"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all hover:scale-[1.02]" 
                        type="submit">
                    Masuk
                </button>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        <a href="register.php" class="inline-flex items-center justify-center w-full px-4 py-2 border border-green-600 rounded-lg text-green-600 hover:bg-green-50 transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Daftar Sebagai Staff/Pengawas
                        </a>
                        <p class="text-xs text-gray-500 mt-2">
                            Pendaftaran memerlukan persetujuan dari Super Admin
                        </p>
                    </div>
                </div>
            </form>
        </div>
        
        <p class="text-center text-gray-600 text-xs mt-6">
            © 2025 BAKTI KOMDIGI - Dashboard Internal
        </p>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeSlashIcon = document.getElementById('eye-slash-icon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            eyeIcon.classList.toggle('hidden');
            eyeSlashIcon.classList.toggle('hidden');
        });
    </script>
</body>
</html>