<?php
if (!isset($currentPage)) {
    $currentPage = ''; // Nilai default untuk menghindari error jika variabel tidak diatur.
}
?>

<div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-64 z-30 text-white">
    <div class="p-6 border-b border-slate-600">
        <div class="flex items-center space-x-3">
            <div>
                <h2 class="text-lg font-bold">Dashboard BTS</h2>
                <p class="text-xs text-slate-300">Bakti Kominfo</p>
            </div>
        </div>
    </div>

    <nav class="py-6 px-4">
        <div class="space-y-2">
            <a href="../dashboard/dashboard.php" class="sidebar-nav-item flex items-center px-4 py-3 rounded-lg <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                </svg>
                Dashboard
            </a>

            <?php if (checkUserAccess(['super_admin', 'staff', 'pengawas'], false)): ?>
            <a href="../dashboard/manage_data.php" class="sidebar-nav-item flex items-center px-4 py-3 rounded-lg <?php echo ($currentPage === 'manage_data') ? 'active' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Kelola Data
            </a>
            <?php endif; ?>

            <?php if (checkUserAccess(['super_admin', 'staff', 'pengawas'], false)): ?>
            <a href="../dashboard/statistics.php" class="sidebar-nav-item flex items-center px-4 py-3 rounded-lg <?php echo ($currentPage === 'statistics') ? 'active' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                </svg>
                Statistik
            </a>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_role'] === 'super_admin'): ?>
            <a href="../dashboard/manage_users.php" class="sidebar-nav-item flex items-center px-4 py-3 rounded-lg <?php echo ($currentPage === 'manage_users') ? 'active' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                    <path d="M6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                </svg>
                Kelola Pengguna
            </a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-600">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold text-white">
                        <?php echo strtoupper(substr($_SESSION['user_nama'], 0, 1)); ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['user_nama']); ?></p>
                    <p class="text-xs text-slate-300"><?php echo str_replace('_', ' ', ucfirst($_SESSION['user_role'])); ?></p>
                </div>
            </div>
        </div>
        <a href="../auth/logout.php" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 rounded-lg text-base font-bold text-white shadow-md hover:shadow-lg transition-all duration-200">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"></path>
</svg>
    Logout
</a>
    </div>
</div>