<?php
session_start();
require '../includes/check_session.php';

if (!checkUserAccess(['super_admin', 'staff'], false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya Super Admin dan Staff yang dapat menambah data.']);
    exit;
}

header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_bts_aksesinternet_dashboard_internal';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit;
}

$type = $_POST['type'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$type) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Request tidak valid.']);
    exit;
}

$provinsi = strtoupper(trim($_POST['provinsi'] ?? ''));
$kabupaten = strtoupper(trim($_POST['kabupaten'] ?? ''));
$latitude = (float)($_POST['latitude'] ?? 0);
$longitude = (float)($_POST['longitude'] ?? 0);
$status = $_POST['status'] ?? '';
$jenis_layanan = trim($_POST['jenis_layanan'] ?? '');

if (!in_array($type, ['bts', 'internet'])) {
    echo json_encode(['success' => false, 'message' => 'Tipe data tidak valid']);
    exit;
}

if (empty($provinsi) || empty($kabupaten) || empty($status) || empty($jenis_layanan)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit;
}

if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
    echo json_encode(['success' => false, 'message' => 'Koordinat tidak valid']);
    exit;
}

try {
    if ($type === 'bts') {
        $nama_situs = strtoupper(trim($_POST['nama_situs'] ?? ''));
        $jaringan = trim($_POST['jaringan'] ?? '');
        
        if (empty($nama_situs)) {
            echo json_encode(['success' => false, 'message' => 'Nama situs harus diisi']);
            exit;
        }
        
        if (!in_array($status, ['On Air', 'Dalam Pembangunan'])) {
            echo json_encode(['success' => false, 'message' => 'Status BTS tidak valid']);
            exit;
        }
        
        $sql = "INSERT INTO bts (nama_situs, provinsi, kabupaten, latitude, longitude, status, jaringan, jenis_layanan, last_updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Gagal menyiapkan query: " . $conn->error);
        }
        
        $stmt->bind_param("sssddsss", $nama_situs, $provinsi, $kabupaten, $latitude, $longitude, $status, $jaringan, $jenis_layanan);
        
    } elseif ($type === 'internet') {
        $nama_lokasi = strtoupper(trim($_POST['nama_lokasi'] ?? ''));
        
        if (empty($nama_lokasi)) {
            echo json_encode(['success' => false, 'message' => 'Nama lokasi harus diisi']);
            exit;
        }
        
        if (!in_array($status, ['Aktif', 'Dalam Instalasi'])) {
            echo json_encode(['success' => false, 'message' => 'Status akses internet tidak valid']);
            exit;
        }
        
        $sql = "INSERT INTO akses_internet (nama_lokasi, provinsi, kabupaten, latitude, longitude, status, jenis_layanan, last_updated) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Gagal menyiapkan query: " . $conn->error);
        }
        
        $stmt->bind_param("sssddss", $nama_lokasi, $provinsi, $kabupaten, $latitude, $longitude, $status, $jenis_layanan);
        
    } else {
        throw new Exception("Tipe data tidak dikenal.");
    }

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        
        $action_desc = $type === 'bts' ? "Menambah data BTS: {$nama_situs}" : "Menambah data Akses Internet: {$nama_lokasi}";
        if (function_exists('logUserActivity')) {
            logUserActivity('add_data', $action_desc);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil ditambahkan.',
            'id' => $new_id
        ]);
    } else {
        throw new Exception("Gagal menyimpan data ke database: " . $stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>