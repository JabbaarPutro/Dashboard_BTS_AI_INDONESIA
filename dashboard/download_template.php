<?php
require '../includes/check_session.php';

$canEditData = checkUserAccess(['super_admin', 'staff'], false);
if (!$canEditData) {
    header('Location: ../includes/access_denied.php');
    exit;
}

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$type = isset($_GET['type']) ? $_GET['type'] : 'bts';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4F46E5']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];

$dataStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'CCCCCC']
        ]
    ]
];

if ($type === 'bts') {
    $headers = ['nama_situs', 'provinsi', 'kabupaten', 'latitude', 'longitude', 'status', 'jaringan', 'jenis_layanan'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Data contoh
    $exampleData = [
        ['BTS Jakarta Pusat 1', 'DKI JAKARTA', 'JAKARTA PUSAT', -6.200000, 106.816666, 'On Air', '4G', 'Fiber Optik'],
        ['BTS Bandung 1', 'JAWA BARAT', 'BANDUNG', -6.917464, 107.619123, 'Dalam Pembangunan', '5G', 'Wireless'],
        ['BTS Surabaya 1', 'JAWA TIMUR', 'SURABAYA', -7.250445, 112.768845, 'On Air', '4G', 'Fiber Optik']
    ];
    $sheet->fromArray($exampleData, null, 'A2');
    
    // Set column width
    $sheet->getColumnDimension('A')->setWidth(25);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(12);
    $sheet->getColumnDimension('E')->setWidth(12);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(12);
    $sheet->getColumnDimension('H')->setWidth(20);
    
    $sheet->setCellValue('A6', 'INSTRUKSI:');
    $sheet->setCellValue('A7', '1. Jangan mengubah nama kolom header (baris pertama)');
    $sheet->setCellValue('A8', '2. Nama provinsi dan kabupaten harus HURUF KAPITAL SEMUA (contoh: DKI JAKARTA, JAKARTA PUSAT)');
    $sheet->setCellValue('A9', '3. Status harus: "On Air" atau "Dalam Pembangunan"');
    $sheet->setCellValue('A10', '4. Jaringan harus: "2G", "3G", "4G", atau "5G"');
    $sheet->setCellValue('A11', '5. Jenis Layanan harus: "Fiber Optik", "Wireless", atau "Satelit (VSAT)"');
    $sheet->setCellValue('A12', '6. Latitude dan Longitude harus berupa angka desimal');
    $sheet->setCellValue('A13', '7. Hapus baris contoh ini sebelum import');
    
    $sheet->getStyle('A6:A13')->getFont()->setItalic(true)->setSize(10);
    
    $filename = 'template_import_bts.xlsx';
    
} else {
    $headers = ['nama_lokasi', 'provinsi', 'kabupaten', 'latitude', 'longitude', 'status', 'jenis_layanan'];
    $sheet->fromArray($headers, null, 'A1');
    
    $exampleData = [
        ['Kantor Desa Jakarta 1', 'DKI JAKARTA', 'JAKARTA SELATAN', -6.261493, 106.810600, 'Aktif', 'Fiber Optik'],
        ['Sekolah Bandung 1', 'JAWA BARAT', 'BANDUNG', -6.914744, 107.609810, 'Dalam Instalasi', 'Wireless'],
        ['Puskesmas Surabaya 1', 'JAWA TIMUR', 'SURABAYA', -7.289150, 112.734398, 'Aktif', 'Fiber Optik']
    ];
    $sheet->fromArray($exampleData, null, 'A2');
    
    $sheet->getColumnDimension('A')->setWidth(25);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(12);
    $sheet->getColumnDimension('E')->setWidth(12);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(20);
    
    $sheet->setCellValue('A6', 'INSTRUKSI:');
    $sheet->setCellValue('A7', '1. Jangan mengubah nama kolom header (baris pertama)');
    $sheet->setCellValue('A8', '2. Nama provinsi dan kabupaten harus HURUF KAPITAL SEMUA (contoh: DKI JAKARTA, JAKARTA SELATAN)');
    $sheet->setCellValue('A9', '3. Status harus: "Aktif" atau "Dalam Instalasi"');
    $sheet->setCellValue('A10', '4. Jenis Layanan harus: "Fiber Optik", "Wireless", atau "Satelit (VSAT)"');
    $sheet->setCellValue('A11', '5. Latitude dan Longitude harus berupa angka desimal');
    $sheet->setCellValue('A12', '6. Hapus baris contoh ini sebelum import');
    
    $sheet->getStyle('A6:A12')->getFont()->setItalic(true)->setSize(10);
    
    $filename = 'template_import_akses_internet.xlsx';
}

$lastColumn = chr(64 + count($headers));
$sheet->getStyle("A1:{$lastColumn}1")->applyFromArray($headerStyle);

$lastRow = 2 + count($exampleData) - 1;
$sheet->getStyle("A2:{$lastColumn}{$lastRow}")->applyFromArray($dataStyle);

$sheet->freezePane('A2');

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>