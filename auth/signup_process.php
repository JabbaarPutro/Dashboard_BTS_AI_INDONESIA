<?php
session_start();

$host = 'localhost'; 
$user = 'root'; 
$pass = ''; 
$db   = 'db_bts_aksesinternet_dashboard_internal';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit;
}

$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($nama_lengkap) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
    header('Location: signup.php?error=empty');
    exit;
}

$required_domain = '@baktikominfo.id';
if (!str_ends_with(strtolower($email), $required_domain)) {
    header('Location: signup.php?error=domain');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: signup.php?error=email_format');
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    header('Location: signup.php?error=username_format');
    exit;
}

if ($password !== $confirm_password) {
    header('Location: signup.php?error=password');
    exit;
}

if (strlen($password) < 8) {
    header('Location: signup.php?error=password_length');
    exit;
}

$password_hashed = password_hash($password, PASSWORD_DEFAULT);

$check_stmt = $conn->prepare("SELECT id FROM tabel_user WHERE username = ? OR email = ?");
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $check_stmt->close();
    header('Location: signup.php?error=exists');
    exit;
}
$check_stmt->close();

$sql = "INSERT INTO tabel_user (nama_lengkap, username, email, password, role, status) VALUES (?, ?, ?, ?, 'super_admin', 'active')";
$sql = "INSERT INTO tabel_user (nama_lengkap, username, email, password) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    header('Location: signup.php?error=general');
    exit;
}

$stmt->bind_param("ssss", $nama_lengkap, $username, $email, $password_hashed);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: login.php?success=1');
    exit;
} else {
    $stmt->close();
    $conn->close();
    
    if ($conn->errno == 1062) {
        header('Location: signup.php?error=exists');
    } else {
        header('Location: signup.php?error=general');
    }
    exit;
}
?>