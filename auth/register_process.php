<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost'; 
$user = 'root'; 
$pass = ''; 
$db   = 'db_bts_aksesinternet_dashboard_internal';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$role = trim($_POST['role'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($nama_lengkap) || empty($email) || empty($username) || empty($role) || empty($password) || empty($confirm_password)) {
    header('Location: register.php?error=empty');
    exit;
}

if (!in_array($role, ['staff', 'pengawas'])) {
    header('Location: register.php?error=role');
    exit;
}

$required_domain = '@baktikominfo.id';
if (!str_ends_with(strtolower($email), $required_domain)) {
    header('Location: register.php?error=domain');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php?error=email_format');
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    header('Location: register.php?error=username_format');
    exit;
}

if ($password !== $confirm_password) {
    header('Location: register.php?error=password');
    exit;
}

if (strlen($password) < 8) {
    header('Location: register.php?error=password_length');
    exit;
}

$password_hashed = password_hash($password, PASSWORD_DEFAULT);

$check_user = $conn->prepare("SELECT id FROM tabel_user WHERE username = ? OR email = ?");
$check_user->bind_param("ss", $username, $email);
$check_user->execute();
$user_result = $check_user->get_result();

$check_request = $conn->prepare("SELECT id FROM tabel_user_requests WHERE username = ? OR email = ?");
$check_request->bind_param("ss", $username, $email);
$check_request->execute();
$request_result = $check_request->get_result();

if ($user_result->num_rows > 0 || $request_result->num_rows > 0) {
    $check_user->close();
    $check_request->close();
    header('Location: register.php?error=exists');
    exit;
}

$check_user->close();
$check_request->close();

$sql = "INSERT INTO tabel_user_requests (nama_lengkap, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Prepare Gagal: " . $conn->error);
}

$stmt->bind_param("sssss", $nama_lengkap, $username, $email, $password_hashed, $role);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    header('Location: register.php?success=1');
    exit;
} else {
    $error_message = $stmt->error;
    $stmt->close();
    $conn->close();
    die("Eksekusi Gagal: " . $error_message);
}
?>