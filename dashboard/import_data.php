<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

require '../includes/check_session.php';

$canEditData = checkUserAccess(['super_admin', 'staff'], false);
if (!$canEditData) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki izin untuk mengimpor data.']);
    exit;
}

header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_bts_aksesinternet_dashboard_internal';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
    exit;
}

if (!isset($_FILES['import_file']) || !isset($_POST['type'])) {
    echo json_encode(['success' => false, 'message' => 'File atau tipe data tidak ditemukan.']);
    exit;
}

$type = $_POST['type'];
$file = $_FILES['import_file'];

$allowedExtensions = ['csv', 'xls', 'xlsx'];
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan CSV atau Excel (.xls, .xlsx)']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.']);
    exit;
}

try {
    
    $spreadsheet = IOFactory::load($file['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    if (count($rows) < 2) {
        echo json_encode(['success' => false, 'message' => 'File kosong atau tidak memiliki data.']);
        exit;
    }
    
    $headers = array_map('trim', array_map('strtolower', $rows[0]));
    
    $requiredColumns = [];
    if ($type === 'bts') {
        $requiredColumns = ['nama_situs', 'provinsi', 'kabupaten', 'latitude', 'longitude', 'status', 'jaringan', 'jenis_layanan'];
    } else if ($type === 'internet') {
        $requiredColumns = ['nama_lokasi', 'provinsi', 'kabupaten', 'latitude', 'longitude', 'status', 'jenis_layanan'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipe data tidak valid.']);
        exit;
    }
    
    $missingColumns = [];
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $headers)) {
            $missingColumns[] = $col;
        }
    }
    
    if (!empty($missingColumns)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Kolom yang diperlukan tidak ditemukan: ' . implode(', ', $missingColumns)
        ]);
        exit;
    }
    
    $columnMap = array_flip($headers);
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    $conn->begin_transaction();
    
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        
        if (empty(array_filter($row))) {
            continue;
        }
        
        try {
            if ($type === 'bts') {
                $nama_situs = trim($row[$columnMap['nama_situs']] ?? '');
                $provinsi = trim($row[$columnMap['provinsi']] ?? '');
                $kabupaten = trim($row[$columnMap['kabupaten']] ?? '');
                $latitude = floatval($row[$columnMap['latitude']] ?? 0);
                $longitude = floatval($row[$columnMap['longitude']] ?? 0);
                $status = trim($row[$columnMap['status']] ?? '');
                $jaringan = trim($row[$columnMap['jaringan']] ?? '');
                $jenis_layanan = trim($row[$columnMap['jenis_layanan']] ?? '');
                
                if (empty($nama_situs) || empty($provinsi) || empty($kabupaten)) {
                    $errors[] = "Baris " . ($i + 1) . ": Data tidak lengkap";
                    $errorCount++;
                    continue;
                }
                
                if (!in_array($status, ['On Air', 'Dalam Pembangunan'])) {
                    $errors[] = "Baris " . ($i + 1) . ": Status tidak valid (gunakan 'On Air' atau 'Dalam Pembangunan')";
                    $errorCount++;
                    continue;
                }
                
                if (!in_array($jaringan, ['2G', '3G', '4G', '5G'])) {
                    $errors[] = "Baris " . ($i + 1) . ": Jaringan tidak valid (gunakan '2G', '3G', '4G', atau '5G')";
                    $errorCount++;
                    continue;
                }
                
                $stmt = $conn->prepare("INSERT INTO bts (nama_situs, provinsi, kabupaten, latitude, longitude, status, jaringan, jenis_layanan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssddsss", $nama_situs, $provinsi, $kabupaten, $latitude, $longitude, $status, $jaringan, $jenis_layanan);
                
            } else { // internet
                $nama_lokasi = trim($row[$columnMap['nama_lokasi']] ?? '');
                $provinsi = trim($row[$columnMap['provinsi']] ?? '');
                $kabupaten = trim($row[$columnMap['kabupaten']] ?? '');
                $latitude = floatval($row[$columnMap['latitude']] ?? 0);
                $longitude = floatval($row[$columnMap['longitude']] ?? 0);
                $status = trim($row[$columnMap['status']] ?? '');
                $jenis_layanan = trim($row[$columnMap['jenis_layanan']] ?? '');
                
                if (empty($nama_lokasi) || empty($provinsi) || empty($kabupaten)) {
                    $errors[] = "Baris " . ($i + 1) . ": Data tidak lengkap";
                    $errorCount++;
                    continue;
                }
                
                if (!in_array($status, ['Aktif', 'Dalam Instalasi'])) {
                    $errors[] = "Baris " . ($i + 1) . ": Status tidak valid (gunakan 'Aktif' atau 'Dalam Instalasi')";
                    $errorCount++;
                    continue;
                }
                
                $stmt = $conn->prepare("INSERT INTO akses_internet (nama_lokasi, provinsi, kabupaten, latitude, longitude, status, jenis_layanan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssddss", $nama_lokasi, $provinsi, $kabupaten, $latitude, $longitude, $status, $jenis_layanan);
            }
            
            if ($stmt->execute()) {
                $successCount++;
            } else {
                $errors[] = "Baris " . ($i + 1) . ": " . $stmt->error;
                $errorCount++;
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $errors[] = "Baris " . ($i + 1) . ": " . $e->getMessage();
            $errorCount++;
        }
    }
    
    $conn->commit();
    
    if (function_exists('logUserActivity')) {
        logUserActivity('import_data', "Import data {$type}: {$successCount} berhasil, {$errorCount} gagal");
    }
    
    $message = "Import selesai! {$successCount} data berhasil diimpor.";
    if ($errorCount > 0) {
        $message .= " {$errorCount} data gagal diimpor.";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'successCount' => $successCount,
        'errorCount' => $errorCount,
        'errors' => array_slice($errors, 0, 10) // Batasi error yang ditampilkan
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>