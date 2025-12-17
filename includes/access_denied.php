<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Dashboard BTS & Akses Internet</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-red-50 to-red-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white shadow-2xl rounded-2xl p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-red-800 mb-2">Akses Ditolak</h1>
                <p class="text-red-600">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-700">
                    Halaman ini memerlukan tingkat akses yang lebih tinggi. 
                    Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
                </p>
            </div>
            
            <div class="space-y-3">
                <a href="../index.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                    Kembali ke Dashboard
                </a>
                <a href="../auth/logout.php" class="block w-full bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-3 px-4 rounded-lg transition-colors">
                    Logout
                </a>
            </div>
        </div>
        
        <p class="text-center text-gray-600 text-xs mt-6">
            © 2025 BAKTI Kominfo - Dashboard Internal
        </p>
    </div>
</body>
</html>