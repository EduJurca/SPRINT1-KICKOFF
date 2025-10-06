<?php
/**
 * Admin Booking Management
 * Interface for managing bookings (view, filter, approve, cancel, generate reports)
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
            case 'update_status':
                // Update booking status
                $booking_id = intval($_POST['booking_id']);
                $new_status = $db->real_escape_string($_POST['status']);
                
                $sql = "UPDATE bookings SET status=?, updated_at=NOW() WHERE id=?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('si', $new_status, $booking_id);
                
                if ($stmt->execute()) {
                    $message = translate('booking_updated_successfully');
                    $message_type = 'success';
                } else {
                    $message = translate('error_updating_booking');
                    $message_type = 'error';
                }
                break;
                
            case 'export':
                // Export bookings to CSV
                $status_filter = $_POST['export_status'] ?? '';
                $date_from = $_POST['export_date_from'] ?? '';
                $date_to = $_POST['export_date_to'] ?? '';
                
                $where_clauses = [];
                if ($status_filter) {
                    $where_clauses[] = "b.status = '$status_filter'";
                }
                if ($date_from) {
                    $where_clauses[] = "b.start_datetime >= '$date_from'";
                }
                if ($date_to) {
                    $where_clauses[] = "b.end_datetime <= '$date_to'";
                }
                
                $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
                
                $sql = "SELECT b.id, b.start_datetime, b.end_datetime, b.total_cost, b.status, b.created_at,
                        u.username, u.email, v.brand, v.model, v.plate
                        FROM bookings b
                        JOIN users u ON b.user_id = u.id
                        JOIN vehicles v ON b.vehicle_id = v.id
                        $where_sql
                        ORDER BY b.created_at DESC";
                
                $result = $db->query($sql);
                
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="bookings_export_' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Booking ID', 'Username', 'Email', 'Vehicle', 'Plate', 'Start', 'End', 'Cost', 'Status', 'Created']);
                
                while ($row = $result->fetch_assoc()) {
                    fputcsv($output, [
                        $row['id'],
                        $row['username'],
                        $row['email'],
                        $row['brand'] . ' ' . $row['model'],
                        $row['plate'],
                        $row['start_datetime'],
                        $row['end_datetime'],
                        $row['total_cost'],
                        $row['status'],
                        $row['created_at']
                    ]);
                }
                
                fclose($output);
                exit;
                break;
        }
    }
}

// Handle filters
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$user_search = $_GET['user_search'] ?? '';
$vehicle_search = $_GET['vehicle_search'] ?? '';

$where_clauses = [];
$params = [];
$types = '';

if ($status_filter) {
    $where_clauses[] = "b.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($date_from) {
    $where_clauses[] = "b.start_datetime >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if ($date_to) {
    $where_clauses[] = "b.end_datetime <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if ($user_search) {
    $where_clauses[] = "(u.username LIKE ? OR u.email LIKE ?)";
    $search_param = "%$user_search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($vehicle_search) {
    $where_clauses[] = "(v.plate LIKE ? OR v.brand LIKE ? OR v.model LIKE ?)";
    $search_param = "%$vehicle_search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch bookings with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Count total bookings
$count_sql = "SELECT COUNT(*) as total 
              FROM bookings b
              JOIN users u ON b.user_id = u.id
              JOIN vehicles v ON b.vehicle_id = v.id
              $where_sql";

if (!empty($params)) {
    $stmt = $db->prepare($count_sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total_bookings = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_bookings = $db->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_bookings / $per_page);

// Fetch bookings
$sql = "SELECT b.*, u.username, u.email, v.brand, v.model, v.plate
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN vehicles v ON b.vehicle_id = v.id
        $where_sql
        ORDER BY b.created_at DESC
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

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// Get statistics
$stats = [];
$stats['total'] = $db->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$stats['pending'] = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status='pending'")->fetch_assoc()['count'];
$stats['confirmed'] = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status='confirmed'")->fetch_assoc()['count'];
$stats['active'] = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status='active'")->fetch_assoc()['count'];
$stats['completed'] = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status='completed'")->fetch_assoc()['count'];
$stats['cancelled'] = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status='cancelled'")->fetch_assoc()['count'];

// Get current language
$lang = getCurrentLanguage();
$csrf_token = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('booking_management'); ?> - VoltiaCar Admin</title>
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
                    <span class="text-green-100"><?php echo translate('booking_management'); ?></span>
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
                <a href="users.php" class="text-gray-600 hover:text-green-600 pb-2 transition">
                    <?php echo translate('users'); ?>
                </a>
                <a href="bookings.php" class="text-green-600 font-semibold border-b-2 border-green-600 pb-2">
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm"><?php echo translate('total'); ?></p>
                <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total']; ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm"><?php echo translate('pending'); ?></p>
                <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm"><?php echo translate('confirmed'); ?></p>
                <p class="text-2xl font-bold text-blue-600"><?php echo $stats['confirmed']; ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm"><?php echo translate('active'); ?></p>
                <p class="text-2xl font-bold text-green-600"><?php echo $stats['active']; ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm"><?php echo translate('completed'); ?></p>
                <p class="text-2xl font-bold text-gray-600"><?php echo $stats['completed']; ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm"><?php echo translate('cancelled'); ?></p>
                <p class="text-2xl font-bold text-red-600"><?php echo $stats['cancelled']; ?></p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('status'); ?></label>
                    <select name="status" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value=""><?php echo translate('all_statuses'); ?></option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>><?php echo translate('pending'); ?></option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>><?php echo translate('confirmed'); ?></option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>><?php echo translate('active'); ?></option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>><?php echo translate('completed'); ?></option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>><?php echo translate('cancelled'); ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('date_from'); ?></label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('date_to'); ?></label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('user'); ?></label>
                    <input type="text" name="user_search" value="<?php echo htmlspecialchars($user_search); ?>" placeholder="<?php echo translate('search_user'); ?>" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('vehicle'); ?></label>
                    <input type="text" name="vehicle_search" value="<?php echo htmlspecialchars($vehicle_search); ?>" placeholder="<?php echo translate('search_vehicle'); ?>" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="md:col-span-2 lg:col-span-5 flex gap-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                        <?php echo translate('filter'); ?>
                    </button>
                    <a href="bookings.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition inline-block">
                        <?php echo translate('clear_filters'); ?>
                    </a>
                    <button type="button" onclick="showExportModal()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded transition">
                        <?php echo translate('export_report'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('booking_id'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('user'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('vehicle'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('start_date'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('end_date'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('cost'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('status'); ?>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo translate('actions'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($bookings as $booking): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?php echo $booking['id']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($booking['username']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model'] . ' (' . $booking['plate'] . ')'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($booking['start_datetime'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($booking['end_datetime'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                €<?php echo number_format($booking['total_cost'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    echo match($booking['status']) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                        'active' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    ?>">
                                    <?php echo translate('status_' . $booking['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick='viewBooking(<?php echo json_encode($booking); ?>)' class="text-blue-600 hover:text-blue-900">
                                    <?php echo translate('view'); ?>
                                </button>
                                <button onclick='editBookingStatus(<?php echo $booking['id']; ?>, "<?php echo $booking['status']; ?>")' class="text-green-600 hover:text-green-900">
                                    <?php echo translate('edit_status'); ?>
                                </button>
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
                <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&user_search=<?php echo urlencode($user_search); ?>&vehicle_search=<?php echo urlencode($vehicle_search); ?>" 
                   class="px-4 py-2 rounded <?php echo $i === $page ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- View Booking Modal -->
    <div id="viewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900"><?php echo translate('booking_details'); ?></h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="viewBookingContent" class="space-y-4"></div>
            <div class="flex justify-end mt-6">
                <button onclick="closeViewModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition">
                    <?php echo translate('close'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Status Modal -->
    <div id="editStatusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo translate('update_booking_status'); ?></h3>
            <form method="POST" id="editStatusForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" id="editBookingId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('status'); ?></label>
                    <select name="status" id="editStatus" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="pending"><?php echo translate('pending'); ?></option>
                        <option value="confirmed"><?php echo translate('confirmed'); ?></option>
                        <option value="active"><?php echo translate('active'); ?></option>
                        <option value="completed"><?php echo translate('completed'); ?></option>
                        <option value="cancelled"><?php echo translate('cancelled'); ?></option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeEditStatusModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded transition">
                        <?php echo translate('cancel'); ?>
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">
                        <?php echo translate('update'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Modal -->
    <div id="exportModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo translate('export_bookings'); ?></h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="export">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('status'); ?></label>
                        <select name="export_status" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value=""><?php echo translate('all_statuses'); ?></option>
                            <option value="pending"><?php echo translate('pending'); ?></option>
                            <option value="confirmed"><?php echo translate('confirmed'); ?></option>
                            <option value="active"><?php echo translate('active'); ?></option>
                            <option value="completed"><?php echo translate('completed'); ?></option>
                            <option value="cancelled"><?php echo translate('cancelled'); ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('date_from'); ?></label>
                        <input type="date" name="export_date_from" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo translate('date_to'); ?></label>
                        <input type="date" name="export_date_to" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeExportModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded transition">
                        <?php echo translate('cancel'); ?>
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">
                        <?php echo translate('export'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewBooking(booking) {
            const content = document.getElementById('viewBookingContent');
            content.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div><strong><?php echo translate('booking_id'); ?>:</strong> #${booking.id}</div>
                    <div><strong><?php echo translate('status'); ?>:</strong> ${booking.status}</div>
                    <div><strong><?php echo translate('user'); ?>:</strong> ${booking.username}</div>
                    <div><strong><?php echo translate('email'); ?>:</strong> ${booking.email}</div>
                    <div class="col-span-2"><strong><?php echo translate('vehicle'); ?>:</strong> ${booking.brand} ${booking.model} (${booking.plate})</div>
                    <div><strong><?php echo translate('start_date'); ?>:</strong> ${new Date(booking.start_datetime).toLocaleString()}</div>
                    <div><strong><?php echo translate('end_date'); ?>:</strong> ${new Date(booking.end_datetime).toLocaleString()}</div>
                    <div><strong><?php echo translate('total_cost'); ?>:</strong> €${parseFloat(booking.total_cost).toFixed(2)}</div>
                    <div><strong><?php echo translate('created'); ?>:</strong> ${new Date(booking.created_at).toLocaleString()}</div>
                </div>
            `;
            document.getElementById('viewModal').classList.remove('hidden');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        function editBookingStatus(id, currentStatus) {
            document.getElementById('editBookingId').value = id;
            document.getElementById('editStatus').value = currentStatus;
            document.getElementById('editStatusModal').classList.remove('hidden');
        }

        function closeEditStatusModal() {
            document.getElementById('editStatusModal').classList.add('hidden');
        }

        function showExportModal() {
            document.getElementById('exportModal').classList.remove('hidden');
        }

        function closeExportModal() {
            document.getElementById('exportModal').classList.add('hidden');
        }
    </script>

    <!-- Accessibility Script -->
    <script src="../../js/accessibility.js"></script>
</body>
</html>
