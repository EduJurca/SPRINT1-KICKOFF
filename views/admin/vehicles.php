<?php
/**
 * Vista: Gestió de Vehicles
 * Panel de administración de vehículos del sistema
 */

// La autenticación ya se verifica en AdminController::requireAdmin()

// Configuración de la vista
$title = $title ?? 'Vehicles - Panel d\'Administració';
$pageTitle = $pageTitle ?? 'Gestió de Vehicles';
$currentPage = $currentPage ?? 'vehicles';

// Incluir el header de admin
require_once __DIR__ . '/admin-header.php';
?>

<!-- Contenido principal -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Gestió de Vehicles</h2>
        <p class="text-gray-600 mt-1">Administra els vehicles del sistema</p>
    </div>
    <a href="/admin/vehicles/create" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
        <i class="fas fa-plus mr-2"></i>Afegir Vehicle
    </a>
</div>

<!-- Filtros y búsqueda -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="/admin/vehicles" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cerca</label>
            <input type="text" name="search" placeholder="Matrícula, marca, model..." 
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Estat</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Tots</option>
                <option value="available" <?php echo ($_GET['status'] ?? '') === 'available' ? 'selected' : ''; ?>>Disponible</option>
                <option value="in_use" <?php echo ($_GET['status'] ?? '') === 'in_use' ? 'selected' : ''; ?>>En ús</option>
                <option value="maintenance" <?php echo ($_GET['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Manteniment</option>
                <option value="unavailable" <?php echo ($_GET['status'] ?? '') === 'unavailable' ? 'selected' : ''; ?>>No disponible</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipus</label>
            <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Tots</option>
                <option value="car" <?php echo ($_GET['type'] ?? '') === 'car' ? 'selected' : ''; ?>>Cotxe</option>
                <option value="scooter" <?php echo ($_GET['type'] ?? '') === 'scooter' ? 'selected' : ''; ?>>Patinet</option>
                <option value="bike" <?php echo ($_GET['type'] ?? '') === 'bike' ? 'selected' : ''; ?>>Bicicleta</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white px-6 py-2 rounded-lg transition-colors">
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Tabla de vehículos -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Vehicle
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Matrícula
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipus
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estat
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Bateria
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ubicació
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Accions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($vehicles ?? [])): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-car text-5xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium">No hi ha vehicles</p>
                            <p class="text-sm mt-1">Comença afegint el primer vehicle al sistema</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <?php if (!empty($vehicle['image_url'])): ?>
                                        <img class="h-12 w-12 rounded object-cover" 
                                             src="<?php echo htmlspecialchars($vehicle['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>">
                                    <?php else: ?>
                                        <div class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-car text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Any: <?php echo htmlspecialchars($vehicle['year'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono font-semibold text-gray-900">
                                <?php echo htmlspecialchars($vehicle['plate']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                <?php 
                                $types = ['car' => 'Cotxe', 'scooter' => 'Patinet', 'bike' => 'Bicicleta'];
                                echo $types[$vehicle['type']] ?? $vehicle['type']; 
                                ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusColors = [
                                'available' => 'bg-green-100 text-green-800',
                                'in_use' => 'bg-blue-100 text-blue-800',
                                'maintenance' => 'bg-yellow-100 text-yellow-800',
                                'unavailable' => 'bg-red-100 text-red-800'
                            ];
                            $statusNames = [
                                'available' => 'Disponible',
                                'in_use' => 'En ús',
                                'maintenance' => 'Manteniment',
                                'unavailable' => 'No disponible'
                            ];
                            $status = $vehicle['status'] ?? 'available';
                            $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $colorClass; ?>">
                                <?php echo $statusNames[$status] ?? $status; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-green-500 h-2 rounded-full" 
                                         style="width: <?php echo $vehicle['battery_level'] ?? 0; ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-700"><?php echo $vehicle['battery_level'] ?? 0; ?>%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <?php echo number_format($vehicle['latitude'] ?? 0, 5) . ', ' . number_format($vehicle['longitude'] ?? 0, 5); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="/admin/vehicles/edit?id=<?php echo $vehicle['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/admin/vehicles/view?id=<?php echo $vehicle['id']; ?>" 
                                   class="text-gray-600 hover:text-gray-900" title="Veure detalls">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)" 
                                        class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación (si hay vehículos) -->
<?php if (!empty($vehicles) && isset($totalPages) && $totalPages > 1): ?>
    <div class="bg-white rounded-lg shadow mt-6 p-4">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Mostrant <?php echo count($vehicles); ?> de <?php echo $totalVehicles ?? 0; ?> vehicles
            </div>
            <div class="flex space-x-1">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo !empty($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?>" 
                       class="px-3 py-1 rounded <?php echo ($page ?? 1) == $i ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function deleteVehicle(id) {
    if (confirm('Estàs segur que vols eliminar aquest vehicle?')) {
        fetch('/admin/vehicles/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al eliminar el vehicle: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al eliminar el vehicle');
            console.error('Error:', error);
        });
    }
}
</script>

<?php
// Incluir el footer de admin
require_once __DIR__ . '/admin-footer.php';
?>
