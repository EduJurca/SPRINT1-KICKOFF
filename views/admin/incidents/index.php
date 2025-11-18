<?php
/**
 * 📋 Vista: Listado de Incidencias (Admin)
 * Muestra tabla con todas las incidencias y permite filtrar, crear, editar, eliminar
 */

$currentPage = 'incidents';

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
                    <h2 class="text-2xl font-bold text-gray-900"><?php echo __('incident.incidents_management'); ?></h2>
                    <p class="text-sm text-gray-600 mt-1"><?php echo __('incident.create_heading'); ?></p>
                </div>
                <a href="/admin/incidents/create" class="bg-[#1565C0] hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <?php echo __('incident.button_create'); ?>
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
            <form method="GET" action="/admin/incidents" class="space-y-4">
                <!-- Barra de búsqueda principal -->
                <?php 
                $hasAdvancedFilters = !empty($filters['type']) || !empty($filters['assignee']) || !empty($filters['status']) || !empty($filters['created_date']);
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
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                            placeholder="<?php echo __('incident.placeholder_description'); ?>..."
                            <?= $hasAdvancedFilters ? 'disabled' : '' ?>
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all <?= $hasAdvancedFilters ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : '' ?>"
                        >
                    </div>
                    <button type="submit" class="bg-[#1565C0] hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <?php echo __('incident.search_button'); ?>
                    </button>
                    <?php if (!empty($_GET['search']) || !empty($filters)): ?>
                        <a href="/admin/incidents" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm hover:shadow-md flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <?php echo __('incident.clear_button'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Toggle para Filtros Avanzados -->
                <div class="border-t border-gray-300 pt-4">
                    <button type="button" id="toggleFilters" class="flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-[#1565C0] transition-colors">
                        <svg id="filterIcon" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        <span id="filterText"><?php echo __('incident.show_advanced_filters'); ?></span>
                        <?php 
                        $activeFilters = 0;
                        if (!empty($filters['type'])) $activeFilters++;
                        if (!empty($filters['assignee'])) $activeFilters++;
                        if (!empty($filters['status'])) $activeFilters++;
                        if (!empty($filters['created_date'])) $activeFilters++;
                        
                        if ($activeFilters > 0): 
                        ?>
                            <span class="ml-2 bg-[#1565C0] text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $activeFilters ?></span>
                        <?php endif; ?>
                    </button>

                    <!-- Panel de Filtros Avanzados (oculto por defecto) -->
                    <div id="advancedFilters" class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 <?= !empty($filters) ? '' : 'hidden' ?>">
                        
                        <!-- Filtro por Tipo -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?php echo __('incident.filter_type_label'); ?></label>
                            <select name="type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value=""><?php echo __('incident.filter_all'); ?></option>
                                <option value="technical" <?= ($_GET['type'] ?? '') === 'technical' ? 'selected' : '' ?>><?php echo __('incident.type_technical'); ?></option>
                                <option value="maintenance" <?= ($_GET['type'] ?? '') === 'maintenance' ? 'selected' : '' ?>><?php echo __('incident.type_maintenance'); ?></option>
                                <option value="user_complaint" <?= ($_GET['type'] ?? '') === 'user_complaint' ? 'selected' : '' ?>><?php echo __('incident.type_user_complaint'); ?></option>
                                <option value="accident" <?= ($_GET['type'] ?? '') === 'accident' ? 'selected' : '' ?>><?php echo __('incident.type_accident'); ?></option>
                                <option value="other" <?= ($_GET['type'] ?? '') === 'other' ? 'selected' : '' ?>><?php echo __('incident.type_other'); ?></option>
                            </select>
                        </div>

                        <!-- Filtro por Usuario Asignado -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?php echo __('incident.filter_assigned_to'); ?></label>
                            <select name="assignee" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value=""><?php echo __('incident.filter_all'); ?></option>
                                <?php foreach ($allUsers as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= ($_GET['assignee'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtro por Estado -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?php echo __('incident.filter_status_label'); ?></label>
                            <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value=""><?php echo __('incident.filter_all'); ?></option>
                                <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>><?php echo __('incident.status_pending'); ?></option>
                                <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>><?php echo __('incident.status_in_progress'); ?></option>
                                <option value="resolved" <?= ($_GET['status'] ?? '') === 'resolved' ? 'selected' : '' ?>><?php echo __('incident.status_resolved'); ?></option>
                            </select>
                        </div>

                        <!-- Filtro por Fecha de Creación -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?php echo __('incident.filter_created_date'); ?></label>
                                <div>
                                    <input
                                        type="date"
                                        name="created_date"
                                        value="<?= htmlspecialchars($_GET['created_date'] ?? '') ?>"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all text-sm"
                                    >
                                </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Incidencias -->
        <div class="bg-gray-100 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full table-auto divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?php echo __('form.labels.type'); ?></th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?php echo __('form.labels.description'); ?></th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?php echo __('form.labels.creator'); ?></th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?php echo __('form.labels.assignee'); ?></th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?php echo __('form.labels.creation_date'); ?></th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?php echo __('form.labels.status'); ?></th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200"><?php echo __('form.labels.actions'); ?></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($incidents)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-semibold text-gray-900 mb-1"><?php echo __('incident.no_registered_incidents'); ?></p>
                                    <p class="text-sm text-gray-500 mb-4"><?php echo __('incident.no_incidents_hint'); ?></p>
                                </div>
                                <a href="/admin/incidents/create" class="text-[#1565C0] hover:text-blue-700 font-semibold inline-flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <?php echo __('incident.create_first_cta'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($incidents as $incident): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php 
                                $typeLabels = [
                                    'technical' => __('incident.type_technical'),
                                    'maintenance' => __('incident.type_maintenance'),
                                    'user_complaint' => __('incident.type_user_complaint'),
                                    'accident' => __('incident.type_accident'),
                                    'other' => __('incident.type_other')
                                ];
                                echo $typeLabels[$incident['type']] ?? htmlspecialchars($incident['type']);
                                ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                <?php echo htmlspecialchars($incident['description']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['creator_name'] ?? __('unknown')); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['assignee_name'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['created_at']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'in_progress' => 'bg-blue-100 text-blue-800',
                                    'resolved' => 'bg-green-100 text-green-800'
                                ];
                                $statusLabels = [
                                    'pending' => __('incident.status_pending'),
                                    'in_progress' => __('incident.status_in_progress'),
                                    'resolved' => __('incident.status_resolved')
                                ];
                                $color = $statusColors[$incident['status']] ?? 'bg-gray-100 text-gray-800';
                                $label = $statusLabels[$incident['status']] ?? $incident['status'];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $color ?>">
                                    <?= $label ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                <?php if (Permissions::can('incidents.edit')): ?>
                                    <a href="/admin/incidents/<?= $incident['id'] ?>/edit" class="text-gray-500 hover:text-blue-600 transition-colors p-2 hover:bg-gray-100 rounded-lg" title="<?php echo __('actions.edit'); ?>">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                <?php if (Permissions::can('incidents.resolve') && $incident['status'] !== 'resolved'): ?>
                                    <form method="POST" action="/admin/incidents/<?= $incident['id'] ?>/resolve" class="inline js-confirm" data-confirm-message="<?php echo __('confirm_resolve_incident'); ?>">
                                        <button type="submit" class="text-gray-500 hover:text-green-600 transition-colors p-2 hover:bg-gray-100 rounded-lg" title="<?php echo __('incident.mark_as_resolved'); ?>">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if (Permissions::can('incidents.delete')): ?>
                                    <form method="POST" action="/admin/incidents/<?= $incident['id'] ?>" class="inline js-confirm" data-confirm-message="<?php echo __('confirm_delete_incident'); ?>">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="text-gray-500 hover:text-red-600 transition-colors p-2 hover:bg-gray-100 rounded-lg" title="<?php echo __('actions.delete'); ?>">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-gray-200 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '' ?><?= !empty($_GET['assignee']) ? '&assignee=' . urlencode($_GET['assignee']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?><?= !empty($_GET['created_date']) ? '&created_date=' . urlencode($_GET['created_date']) : '' ?>" 
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <?php echo __('incident.pagination_previous'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '' ?><?= !empty($_GET['assignee']) ? '&assignee=' . urlencode($_GET['assignee']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?><?= !empty($_GET['created_date']) ? '&created_date=' . urlencode($_GET['created_date']) : '' ?>" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <?php echo __('incident.pagination_next'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">
                            <?php echo __('incident.pagination_showing'); ?>
                            <span class="font-medium"><?= min(($page - 1) * $itemsPerPage + 1, $totalIncidents) ?></span>
                            -
                            <span class="font-medium"><?= min($page * $itemsPerPage, $totalIncidents) ?></span>
                            <?php echo __('incident.pagination_of'); ?>
                            <span class="font-medium"><?= $totalIncidents ?></span>
                            <?php echo __('incident.pagination_results'); ?>
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex items-center gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '' ?><?= !empty($_GET['assignee']) ? '&assignee=' . urlencode($_GET['assignee']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?><?= !empty($_GET['created_date']) ? '&created_date=' . urlencode($_GET['created_date']) : '' ?>" 
                                   class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <div class="flex items-center gap-1">
                            <?php 
                            $range = 2;
                            $start = max(1, $page - $range);
                            $end = min($totalPages, $page + $range);
                            
                            if ($start > 1):
                            ?>
                                <a href="?page=1<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '' ?><?= !empty($_GET['assignee']) ? '&assignee=' . urlencode($_GET['assignee']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?><?= !empty($_GET['created_date']) ? '&created_date=' . urlencode($_GET['created_date']) : '' ?>" 
                                   class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">1</a>
                                <?php if ($start > 2): ?>
                                    <span class="px-2 text-gray-500">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <a href="?page=<?= $i ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '' ?><?= !empty($_GET['assignee']) ? '&assignee=' . urlencode($_GET['assignee']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?><?= !empty($_GET['created_date']) ? '&created_date=' . urlencode($_GET['created_date']) : '' ?>" 
                                   class="px-3 py-1.5 text-sm font-<?= $i === $page ? 'semibold text-white bg-[#1565C0]' : 'medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50' ?> rounded-lg transition-colors">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <span class="px-2 text-gray-500">...</span>
                                <?php endif; ?>
                                <a href="?page=<?= $totalPages ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '' ?><?= !empty($_GET['assignee']) ? '&assignee=' . urlencode($_GET['assignee']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?><?= !empty($_GET['created_date']) ? '&created_date=' . urlencode($_GET['created_date']) : '' ?>" 
                                   class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <?= $totalPages ?>
                                </a>
                            <?php endif; ?>
                            </div>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '' ?><?= !empty($_GET['assignee']) ? '&assignee=' . urlencode($_GET['assignee']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?><?= !empty($_GET['created_date']) ? '&created_date=' . urlencode($_GET['created_date']) : '' ?>" 
                                   class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
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
                filterText.textContent = '<?php echo __('incident.hide_advanced_filters'); ?>';
            } else {
                filtersPanel.classList.add('hidden');
                filterIcon.style.transform = 'rotate(0deg)';
                filterText.textContent = '<?php echo __('incident.show_advanced_filters'); ?>';
            }
        });
    }
});
</script>

<?php
// Incluir footer de admin
require_once __DIR__ . '/../admin-footer.php';
?>