
document.addEventListener('DOMContentLoaded', function() {
    let technologyChart = null;
    let currentView = 'bts';

    const btnBts = document.getElementById('btn-stats-bts');
    const btnInternet = document.getElementById('btn-stats-internet');
    const titleElement = document.getElementById('stats-title');
    const subtitleElement = document.getElementById('stats-subtitle');
    const timeFilter = document.getElementById('timeFilter');
    const constructionLabel = document.getElementById('construction-label');
    
    function updateButtonStyles() {
        if (currentView === 'bts') {
            btnBts.classList.add('bg-white', 'text-blue-600', 'shadow-md');
            btnBts.classList.remove('text-slate-600');
            btnInternet.classList.remove('bg-white', 'text-blue-600', 'shadow-md');
            btnInternet.classList.add('text-slate-600');
        } else {
            btnInternet.classList.add('bg-white', 'text-blue-600', 'shadow-md');
            btnInternet.classList.remove('text-slate-600');
            btnBts.classList.remove('bg-white', 'text-blue-600', 'shadow-md');
            btnBts.classList.add('text-slate-600');
        }
    }

    function updateTitlesAndLabels() {
        if (currentView === 'bts') {
            titleElement.textContent = 'Statistik BTS';
            subtitleElement.textContent = 'Analisis mendalam infrastruktur Base Transceiver Station (BTS)';
            constructionLabel.textContent = 'Dalam Pembangunan';
        } else {
            titleElement.textContent = 'Statistik Akses Internet';
            subtitleElement.textContent = 'Analisis mendalam infrastruktur titik akses internet publik';
            constructionLabel.textContent = 'Dalam Instalasi';
        }
    }
    
    async function loadStatistics() {
        try {
            const sourceParam = `&source=${currentView}`;
            
            const [regional, overview, technology, insights] = await Promise.all([
                fetch(`../api/api.php?type=stats_regional${sourceParam}`).then(r => r.json()),
                fetch(`../api/api.php?type=stats_overview${sourceParam}`).then(r => r.json()),
                fetch(`../api/api.php?type=stats_technology${sourceParam}`).then(r => r.json()),
                fetch(`../api/api.php?type=stats_insights${sourceParam}`).then(r => r.json())
            ]);
            
            updateProvinceDistribution(regional);
            updateOverview(overview);
            updateTechnologyChart(technology);
            updateInsights(insights);
            
        } catch (error) {
            console.error('Error loading statistics:', error);
            showError('Gagal memuat statistik. Silakan refresh halaman.');
        }
    }
    
    function switchView(view) {
        if (currentView === view) return; 
        
        currentView = view;
        updateButtonStyles();
        updateTitlesAndLabels();
        loadStatistics();
    }

    function initializePage() {
        updateButtonStyles();
        updateTitlesAndLabels();
        loadStatistics();
    }

    function updateProvinceDistribution(data) {
        const container = document.getElementById('regionalData');
        container.innerHTML = '';
        const { topProvinces, grandTotal } = data;
        if (!topProvinces || topProvinces.length === 0) {
            container.innerHTML = '<p class="text-slate-500">Data provinsi tidak tersedia.</p>';
            return;
        }
        topProvinces.forEach((province, index) => {
            const percentage = grandTotal > 0 ? ((province.count / grandTotal) * 100).toFixed(1) : 0;
            const rank = index + 1;
            container.innerHTML += `
                <div class="flex items-center justify-between">
                    <div class="flex items-center flex-1">
                        <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs mr-3">
                            ${rank}
                        </div>
                        <span class="text-sm font-medium text-slate-700">${province.provinsi}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-slate-900">${Number(province.count).toLocaleString('id-ID')} unit</span>
                        <p class="text-xs text-slate-500">${percentage}% dari total</p>
                    </div>
                </div>`;
        });
    }

    function updateOverview(data) {
        animateCounter('totalInfrastructure', data.totalInfrastructure);
        document.getElementById('infrastructureBreakdown').textContent = data.infrastructureBreakdown;
        animateCounter('operationalRate', data.operationalRate, '%');
        document.getElementById('operationalCount').textContent = data.operationalCount;
        animateCounter('underConstruction', data.underConstruction);
        document.getElementById('constructionPercentage').textContent = data.constructionPercentage;
        document.getElementById('coverageScore').textContent = data.coverageScore;
    }
    
    function updateTechnologyChart(data) {
        const ctx = document.getElementById('technologyChart').getContext('2d');
        if (technologyChart) technologyChart.destroy();
        technologyChart = new Chart(ctx, {
            type: 'doughnut',
            data: { 
                labels: data.labels, 
                datasets: [{ data: data.data, backgroundColor: data.colors, borderWidth: 0 }] 
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { 
                    legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value.toLocaleString('id-ID')} (${percentage}%)`;
                            }
                        }
                    }
                } 
            }
        });
    }
    
    function updateInsights(data) {
        const setVisibility = (elementId, value) => {
            const el = document.getElementById(elementId);
            if (!el) return;
            const parentContainer = el.closest('.bg-white.rounded-xl.shadow-md') || el.closest('div[class^="space-y"] > div');
            if (parentContainer) {
                parentContainer.style.display = (value === 'N/A' || value === null) ? 'none' : '';
            }
        };
        setVisibility('digitalDivideIndex', data.digitalDivideIndex);
        document.getElementById('digitalDivideIndex').textContent = data.digitalDivideIndex;
        document.getElementById('divideInsight').textContent = data.divideInsight;
        setVisibility('redundancyScore', data.redundancyScore);
        document.getElementById('redundancyScore').textContent = (data.redundancyScore || 0) + '%';
        const circumference = 2 * Math.PI * 30;
        const offset = circumference - ((data.redundancyScore || 0) / 100) * circumference;
        document.getElementById('redundancyCircle').style.strokeDashoffset = offset;
        setVisibility('fiveGReady', data.fiveGReady);
        document.getElementById('fiveGReady').textContent = (data.fiveGReady || 0) + '%';
        document.getElementById('fiveGBar').style.width = (data.fiveGReady || 0) + '%';
        setVisibility('fiberCoverage', data.fiberCoverage);
        document.getElementById('fiberCoverage').textContent = (data.fiberCoverage || 0) + '%';
        document.getElementById('fiberBar').style.width = (data.fiberCoverage || 0) + '%';
        setVisibility('legacyCoverage', data.legacyCoverage);
        document.getElementById('legacyCoverage').textContent = (data.legacyCoverage || 0) + '%';
        document.getElementById('legacyBar').style.width = (data.legacyCoverage || 0) + '%';
        const highPriorityContainer = document.getElementById('highPriorityInsights');
        const optimizationContainer = document.getElementById('optimizationInsights');
        if (data.highPriority && data.highPriority.length > 0) {
            highPriorityContainer.innerHTML = data.highPriority.map(item => `<div class="flex items-start"><i class="fas fa-circle text-red-500 text-xs mr-2 mt-1"></i><span>${item}</span></div>`).join('');
        } else {
            highPriorityContainer.innerHTML = '<p class="text-green-600">Tidak ada prioritas tinggi saat ini.</p>';
        }
        if (data.optimization && data.optimization.length > 0) {
            optimizationContainer.innerHTML = data.optimization.map(item => `<div class="flex items-start"><i class="fas fa-circle text-green-500 text-xs mr-2 mt-1"></i><span>${item}</span></div>`).join('');
        } else {
            optimizationContainer.innerHTML = '<p class="text-blue-600">Sistem berjalan optimal.</p>';
        }
    }

    function animateCounter(id, target, suffix = '') {
        const el = document.getElementById(id);
        if (!el || isNaN(target) || target === null) {
            if (el) el.textContent = '-';
            return;
        };
        let current = 0;
        const targetNumber = Number(target);
        const inc = targetNumber / 50;
        const timer = setInterval(() => {
            current += inc;
            if (current >= targetNumber) {
                current = targetNumber;
                clearInterval(timer);
            }
            el.textContent = (suffix === '%') ? current.toFixed(1) + suffix : Math.floor(current).toLocaleString('id-ID') + suffix;
        }, 20);
    }
    
    function showError(message) {
        const container = document.querySelector('.container');
        if (!container) return;
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4';
        errorDiv.innerHTML = `<strong class="font-bold">Error!</strong> <span class="block sm:inline">${message}</span>`;
        container.insertBefore(errorDiv, container.firstChild);
        setTimeout(() => errorDiv.remove(), 5000);
    }
    
    btnBts.addEventListener('click', () => switchView('bts'));
    btnInternet.addEventListener('click', () => switchView('internet'));
    timeFilter.addEventListener('change', loadStatistics);
    
    initializePage(); 
});