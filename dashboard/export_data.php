<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require '../includes/check_session.php';

if (!checkUserAccess(['super_admin', 'staff', 'pengawas'], false)) {
    die('Akses ditolak. Hanya Super Admin, Staff, dan Pengawas yang dapat mengunduh data.');
}

if (ob_get_level()) {
    ob_end_clean();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_bts_aksesinternet_dashboard_internal';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$type = $_GET['type'] ?? '';
$provinsi = $_GET['provinsi'] ?? '';

if (!in_array($type, ['bts', 'internet'])) {
    die('Tipe data tidak valid');
}

if ($type === 'bts') {
    $table = 'bts';
    $nama_field = 'nama_situs';
    $extra_fields = ', jaringan';
} else {
    $table = 'akses_internet';
    $nama_field = 'nama_lokasi';
    $extra_fields = '';
}

$sql = "SELECT {$nama_field} as nama, provinsi, kabupaten, latitude, longitude, status{$extra_fields}, jenis_layanan FROM {$table}";
if ($provinsi !== 'all' && !empty($provinsi)) {
    $sql .= " WHERE provinsi = ?";
}
$sql .= " ORDER BY provinsi, kabupaten, nama";

$stmt = $conn->prepare($sql);

if ($provinsi !== 'all' && !empty($provinsi)) {
    $stmt->bind_param("s", $provinsi);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $message = ($provinsi === 'all') ? "Tidak ada data untuk diekspor." : "Tidak ada data untuk provinsi {$provinsi}";
    die($message);
}

$provinsi_filename = ($provinsi === 'all' || empty($provinsi)) ? 'Semua_Provinsi' : str_replace([' ', '/', '\\'], '_', $provinsi);
$filename = "Data_" . ucfirst($type) . "_" . $provinsi_filename . "_" . date('Y-m-d_H-i-s');


$useExcel = false;
if (file_exists('vendor/autoload.php')) {
    try {
        require_once '../vendor/autoload.php';
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $useExcel = true;
        }
    } catch (Exception $e) {
        $useExcel = false;
    }
}

while (ob_get_level()) {
    ob_end_clean();
}

if ($useExcel) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    header('Expires: 0');
    
    try {
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $spreadsheet->getProperties()
            ->setCreator('BAKTI Kominfo Dashboard')
            ->setTitle('Data ' . ucfirst($type) . ' - ' . $provinsi)
            ->setSubject('Export Data ' . ucfirst($type))
            ->setDescription('Data ' . ucfirst($type) . ' untuk provinsi ' . $provinsi);
        
        $sheet->setTitle('Data ' . ucfirst($type));
        
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        $headers = ['Nama', 'Provinsi', 'Kabupaten/Kota', 'Latitude', 'Longitude', 'Status'];
        if ($type === 'bts') {
            $headers[] = 'Jaringan';
        }
        $headers[] = 'Jenis Layanan';
        
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $sheet->getStyleByColumnAndRow($col, 1)->applyFromArray($headerStyle);
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            $col++;
        }
        
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        
        $row = 2;
        $result->data_seek(0);
        while ($data = $result->fetch_assoc()) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, $row, $data['nama']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $data['provinsi']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $data['kabupaten']);
            $sheet->setCellValueByColumnAndRow($col++, $row, (float)$data['latitude']);
            $sheet->setCellValueByColumnAndRow($col++, $row, (float)$data['longitude']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $data['status']);
            
            if ($type === 'bts') {
                $sheet->setCellValueByColumnAndRow($col++, $row, $data['jaringan'] ?? '');
            }
            $sheet->setCellValueByColumnAndRow($col++, $row, $data['jenis_layanan']);
            
            $sheet->getStyleByColumnAndRow(1, $row, $col-1, $row)->applyFromArray($dataStyle);
            
            $row++;
        }
        
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        
    } catch (Exception $e) {
        $useExcel = false;
    }
}

if (!$useExcel) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    header('Expires: 0');
    
    echo "\xEF\xBB\xBF";
    
    $headers = ['Nama', 'Provinsi', 'Kabupaten/Kota', 'Latitude', 'Longitude', 'Status'];
    if ($type === 'bts') {
        $headers[] = 'Jaringan';
    }
    $headers[] = 'Jenis Layanan';
    
    echo implode(';', $headers) . "\r\n";
    
    $result->data_seek(0);
    while ($data = $result->fetch_assoc()) {
        $row_data = [
            '"' . str_replace('"', '""', $data['nama']) . '"',
            '"' . str_replace('"', '""', $data['provinsi']) . '"',
            '"' . str_replace('"', '""', $data['kabupaten']) . '"',
            $data['latitude'],
            $data['longitude'],
            '"' . str_replace('"', '""', $data['status']) . '"'
        ];
        
        if ($type === 'bts') {
            $row_data[] = '"' . str_replace('"', '""', $data['jaringan'] ?? '') . '"';
        }
        $row_data[] = '"' . str_replace('"', '""', $data['jenis_layanan']) . '"';
        
        echo implode(';', $row_data) . "\r\n";
    }
}

try {
    $final_filename = $filename . ($useExcel ? '.xlsx' : '.csv');
    
    $log_conn = new mysqli($host, $user, $pass, $db);
    if (!$log_conn->connect_error) {
        $log_stmt = $log_conn->prepare("INSERT INTO tabel_export_logs (user_id, export_type, provinsi, file_name) VALUES (?, ?, ?, ?)");
        if ($log_stmt) {
            $log_stmt->bind_param("isss", $_SESSION['user_id'], $type, $provinsi, $final_filename);
            $log_stmt->execute();
            $log_stmt->close();
        }
        $log_conn->close();
    }
} catch (Exception $e) {
}

$stmt->close();
$conn->close();
exit;