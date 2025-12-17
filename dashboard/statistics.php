<?php

require '../includes/check_session.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik - Dashboard BTS & Akses Internet</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-100">

    <?php 
    $currentPage = 'statistics';
    require '../includes/sidebar.php'; 
    ?>

    <button id="mobile-menu-button" class="lg:hidden fixed top-4 left-4 z-40 bg-slate-800 text-white p-2 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <div id="main-content" class="main-content ml-64 min-h-screen">
        <div class="container mx-auto p-4 md:p-8">
            <div class="flex flex-col lg:flex-row justify-between lg:items-center mb-8 space-y-4 lg:space-y-0">
                <div>
                    <h1 id="stats-title" class="text-4xl font-extrabold text-slate-800">Memuat...</h1>
                    <p id="stats-subtitle" class="text-sm text-slate-600 mt-1">Silakan tunggu...</p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="bg-slate-200 p-1 rounded-lg flex items-center space-x-1">
                        <button id="btn-stats-bts" class="px-4 py-1.5 rounded-md text-sm font-semibold transition-colors">
                            <i class="fas fa-broadcast-tower mr-2"></i>Statistik BTS
                        </button>
                        <button id="btn-stats-internet" class="px-4 py-1.5 rounded-md text-sm font-semibold transition-colors">
                            <i class="fas fa-wifi mr-2"></i>Statistik Internet
                        </button>
                    </div>
                    
                    <select id="timeFilter" class="bg-white rounded-lg px-4 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Semua Data</option>
                        <option value="30">30 Hari Terakhir</option>
                        <option value="90">90 Hari Terakhir</option>
                        <option value="365">1 Tahun Terakhir</option>
                    </select>
                </div>
            </div>

            <div id="loadingState" class="text-center py-12 hidden">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-slate-600 mt-2">Memuat statistik...</p>
            </div>

            <div id="statisticsContent">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600 uppercase tracking-wide">Total Infrastruktur</p>
                                <p class="text-3xl font-bold text-slate-800" id="totalInfrastructure">-</p>
                                <p class="text-xs text-slate-500 mt-1" id="infrastructureBreakdown">Loading...</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fas fa-broadcast-tower text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600 uppercase tracking-wide">Tingkat Operasional</p>
                                <p class="text-3xl font-bold text-green-600" id="operationalRate">-</p>
                                <p class="text-xs text-slate-500 mt-1" id="operationalCount">Loading...</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p id="construction-label" class="text-sm font-medium text-slate-600 uppercase tracking-wide">Dalam Pembangunan</p>                                <p class="text-3xl font-bold text-yellow-600" id="underConstruction">-</p>
                                <p class="text-xs text-slate-500 mt-1" id="constructionPercentage">Loading...</p>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <i class="fas fa-hard-hat text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600 uppercase tracking-wide">Skor Cakupan</p>
                                <p class="text-3xl font-bold text-purple-600" id="coverageScore">-</p>
                                <p class="text-xs text-slate-500 mt-1">Distribusi geografis</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="fas fa-map-marked-alt text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Distribusi Regional</h3>
                        <div id="regionalDistribution">
                            <div class="space-y-4" id="regionalData">
                                </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Distribusi Teknologi</h3>
                        <div class="h-64">
                            <canvas id="technologyChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Indeks Kesenjangan Digital</h3>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-red-600 mb-2" id="digitalDivideIndex">-</div>
                            <p class="text-sm text-slate-600 mb-4">Gap akses antar wilayah</p>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <p class="text-xs text-red-700" id="divideInsight">Menghitung...</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Network Redundancy</h3>
                        <div class="text-center">
                            <div class="relative inline-flex">
                                <svg class="w-20 h-20 transform -rotate-90">
                                    <circle cx="40" cy="40" r="30" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                                    <circle cx="40" cy="40" r="30" stroke="#10b981" stroke-width="8" fill="none"
                                        stroke-dasharray="188.5" stroke-dashoffset="75.4" stroke-linecap="round" id="redundancyCircle"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-slate-800" id="redundancyScore">-</span>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 mt-2">Wilayah dengan backup</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Technology Readiness</h3>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-slate-700">5G Ready</span>
                                    <span class="text-sm text-slate-500" id="fiveGReady">-%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-1000" id="fiveGBar"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-slate-700">Fiber Coverage</span>
                                    <span class="text-sm text-slate-500" id="fiberCoverage">-%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full transition-all duration-1000" id="fiberBar"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-slate-700">Legacy (2G/3G)</span>
                                    <span class="text-sm text-slate-500" id="legacyCoverage">-%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2">
                                    <div class="bg-yellow-600 h-2 rounded-full transition-all duration-1000" id="legacyBar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Insight & Rekomendasi Strategis
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <h4 class="font-bold text-slate-800 mb-2 flex items-center">
                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                Prioritas Tinggi
                            </h4>
                            <div id="highPriorityInsights" class="text-sm text-slate-600 space-y-1">
                                <p>Memuat rekomendasi...</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <h4 class="font-bold text-slate-800 mb-2 flex items-center">
                                <i class="fas fa-chart-line text-green-500 mr-2"></i>
                                Peluang Optimisasi
                            </h4>
                            <div id="optimizationInsights" class="text-sm text-slate-600 space-y-1">
                                <p>Memuat rekomendasi...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        window.userRole = '<?php echo $_SESSION['user_role']; ?>';
        
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('mobile-open');
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
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('mobile-open');
                mainContent.style.marginLeft = '256px';
            } else {
                mainContent.style.marginLeft = '0';
            }
        });
    </script>
    
    <script src="../assets/js/statistics.js?v=<?php echo time(); ?>"></script>
</body>
</html>