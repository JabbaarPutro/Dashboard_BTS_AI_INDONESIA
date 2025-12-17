<?php require '../includes/check_session.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Peta Sebaran Infrastruktur Digital</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .leaflet-control-attribution {
            display: none !important;
        }
        .leaflet-bottom.leaflet-right {
            display: none !important;
        }
    </style>
</head>
<body class="bg-slate-100">

    <?php 
    $currentPage = 'dashboard';
    require '../includes/sidebar.php'; 
    ?>

    <button id="mobile-menu-button" class="lg:hidden fixed top-4 left-4 z-40 bg-slate-800 text-white p-2 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <div id="main-content" class="main-content ml-64 min-h-screen">
        <div id="province-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-40 flex justify-center items-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 id="modal-province-title" class="text-2xl font-bold text-slate-800">Detail Provinsi</h3>
                    <div class="flex items-center space-x-3">
                        <?php if ($_SESSION['user_role'] === 'super_admin' || $_SESSION['user_role'] === 'staff'): ?>
                        <button id="export-province-data" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Excel
                        </button>
                        <?php endif; ?>
                        <button id="close-province-modal" class="text-slate-500 hover:text-red-600 text-4xl leading-none font-bold">&times;</button>
                    </div>
                </div>
                <div id="modal-province-content" class="p-6 overflow-y-auto"></div>
            </div>
        </div>
        
        <div id="add-data-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4" style="z-index: 1050;"></div>
        
        <div class="container mx-auto p-4 md:p-8">
            <div class="flex flex-col lg:flex-row justify-between lg:items-center mb-8 space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <img src="../assets/images/logo_bakti_komdigi.png" alt="Logo BAKTI Kominfo" class="h-16 w-auto">
                    <div>
                        <h1 id="main-title" class="text-4xl font-extrabold text-slate-800">Dashboard</h1>
                        <p class="text-sm text-slate-600 mt-1">Selamat datang, <?php echo htmlspecialchars($_SESSION['user_nama']); ?></p>
                        <p id="last-updated" class="text-sm text-slate-500 mt-2">Memilih tampilan...</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="flex rounded-full bg-slate-200 p-1 w-full sm:w-auto">
                        <button id="btn-view-bts" data-dashboard="bts" class="px-6 py-2 text-sm font-semibold rounded-full bg-white text-blue-600 shadow-md">BTS</button>
                        <button id="btn-view-internet" data-dashboard="internet" class="px-6 py-2 text-sm font-semibold rounded-full text-slate-600">Akses Internet</button>
                    </div>
                    
                    <?php if ($_SESSION['user_role'] === 'super_admin' || $_SESSION['user_role'] === 'staff'): ?>
                    <button id="btn-add-data" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-full shadow-md w-full sm:w-auto flex-shrink-0">Tambah Data</button>
                    <?php endif; ?>
                </div>
            </div>

            <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <strong class="font-bold">Koneksi Gagal!</strong>
                <span class="block sm:inline">Tidak dapat mengambil data dari server. Pastikan XAMPP berjalan dan file PHP benar.</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-md stat-card flex items-center space-x-4">
                    <div class="bg-slate-100 p-3 rounded-full"><svg class="w-8 h-8 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" /></svg></div>
                    <div><h2 id="stat-label-total" class="text-slate-500 text-sm font-medium">Total</h2><p id="stat-total" class="text-3xl font-bold text-slate-800">0</p></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md stat-card flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0" />
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25h.01" />
                        </svg>
                    </div>
                    <div><h2 id="stat-label-status1" class="text-slate-500 text-sm font-medium">Status 1</h2><p id="stat-on-air" class="text-3xl font-bold text-blue-600">0</p></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md stat-card flex items-center space-x-4">
                    <div class="bg-orange-100 p-3 rounded-full"><svg class="w-8 h-8 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                    <div><h2 id="stat-label-status2" class="text-slate-500 text-sm font-medium">Status 2</h2><p id="stat-on-progress" class="text-3xl font-bold text-orange-500">0</p></div>
                </div>
            </div>
            
            <div class="flex flex-col lg:flex-row items-center justify-between space-y-4 lg:space-y-0 lg:space-x-4 mb-8">
                <div id="filters-container" class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 w-full lg:w-auto"></div>
                <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 w-full lg:w-auto">
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="province-search" list="province-list" placeholder="Cari Provinsi..." class="w-full bg-white rounded-full py-2.5 pl-4 pr-10 text-slate-700 shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <datalist id="province-list"></datalist>
                    </div>
                </div>
            </div>

           <div class="flex flex-col gap-6">
                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md">
                    <h2 id="map-title" class="text-xl font-bold text-slate-800 mb-4">Peta Sebaran</h2>
                    <div class="h-[65vh] w-full rounded-lg overflow-hidden"><div id="map" class="h-full w-full"></div></div>
                </div>

                <div id="province-detail-sidebar" class="hidden w-full bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-4 pb-4 border-b">
                        <h3 id="sidebar-province-title" class="text-2xl font-bold text-slate-800">Detail Provinsi</h3>
                        <div class="flex items-center space-x-3">
                            <?php if ($_SESSION['user_role'] === 'super_admin' || $_SESSION['user_role'] === 'staff' || $_SESSION['user_role'] === 'pengawas'): ?>
                            <button id="export-sidebar-data" style="display: none;" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </button>
                            <?php endif; ?>
                            <button id="close-sidebar-btn" class="text-slate-500 hover:text-red-600 text-4xl leading-none font-bold">&times;</button>
                        </div>
                    </div>
                    <div id="sidebar-content" class="max-h-[80vh] overflow-y-auto">
                        <p id="sidebar-placeholder" class="text-slate-500 text-center py-10">Klik sebuah provinsi pada peta untuk melihat detailnya.</p>
                    </div>
                </div>
             </div>
        </div>
    </div>
    
    <script>
        window.userRole = '<?php echo $_SESSION['user_role']; ?>';
        
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        });

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileButton = document.getElementById('mobile-menu-button');
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(event.target) && !mobileButton.contains(event.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                document.getElementById('sidebar').classList.remove('mobile-open');
            }
        });
    </script>
    
    <script src="../assets/js/bts.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/internet.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>