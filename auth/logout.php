<?php
session_start();

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'db_bts_aksesinternet_dashboard_internal';
    $conn = new mysqli($host, $user, $pass, $db);
    
    if (!$conn->connect_error && isset($_SESSION['user_id'])) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO tabel_user_audit (user_id, action, description, ip_address, user_agent) VALUES (?, 'logout', 'User berhasil logout', ?, ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $ip, $user_agent);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

session_destroy();

header('Location: ../index.php');
exit;
?>