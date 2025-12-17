<?php 
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
    die("Koneksi database gagal: " . $conn->connect_error);
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        
        if ($user_id != $_SESSION['user_id']) {
            $check_user = $conn->prepare("SELECT username, role FROM tabel_user WHERE id = ?");
            $check_user->bind_param("i", $user_id);
            $check_user->execute();
            $user_result = $check_user->get_result();
            
            if ($user_result->num_rows === 1) {
                $user_data = $user_result->fetch_assoc();
                
                if ($user_data['role'] !== 'super_admin') {
                    $delete_stmt = $conn->prepare("DELETE FROM tabel_user WHERE id = ?");
                    $delete_stmt->bind_param("i", $user_id);
                    
                    if ($delete_stmt->execute()) {
                        $message = "User {$user_data['username']} berhasil dihapus dari database.";
                        logUserActivity('delete_user', "Menghapus user: {$user_data['username']}");
                    } else {
                        $error = "Gagal menghapus user.";
                    }
                    $delete_stmt->close();
                } else {
                    $error = "Tidak dapat menghapus super admin.";
                }
            } else {
                $error = "User tidak ditemukan.";
            }
            $check_user->close();
        } else {
            $error = "Tidak dapat menghapus akun sendiri.";
        }
    } elseif (isset($_POST['action']) && isset($_POST['request_id'])) {
        $action = $_POST['action'];
        $request_id = (int)$_POST['request_id'];
        
        if ($action === 'approve') {
            $get_request = $conn->prepare("SELECT * FROM tabel_user_requests WHERE id = ? AND status = 'pending'");
            $get_request->bind_param("i", $request_id);
            $get_request->execute();
            $result = $get_request->get_result();
            
            if ($result->num_rows === 1) {
                $request = $result->fetch_assoc();
                
                $insert_user = $conn->prepare("INSERT INTO tabel_user (nama_lengkap, username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
                $insert_user->bind_param("sssss", 
                    $request['nama_lengkap'], 
                    $request['username'], 
                    $request['email'], 
                    $request['password'], 
                    $request['role']
                );
                
                if ($insert_user->execute()) {
                    $update_request = $conn->prepare("UPDATE tabel_user_requests SET status = 'approved', processed_at = NOW() WHERE id = ?");
                    $update_request->bind_param("i", $request_id);
                    $update_request->execute();
                    
                    $message = "User {$request['nama_lengkap']} berhasil disetujui dan diaktivasi.";
                    logUserActivity('approve_user', "Menyetujui pendaftaran user: {$request['username']}");
                } else {
                    $error = "Gagal mengaktivasi user.";
                }
                $insert_user->close();
            } else {
                $error = "Request tidak ditemukan atau sudah diproses.";
            }
            $get_request->close();
            
        } elseif ($action === 'reject') {
            $update_request = $conn->prepare("UPDATE tabel_user_requests SET status = 'rejected', processed_at = NOW() WHERE id = ? AND status = 'pending'");
            $update_request->bind_param("i", $request_id);
            
            if ($update_request->execute() && $conn->affected_rows > 0) {
                $message = "Request pendaftaran berhasil ditolak.";
                logUserActivity('reject_user', "Menolak pendaftaran user request ID: {$request_id}");
            } else {
                $error = "Gagal menolak request atau request sudah diproses.";
            }
            $update_request->close();
        }
    }
}

$users = [];
$sql = "SELECT u.id, u.nama_lengkap, u.username, u.email, u.role, u.status, u.created_at FROM tabel_user u ORDER BY u.created_at DESC";
$stmt = $conn->query($sql);
if ($stmt) {
    $users = $stmt->fetch_all(MYSQLI_ASSOC);
}

$pending_requests = [];
$sql_requests = "SELECT ur.id, ur.nama_lengkap, ur.username, ur.email, ur.role, ur.status, ur.requested_at FROM tabel_user_requests ur WHERE ur.status = 'pending' ORDER BY ur.requested_at DESC";
$stmt_requests = $conn->query($sql_requests);
if ($stmt_requests) {
    $pending_requests = $stmt_requests->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-100">

    <?php 
    $currentPage = 'manage_users';
    require '../includes/sidebar.php'; 
    ?>

    <button id="mobile-menu-button" class="lg:hidden fixed top-4 left-4 z-40 bg-slate-800 text-white p-2 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>

    <div id="main-content" class="main-content ml-0 lg:ml-64 min-h-screen p-4 md:p-8">
        
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen User</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola user dan persetujuan pendaftaran.</p>
            </div>
            <button onclick="showAddUserModal()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg transition-colors mt-4 sm:mt-0">
                + Tambah User Baru
            </button>
        </div>

        <?php if (!empty($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($pending_requests)): ?>
        <div class="bg-white rounded-xl shadow-md mb-8">
            <div class="p-6 border-b border-slate-200 bg-amber-50">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div>
                        <h2 class="text-xl font-bold text-amber-800">Request Pendaftaran Pending</h2>
                        <p class="text-sm text-amber-700 mt-1"><?php echo count($pending_requests); ?> user menunggu persetujuan</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pendaftar</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal Daftar</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <?php foreach ($pending_requests as $request): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-amber-500 flex items-center justify-center">
                                            <span class="text-md font-bold text-white"><?php echo strtoupper(substr($request['nama_lengkap'], 0, 1)); ?></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($request['nama_lengkap']); ?></div>
                                        <div class="text-xs text-amber-600 font-semibold">Menunggu Persetujuan</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo htmlspecialchars($request['email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo htmlspecialchars($request['username']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full <?php 
                                    echo match($request['role']) {
                                        'staff' => 'bg-blue-100 text-blue-800',
                                        'pengawas' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>"><?php echo str_replace('_', ' ', ucwords($request['role'])); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?php 
                                    $requestedDate = new DateTime($request['requested_at']);
                                    echo $requestedDate->format('d M Y ');
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200" onclick="return confirm('Setujui pendaftaran <?php echo htmlspecialchars($request['nama_lengkap']); ?>?')">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Setujui
                                        </button>
                                    </form>
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200" onclick="return confirm('Tolak pendaftaran <?php echo htmlspecialchars($request['nama_lengkap']); ?>?')">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-md">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">Daftar User Aktif</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Bergabung</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">Tidak ada user yang ditemukan.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                                            <span class="text-md font-bold text-white"><?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <div class="text-xs text-blue-600 font-semibold">Anda</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full <?php 
                                    echo match($user['role']) {
                                        'super_admin' => 'bg-red-100 text-red-800',
                                        'staff' => 'bg-blue-100 text-blue-800',
                                        'pengawas' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>"><?php echo str_replace('_', ' ', ucwords($user['role'])); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800'; ?>">
                                    <?php echo $user['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?php 
                                    $createdDate = new DateTime($user['created_at']);
                                    $now = new DateTime();
                                    $interval = $now->diff($createdDate);
                                    echo $interval->days . " hari lalu";
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <div class="w-32">
                                        <form method="POST" action="toggle_user_status.php" class="inline-block">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="<?php echo $user['status'] === 'active' ? 'deactivate' : 'activate'; ?>">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md <?php echo $user['status'] === 'active' ? 'text-red-700 bg-red-100 hover:bg-red-200' : 'text-green-700 bg-green-100 hover:bg-green-200'; ?>">
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    Nonaktifkan
                                                <?php else: ?>
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Aktifkan
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="w-24">
                                        <?php if ($user['role'] !== 'super_admin'): ?>
                                        <form method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus user <?php echo htmlspecialchars($user['username']); ?> dari database?\n\nTindakan ini tidak dapat dibatalkan!');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="delete_user" value="1">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Hapus
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>

                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-500 bg-slate-100 rounded-md">
                                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                                        Akun Anda
                                    </span>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="add-user-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
            <form action="create_user.php" method="POST" class="p-0" onsubmit="return validateAddUserForm()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-slate-900">Tambah User Baru</h3>
                        <button type="button" onclick="closeAddUserModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>
                    <div class="space-y-4">
                        <div><label for="nama_lengkap" class="block text-sm font-medium text-slate-700">Nama Lengkap</label><input type="text" name="nama_lengkap" id="nama_lengkap" required class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></div>
                        <div><label for="email" class="block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" id="email" required class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" pattern=".*@baktikominfo\.id$" title="Email harus menggunakan domain @baktikominfo.id"><p class="text-xs text-gray-500 mt-1">Gunakan email resmi @baktikominfo.id</p></div>
                        <div><label for="username" class="block text-sm font-medium text-slate-700">Username</label><input type="text" name="username" id="username" required class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></div>
                        <div><label for="role" class="block text-sm font-medium text-slate-700">Role</label><select name="role" id="role" required class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><option value="staff">Staff</option><option value="pengawas">Pengawas</option><option value="super_admin">Super Admin</option></select></div>
                        <div><label for="password" class="block text-sm font-medium text-slate-700">Password</label><input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" minlength="8"><p class="text-xs text-gray-500 mt-1">Minimal 8 karakter.</p></div>
                        <div><label for="confirm_password" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label><input type="password" name="confirm_password" id="confirm_password" required class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 text-right rounded-b-lg">
                    <button type="button" onclick="closeAddUserModal()" class="mr-2 text-sm font-semibold text-slate-600 bg-slate-200 hover:bg-slate-300 px-4 py-2 rounded-md">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold">Simpan User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        });
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileButton = document.getElementById('mobile-menu-button');
            if (window.innerWidth <= 1024 && !sidebar.contains(event.target) && !mobileButton.contains(event.target)) {
                sidebar.classList.remove('mobile-open');
            }
        });

        const addUserModal = document.getElementById('add-user-modal');
        function showAddUserModal() { addUserModal.classList.remove('hidden'); }
        function closeAddUserModal() { addUserModal.classList.add('hidden'); }
        function validateAddUserForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password.length < 8) {
                alert('Password minimal harus 8 karakter.');
                return false;
            }
            if (password !== confirmPassword) {
                alert('Password dan Konfirmasi Password tidak cocok!');
                return false;
            }
            return true;
        }

        function showUserDetails(userId) {
            alert('Fungsi detail untuk user ID ' + userId + ' belum diimplementasikan.');
        }
        
        document.addEventListener('keydown', function (event) {
            if (event.key === "Escape") {
                closeAddUserModal();
            }
        });
    </script>
</body>
</html>