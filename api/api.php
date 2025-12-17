<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_bts_aksesinternet_dashboard_internal';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : 'bts';

if (in_array($type, ['stats_overview', 'stats_regional', 'stats_technology', 'stats_insights'])) {
    $source = isset($_GET['source']) && in_array($_GET['source'], ['bts', 'internet']) ? $_GET['source'] : 'bts';

    switch($type) {
        case 'stats_overview':
            echo json_encode(getOverviewStats($conn, $source));
            break;
        case 'stats_regional':
            echo json_encode(getRegionalDistribution($conn, $source));
            break;
        case 'stats_technology':
            echo json_encode(getTechnologyDistribution($conn, $source));
            break;
        case 'stats_insights':
            echo json_encode(getStrategicInsights($conn, $source));
            break;
    }
    $conn->close();
    exit();
}

if ($type === 'get_provinces') {
    $provinces = [];
    $sql = "SELECT DISTINCT provinsi FROM bts UNION SELECT DISTINCT provinsi FROM akses_internet ORDER BY provinsi ASC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if (!empty($row['provinsi'])) {
                $provinces[] = $row['provinsi'];
            }
        }
    }
    echo json_encode(['provinces' => $provinces]);
    $conn->close();
    exit();
}

$pointData = [];
$provinceData = [];
$filters = []; 
$lastUpdated = null;

if ($type === 'bts') {
    $sql = "SELECT id, nama_situs, provinsi, kabupaten, latitude, longitude, status, jaringan, jenis_layanan FROM bts";
    $lastUpdatedQuery = "SELECT MAX(last_updated) as last_updated FROM bts";
    $statusMapping = ['On Air' => 'onAir', 'Dalam Pembangunan' => 'dalamPembangunan'];

} elseif ($type === 'internet') {
    $sql = "SELECT id, nama_lokasi, provinsi, kabupaten, latitude, longitude, status, jenis_layanan FROM akses_internet";
    $lastUpdatedQuery = "SELECT MAX(last_updated) as last_updated FROM akses_internet";
    $statusMapping = ['Aktif' => 'onAir', 'Dalam Instalasi' => 'dalamPembangunan'];
} else {
    echo json_encode(['error' => 'Tipe data tidak valid.']);
    $conn->close();
    exit();
}

$lastUpdatedResult = $conn->query($lastUpdatedQuery);
if ($lastUpdatedResult && $lastUpdatedResult->num_rows > 0) {
    $row = $lastUpdatedResult->fetch_assoc();
    if ($row['last_updated']) {
        $timestamp = strtotime($row['last_updated']);
        $bulan = [ 1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember' ];
        $lastUpdated = date('j', $timestamp) . ' ' . $bulan[date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}

$result = $conn->query($sql);

$jaringanTypes = [];
$layananTypes = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $point = [
            'id' => (int)$row['id'],
            'provinsi' => $row['provinsi'],
            'kabupaten' => $row['kabupaten'],
            'lat' => (float)$row['latitude'],
            'lon' => (float)$row['longitude'],
            'status' => $row['status'],
        ];

        if ($type === 'bts') {
            $point['nama_situs'] = $row['nama_situs'];
            $point['jaringan'] = $row['jaringan'];
            $point['jenis_layanan'] = $row['jenis_layanan'];
            
            if ($row['jaringan'] && !in_array($row['jaringan'], $jaringanTypes)) $jaringanTypes[] = $row['jaringan'];
            if ($row['jenis_layanan'] && !in_array($row['jenis_layanan'], $layananTypes)) $layananTypes[] = $row['jenis_layanan'];
        } else {
            $point['nama_lokasi'] = $row['nama_lokasi'];
            $point['jaringan'] = null;
            $point['jenis_layanan'] = $row['jenis_layanan'];
            
            if ($row['jenis_layanan'] && !in_array($row['jenis_layanan'], $layananTypes)) $layananTypes[] = $row['jenis_layanan'];
        }
        $pointData[] = $point;

        $provinsi = $row['provinsi'];
        $kabupaten = $row['kabupaten'];
        $status = $row['status'];
        $statusKey = $statusMapping[$status] ?? 'lainnya';

        if (!isset($provinceData[$provinsi])) {
            $provinceData[$provinsi] = ['total' => 0, 'onAir' => 0, 'dalamPembangunan' => 0, 'regencies' => []];
        }
        if (!isset($provinceData[$provinsi]['regencies'][$kabupaten])) {
            $provinceData[$provinsi]['regencies'][$kabupaten] = ['total' => 0, 'onAir' => 0, 'dalamPembangunan' => 0];
        }

        $provinceData[$provinsi]['total']++;
        $provinceData[$provinsi]['regencies'][$kabupaten]['total']++;
        if (isset($provinceData[$provinsi][$statusKey])) {
            $provinceData[$provinsi][$statusKey]++;
            $provinceData[$provinsi]['regencies'][$kabupaten][$statusKey]++;
        }
    }
}
$conn->close();

if ($type === 'bts') {
    sort($jaringanTypes);
    sort($layananTypes);
    $filters['jaringan'] = $jaringanTypes;
    $filters['jenis_layanan'] = $layananTypes;
} else {
    sort($layananTypes);
    $filters['jenis_layanan'] = $layananTypes;
}

$output = [
    'pointData' => $pointData,
    'provinceData' => $provinceData,
    'lastUpdated' => $lastUpdated,
    'filters' => $filters 
];

echo json_encode($output, JSON_PRETTY_PRINT);


function getOverviewStats($conn, $source) {
    if ($source === 'bts') {
        $total = $conn->query("SELECT COUNT(*) as count FROM bts")->fetch_assoc()['count'];
        $operational = $conn->query("SELECT COUNT(*) as count FROM bts WHERE status = 'On Air'")->fetch_assoc()['count'];
        $construction = $conn->query("SELECT COUNT(*) as count FROM bts WHERE status = 'Dalam Pembangunan'")->fetch_assoc()['count'];
        $provinceQuery = "SELECT provinsi FROM bts";
    } else { 
        $total = $conn->query("SELECT COUNT(*) as count FROM akses_internet")->fetch_assoc()['count'];
        $operational = $conn->query("SELECT COUNT(*) as count FROM akses_internet WHERE status = 'Aktif'")->fetch_assoc()['count'];
        $construction = $conn->query("SELECT COUNT(*) as count FROM akses_internet WHERE status = 'Dalam Instalasi'")->fetch_assoc()['count'];
        $provinceQuery = "SELECT provinsi FROM akses_internet";
    }
    
    $operationalRate = $total > 0 ? ($operational / $total) * 100 : 0;
    
    $provinceCountQuery = "SELECT COUNT(DISTINCT provinsi) as count FROM ($provinceQuery) as sub";
    $provinceCount = $conn->query($provinceCountQuery)->fetch_assoc()['count'];
    
    $coverageScore = $total > 0 ? min(10, ($provinceCount / 38) * 10) : 0;
    
    return [
        'totalInfrastructure' => $total,
        'infrastructureBreakdown' => ($source === 'bts' ? "Total Menara BTS" : "Total Titik Internet"),
        'operationalRate' => round($operationalRate, 1),
        'operationalCount' => "{$operational} dari {$total} aktif",
        'underConstruction' => $construction,
        'constructionPercentage' => $total > 0 ? round(($construction / $total) * 100, 1) . "% dari total" : "0% dari total",
        'coverageScore' => round($coverageScore, 1) . "/10"
    ];
}

function getRegionalDistribution($conn, $source) {
    $tableName = ($source === 'bts') ? 'bts' : 'akses_internet';

    $top5Query = "
        SELECT provinsi, COUNT(*) as count 
        FROM {$tableName} 
        WHERE provinsi IS NOT NULL AND provinsi != ''
        GROUP BY provinsi 
        ORDER BY count DESC 
        LIMIT 5
    ";

    $topProvinces = $conn->query($top5Query)->fetch_all(MYSQLI_ASSOC);
    $totalQuery = "SELECT COUNT(*) as total FROM {$tableName}";
    $grandTotal = $conn->query($totalQuery)->fetch_assoc()['total'];

    return [
        'topProvinces' => $topProvinces,
        'grandTotal' => (int)$grandTotal
    ];
}

function getTechnologyDistribution($conn, $source) {
    $labels = [];
    $data = [];
    $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];

    if ($source === 'bts') {
        $query = "
                        SELECT 
                            jaringan as tech,
                            COUNT(*) as count
                        FROM bts 
                        WHERE jaringan IS NOT NULL AND jaringan != ''
                        GROUP BY jaringan
                        ORDER BY jaringan ASC
                 "; 
        $techData = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
        foreach ($techData as $tech) {
            $labels[] = $tech['tech'];
            $data[] = $tech['count'];
        }
    } else { 
        $query = "
            SELECT jenis_layanan as tech, COUNT(*) as count 
            FROM akses_internet 
            GROUP BY jenis_layanan
        ";
        $techData = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
        foreach ($techData as $tech) {
            $labels[] = $tech['tech'];
            $data[] = $tech['count'];
        }
    }
    
    return [
        'labels' => $labels,
        'data' => $data,
        'colors' => array_slice($colors, 0, count($labels))
    ];
}

function getStrategicInsights($conn, $source) {
    $tableName = ($source === 'bts') ? 'bts' : 'akses_internet';
    $totalCount = $conn->query("SELECT COUNT(*) as count FROM {$tableName}")->fetch_assoc()['count'];
    $highPriority = [];
    $optimization = [];

if ($source === 'bts') {
    $totalCount = $conn->query("SELECT COUNT(*) as count FROM bts")->fetch_assoc()['count']; 
    $legacyCount = $conn->query("SELECT COUNT(*) as count FROM bts WHERE jaringan IN ('2G', '3G')")->fetch_assoc()['count'];
    $fiveGReady = $conn->query("SELECT COUNT(*) as count FROM bts WHERE jaringan = '5G'")->fetch_assoc()['count'];
    $fiberCount = $conn->query("SELECT COUNT(*) as count FROM bts WHERE jenis_layanan = 'Fiber Optik'")->fetch_assoc()['count'];

    $legacyPercentage = $totalCount > 0 ? round(($legacyCount / $totalCount) * 100) : 0;
    $fiveGPercentage = $totalCount > 0 ? round(($fiveGReady / $totalCount) * 100) : 0;
    $fiberPercentage = $totalCount > 0 ? round(($fiberCount / $totalCount) * 100) : 0;

    if ($legacyPercentage > 30) $highPriority[] = "Upgrade {$legacyCount} BTS dari teknologi 2G/3G ke 4G/5G";
    if ($fiveGPercentage < 15) $optimization[] = "Percepat implementasi 5G di area metropolitan";

    return [
        'digitalDivideIndex' => 'N/A', 'redundancyScore' => 'N/A',
        'fiveGReady' => $fiveGPercentage, 
        'legacyCoverage' => $legacyPercentage,
        'fiberCoverage' => $fiberPercentage, 
        'highPriority' => $highPriority,
        'optimization' => $optimization, 
        'divideInsight' => 'Analisis per-sumber daya.'
    ];

    }else { 
        $fiberCount = $conn->query("SELECT COUNT(*) as count FROM akses_internet WHERE jenis_layanan = 'Fiber Optik'")->fetch_assoc()['count'];
        $fiberPercentage = $totalCount > 0 ? round(($fiberCount / $totalCount) * 100) : 0;
        
        if ($fiberPercentage < 60) $optimization[] = "Ekspansi fiber optik untuk meningkatkan kualitas koneksi";

        return [
            'digitalDivideIndex' => 'N/A', 'redundancyScore' => 'N/A',
            'fiveGReady' => 'N/A', 'legacyCoverage' => 'N/A',
            'fiberCoverage' => $fiberPercentage, 'highPriority' => $highPriority,
            'optimization' => $optimization, 'divideInsight' => 'Analisis per-sumber daya.'
        ];
    }
}
?>