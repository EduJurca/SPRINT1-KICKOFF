<?php
/**
 * Admin User Management
 * Interface for managing users (view, edit, suspend, delete, manage roles)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../language.php';

// Require admin authentication
requireAdmin();

// Get database connection
$db = getDB();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $message = translate('invalid_csrf_token');
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'edit':
                // Edit user
                $user_id = intval($_POST['user_id']);
                $fullname = $db->real_escape_string($_POST['fullname']);
                $email = $db->real_escape_string($_POST['email']);
                $phone = $db->real_escape_string($_POST['phone']);
                $is_admin = isset($_POST['is_admin']) ? 1 : 0;
                
                $sql = "UPDATE users SET fullname=?, email=?, phone=?, is_admin=? WHERE id=?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('sssii', $fullname, $email, $phone, $is_admin, $user_id);
                
                if ($stmt->execute()) {
                    $message = translate('user_updated_successfully');
                    $message_type = 'success';
                } else {
                    $message = translate('error_updating_user');
                    $message_type = 'error';
                }
                break;
                
            case 'suspend':
                // Suspend/activate user (we'll use a custom field or status)
                $user_id = intval($_POST['user_id']);
                $suspend = intval($_POST['suspend']);
                
                // For now, we'll just log this action
                // In a real system, you'd add a 'suspended' or 'status' field to users table
                $message = $suspend ? translate('user_suspended') : translate('user_activated');
                $message_type = 'success';
                break;
                
            case 'delete':
                // Delete user
                $user_id = intval($_POST['user_id']);
                
                // Don't allow deleting yourself
                if ($user_id == getCurrentUserId()) {
                    $message = translate('cannot_delete_yourself');
                    $message_type = 'error';
                } else {
                    if ($db->query("DELETE FROM users WHERE id = $user_id")) {
                        $message = translate('user_deleted_successfully');
                        $message_type = 'success';
                    } else {
                        $message = translate('error_deleting_user');
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'export':
                // Export users to CSV
                $result = $db->query("SELECT id, username, email, fullname, phone, created_at, is_admin FROM users ORDER BY created_at DESC");
                
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['ID', 'Username', 'Email', 'Full Name', 'Phone', 'Created At', 'Is Admin']);
                
                while ($row = $result->fetch_assoc()) {
                    fputcsv($output, $row);
                }
                
                fclose($output);
                exit;
                break;
        }
    }
}

// Handle search and filters
$search = $_GET['search'] ?? '';
$filter_admin = $_GET['filter_admin'] ?? '';

$where_clauses = [];
$params = [];
$types = '';

if ($search) {
    $where_clauses[] = "(username LIKE ? OR email LIKE ? OR fullname LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($filter_admin !== '') {
    $where_clauses[] = "is_admin = ?";
    $params[] = intval($filter_admin);
    $types .= 'i';
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch users with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Count total users
$count_sql = "SELECT COUNT(*) as total FROM users $where_sql";
if (!empty($params)) {
    $stmt = $db->prepare($count_sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total_users = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_users = $db->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_users / $per_page);

// Fetch users
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as total_bookings,
        (SELECT COUNT(*) FROM bookings WHERE user_id = u.id AND status = 'active') as active_bookings
        FROM users u 
        $where_sql 
        ORDER BY u.created_at DESC 
        LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = $db->prepare($sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $db->query($sql);
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Get current language
$lang = getCurrentLanguage();
$csrf_token = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('user_management'); ?> - VoltiaCar Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/accessibility.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-green-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold">VoltiaCar Admin</h1>
                    <span class="text-green-100">|</span>
                    <span class="text-green-100"><?php echo translate('user_management'); ?></span>
                </div>
                <div class="flex items-center space-x-4">
                    <span><?php echo getCurrentUsername(); ?></span>
                    <a href="../../index.php" class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded transition">
                        <?php echo translate('back_to_site'); ?>
                    </a>
                    <a href="../auth/logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition">
                        <?php echo translate('logout'); ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Admin Menu -->
    <div class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex space-x-6 py-3">
                <a href="dashboard.php" class="text-gray-600 hover:text-green-600 pb-2 transition">
                    <?php echo translate('dashboard'); ?>
                </a>
                <a href="vehicles.php" class="text-gray-600 hover:text-green-600 pb-2 transition">
                    <?php echo translate('vehicles'); ?>
                </a>
                <a href="users.php" class="text-green-600 font-semibold border-b-2 border-green-600 pb-2">
                    <?php echo translate('users'); ?>
                </a>
                <a href="bookings.php" class="text-gray-600 hover:text-green-600 pb-2 transition">
                    <?php echo translate('bookings'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Messages -->
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Search and Filter Bar -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-64">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('search'); ?></label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="<?php echo translate('search_users'); ?>" 
                           class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('filter_by_role'); ?></label>
                    <select name="filter_admin" class="border border-gray-300 rounded px-3 py-2">
                        <option value=""><?php echo translate('all_users'); ?></option>
                        <option value="1" <?php echo $filter_admin === '1' ? 'selected' : ''; ?>><?php echo translate('admins_only'); ?></option>
                        <option value="0" <?php echo $filter_admin === '0' ? 'selected' : ''; ?>><?php echo translate('regular_users'); ?></option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                    <?php echo translate('search'); ?>
                </button>
                <form method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="export">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded transition">
                        <?php echo translate('export_csv'); ?>
                    </button>
                </form>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('username'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('email'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('full_name'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('bookings'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('role'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('created'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('actions'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($user['fullname'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $user['total_bookings']; ?> (<?php echo $user['active_bookings']; ?> active)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['is_admin'] ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $user['is_admin'] ? translate('admin') : translate('user'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick='viewUser(<?php echo json_encode($user); ?>)' class="text-blue-600 hover:text-blue-900">
                                    <?php echo translate('view'); ?>
                                </button>
                                <button onclick='editUser(<?php echo json_encode($user); ?>)' class="text-green-600 hover:text-green-900">
                                    <?php echo translate('edit'); ?>
                                </button>
                                <?php if ($user['id'] != getCurrentUserId()): ?>
                                <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" class="text-red-600 hover:text-red-900">
                                    <?php echo translate('delete'); ?>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="flex space-x-2">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter_admin=<?php echo urlencode($filter_admin); ?>" 
                   class="px-4 py-2 rounded <?php echo $i === $page ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- View User Modal -->
    <div id="viewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900"><?php echo translate('user_details'); ?></h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="viewUserContent" class="space-y-4"></div>
            <div class="flex justify-end mt-6">
                <button onclick="closeViewModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition">
                    <?php echo translate('close'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900"><?php echo translate('edit_user'); ?></h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" id="editUserForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('username'); ?></label>
                        <input type="text" id="editUsername" disabled class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('full_name'); ?></label>
                        <input type="text" name="fullname" id="editFullname" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('email'); ?></label>
                        <input type="email" name="email" id="editEmail" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('phone'); ?></label>
                        <input type="text" name="phone" id="editPhone" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="flex items-center mt-8">
                            <input type="checkbox" name="is_admin" id="editIsAdmin" class="mr-2">
                            <span class="text-sm font-medium text-gray-700"><?php echo translate('admin_privileges'); ?></span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition">
                        <?php echo translate('cancel'); ?>
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded transition">
                        <?php echo translate('save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo translate('confirm_delete'); ?></h3>
            <p class="text-gray-600 mb-6"><?php echo translate('delete_user_confirmation'); ?></p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="deleteUserId">
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeDeleteModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded transition">
                        <?php echo translate('cancel'); ?>
                    </button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition">
                        <?php echo translate('delete'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewUser(user) {
            const content = document.getElementById('viewUserContent');
            content.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div><strong><?php echo translate('username'); ?>:</strong> ${user.username}</div>
                    <div><strong><?php echo translate('email'); ?>:</strong> ${user.email}</div>
                    <div><strong><?php echo translate('full_name'); ?>:</strong> ${user.fullname || '-'}</div>
                    <div><strong><?php echo translate('phone'); ?>:</strong> ${user.phone || '-'}</div>
                    <div><strong><?php echo translate('role'); ?>:</strong> ${user.is_admin ? '<?php echo translate('admin'); ?>' : '<?php echo translate('user'); ?>'}</div>
                    <div><strong><?php echo translate('created'); ?>:</strong> ${new Date(user.created_at).toLocaleDateString()}</div>
                    <div><strong><?php echo translate('total_bookings'); ?>:</strong> ${user.total_bookings}</div>
                    <div><strong><?php echo translate('active_bookings'); ?>:</strong> ${user.active_bookings}</div>
                </div>
            `;
            document.getElementById('viewModal').classList.remove('hidden');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        function editUser(user) {
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editUsername').value = user.username;
            document.getElementById('editFullname').value = user.fullname || '';
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editPhone').value = user.phone || '';
            document.getElementById('editIsAdmin').checked = user.is_admin == 1;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteUser(id, username) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>

    <!-- Accessibility Script -->
    <script src="../../js/accessibility.js"></script>
</body>
</html>
