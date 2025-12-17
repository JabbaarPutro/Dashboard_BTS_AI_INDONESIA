<?php 
require '../includes/check_session.php';

$canViewData = checkUserAccess(['super_admin', 'staff', 'pengawas'], false);
$canEditData = checkUserAccess(['super_admin', 'staff'], false);
$canExportData = checkUserAccess(['super_admin', 'staff', 'pengawas'], false);

if (!$canViewData) {
    header('Location: ../includes/access_denied.php');
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_bts_aksesinternet_dashboard_internal';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!$canEditData) {
        $error = 'Anda tidak memiliki izin untuk menghapus data.';
    } else {
        $table = $_POST['table'] === 'bts' ? 'bts' : 'akses_internet';
        $id = (int)$_POST['id'];
        
        $stmt = $conn->prepare("DELETE FROM {$table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = 'Data berhasil dihapus.';
            if (function_exists('logUserActivity')) {
                logUserActivity('delete_data', "Menghapus data dari tabel {$table}, ID: {$id}");
            }
        } else {
            $error = 'Gagal menghapus data.';
        }
        $stmt->close();
    }
}

$bts_data = [];
$bts_result = $conn->query("SELECT * FROM bts ORDER BY last_updated DESC");
if ($bts_result) {
    $bts_data = $bts_result->fetch_all(MYSQLI_ASSOC);
}

$internet_data = [];
$internet_result = $conn->query("SELECT * FROM akses_internet ORDER BY last_updated DESC");
if ($internet_result) {
    $internet_data = $internet_result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-100">

    <?php 
    $currentPage = 'manage_data';
    require '../includes/sidebar.php'; 
    ?>

    <button id="mobile-menu-button" class="lg:hidden fixed top-4 left-4 z-40 bg-slate-800 text-white p-2 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <div class="ml-0 lg:ml-64 min-h-screen">
        <div class="container mx-auto p-4 md:p-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 space-y-4 lg:space-y-0">
            <div class="flex items-start space-x-6">
                <img src="../assets/images/logo_bakti_komdigi.png" alt="Logo BAKTI Kominfo" class="h-16">
                <div>
                    <h1 class="text-4xl font-extrabold text-slate-800">Kelola Data</h1>
                    <p class="text-sm text-slate-600 mt-1">Kelola data BTS dan Akses Internet</p>
                    <p class="text-xs text-slate-500">Role: <?php echo str_replace('_', ' ', ucfirst($_SESSION['user_role'])); ?></p>
                </div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-md mb-6">
            <div class="flex border-b border-slate-200">
                <button id="tab-bts" class="tab-button active px-6 py-4 text-sm font-semibold text-blue-600 border-b-2 border-blue-600">
                    Data BTS (<?php echo count($bts_data); ?>)
                </button>
                <button id="tab-internet" class="tab-button px-6 py-4 text-sm font-semibold text-slate-500 hover:text-slate-700">
                    Data Akses Internet (<?php echo count($internet_data); ?>)
                </button>
            </div>
        </div>

        <div id="bts-content" class="tab-content bg-white rounded-xl shadow-md p-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 space-y-4 lg:space-y-0">
                <h2 class="text-xl font-bold text-slate-800">Data BTS</h2>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                    <div class="relative flex-grow sm:flex-grow-0">
                        <input 
                            type="text" 
                            id="search-bts" 
                            placeholder="Cari nama situs, provinsi, kabupaten..." 
                            class="w-full sm:w-80 px-4 py-2 pl-10 pr-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        >
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <div class="flex space-x-3">
                    <?php if ($canEditData): ?>
                    <button onclick="openAddModal('bts')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Data
                    </button>
                    <button onclick="openImportModal('bts')" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Import
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($canExportData): ?>
                    <button onclick="exportData('bts')" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </button>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Situs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Provinsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kabupaten</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Latitude</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Longitude</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jaringan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jenis Layanan</th>
                            <?php if ($canEditData): ?>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200" id="bts-table-body">
                        <?php if (empty($bts_data)): ?>
                        <tr id="no-data-bts">
                            <td colspan="<?php echo $canEditData ? '9' : '8'; ?>" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada data BTS
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($bts_data as $bts): ?>
                        <tr class="hover:bg-slate-50 bts-row" data-search-content="<?php echo strtolower(htmlspecialchars($bts['nama_situs'] . ' ' . $bts['provinsi'] . ' ' . $bts['kabupaten'] . ' ' . $bts['latitude'] . ' ' . $bts['longitude'] . ' ' . $bts['status'] . ' ' . ($bts['jaringan'] ?: '') . ' ' . ($bts['jenis_layanan'] ?: ''))); ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($bts['nama_situs']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($bts['provinsi']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($bts['kabupaten']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($bts['latitude']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($bts['longitude']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $bts['status'] === 'On Air' ? 'status' : 'bg-orange-100 text-orange-800'; ?>">
                                    <?php echo htmlspecialchars($bts['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    <?php echo htmlspecialchars($bts['jaringan'] ?: 'N/A'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($bts['jenis_layanan'] ?: 'N/A'); ?>
                            </td>
                            <?php if ($canEditData): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="deleteData('bts', <?php echo $bts['id']; ?>)" class="text-red-600 hover:text-red-900 ml-4">
                                    Hapus
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="internet-content" class="tab-content hidden bg-white rounded-xl shadow-md p-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 space-y-4 lg:space-y-0">
                <h2 class="text-xl font-bold text-slate-800">Data Akses Internet</h2>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-3 w-full lg:w-auto">
                    <div class="relative flex-grow sm:flex-grow-0">
                        <input 
                            type="text" 
                            id="search-internet" 
                            placeholder="Cari nama lokasi, provinsi, kabupaten..." 
                            class="w-full sm:w-80 px-4 py-2 pl-10 pr-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        >
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <div class="flex space-x-3">
                    <?php if ($canEditData): ?>
                    <button onclick="openAddModal('internet')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Data
                    </button>
                    <button onclick="openImportModal('internet')" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Import
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($canExportData): ?>
                    <button onclick="exportData('internet')" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </button>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Provinsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kabupaten</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Latitude</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Longitude</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jenis Layanan</th>
                            <?php if ($canEditData): ?>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200" id="internet-table-body">
                        <?php if (empty($internet_data)): ?>
                        <tr id="no-data-internet">
                            <td colspan="<?php echo $canEditData ? '8' : '7'; ?>" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada data Akses Internet
                            </td>
                        </tr>
                        <?php else: ?>
                       <?php foreach ($internet_data as $internet): ?>
                        <tr class="hover:bg-slate-50 internet-row" data-search-content="<?php echo strtolower(htmlspecialchars($internet['nama_lokasi'] . ' ' . $internet['provinsi'] . ' ' . $internet['kabupaten'] . ' ' . $internet['latitude'] . ' ' . $internet['longitude'] . ' ' . $internet['status'] . ' ' . ($internet['jenis_layanan'] ?: ''))); ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($internet['nama_lokasi']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($internet['provinsi']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($internet['kabupaten']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($internet['latitude']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($internet['longitude']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $internet['status'] === 'Aktif' ? 'status' : 'bg-orange-100 text-orange-800'; ?>">
                                    <?php echo htmlspecialchars($internet['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <?php echo htmlspecialchars($internet['jenis_layanan'] ?: 'N/A'); ?>
                            </td>
                            <?php if ($canEditData): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="deleteData('internet', <?php echo $internet['id']; ?>)" class="text-red-600 hover:text-red-900 ml-4">
                                    Hapus
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <div id="add-data-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg">
            <form id="add-data-form" action="add_data.php" method="POST">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 id="modal-title" class="text-xl font-semibold text-slate-900">Tambah Data</h3>
                        <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="modal-form-fields" class="space-y-4">
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-3 text-right rounded-b-xl">
                    <button type="button" onclick="closeAddModal()" class="mr-4 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="import-data-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 id="import-modal-title" class="text-xl font-semibold text-slate-900">Import Data</h3>
                        <p class="text-sm text-slate-500 mt-1">Upload file Excel atau CSV untuk import data bulk</p>
                    </div>
                    <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="import-form" enctype="multipart/form-data">
                    <input type="hidden" id="import-type" name="type">
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-2">Format File:</p>
                                <ul class="list-disc list-inside space-y-1" id="import-format-info">
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Pilih File</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="import-file-input" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-10 h-10 mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="mb-2 text-sm text-slate-500"><span class="font-semibold">Klik untuk upload</span> atau drag & drop</p>
                                    <p class="text-xs text-slate-500">CSV, XLS, atau XLSX (Max. 5MB)</p>
                                </div>
                                <input id="import-file-input" name="import_file" type="file" class="hidden" accept=".csv,.xls,.xlsx" onchange="updateFileName(this)" />
                            </label>
                        </div>
                        <p id="file-name" class="mt-2 text-sm text-slate-600"></p>
                    </div>

                    <div id="import-progress" class="hidden mb-6">
                        <div class="flex items-center mb-2">
                            <div class="flex-1">
                                <div class="bg-slate-200 rounded-full h-2">
                                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                            </div>
                            <span id="progress-text" class="ml-3 text-sm font-medium text-slate-700">0%</span>
                        </div>
                        <p id="import-status" class="text-sm text-slate-600"></p>
                    </div>

                    <div id="import-result" class="hidden mb-6"></div>

                    <div class="bg-slate-50 px-6 py-3 text-right rounded-b-xl -mx-6 -mb-6 mt-6">
                        <button type="button" onclick="closeImportModal()" class="mr-4 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                            Batal
                        </button>
                        <button type="button" onclick="downloadTemplate()" class="mr-4 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">
                            Download Template
                        </button>
                        <button type="submit" id="import-submit-btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
                            Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileButton = document.getElementById('mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            
            if (mobileButton) {
                mobileButton.addEventListener('click', function() {
                    sidebar.classList.toggle('mobile-open');
                });
            }

            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnButton = mobileButton.contains(event.target);

                    if (!isClickInsideSidebar && !isClickOnButton && sidebar.classList.contains('mobile-open')) {
                        sidebar.classList.remove('mobile-open');
                    }
                }
            });

            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.id.replace('tab-', '') + '-content';
                    
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'text-blue-600', 'border-blue-600');
                        btn.classList.add('text-slate-500');
                    });
                    
                    this.classList.add('active', 'text-blue-600', 'border-blue-600', 'border-b-2');
                    this.classList.remove('text-slate-500');
                    
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    document.getElementById(targetId).classList.remove('hidden');
                });
            });

            initializeSearch();
            clearSearchOnTabSwitch();
        });

        function initializeSearch() {
            function setupSearch(inputId, rowSelector, noDataId, entityName) {
                const searchInput = document.getElementById(inputId);
                const tableRows = document.querySelectorAll(rowSelector);
                const noDataRow = document.getElementById(noDataId);

                if (searchInput) {
                    searchInput.addEventListener('input', function(e) {
                        const searchTerm = e.target.value.toLowerCase().trim();
                        let visibleCount = 0;
                        
                        tableRows.forEach(row => {
                            const searchContent = row.getAttribute('data-search-content') || '';
                            if (searchContent.includes(searchTerm)) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        if (noDataRow) {
                            const td = noDataRow.querySelector('td');
                            if (visibleCount === 0) {
                                noDataRow.style.display = '';
                                if (searchTerm !== '') {
                                    td.textContent = `Tidak ditemukan data ${entityName} yang cocok dengan "${e.target.value}"`;
                                } else if (tableRows.length === 0) {
                                    td.textContent = `Tidak ada data ${entityName}`;
                                }
                            } else {
                                noDataRow.style.display = 'none';
                            }
                        }
                    });
                }
            }

            setupSearch('search-bts', '.bts-row', 'no-data-bts', 'BTS');
            setupSearch('search-internet', '.internet-row', 'no-data-internet', 'Akses Internet');
        }

        // Clear search when switching tabs
        function clearSearchOnTabSwitch() {
            document.getElementById('tab-bts').addEventListener('click', function() {
                const searchInput = document.getElementById('search-bts');
                if (searchInput) searchInput.value = '';
                document.querySelectorAll('.bts-row').forEach(row => row.style.display = '');
                const noDataRow = document.getElementById('no-data-bts');
                if (noDataRow && document.querySelectorAll('.bts-row').length > 0) {
                    noDataRow.style.display = 'none';
                }
            });

            document.getElementById('tab-internet').addEventListener('click', function() {
                const searchInput = document.getElementById('search-internet');
                if(searchInput) searchInput.value = '';
                document.querySelectorAll('.internet-row').forEach(row => row.style.display = '');
                const noDataRow = document.getElementById('no-data-internet');
                if (noDataRow && document.querySelectorAll('.internet-row').length > 0) {
                    noDataRow.style.display = 'none';
                }
            });
        }

        function openAddModal(type) {
            const modal = document.getElementById('add-data-modal');
            const title = document.getElementById('modal-title');
            const formFields = document.getElementById('modal-form-fields');
            
            title.textContent = type === 'bts' ? 'Tambah Data BTS' : 'Tambah Data Akses Internet';
            
            const isBts = type === 'bts';
            const namaLabel = isBts ? 'Nama Situs' : 'Nama Lokasi';
            const namaInputName = isBts ? 'nama_situs' : 'nama_lokasi';
            const statusOptions = isBts 
                ? '<option value="On Air">On Air</option><option value="Dalam Pembangunan">Dalam Pembangunan</option>'
                : '<option value="Aktif">Aktif</option><option value="Dalam Instalasi">Dalam Instalasi</option>';

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

            formFields.innerHTML = `
                <input type="hidden" name="type" value="${type}">
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
                <div>
                    <label for="jenis_layanan" class="block text-sm font-medium text-slate-700">Jenis Layanan</label>
                    <select id="jenis_layanan" name="jenis_layanan" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="Fiber Optik">Fiber Optik</option>
                        <option value="Wireless">Wireless</option>
                        <option value="Satelit (VSAT)">Satelit (VSAT)</option>
                    </select>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('add-data-modal').classList.add('hidden');
        }

        function deleteData(table, id) {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'manage_data.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="table" value="${table}">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function exportData(type) {
            const exportUrl = `export_data.php?type=${type}`;
            window.location.href = exportUrl;
        }

        document.getElementById('add-data-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            submitButton.disabled = true;
            submitButton.textContent = 'Menyimpan...';
            
            try {
                const response = await fetch('add_data.php', {
                    method: 'POST',
                    body: formData
                });
                
                const resultText = await response.text();
                try {
                    const result = JSON.parse(resultText);
                    if (response.ok && result.success) {
                        alert(result.message || 'Data berhasil ditambahkan!');
                        location.reload();
                    } else {
                        throw new Error(result.message || 'Terjadi kesalahan pada server.');
                    }
                } catch(jsonError) {
                    console.error('Failed to parse JSON:', resultText);
                    throw new Error('Respons server tidak valid.');
                }

            } catch (error) {
                console.error('Error submitting form:', error);
                alert('Error: ' + error.message);
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Simpan';
            }
        });

        let currentImportType = '';

        function openImportModal(type) {
            currentImportType = type;
            const modal = document.getElementById('import-data-modal');
            const title = document.getElementById('import-modal-title');
            const formatInfo = document.getElementById('import-format-info');
            document.getElementById('import-type').value = type;
            
            title.textContent = type === 'bts' ? 'Import Data BTS' : 'Import Data Akses Internet';
            
            if (type === 'bts') {
                formatInfo.innerHTML = `
                    <li>Kolom: nama_situs, provinsi, kabupaten, latitude, longitude, status, jaringan, jenis_layanan</li>
                    <li>Status: "On Air" atau "Dalam Pembangunan"</li>
                    <li>Jaringan: "2G", "3G", "4G", atau "5G"</li>
                    <li>Jenis Layanan: "Fiber Optik", "Wireless", atau "Satelit (VSAT)"</li>
                `;
            } else {
                formatInfo.innerHTML = `
                    <li>Kolom: nama_lokasi, provinsi, kabupaten, latitude, longitude, status, jenis_layanan</li>
                    <li>Status: "Aktif" atau "Dalam Instalasi"</li>
                    <li>Jenis Layanan: "Fiber Optik", "Wireless", atau "Satelit (VSAT)"</li>
                `;
            }
            
            document.getElementById('import-form').reset();
            document.getElementById('file-name').textContent = '';
            document.getElementById('import-progress').classList.add('hidden');
            document.getElementById('import-result').classList.add('hidden');
            
            modal.classList.remove('hidden');
        }

        function closeImportModal() {
            document.getElementById('import-data-modal').classList.add('hidden');
        }

        function updateFileName(input) {
            const fileName = document.getElementById('file-name');
            if (input.files.length > 0) {
                const file = input.files[0];
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                fileName.textContent = `File terpilih: ${file.name} (${sizeMB} MB)`;
                fileName.classList.remove('text-red-600');
                fileName.classList.add('text-green-600');
            } else {
                fileName.textContent = '';
            }
        }

        function downloadTemplate() {
            const type = currentImportType;
            window.location.href = `download_template.php?type=${type}`;
        }

        document.getElementById('import-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('import-file-input');
            const submitBtn = document.getElementById('import-submit-btn');
            const progressDiv = document.getElementById('import-progress');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const statusText = document.getElementById('import-status');
            const resultDiv = document.getElementById('import-result');
            
            if (!fileInput.files.length) {
                alert('Pilih file terlebih dahulu!');
                return;
            }
            
            const formData = new FormData(this);
            
            progressDiv.classList.remove('hidden');
            resultDiv.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengimport...';
            
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 10;
                if (progress <= 90) {
                    progressBar.style.width = progress + '%';
                    progressText.textContent = progress + '%';
                    statusText.textContent = 'Memproses data...';
                }
            }, 200);
            
            try {
                const response = await fetch('import_data.php', {
                    method: 'POST',
                    body: formData
                });
                
                clearInterval(progressInterval);
                
                const result = await response.json();
                
                progressBar.style.width = '100%';
                progressText.textContent = '100%';
                statusText.textContent = 'Selesai!';
                
                resultDiv.classList.remove('hidden');
                
                if (result.success) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-green-800">${result.message}</h4>
                                    ${result.errorCount > 0 ? `
                                        <div class="mt-2 text-sm text-green-700">
                                            <p class="font-medium">Detail Error:</p>
                                            <ul class="list-disc list-inside mt-1 max-h-40 overflow-y-auto">
                                                ${result.errors.map(err => `<li>${err}</li>`).join('')}
                                            </ul>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-semibold text-red-800">Import Gagal</h4>
                                    <p class="text-sm text-red-700 mt-1">${result.message}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
            } catch (error) {
                clearInterval(progressInterval);
                
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-red-800">Error</h4>
                                <p class="text-sm text-red-700 mt-1">${error.message}</p>
                            </div>
                        </div>
                    </div>
                `;
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Import Data';
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddModal();
                closeImportModal();
            }
        });

        document.getElementById('add-data-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddModal();
            }
        });

        document.getElementById('import-data-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImportModal();
            }
        });
    </script>
</body>
</html>