<?php
/**
 * Vista: Charging Stations Management
 * Admin panel to manage charging stations
 */

// Check if user is admin
// if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
//     header('Location: /login');
//     exit;
// }

$title = 'Charging Stations - Admin Panel';
$pageTitle = 'Charging Stations';
$currentPage = 'charging-stations';

require_once __DIR__ . '/../admin-header.php';
?>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['success'])): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    <?= htmlspecialchars($_SESSION['success']) ?>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    <?= htmlspecialchars($_SESSION['error']) ?>
</div>
<?php unset($_SESSION['error']); endif; ?>

<!-- Header Actions -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">
        <i class="fas fa-charging-station"></i>
        Charging Stations Management
    </h1>
    <div class="flex gap-3">
        <a href="/charging-stations/map" 
           class="bg-blue-600 hover:bg-blue-700 text-white hover:text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
            <i class="fas fa-map-marked-alt"></i>
            View Map
        </a>
        <a href="/admin/charging-stations/create" 
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
            <i class="fas fa-plus"></i>
            Add New Station
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Stations</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $totalStations ?? 0 ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-charging-station text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Active</p>
                <p class="text-3xl font-bold text-green-600 mt-2">
                    <?= count(array_filter($stations, fn($s) => $s['status'] === 'active')) ?>
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Slots</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    <?= array_sum(array_column($stations, 'total_slots')) ?>
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-plug text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Stations Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">All Charging Stations</h2>
            <input type="text" 
                   id="searchStations" 
                   placeholder="Search by name or city..." 
                   class="border border-gray-300 rounded-lg px-4 py-2 w-64">
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slots</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Power</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($stations)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>No charging stations found. Add your first station!</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($stations as $station): ?>
                    <tr class="hover:bg-gray-50 transition">
                        
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($station['name']) ?></div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($station['operator'] ?? 'VoltiaCar') ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($station['city']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($station['address']) ?></td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium">
                                <?= $station['available_slots'] ?>/<?= $station['total_slots'] ?>
                            </span>
                            <?php if ($station['available_slots'] > 0): ?>
                                <span class="ml-2 text-green-600">●</span>
                            <?php else: ?>
                                <span class="ml-2 text-red-600">●</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900"><?= $station['power_kw'] ?> kW</td>
                        <td class="px-6 py-4">
                            <?php if ($station['status'] === 'active'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            <?php elseif ($station['status'] === 'maintenance'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Maintenance
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Out of Service
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex gap-3">
                                <a href="/admin/charging-stations/<?= $station['id'] ?>/edit" 
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium shadow-sm hover:shadow-md" 
                                   title="Edit Station">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </a>
                                <button onclick="confirmDelete(<?= $station['id'] ?>, '<?= htmlspecialchars($station['name']) ?>')" 
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium shadow-sm hover:shadow-md" 
                                        title="Delete Station">
                                    <i class="fas fa-trash"></i>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Delete Charging Station</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="deleteMessage"></p>
            </div>
            <div class="flex gap-3 justify-center mt-4">
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteMessage').textContent = 
        `Are you sure you want to delete "${name}"? This action cannot be undone.`;
    document.getElementById('deleteForm').action = `/admin/charging-stations/${id}/delete`;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Search functionality
document.getElementById('searchStations')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
