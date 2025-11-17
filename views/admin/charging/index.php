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

// Inicializar variables si no existen
$search = $search ?? '';
$filters = $filters ?? [];
$stations = $stations ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalStations = $totalStations ?? 0;
$perPage = $perPage ?? 10;
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
           class="bg-green-600 hover:bg-green-700 text-white hover:text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
            <i class="fas fa-map-marked-alt"></i>
            View Map
        </a>
        <a href="/admin/charging-stations/create" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
            <i class="fas fa-plus"></i>
            Add New Station
        </a>
    </div>
</div>

<!-- Búsqueda Global y Filtros Avanzados -->
<div class="bg-gray-100 rounded-lg shadow-md p-6 mb-6">
    <form method="GET" action="/admin/charging-stations" class="space-y-4">
        <!-- Barra de búsqueda principal -->
        <div class="flex gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="<?= htmlspecialchars($search ?? '') ?>"
                    placeholder="Buscar por nombre, ciudad o calle..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                >
            </div>
            <button type="submit" class="bg-[#1565C0] hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Buscar
            </button>
            <?php if (!empty($search) || !empty($filters)): ?>
                <a href="/admin/charging-stations" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm hover:shadow-md flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Limpiar
                </a>
            <?php endif; ?>
        </div>

        <!-- Toggle para Filtros Avanzados -->
        <div class="border-t border-gray-300 pt-4">
            <button type="button" id="toggleFilters" class="flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-[#1565C0] transition-colors">
                <svg id="filterIcon" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                <span id="filterText">Mostrar filtros avanzados</span>
                <?php 
                $activeFilters = 0;
                if (!empty($filters['city'])) $activeFilters++;
                if (!empty($filters['status'])) $activeFilters++;
                if (!empty($filters['availability'])) $activeFilters++;
                if (isset($filters['min_power']) && $filters['min_power'] !== '') $activeFilters++;
                
                if ($activeFilters > 0): 
                ?>
                    <span class="ml-2 bg-[#1565C0] text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $activeFilters ?></span>
                <?php endif; ?>
            </button>

            <!-- Panel de Filtros Avanzados (oculto por defecto) -->
            <div id="advancedFilters" class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 hidden">
                
                <!-- Filtro por Ciudad -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ciudad</label>
                    <input 
                        type="text" 
                        name="city" 
                        value="<?= htmlspecialchars($filters['city'] ?? '') ?>"
                        placeholder="Ej: Barcelona, Madrid..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                    >
                </div>

                <!-- Filtro por Estado -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                        <option value="">Todos</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Activa</option>
                        <option value="maintenance" <?= ($filters['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Mantenimiento</option>
                        <option value="out_of_service" <?= ($filters['status'] ?? '') === 'out_of_service' ? 'selected' : '' ?>>Fuera de servicio</option>
                    </select>
                </div>

                <!-- Filtro por Slots Disponibles -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Slots disponibles</label>
                    <select name="availability" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                        <option value="">Todos</option>
                        <option value="available" <?= ($filters['availability'] ?? '') === 'available' ? 'selected' : '' ?>>Disponibles</option>
                        <option value="full" <?= ($filters['availability'] ?? '') === 'full' ? 'selected' : '' ?>>Llenos</option>
                    </select>
                </div>

                <!-- Filtro por Potencia Mínima -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Potencia mínima (kW)</label>
                    <input 
                        type="number" 
                        name="min_power" 
                        value="<?= htmlspecialchars($filters['min_power'] ?? '') ?>"
                        min="0" 
                        placeholder="Ej: 50"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                    >
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Stations Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6 border-b">
        <h2 class="text-lg font-semibold text-gray-900">All Charging Stations</h2>
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
    
    <?php if (!empty($stations)): ?>
        <div class="bg-gray-200 px-6 py-4 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm font-medium text-gray-700">
                    <?php
                    $showing = count($stations);
                    $startItem = (($page - 1) * $perPage) + 1;
                    $endItem = min($startItem + $showing - 1, $totalStations);
                    ?>
                    Mostrando <?= $startItem ?> - <?= $endItem ?> de <?= $totalStations ?> estaciones
                </p>
                
                <!-- Paginación dinámica -->
                <?php
                // Construir URL base con parámetros de búsqueda y filtros
                $urlParams = [];
                if (!empty($search)) $urlParams[] = 'search=' . urlencode($search);
                if (!empty($filters['city'])) $urlParams[] = 'city=' . urlencode($filters['city']);
                if (!empty($filters['status'])) $urlParams[] = 'status=' . urlencode($filters['status']);
                if (!empty($filters['availability'])) $urlParams[] = 'availability=' . urlencode($filters['availability']);
                if (isset($filters['min_power']) && $filters['min_power'] !== '') $urlParams[] = 'min_power=' . urlencode($filters['min_power']);
                
                // Construir la URL base sin el parámetro page
                $baseUrl = '/admin/charging-stations';
                if (!empty($urlParams)) {
                    $baseUrl .= '?' . implode('&', $urlParams);
                    $separator = '&';
                } else {
                    $separator = '?';
                }
                
                // Solo mostrar paginación si hay más de 1 página
                if ($totalPages > 1):
                ?>
                <nav class="flex items-center gap-2" aria-label="Pagination">
                    <!-- Botón Anterior -->
                    <a href="<?= $baseUrl . $separator ?>page=<?= max(1, $page - 1) ?>" 
                       class="px-3 py-1.5 text-sm font-medium <?= $page <= 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?> bg-white border border-gray-300 rounded-lg transition-colors <?= $page <= 1 ? 'pointer-events-none' : '' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    
                    <div class="flex items-center gap-1">
                        <?php
                        // Lógica para mostrar números de página
                        $range = 2; // Páginas a mostrar a cada lado de la actual
                        $start = max(1, $page - $range);
                        $end = min($totalPages, $page + $range);
                        
                        // Mostrar primera página si no está en el rango
                        if ($start > 1):
                        ?>
                            <a href="<?= $baseUrl . $separator ?>page=1" 
                               class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">1</a>
                            <?php if ($start > 2): ?>
                                <span class="px-2 text-gray-500">...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Páginas del rango -->
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <a href="<?= $baseUrl . $separator ?>page=<?= $i ?>" 
                               class="px-3 py-1.5 text-sm font-<?= $i === $page ? 'semibold text-white bg-[#1565C0]' : 'medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50' ?> rounded-lg transition-colors">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <!-- Mostrar última página si no está en el rango -->
                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?>
                                <span class="px-2 text-gray-500">...</span>
                            <?php endif; ?>
                            <a href="<?= $baseUrl . $separator ?>page=<?= $totalPages ?>" 
                               class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <?= $totalPages ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Botón Siguiente -->
                    <a href="<?= $baseUrl . $separator ?>page=<?= min($totalPages, $page + 1) ?>" 
                       class="px-3 py-1.5 text-sm font-medium <?= $page >= $totalPages ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?> bg-white border border-gray-300 rounded-lg transition-colors <?= $page >= $totalPages ? 'pointer-events-none' : '' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
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

// Toggle para filtros avanzados
(function() {
    function initToggle() {
        const toggleButton = document.getElementById('toggleFilters');
        const filtersPanel = document.getElementById('advancedFilters');
        const filterIcon = document.getElementById('filterIcon');
        const filterText = document.getElementById('filterText');
        
        if (!toggleButton || !filtersPanel) {
            console.warn('Toggle elements not found');
            return;
        }
        
        // Mostrar automáticamente si hay filtros activos
        const activeFilters = <?= $activeFilters ?>;
        if (activeFilters > 0) {
            filtersPanel.classList.remove('hidden');
            if (filterIcon) filterIcon.style.transform = 'rotate(180deg)';
            if (filterText) filterText.textContent = 'Ocultar filtros avanzados';
        }
        
        toggleButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isHidden = filtersPanel.classList.contains('hidden');
            
            if (isHidden) {
                filtersPanel.classList.remove('hidden');
                if (filterIcon) filterIcon.style.transform = 'rotate(180deg)';
                if (filterText) filterText.textContent = 'Ocultar filtros avanzados';
            } else {
                filtersPanel.classList.add('hidden');
                if (filterIcon) filterIcon.style.transform = 'rotate(0deg)';
                if (filterText) filterText.textContent = 'Mostrar filtros avanzados';
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initToggle);
    } else {
        initToggle();
    }
})();
</script>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
