<?php
/**
 * 📋 Vista: Listado de Vehículos (Admin)
 * Muestra tabla con todos los vehículos y permite filtrar, crear, editar, eliminar
 */

// Incluir header de admin
require_once __DIR__ . '/../admin-header.php';

// Obtener mensajes de sesión
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900"><?= __('admin.vehicles.title') ?></h2>
                    <p class="text-sm text-gray-600 mt-1">Gestiona els vehicles del sistema</p>
                </div>
                <a href="/admin/vehicles/create" class="bg-[#1565C0] hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <?= __('admin.vehicles.new_button') ?>
                </a>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm" role="alert">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-semibold"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm" role="alert">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-semibold"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Búsqueda Global -->
        <div class="bg-gray-100 rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="/admin/vehicles" class="space-y-4">
                <!-- Barra de búsqueda principal -->
                <?php 
                $hasAdvancedFilters = !empty($filters['brand']) || !empty($filters['model']);
                ?>
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 <?= $hasAdvancedFilters ? 'text-gray-300' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($search ?? '') ?>"
                            placeholder="Buscar por matrícula, marca o modelo..."
                            <?= $hasAdvancedFilters ? 'disabled' : '' ?>
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all <?= $hasAdvancedFilters ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : '' ?>"
                        >
                    </div>
                    <button type="submit" class="bg-[#1565C0] hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Buscar
                    </button>
                    <?php if (!empty($search) || !empty($filters)): ?>
                        <a href="/admin/vehicles" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm hover:shadow-md flex items-center gap-2">
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
                        if (!empty($filters['brand'])) $activeFilters++;
                        if (!empty($filters['model'])) $activeFilters++;
                        if (!empty($filters['status'])) $activeFilters++;
                        if (isset($filters['is_accessible']) && $filters['is_accessible'] !== '') $activeFilters++;
                        if (!empty($filters['min_battery'])) $activeFilters++;
                        
                        if ($activeFilters > 0): 
                        ?>
                            <span class="ml-2 bg-[#1565C0] text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $activeFilters ?></span>
                        <?php endif; ?>
                    </button>

                    <!-- Panel de Filtros Avanzados (oculto por defecto) -->
                    <div id="advancedFilters" class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 <?= !empty($filters) ? '' : 'hidden' ?>">
                        
                        <!-- Filtro por Marca -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Marca</label>
                            <input 
                                type="text" 
                                name="brand" 
                                value="<?= htmlspecialchars($filters['brand'] ?? '') ?>"
                                placeholder="Ej: Tesla, BMW..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                            >
                        </div>

                        <!-- Filtro por Modelo -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Modelo</label>
                            <input 
                                type="text" 
                                name="model" 
                                value="<?= htmlspecialchars($filters['model'] ?? '') ?>"
                                placeholder="Ej: Model 3, Q3..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                            >
                        </div>

                        <!-- Filtro por Estado -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                            <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value="">Todos</option>
                                <option value="available" <?= ($filters['status'] ?? '') === 'available' ? 'selected' : '' ?>>Disponible</option>
                                <option value="in_use" <?= ($filters['status'] ?? '') === 'in_use' ? 'selected' : '' ?>>En uso</option>
                                <option value="charging" <?= ($filters['status'] ?? '') === 'charging' ? 'selected' : '' ?>>Cargando</option>
                                <option value="maintenance" <?= ($filters['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Mantenimiento</option>
                                <option value="reserved" <?= ($filters['status'] ?? '') === 'reserved' ? 'selected' : '' ?>>Reservado</option>
                            </select>
                        </div>

                        <!-- Filtro por Accesibilidad -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Accesibilidad</label>
                            <select name="is_accessible" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value="">Todos</option>
                                <option value="1" <?= (isset($filters['is_accessible']) && $filters['is_accessible'] == '1') ? 'selected' : '' ?>>Solo accesibles</option>
                                <option value="0" <?= (isset($filters['is_accessible']) && $filters['is_accessible'] == '0') ? 'selected' : '' ?>>Solo no accesibles</option>
                            </select>
                        </div>

                        <!-- Filtro por Batería Mínima -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Batería Mínima (%)</label>
                            <input 
                                type="number" 
                                name="min_battery" 
                                value="<?= htmlspecialchars($filters['min_battery'] ?? '') ?>"
                                min="0" 
                                max="100"
                                placeholder="Ej: 50"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                            >
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de vehículos -->
        <div class="bg-gray-100 rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?= __('admin.vehicles.table.license_plate') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?= __('admin.vehicles.table.vehicle') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?= __('admin.vehicles.table.status') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?= __('admin.vehicles.table.battery') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?= __('admin.vehicles.table.price_per_min') ?></th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?= __('admin.vehicles.table.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($vehicles)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-lg font-semibold text-gray-900 mb-1"><?= __('admin.vehicles.no_vehicles') ?></p>
                                            <p class="text-sm text-gray-500 mb-4">Comença creant el primer vehicle</p>
                                        </div>
                                        <a href="/admin/vehicles/create" class="text-[#1565C0] hover:text-blue-700 font-semibold inline-flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <?= __('admin.vehicles.create_first') ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        <?= htmlspecialchars($vehicle['license_plate'] ?? $vehicle['plate'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($vehicle['brand']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($vehicle['model']) ?> (<?= $vehicle['year'] ?>)</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusColors = [
                                            'available' => 'bg-green-100 text-green-800',
                                            'in_use' => 'bg-blue-100 text-blue-800',
                                            'charging' => 'bg-yellow-100 text-yellow-800',
                                            'maintenance' => 'bg-red-100 text-red-800',
                                            'reserved' => 'bg-purple-100 text-purple-800'
                                        ];
                                        $statusNames = [
                                            'available' => 'Disponible',
                                            'in_use' => 'En uso',
                                            'charging' => 'Cargando',
                                            'maintenance' => 'Mantenimiento',
                                            'reserved' => 'Reservado'
                                        ];
                                        $color = $statusColors[$vehicle['status']] ?? 'bg-gray-100 text-gray-800';
                                        $name = $statusNames[$vehicle['status']] ?? $vehicle['status'];
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $color ?>">
                                            <?= $name ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-24 bg-gray-200 rounded-full h-2.5">
                                                <div class="<?= $vehicle['battery_level'] > 50 ? 'bg-green-500' : ($vehicle['battery_level'] > 20 ? 'bg-yellow-500' : 'bg-red-500') ?> h-2.5 rounded-full transition-all" style="width: <?= $vehicle['battery_level'] ?>%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700"><?= $vehicle['battery_level'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= number_format($vehicle['price_per_minute'], 2) ?>€
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="/admin/vehicles/<?= $vehicle['id'] ?>" class="text-gray-500 hover:text-[#1565C0] transition-colors p-2 hover:bg-gray-100 rounded-lg" title="<?= __('admin.vehicles.buttons.view') ?>">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="/admin/vehicles/<?= $vehicle['id'] ?>/edit" class="text-gray-500 hover:text-blue-600 transition-colors p-2 hover:bg-gray-100 rounded-lg" title="<?= __('admin.vehicles.buttons.edit') ?>">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <button onclick="confirmDelete(<?= $vehicle['id'] ?>, '<?= htmlspecialchars($vehicle['license_plate'] ?? $vehicle['plate']) ?>')" 
                                                class="text-gray-500 hover:text-red-600 transition-colors p-2 hover:bg-gray-100 rounded-lg" title="<?= __('admin.vehicles.buttons.delete') ?>">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($vehicles)): ?>
                <div class="bg-gray-200 px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-sm font-medium text-gray-700">
                            <?php
                            $showing = count($vehicles);
                            $startItem = (($currentPage - 1) * $perPage) + 1;
                            $endItem = min($startItem + $showing - 1, $totalVehicles);
                            ?>
                            Mostrando <?= $startItem ?> - <?= $endItem ?> de <?= $totalVehicles ?> vehículos
                        </p>
                        
                        <!-- Paginación dinámica -->
                        <?php
                        // Construir URL base con parámetros de búsqueda y filtros
                        $urlParams = [];
                        if (!empty($search)) $urlParams[] = 'search=' . urlencode($search);
                        if (!empty($filters['brand'])) $urlParams[] = 'brand=' . urlencode($filters['brand']);
                        if (!empty($filters['model'])) $urlParams[] = 'model=' . urlencode($filters['model']);
                        if (!empty($filters['status'])) $urlParams[] = 'status=' . urlencode($filters['status']);
                        if (isset($filters['is_accessible']) && $filters['is_accessible'] !== '') $urlParams[] = 'is_accessible=' . urlencode($filters['is_accessible']);
                        if (!empty($filters['min_battery'])) $urlParams[] = 'min_battery=' . urlencode($filters['min_battery']);
                        
                        // Construir la URL base sin el parámetro page
                        $baseUrl = '/admin/vehicles';
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
                            <a href="<?= $baseUrl . $separator ?>page=<?= max(1, $currentPage - 1) ?>" 
                               class="px-3 py-1.5 text-sm font-medium <?= $currentPage <= 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?> bg-white border border-gray-300 rounded-lg transition-colors <?= $currentPage <= 1 ? 'pointer-events-none' : '' ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                            
                            <div class="flex items-center gap-1">
                                <?php
                                // Lógica para mostrar números de página
                                $range = 2; // Páginas a mostrar a cada lado de la actual
                                $start = max(1, $currentPage - $range);
                                $end = min($totalPages, $currentPage + $range);
                                
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
                                       class="px-3 py-1.5 text-sm font-<?= $i === $currentPage ? 'semibold text-white bg-[#1565C0]' : 'medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50' ?> rounded-lg transition-colors">
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
                            <a href="<?= $baseUrl . $separator ?>page=<?= min($totalPages, $currentPage + 1) ?>" 
                               class="px-3 py-1.5 text-sm font-medium <?= $currentPage >= $totalPages ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?> bg-white border border-gray-300 rounded-lg transition-colors <?= $currentPage >= $totalPages ? 'pointer-events-none' : '' ?>">
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
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="deleteForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
const confirmTemplateList = <?= json_encode(__('admin.vehicles.confirm_delete')) ?>;
function confirmDelete(id, plate) {
    const msg = confirmTemplateList.replace(':plate', plate);
    if (confirm(msg)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/vehicles/${id}`;
        form.submit();
    }
}

// Toggle para filtros avanzados
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleFilters');
    const filtersPanel = document.getElementById('advancedFilters');
    const filterIcon = document.getElementById('filterIcon');
    const filterText = document.getElementById('filterText');
    
    if (toggleButton && filtersPanel) {
        toggleButton.addEventListener('click', function() {
            const isHidden = filtersPanel.classList.contains('hidden');
            
            if (isHidden) {
                filtersPanel.classList.remove('hidden');
                filterIcon.style.transform = 'rotate(180deg)';
                filterText.textContent = 'Ocultar filtros avanzados';
            } else {
                filtersPanel.classList.add('hidden');
                filterIcon.style.transform = 'rotate(0deg)';
                filterText.textContent = 'Mostrar filtros avanzados';
            }
        });
    }
});
</script>

<?php
// Incluir footer de admin
require_once __DIR__ . '/../admin-footer.php';
?>
