<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAKTI KOMDIGI - Peta Sebaran Infrastruktur Digital Indonesia</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%);
        }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .8;
            }
        }
        
        .hero-decoration {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>
<body class="bg-slate-50">
    
    <!-- Hero Section -->
    <section class="gradient-bg relative overflow-hidden min-h-screen flex items-center">
        <!-- Decorative Elements -->
        <div class="hero-decoration" style="width: 300px; height: 300px; top: 10%; left: -100px;"></div>
        <div class="hero-decoration" style="width: 200px; height: 200px; bottom: 20%; right: -50px; animation-delay: 1s;"></div>
        <div class="hero-decoration" style="width: 150px; height: 150px; top: 50%; right: 10%; animation-delay: 2s;"></div>
        
        <div class="container mx-auto px-6 lg:px-12 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div class="text-white">
                    <div class="bg-white rounded-2xl p-6 inline-block mb-8 shadow-2xl">
                        <img src="assets/images/logo_bakti_komdigi.png" alt="BAKTI KOMDIGI" class="h-16 w-auto">
                    </div>
                    
                    <h1 class="text-5xl lg:text-6xl font-extrabold mb-6 leading-tight">
                        <span class="text-blue-200">Sistem Manajemen</span><br>
                        <span class="text-blue-200">Infrastruktur Digital</span><br>
                        BAKTI KOMDIGI
                    </h1>
                    
                    <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                        Platform digital terintegrasi untuk mengelola peta sebaran dan monitoring infrastruktur BTS & Akses Internet di seluruh Indonesia dengan efisien, transparan, dan terukur.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="auth/login.php" class="bg-white text-blue-900 font-bold py-4 px-8 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center group">
                            <i class="fas fa-sign-in-alt mr-3 group-hover:translate-x-1 transition-transform"></i>
                            Masuk ke Sistem
                        </a>
                        <a href="#fitur" class="bg-transparent border-2 border-white text-white font-bold py-4 px-8 rounded-full hover:bg-white hover:text-blue-900 transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-chevron-down mr-3"></i>
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                
                <!-- Right Content - Feature Preview -->
                <div class="hidden lg:block">
                    <div class="bg-white rounded-3xl p-8 shadow-2xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                        <h3 class="text-2xl font-bold text-slate-800 mb-6 flex items-center">
                            <i class="fas fa-star text-yellow-500 mr-3"></i>
                            Fitur Unggulan
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Feature 1 -->
                            <div class="flex items-start space-x-4">
                                <div class="bg-blue-100 p-4 rounded-xl flex-shrink-0">
                                    <i class="fas fa-map-marked-alt text-blue-600 text-2xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg mb-1">Peta Interaktif</h4>
                                    <p class="text-slate-600 text-sm">Visualisasi sebaran BTS dan akses internet di seluruh Indonesia</p>
                                </div>
                            </div>
                            
                            <!-- Feature 2 -->
                            <div class="flex items-start space-x-4">
                                <div class="bg-green-100 p-4 rounded-xl flex-shrink-0">
                                    <i class="fas fa-chart-line text-green-600 text-2xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg mb-1">Dashboard Statistik</h4>
                                    <p class="text-slate-600 text-sm">Analisis mendalam dengan visualisasi data yang komprehensif dan mudah dipahami</p>
                                </div>
                            </div>
                            
                            <!-- Feature 3 -->
                            <div class="flex items-start space-x-4">
                                <div class="bg-purple-100 p-4 rounded-xl flex-shrink-0">
                                    <i class="fas fa-file-export text-purple-600 text-2xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg mb-1">Export Data Fleksibel</h4>
                                    <p class="text-slate-600 text-sm">Export data dalam format Excel/CSV untuk analisis dan pelaporan lebih lanjut</p>
                                </div>
                            </div>
                            
                            <!-- Feature 4 -->
                            <div class="flex items-start space-x-4">
                                <div class="bg-orange-100 p-4 rounded-xl flex-shrink-0">
                                    <i class="fas fa-user-shield text-orange-600 text-2xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg mb-1">Sistem Akses Berlapis</h4>
                                    <p class="text-slate-600 text-sm">Kontrol akses berbasis role untuk keamanan dan pengelolaan data yang optimal</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Statistics Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="stat-number pulse-animation">34</div>
                    <h3 class="text-xl font-bold text-slate-800 mt-2">Provinsi</h3>
                    <p class="text-slate-600 mt-1">Cakupan Seluruh Indonesia</p>
                </div>
                <div class="text-center">
                    <div class="stat-number pulse-animation">1000+</div>
                    <h3 class="text-xl font-bold text-slate-800 mt-2">Infrastruktur BTS</h3>
                    <p class="text-slate-600 mt-1">Tersebar di Seluruh Nusantara</p>
                </div>
                <div class="text-center">
                    <div class="stat-number pulse-animation">500+</div>
                    <h3 class="text-xl font-bold text-slate-800 mt-2">Titik Akses Internet</h3>
                    <p class="text-slate-600 mt-1">Meningkatkan Konektivitas Digital</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="fitur" class="py-20 bg-gradient-to-b from-slate-50 to-white">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-extrabold text-slate-800 mb-4">
                    <i class="fas fa-sparkles text-yellow-500"></i>
                    Fitur Unggulan
                </h2>
                <p class="text-xl text-slate-600 max-w-3xl mx-auto">
                    Solusi komprehensif untuk pengelolaan infrastruktur digital Indonesia
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature Card 1 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-lg border border-slate-100">
                    <div class="bg-blue-100 w-16 h-16 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-map-marked-alt text-blue-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-3">Peta Interaktif</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Visualisasi geografis infrastruktur BTS dan akses internet dengan teknologi peta interaktif yang responsif
                    </p>
                </div>
                
                <!-- Feature Card 2 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-lg border border-slate-100">
                    <div class="bg-green-100 w-16 h-16 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-chart-bar text-green-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-3">Statistik Lengkap</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Dashboard analitik dengan visualisasi data komprehensif untuk monitoring dan evaluasi infrastruktur
                    </p>
                </div>
                
                <!-- Feature Card 3 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-lg border border-slate-100">
                    <div class="bg-purple-100 w-16 h-16 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-download text-purple-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-3">Export Data</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Ekspor data dalam format Excel atau CSV untuk keperluan analisis dan pelaporan eksternal
                    </p>
                </div>
                
                <div class="feature-card bg-white rounded-2xl p-8 shadow-lg border border-slate-100">
                    <div class="bg-orange-100 w-16 h-16 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-users-cog text-orange-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-3">Manajemen User</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Sistem role-based access control dengan level akses: Super Admin, Staff, dan Pengawas
                    </p>
                </div>
            </div>
        </div>
    </section>
    
   <section class="py-20 bg-white">
    <div class="container mx-auto px-6 lg:px-12">
        <div class="lg:max-w-4xl lg:mx-auto">
            <div>
                <h2 class="text-4xl font-extrabold text-slate-800 mb-6">
                    Keunggulan Platform
                </h2>
                <p class="text-lg text-slate-600 mb-8">
                    Sistem yang dirancang khusus untuk memenuhi kebutuhan monitoring dan pengelolaan infrastruktur digital Indonesia
                </p>
                
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-100 p-3 rounded-lg flex-shrink-0">
                            <i class="fas fa-check text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg mb-1">Monitoring</h4>
                            <p class="text-slate-600">Pantau status infrastruktur dengan update otomatis</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="bg-green-100 p-3 rounded-lg flex-shrink-0">
                            <i class="fas fa-check text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg mb-1">Data Terpusat</h4>
                            <p class="text-slate-600">Semua informasi infrastruktur tersimpan dalam satu platform terpadu</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="bg-purple-100 p-3 rounded-lg flex-shrink-0">
                            <i class="fas fa-check text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg mb-1">Keamanan Data</h4>
                            <p class="text-slate-600">Sistem keamanan berlapis dengan enkripsi dan kontrol akses yang ketat</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="bg-orange-100 p-3 rounded-lg flex-shrink-0">
                            <i class="fas fa-check text-orange-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg mb-1">Laporan Otomatis</h4>
                            <p class="text-slate-600">Generate laporan komprehensif dengan sekali klik</p>
                        </div>
                    </div>
                </div>
            </div>
            
            </div>
    </div>
</section>
    
    <section class="py-20 gradient-bg relative overflow-hidden">
        <div class="hero-decoration" style="width: 400px; height: 400px; top: -100px; right: -100px;"></div>
        <div class="hero-decoration" style="width: 300px; height: 300px; bottom: -50px; left: -50px; animation-delay: 1s;"></div>
        
        <div class="container mx-auto px-6 lg:px-12 text-center relative z-10">
            <h2 class="text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Siap Memulai?
            </h2>
            <p class="text-xl text-blue-100 mb-10 max-w-2xl mx-auto">
                Akses sistem manajemen infrastruktur digital dan mulai monitoring jaringan BTS & akses internet di seluruh Indonesia
            </p>
            <a href="login.php" class="inline-flex items-center bg-white text-blue-900 font-bold py-4 px-10 rounded-full shadow-2xl hover:shadow-xl transition-all duration-300 text-lg group">
                <i class="fas fa-sign-in-alt mr-3 group-hover:translate-x-1 transition-transform"></i>
                Masuk ke Dashboard
            </a>
        </div>
    </section>
        
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#' || href === '#!' || !href) {
                    e.preventDefault();
                    return;
                }
                
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = entry.target.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        const finalNumber = stat.textContent.replace('+', '');
                        let current = 0;
                        const increment = parseInt(finalNumber) / 50;
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= parseInt(finalNumber)) {
                                stat.textContent = finalNumber + (stat.textContent.includes('+') ? '+' : '');
                                clearInterval(timer);
                            } else {
                                stat.textContent = Math.floor(current) + (stat.textContent.includes('+') ? '+' : '');
                            }
                        }, 30);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        const statsSection = document.querySelector('.py-20.bg-white');
        if (statsSection) {
            observer.observe(statsSection);
        }
    </script>
</body>
</html>