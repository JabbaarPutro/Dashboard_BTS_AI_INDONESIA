<?php 
require 'header.php'; 

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_bts_aksesinternet_dashboard_internal';
$conn = new mysqli($host, $user, $pass, $db);

$bts_data = [];
$result_bts = $conn->query("SELECT nama_situs, provinsi, kabupaten, status, jaringan, jenis_layanan FROM bts ORDER BY nama_situs ASC");
if ($result_bts) $bts_data = $result_bts->fetch_all(MYSQLI_ASSOC);

$internet_data = [];
$result_internet = $conn->query("SELECT nama_lokasi, provinsi, kabupaten, status, jenis_layanan FROM akses_internet ORDER BY nama_lokasi ASC");
if ($result_internet) $internet_data = $result_internet->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<div class="p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Kelola Data</h1>
        </div>

    <div class="mb-4 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="dataTabs" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="bts-tab" data-tabs-target="#bts" type="button" role="tab">Data BTS (<?php echo count($bts_data); ?>)</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="internet-tab" data-tabs-target="#internet" type="button" role="tab">Data Akses Internet (<?php echo count($internet_data); ?>)</button>
            </li>
        </ul>
    </div>

    <div id="dataTabsContent">
        <div class="hidden p-4 rounded-lg bg-white shadow-md" id="bts" role="tabpanel">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-slate-700">Tabel Data BTS</h2>
                <a href="export_data.php?type=bts&provinsi=all" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Export Semua ke Excel</a>
            </div>
            <div class="overflow-x-auto max-h-[65vh] relative">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-6 py-3">Nama Situs</th>
                            <th class="px-6 py-3">Provinsi</th>
                            <th class="px-6 py-3">Kabupaten</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Jaringan</th>
                            <th class="px-6 py-3">Jenis Layanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bts_data as $row): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_situs']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['provinsi']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['kabupaten']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['jaringan']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['jenis_layanan']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="hidden p-4 rounded-lg bg-white shadow-md" id="internet" role="tabpanel">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-slate-700">Tabel Data Akses Internet</h2>
                <a href="export_data.php?type=internet&provinsi=all" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Export Semua ke Excel</a>
            </div>
            <div class="overflow-x-auto max-h-[65vh] relative">
                <table class="w-full text-sm text-left text-gray-500">
                     <thead class="text-xs text-gray-700 uppercase bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-6 py-3">Nama Lokasi</th>
                            <th class="px-6 py-3">Provinsi</th>
                            <th class="px-6 py-3">Kabupaten</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Jenis Layanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($internet_data as $row): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_lokasi']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['provinsi']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['kabupaten']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['jenis_layanan']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('[data-tabs-target]');
        const tabContents = document.querySelectorAll('[role="tabpanel"]');
        
        function activateTab(tab) {
            const target = document.querySelector(tab.dataset.tabsTarget);
            
            tabs.forEach(t => {
                t.setAttribute('aria-selected', 'false');
                t.classList.remove('border-indigo-600', 'text-indigo-600');
                t.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            });
            tab.setAttribute('aria-selected', 'true');
            tab.classList.add('border-indigo-600', 'text-indigo-600');
            
            tabContents.forEach(c => c.classList.add('hidden'));
            target.classList.remove('hidden');
        }

        const btsTab = document.getElementById('bts-tab');
        if (btsTab) {
            activateTab(btsTab);
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                activateTab(tab);
            });
        });

        document.getElementById('nav-kelola-data').classList.add('active');
    });
</script>

</main> </body>
</html>