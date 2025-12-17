<?php
session_start();
require '../includes/check_session.php';

if (!checkUserAccess(['super_admin'], false)) {
    header('Location: ../includes/access_denied.php');
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_bts_aksesinternet_dashboard_internal';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    header('Location: manage_users.php?error=' . urlencode('Koneksi database gagal'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_users.php');
    exit;
}

$user_id = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($user_id <= 0 || !in_array($action, ['activate', 'deactivate'])) {
    header('Location: manage_users.php?error=' . urlencode('Parameter tidak valid'));
    exit;
}

if ($user_id == $_SESSION['user_id']) {
    header('Location: manage_users.php?error=' . urlencode('Tidak dapat mengubah status akun sendiri'));
    exit;
}

$new_status = $action === 'activate' ? 'active' : 'inactive';

$stmt = $conn->prepare("UPDATE tabel_user SET status = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("si", $new_status, $user_id);

if ($stmt->execute()) {
    $user_stmt = $conn->prepare("SELECT username FROM tabel_user WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_info = $user_stmt->get_result()->fetch_assoc();
    
    if ($user_info) {
        $action_text = $action === 'activate' ? 'mengaktifkan' : 'menonaktifkan';
        logUserActivity('toggle_user_status', "Berhasil {$action_text} user: {$user_info['username']}");
    }
    
    $success_message = $action === 'activate' ? 'User berhasil diaktifkan' : 'User berhasil dinonaktifkan';
    header('Location: manage_users.php?success=' . urlencode($success_message));
} else {
    header('Location: manage_users.php?error=' . urlencode('Gagal mengubah status user'));
}

$stmt->close();
$conn->close();
?>