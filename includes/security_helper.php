<?php

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_email_domain($email, $required_domain = '@baktikominfo.id') {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    if (!str_ends_with(strtolower($email), strtolower($required_domain))) {
        return false;
    }
    
    return true;
}

function validate_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 huruf besar";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 huruf kecil";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 angka";
    }
    
    return $errors;
}

function get_user_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = trim($_SERVER[$key]);
            if (filter_var($ip, FILTER_VALIDATE_IP, 
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function is_ip_locked($conn, $ip) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts 
        FROM tabel_login_attempts 
        WHERE ip_address = ? 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return ($row['attempts'] >= 5);
}

function log_failed_login($conn, $username, $ip) {
    $stmt = $conn->prepare("
        INSERT INTO tabel_login_attempts (username, ip_address) 
        VALUES (?, ?)
    ");
    
    $stmt->bind_param("ss", $username, $ip);
    $stmt->execute();
    $stmt->close();
}

function clean_old_login_attempts($conn) {
    $conn->query("
        DELETE FROM tabel_login_attempts 
        WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
}

function log_admin_activity($conn, $admin_id, $action, $description = '') {
    $ip = get_user_ip();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("
        INSERT INTO tabel_admin_log (admin_id, action, description, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("issss", $admin_id, $action, $description, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function admin_exists($conn) {
    $result = $conn->query("SELECT id FROM tabel_admin LIMIT 1");
    return ($result && $result->num_rows > 0);
}

function update_last_activity($conn, $admin_id) {
    $stmt = $conn->prepare("UPDATE tabel_admin SET last_activity = NOW() WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->close();
}
?>