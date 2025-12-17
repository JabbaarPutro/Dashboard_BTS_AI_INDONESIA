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
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: login.php?error=empty');
    exit;
}

$sql = "SELECT id, username, password, nama_lengkap, email, role, status FROM tabel_user WHERE username = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Database prepare error: " . $conn->error);
    header('Location: login.php?error=system');
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    if ($user['status'] !== 'active') {
        $stmt->close();
        $conn->close();
        
        error_log("Login failed for user {$username}: status is {$user['status']}");
        
        if ($user['status'] === 'pending') {
            header('Location: login.php?error=pending');
        } else {
            header('Location: login.php?error=inactive');
        }
        exit;
    }
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_nama'] = $user['nama_lengkap'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_status'] = $user['status'];
        $_SESSION['last_activity'] = time();
        
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $log_stmt = $conn->prepare("INSERT INTO tabel_user_audit (user_id, action, description, ip_address, user_agent) VALUES (?, 'login', 'User berhasil login', ?, ?)");
            if ($log_stmt) {
                $log_stmt->bind_param("iss", $user['id'], $ip, $user_agent);
                $log_stmt->execute();
                $log_stmt->close();
            }
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
        }
        
        try {
            $update_sql = "UPDATE tabel_user SET last_login = NOW(), last_activity = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt) {
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
        
        $stmt->close();
        $conn->close();
        
        header('Location: ../dashboard/dashboard.php');
        exit;
    } else {
        $stmt->close();
        $conn->close();
        
        error_log("Login failed for user {$username}: wrong password");
        
        header('Location: login.php?error=invalid');
        exit;
    }
} else {
    $stmt->close();
    $conn->close();
    
    error_log("Login failed: user {$username} not found");
    
    header('Location: login.php?error=invalid');
    exit;
}
?>