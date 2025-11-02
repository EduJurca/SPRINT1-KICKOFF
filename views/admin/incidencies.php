<?php
/**
 * Vista: Gestió d'Incidències
 * Panel de gestión de incidencias del sistema
 */

// La autenticación ya se verifica en AdminController::requireAdmin()

// Configuración de la vista
$title = $title ?? 'Incidències - Panel d\'Administració';
$pageTitle = $pageTitle ?? 'Gestió d\'Incidències';
$currentPage = $currentPage ?? 'incidencies';

// Incluir el header de admin
require_once __DIR__ . '/admin-header.php';
?>

<!-- Contenido principal -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Gestió d'Incidències</h2>
        <p class="text-gray-600 mt-1">Gestiona i resol les incidències reportades pels usuaris</p>
    </div>
    <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
        <i class="fas fa-plus mr-2"></i>Nova Incidència
    </button>
</div>

<!-- Estadísticas rápidas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Obertes</p>
                <p class="text-3xl font-bold text-red-600 mt-2"><?php echo $stats['open'] ?? 0; ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">En Procés</p>
                <p class="text-3xl font-bold text-yellow-600 mt-2"><?php echo $stats['in_progress'] ?? 0; ?></p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-tools text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Resoltes Avui</p>
                <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['resolved_today'] ?? 0; ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Resoltes</p>
                <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo $stats['total_resolved'] ?? 0; ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clipboard-check text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="/admin/incidencies" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cerca</label>
            <input type="text" name="search" placeholder="ID, usuari, descripció..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Estat</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Tots</option>
                <option value="open">Obert</option>
                <option value="in_progress">En Procés</option>
                <option value="resolved">Resolt</option>
                <option value="closed">Tancat</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Prioritat</label>
            <select name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Totes</option>
                <option value="high">Alta</option>
                <option value="medium">Mitjana</option>
                <option value="low">Baixa</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipus</label>
            <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Tots</option>
                <option value="vehicle">Vehicle</option>
                <option value="payment">Pagament</option>
                <option value="app">Aplicació</option>
                <option value="other">Altres</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white px-6 py-2 rounded-lg">
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Tabla de incidencias -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuari</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipus</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripció</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioritat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Accions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($incidencies ?? [])): ?>
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium">No hi ha incidències</p>
                            <p class="text-sm mt-1">Les incidències reportades apareixeran aquí</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($incidencies as $incident): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #<?php echo $incident['id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($incident['username'] ?? 'Sistema'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $typeColors = [
                                'vehicle' => 'bg-purple-100 text-purple-800',
                                'payment' => 'bg-green-100 text-green-800',
                                'app' => 'bg-blue-100 text-blue-800',
                                'other' => 'bg-gray-100 text-gray-800'
                            ];
                            $type = $incident['type'] ?? 'other';
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $typeColors[$type] ?? 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($type); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                            <?php echo htmlspecialchars($incident['description'] ?? 'N/A'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $priorityColors = [
                                'high' => 'bg-red-100 text-red-800',
                                'medium' => 'bg-yellow-100 text-yellow-800',
                                'low' => 'bg-green-100 text-green-800'
                            ];
                            $priority = $incident['priority'] ?? 'medium';
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $priorityColors[$priority] ?? 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($priority); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusColors = [
                                'open' => 'bg-red-100 text-red-800',
                                'in_progress' => 'bg-yellow-100 text-yellow-800',
                                'resolved' => 'bg-green-100 text-green-800',
                                'closed' => 'bg-gray-100 text-gray-800'
                            ];
                            $status = $incident['status'] ?? 'open';
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$status] ?? 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y H:i', strtotime($incident['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="/admin/incidencies/view?id=<?php echo $incident['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900" title="Veure detalls">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="text-green-600 hover:text-green-900" title="Resoldre">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Incluir el footer de admin
require_once __DIR__ . '/admin-footer.php';
?>
