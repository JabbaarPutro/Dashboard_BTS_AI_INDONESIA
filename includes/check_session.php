<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_bts_aksesinternet_dashboard_internal';

function checkUserAccess($required_roles = [], $redirect_to_login = true) {
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        if ($redirect_to_login) {
            header('Location: ../auth/login.php');
            exit;
        }
        return false;
    }
    
    if (!isset($_SESSION['user_status']) || $_SESSION['user_status'] !== 'active') {
        session_destroy();
        if ($redirect_to_login) {
            header('Location: ../auth/login.php?error=inactive');
            exit;
        }
        return false;
    }
    
    if (empty($required_roles)) {
        return true;
    }
    
    $user_role = $_SESSION['user_role'] ?? '';
    if (!in_array($user_role, $required_roles)) {
        if ($redirect_to_login) {
            header('Location: ../includes/access_denied.php');
            exit;
        }
        return false;
    }
    
    return true;
}

function updateLastActivity() {
    if (isset($_SESSION['user_id'])) {
        global $host, $user, $pass, $db;
        $conn = new mysqli($host, $user, $pass, $db);
        if (!$conn->connect_error) {
            $stmt = $conn->prepare("UPDATE tabel_user SET last_activity = NOW() WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
}

function logUserActivity($action, $description = '') {
    if (isset($_SESSION['user_id'])) {
        global $host, $user, $pass, $db;
        $conn = new mysqli($host, $user, $pass, $db);
        if (!$conn->connect_error) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $conn->prepare("INSERT INTO tabel_user_audit (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $_SESSION['user_id'], $action, $description, $ip, $user_agent);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
}

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

updateLastActivity();
$_SESSION['last_activity'] = time();
?>