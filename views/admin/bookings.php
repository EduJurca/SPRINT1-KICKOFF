<?php
/**
 * Vista: Gestió de Reserves
 * Panel de administración de reservas del sistema
 */

// La autenticación ya se verifica en AdminController::requireAdmin()

// Configuración de la vista
$title = $title ?? 'Reserves - Panel d\'Administració';
$pageTitle = $pageTitle ?? 'Gestió de Reserves';
$currentPage = $currentPage ?? 'bookings';

// Incluir el header de admin
require_once __DIR__ . '/admin-header.php';
?>

<!-- Contenido principal -->
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Gestió de Reserves</h2>
    <p class="text-gray-600 mt-1">Administra les reserves i usos dels vehicles</p>
</div>

<!-- Estadísticas rápidas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Reserves Actives</p>
                <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo $stats['active_bookings'] ?? 0; ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Avui</p>
                <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['today_bookings'] ?? 0; ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Pendents</p>
                <p class="text-3xl font-bold text-yellow-600 mt-2"><?php echo $stats['pending_bookings'] ?? 0; ?></p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Completades</p>
                <p class="text-3xl font-bold text-gray-600 mt-2"><?php echo $stats['completed_bookings'] ?? 0; ?></p>
            </div>
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-gray-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="/admin/bookings" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cerca</label>
            <input type="text" name="search" placeholder="Usuari, vehicle..." 
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Estat</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Tots</option>
                <option value="active">Activa</option>
                <option value="pending">Pendent</option>
                <option value="completed">Completada</option>
                <option value="cancelled">Cancel·lada</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
            <input type="date" name="date" value="<?php echo $_GET['date'] ?? ''; ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white px-6 py-2 rounded-lg">
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Tabla de reserves -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuari</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inici</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Final</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durada</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Accions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($bookings ?? [])): ?>
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-calendar text-5xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium">No hi ha reserves</p>
                            <p class="text-sm mt-1">Les reserves apareixeran aquí quan els usuaris facin reserves</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #<?php echo $booking['id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($booking['username'] ?? 'N/A'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($booking['vehicle_plate'] ?? 'N/A'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y H:i', strtotime($booking['start_time'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $booking['end_time'] ? date('d/m/Y H:i', strtotime($booking['end_time'])) : 'En curs'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php 
                            if ($booking['end_time']) {
                                $duration = (strtotime($booking['end_time']) - strtotime($booking['start_time'])) / 60;
                                echo round($duration) . ' min';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $status = $booking['status'] ?? 'pending';
                            $statusColors = [
                                'active' => 'bg-blue-100 text-blue-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$status] ?? 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="/admin/bookings/view?id=<?php echo $booking['id']; ?>" 
                               class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye"></i>
                            </a>
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
