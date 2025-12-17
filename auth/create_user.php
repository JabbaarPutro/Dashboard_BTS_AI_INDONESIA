<?php
session_start();
require '../includes/check_session.php';

if (!checkUserAccess(['super_admin'], false)) {
    header('Location: access_denied.php');
    exit;
}

$host = 'localhost'; 
$user = 'root'; 
$pass = ''; 
$db   = 'db_bts_aksesinternet_dashboard_internal';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_users.php');
    exit;
}

$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$role = trim($_POST['role'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

if (empty($nama_lengkap)) $errors[] = 'Nama lengkap harus diisi';
if (empty($email)) $errors[] = 'Email harus diisi';
if (empty($username)) $errors[] = 'Username harus diisi';
if (empty($role)) $errors[] = 'Role harus dipilih';
if (empty($password)) $errors[] = 'Password harus diisi';

if (!str_ends_with(strtolower($email), '@baktikominfo.id')) {
    $errors[] = 'Email harus menggunakan domain @baktikominfo.id';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid';
}

if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $errors[] = 'Username harus 3-20 karakter, hanya huruf, angka, dan underscore';
}

if (!in_array($role, ['super_admin', 'staff', 'pengawas'])) {
    $errors[] = 'Role tidak valid';
}

if (strlen($password) < 8) {
    $errors[] = 'Password minimal 8 karakter';
}

if ($password !== $confirm_password) {
    $errors[] = 'Password dan konfirmasi password tidak cocok';
}

$check_stmt = $conn->prepare("SELECT id FROM tabel_user WHERE username = ? OR email = ?");
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    $errors[] = 'Username atau email sudah terdaftar';
}
$check_stmt->close();

if (!empty($errors)) {
    $error_msg = implode(', ', $errors);
    header("Location: manage_users.php?error=" . urlencode($error_msg));
    exit;
}

$password_hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO tabel_user (nama_lengkap, username, email, password, role, status, approved_by, approved_at, created_at) VALUES (?, ?, ?, ?, ?, 'active', ?, NOW(), NOW())");
$stmt->bind_param("sssssi", $nama_lengkap, $username, $email, $password_hashed, $role, $_SESSION['user_id']);

if ($stmt->execute()) {
    logUserActivity('create_user', "Membuat user baru: {$username} ({$role})");
    
    header('Location: manage_users.php?success=' . urlencode('User berhasil dibuat'));
} else {
    header('Location: manage_users.php?error=' . urlencode('Gagal membuat user'));
}

$stmt->close();
$conn->close();
?>