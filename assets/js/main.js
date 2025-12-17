document.addEventListener('DOMContentLoaded', () => {

    const map = L.map('map', { 
        zoomControl: false, maxBounds: [[-11.0, 95.0], [6.0, 141.0]],
        maxBoundsViscosity: 1.0, minZoom: 5, maxZoom: 19
    }).setView([-2.5489, 118.0149], 5);

    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lite/{z}/{x}/{y}.png', {
        minZoom: 5, maxZoom: 19, subdomains: 'abcd'
    }).addTo(map);

    const provinceSearchInput = document.getElementById('province-search');
    const provinceDataList = document.getElementById('province-list');
    const btnAddData = document.getElementById('btn-add-data');
    const addDataModal = document.getElementById('add-data-modal');
    
    const provinceDetailSidebar = document.getElementById('province-detail-sidebar');
    const sidebarContent = document.getElementById('sidebar-content');
    const closeSidebarBtn = document.getElementById('close-sidebar-btn');
    const sidebarProvinceTitle = document.getElementById('sidebar-province-title');
    const exportSidebarDataBtn = document.getElementById('export-sidebar-data');
    
    const provinceDetailModal = document.getElementById('province-detail-modal');
    const closeProvinceModalBtn = document.getElementById('close-province-modal');

    const userRole = window.userRole || 'pengawas';
    const canAddData = userRole === 'super_admin' || userRole === 'staff';
    const canExportData = userRole === 'super_admin' || userRole === 'staff' || userRole === 'pengawas';

    if (!canAddData && btnAddData) {
        btnAddData.style.display = 'none';
    }

    const dashboards = {
        bts: { path: '../assets/js/bts.js', objectName: 'btsDashboard' },
        internet: { path: '../assets/js/internet.js', objectName: 'internetDashboard' }
    };

    const btnBts = document.getElementById('btn-view-bts');
    const btnInternet = document.getElementById('btn-view-internet');
    let currentDashboard = null;
    let currentDashboardName = '';
    let geojsonLayer = null;
    let currentProvinceData = null; 
    let currentConfig = null; 
    let allPointData = [];
    let currentSelectedProvince = null;
    let activeProvinceLabelMarker = null;

    function hideProvinceSidebar() {
        provinceDetailSidebar.style.display = 'none';
    }

    function hideProvinceModal() {
        provinceDetailModal.classList.add('hidden');
        currentSelectedProvince = null;
    }

    window.showProvinceDetails = function(provinceName) {
        if (!currentProvinceData || !currentConfig || !allPointData) {
            console.warn('Data provinsi, config, atau point data tidak tersedia');
            return;
        }

        currentSelectedProvince = provinceName;
        const provinceKey = provinceName.toUpperCase();
        const data = currentProvinceData[provinceKey];
        
        sidebarProvinceTitle.textContent = `Detail Provinsi ${provinceName}`;
        
        if (exportSidebarDataBtn) {
            exportSidebarDataBtn.style.display = canExportData ? 'flex' : 'none';
        }

        if (!data) {
            sidebarContent.innerHTML = `<p class="text-slate-500 text-center py-10">Data untuk provinsi ${provinceName} tidak tersedia.</p>`;
            provinceDetailSidebar.style.display = 'block';
            provinceDetailSidebar.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        const total = data.total || 0;
        const onAir = data.onAir || 0;
        const inProgress = data.dalamPembangunan || 0;
        const operationalLevel = total > 0 ? ((onAir / total) * 100).toFixed(1) : 0;

        const statLabelTotal = currentConfig.statLabels.total;
        const statLabelStatus1 = currentConfig.statLabels.status1;
        const statLabelStatus2 = currentConfig.statLabels.status2;
        
        const provincePointData = allPointData.filter(point => 
            point.provinsi && point.provinsi.toUpperCase() === provinceKey
        );

        let regenciesHtml = '';
        if (data.regencies && Object.keys(data.regencies).length > 0) {
            const sortedRegencies = Object.entries(data.regencies).sort(([,a],[,b]) => b.total - a.total);
            
            regenciesHtml = sortedRegencies.map(([regencyName, regencyData]) => `
                <div class="flex justify-between items-center text-sm py-2 border-b border-slate-100">
                    <span class="font-medium text-slate-700">${regencyName}</span>
                    <div class="text-right">
                       <span class="font-bold text-slate-800">${regencyData.total}</span>
                       <div class="text-xs text-slate-500">
                            <span class="text-blue-600">${regencyData.onAir || 0} ${statLabelStatus1}</span>, 
                            <span class="text-orange-500">${regencyData.dalamPembangunan || 0} ${statLabelStatus2}</span>
                       </div>
                    </div>
                </div>
            `).join('');
        } else {
            regenciesHtml = '<p class="text-sm text-slate-400">Tidak ada data per kabupaten/kota.</p>';
        }

      
let detailDataHtml = '';
if (provincePointData.length > 0) {
    console.log('Sample point data:', provincePointData[0]);
    
    detailDataHtml = `
        <div class="py-4 border-t border-slate-200">
            <h5 class="font-bold text-slate-700 mb-3">Detail Data ${currentDashboardName === 'bts' ? 'BTS' : 'Akses Internet'}</h5>
            <div class="max-h-80 overflow-y-auto pr-2 space-y-2">
                ${provincePointData.map(point => {
                    if (currentDashboardName === 'bts') {
                        return `
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-3">
                                    <h6 class="font-semibold text-slate-800 text-base">${point.nama_situs || 'Tidak tersedia'}</h6>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full ${
                                        point.status === 'On Air' 
                                            ? 'bg-blue-100 text-blue-700' 
                                            : 'bg-orange-100 text-orange-700'
                                    }">${point.status}</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                        <div class="flex flex-col">
                                            <span class="text-slate-500 text-xs mb-0.5">Kabupaten/Kota</span>
                                            <span class="text-slate-800 font-medium">${point.kabupaten || 'Tidak tersedia'}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-slate-500 text-xs mb-0.5">Jaringan</span>
                                            <span class="text-slate-800 font-medium">${point.jaringan || 'Tidak tersedia'}</span>
                                        </div>
                                        <div class="flex flex-col col-span-2">
                                            <span class="text-slate-500 text-xs mb-0.5">Jenis Layanan</span>
                                            <span class="text-slate-800 font-medium">${point.jenis_layanan || 'Tidak tersedia'}</span>
                                        </div>
                                    </div>
                                    <div class="pt-2 mt-2 border-t border-slate-200">
                                        <div class="grid grid-cols-2 gap-x-4 text-sm">
                                            <div class="flex flex-col">
                                                <span class="text-slate-500 text-xs mb-0.5">Latitude</span>
                                                <span class="text-slate-800 font-mono">${point.lat !== null && point.lat !== undefined ? point.lat : 'Tidak tersedia'}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-slate-500 text-xs mb-0.5">Longitude</span>
                                                <span class="text-slate-800 font-mono">${point.lon !== null && point.lon !== undefined ? point.lon : 'Tidak tersedia'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        return `
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-3">
                                    <h6 class="font-semibold text-slate-800 text-base">${point.nama_lokasi || 'Tidak tersedia'}</h6>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full ${
                                        point.status === 'Aktif' 
                                            ? 'bg-blue-100 text-blue-700' 
                                            : 'bg-orange-100 text-orange-700'
                                    }">${point.status}</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                        <div class="flex flex-col">
                                            <span class="text-slate-500 text-xs mb-0.5">Kabupaten/Kota</span>
                                            <span class="text-slate-800 font-medium">${point.kabupaten || 'Tidak tersedia'}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-slate-500 text-xs mb-0.5">Jenis Layanan</span>
                                            <span class="text-slate-800 font-medium">${point.jenis_layanan || 'Tidak tersedia'}</span>
                                        </div>
                                    </div>
                                    <div class="pt-2 mt-2 border-t border-slate-200">
                                        <div class="grid grid-cols-2 gap-x-4 text-sm">
                                            <div class="flex flex-col">
                                                <span class="text-slate-500 text-xs mb-0.5">Latitude</span>
                                                <span class="text-slate-800 font-mono">${point.lat !== null && point.lat !== undefined ? point.lat : 'Tidak tersedia'}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-slate-500 text-xs mb-0.5">Longitude</span>
                                                <span class="text-slate-800 font-mono">${point.lon !== null && point.lon !== undefined ? point.lon : 'Tidak tersedia'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }).join('')}
            </div>
        </div>
    `;
}

        const contentHtml = `
            <div class="space-y-3 py-4">
                <div class="flex justify-between items-center bg-slate-50 p-3 rounded-lg">
                    <span class="font-semibold text-slate-600">${statLabelTotal}</span>
                    <span class="text-xl font-bold text-slate-800">${total.toLocaleString('id-ID')}</span>
                </div>
                <div class="flex justify-between items-center bg-blue-50 p-3 rounded-lg">
                    <span class="font-semibold text-blue-700">${statLabelStatus1}</span>
                    <span class="text-xl font-bold text-blue-700">${onAir.toLocaleString('id-ID')}</span>
                </div>
                <div class="flex justify-between items-center bg-orange-50 p-3 rounded-lg">
                    <span class="font-semibold text-orange-600">${statLabelStatus2}</span>
                    <span class="text-xl font-bold text-orange-600">${inProgress.toLocaleString('id-ID')}</span>
                </div>
            </div>
            <div class="py-4">
                <h5 class="font-bold text-slate-700 mb-2">Tingkat Operasional</h5>
                <div class="w-full bg-slate-200 rounded-full h-2.5">
                    <div class="bg-green-500 h-2.5 rounded-full" style="width: ${operationalLevel}%"></div>
                </div>
                <p class="text-right text-lg font-bold text-green-600 mt-1">${operationalLevel}%</p>
            </div>
            <div class="py-4 border-t border-slate-200">
                <h5 class="font-bold text-slate-700 mb-2">Sebaran per Kabupaten/Kota</h5>
                <div class="max-h-60 overflow-y-auto pr-2 space-y-1">${regenciesHtml}</div>
            </div>
            ${detailDataHtml}
        `;
        
        sidebarContent.innerHTML = contentHtml;
        provinceDetailSidebar.style.display = 'block';
        provinceDetailSidebar.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    async function populateProvinceSearch() {
        try {
            const response = await fetch('../api/api.php?type=get_provinces');
            const data = await response.json();
            provinceDataList.innerHTML = '';
            data.provinces.forEach(province => {
                const option = document.createElement('option');
                option.value = province;
                provinceDataList.appendChild(option);
            });
        } catch (error) {
            console.error('Gagal memuat daftar provinsi:', error);
        }
    }

    function zoomToProvince(provinceName) {
        if (!geojsonLayer || !provinceName) return;

        if (activeProvinceLabelMarker) {
            map.removeLayer(activeProvinceLabelMarker);
            activeProvinceLabelMarker = null;
        }

        let found = false;
        geojsonLayer.eachLayer(layer => {
            if (layer.feature.properties.state.toUpperCase() === provinceName.toUpperCase()) {
                
                map.fitBounds(layer.getBounds(), {
                    padding: [20, 20] 
                });

                const center = layer.getBounds().getCenter();
                const provinceLabelText = layer.feature.properties.state;

                const labelIcon = L.divIcon({
                    className: 'province-marker-label',
                    html: `<span>${provinceLabelText}</span>`,
                    iconSize: [150, 40],
                    iconAnchor: [75, 20]
                });

                activeProvinceLabelMarker = L.marker(center, { 
                    icon: labelIcon,
                    interactive: false 
                }).addTo(map);

                found = true;
            }
        });

        if (!found) {
            alert('Provinsi tidak ditemukan di peta.');
        }
    }

    function showAddDataModal() {
        if (!canAddData) {
            alert('Anda tidak memiliki izin untuk menambah data.');
            return;
        }

        if (!currentDashboardName) {
            alert('Silakan pilih jenis dashboard terlebih dahulu (BTS atau Akses Internet)');
            return;
        }

        const isBts = currentDashboardName === 'bts';
        const formTitle = isBts ? 'Form Tambah Data BTS Baru' : 'Form Tambah Data Akses Internet';
        const namaLabel = isBts ? 'Nama Situs' : 'Nama Lokasi';
        const namaInputName = isBts ? 'nama_situs' : 'nama_lokasi';
        const statusOptions = isBts 
            ? `<option value="On Air">On Air</option><option value="Dalam Pembangunan">Dalam Pembangunan</option>`
            : `<option value="Aktif">Aktif</option><option value="Dalam Instalasi">Dalam Instalasi</option>`;

        const jaringanField = isBts ? `
            <div>
                <label for="jaringan" class="block text-sm font-medium text-slate-700">Jaringan</label>
                <select id="jaringan" name="jaringan" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="2G">2G</option>
                    <option value="3G">3G</option>
                    <option value="4G">4G</option>
                    <option value="5G">5G</option>
                </select>
            </div>` : '';

        const jenisLayananField = `
            <div>
                <label for="jenis_layanan" class="block text-sm font-medium text-slate-700">Jenis Layanan</label>
                <select id="jenis_layanan" name="jenis_layanan" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="Fiber Optik">Fiber Optik</option>
                    <option value="Wireless">Wireless</option>
                    <option value="Satelit (VSAT)">Satelit (VSAT)</option>
                </select>
            </div>`;

        const formHtml = `
            <div class="bg-white rounded-xl shadow-lg w-full max-w-lg transform transition-all" onclick="event.stopPropagation()">
                <div class="flex justify-between items-center p-5 border-b border-slate-200">
                    <h3 class="text-xl font-bold text-slate-800">${formTitle}</h3>
                    <button type="button" id="close-add-data-modal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form id="add-data-form">
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <input type="hidden" name="type" value="${currentDashboardName}">
                        <div>
                            <label for="${namaInputName}" class="block text-sm font-medium text-slate-700">${namaLabel}</label>
                            <input type="text" id="${namaInputName}" name="${namaInputName}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="provinsi" class="block text-sm font-medium text-slate-700">Provinsi</label>
                            <input type="text" id="provinsi" name="provinsi" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="kabupaten" class="block text-sm font-medium text-slate-700">Kabupaten/Kota</label>
                            <input type="text" id="kabupaten" name="kabupaten" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="latitude" class="block text-sm font-medium text-slate-700">Latitude</label>
                                <input type="number" id="latitude" step="any" name="latitude" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="longitude" class="block text-sm font-medium text-slate-700">Longitude</label>
                                <input type="number" id="longitude" step="any" name="longitude" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                            <select id="status" name="status" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">${statusOptions}</select>
                        </div>
                        ${jaringanField}
                        ${jenisLayananField}
                    </div>
                    <div class="flex justify-end space-x-4 p-5 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                        <button type="button" id="cancel-add-data" class="px-5 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">Simpan Data</button>
                    </div>
                </form>
            </div>`;
            
        addDataModal.innerHTML = formHtml;
        addDataModal.classList.remove('hidden');

        document.getElementById('close-add-data-modal').addEventListener('click', () => {
             addDataModal.classList.add('hidden');
        });
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        
        if (!canAddData) {
            alert('Anda tidak memiliki izin untuk menambah data.');
            return;
        }

        const form = e.target;
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        
        submitButton.disabled = true;
        submitButton.textContent = 'Menyimpan...';
        
        try {
            const response = await fetch('../dashboard/add_data.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                alert(result.message || 'Data berhasil ditambahkan!');
                addDataModal.classList.add('hidden');
                switchView(currentDashboardName);
            } else {
                throw new Error(result.message || 'Terjadi kesalahan pada server.');
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            alert('Error: ' + error.message);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Simpan';
        }
    }

    function handleExportData() {
        if (!canExportData) {
            alert('Anda tidak memiliki izin untuk mengunduh data.');
            return;
        }

        if (!currentSelectedProvince || !currentDashboardName) {
            alert('Tidak ada provinsi yang dipilih.');
            return;
        }

        const exportUrl = `../dashboard/export_data.php?type=${currentDashboardName}&provinsi=${encodeURIComponent(currentSelectedProvince)}`;
        
        const link = document.createElement('a');
        link.href = exportUrl;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function loadScript(path, callback) {
        const oldScript = document.getElementById('dynamic-dashboard-script');
        if (oldScript) {
            oldScript.remove();
        }
        
        const script = document.createElement('script');
        script.id = 'dynamic-dashboard-script';
        script.src = path + '?v=' + Date.now();
        script.onload = () => callback();
        script.onerror = () => {
            console.error(`Gagal memuat script: ${path}`);
            alert(`Gagal memuat dashboard. Pastikan file ${path} tersedia.`);
        };
        document.head.appendChild(script);
    }

    function switchView(dashboardName) {
        console.log(`Switching to dashboard: ${dashboardName}`);
        
        if (currentDashboard && typeof currentDashboard.destroy === 'function') {
            currentDashboard.destroy();
        }
        
        hideProvinceSidebar(); 
        hideProvinceModal();
        map.setView([-2.5489, 118.0149], 5);
        currentDashboardName = dashboardName;
        const dashboardInfo = dashboards[dashboardName];
        
        if (!dashboardInfo) {
            console.error(`Dashboard '${dashboardName}' tidak ditemukan`);
            return;
        }

        loadScript(dashboardInfo.path, () => {
            currentDashboard = window[dashboardInfo.objectName];
            
            if (currentDashboard && typeof currentDashboard.init === 'function') {
                currentDashboard.init(map).then(data => {
                    if (data) {
                        geojsonLayer = data.geojsonLayer;
                        currentProvinceData = data.provinceData;
                        currentConfig = data.config;
                        allPointData = data.pointData || [];
                        console.log('Dashboard loaded successfully');
                    } else {
                        console.warn('Dashboard init returned null data');
                    }
                }).catch(error => {
                    console.error('Error initializing dashboard:', error);
                });
            } else {
                console.error(`Dashboard object ${dashboardInfo.objectName} tidak ditemukan atau tidak memiliki method init`);
            }
        });
    }

    function updateButtonStyles(activeButton) {
        [btnBts, btnInternet].forEach(btn => {
            if (btn === activeButton) {
                btn.classList.add('bg-white', 'text-blue-600', 'shadow-md');
                btn.classList.remove('text-slate-600');
            } else {
                btn.classList.remove('bg-white', 'text-blue-600', 'shadow-md');
                btn.classList.add('text-slate-600');
            }
        });
    }

    btnBts.addEventListener('click', () => {
        updateButtonStyles(btnBts);
        switchView('bts');
    });

    btnInternet.addEventListener('click', () => {
        updateButtonStyles(btnInternet);
        switchView('internet');
    });

    provinceSearchInput.addEventListener('change', (e) => {
        zoomToProvince(e.target.value);
    });

    if (btnAddData) {
        btnAddData.addEventListener('click', showAddDataModal);
    }
    
    addDataModal.addEventListener('click', (e) => {
        if (e.target.id === 'add-data-modal' || e.target.id === 'cancel-add-data') {
            addDataModal.classList.add('hidden');
        }
    });

    addDataModal.addEventListener('submit', handleFormSubmit);

    closeProvinceModalBtn.addEventListener('click', hideProvinceModal);
    provinceDetailModal.addEventListener('click', (e) => {
        if (e.target === provinceDetailModal) {
            hideProvinceModal();
        }
    });

    if (exportSidebarDataBtn) {
        exportSidebarDataBtn.addEventListener('click', handleExportData);
    }

    closeSidebarBtn.addEventListener('click', hideProvinceSidebar);
    
    map.on('click', function() {
        hideProvinceSidebar();
        hideProvinceModal();
        if (activeProvinceLabelMarker) {
            map.removeLayer(activeProvinceLabelMarker);
            activeProvinceLabelMarker = null;
        }
    });

    const footerLinkBts = document.getElementById('footer-link-bts');
    const footerLinkInternet = document.getElementById('footer-link-internet');

    if (footerLinkBts) {
        footerLinkBts.addEventListener('click', (e) => {
            e.preventDefault();
            btnBts.click();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    
    if (footerLinkInternet) {
        footerLinkInternet.addEventListener('click', (e) => {
            e.preventDefault();
            btnInternet.click();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    window.addEventListener('error', function(e) {
        console.error('JavaScript Error:', e.error);
    });

    updateButtonStyles(btnBts);
    populateProvinceSearch();
    switchView('bts');
});