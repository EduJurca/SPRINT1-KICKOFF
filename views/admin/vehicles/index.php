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

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🚗 Gestión de Vehículos</h1>
                    <p class="mt-2 text-sm text-gray-600">Administra los vehículos de la flota</p>
                </div>
                <a href="/views/admin/vehicles/create.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nuevo Vehículo
                </a>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($success): ?>
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                <p class="font-semibold">✅ <?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                <p class="font-semibold">❌ <?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="/admin/vehicles" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                    <input type="text" name="brand" value="<?= htmlspecialchars($filters['brand'] ?? '') ?>" 
                           placeholder="Filtrar por marca" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos</option>
                        <option value="available" <?= ($filters['status'] ?? '') === 'available' ? 'selected' : '' ?>>Disponible</option>
                        <option value="in_use" <?= ($filters['status'] ?? '') === 'in_use' ? 'selected' : '' ?>>En uso</option>
                        <option value="charging" <?= ($filters['status'] ?? '') === 'charging' ? 'selected' : '' ?>>Cargando</option>
                        <option value="maintenance" <?= ($filters['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Mantenimiento</option>
                        <option value="reserved" <?= ($filters['status'] ?? '') === 'reserved' ? 'selected' : '' ?>>Reservado</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                    <select name="vehicle_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos</option>
                        <option value="car" <?= ($filters['vehicle_type'] ?? '') === 'car' ? 'selected' : '' ?>>Coche</option>
                        <option value="bike" <?= ($filters['vehicle_type'] ?? '') === 'bike' ? 'selected' : '' ?>>Bicicleta</option>
                        <option value="scooter" <?= ($filters['vehicle_type'] ?? '') === 'scooter' ? 'selected' : '' ?>>Patinete</option>
                        <option value="motorcycle" <?= ($filters['vehicle_type'] ?? '') === 'motorcycle' ? 'selected' : '' ?>>Moto</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                        🔍 Filtrar
                    </button>
                    <a href="/admin/vehicles" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold transition">
                        ↻
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabla de vehículos -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matrícula</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehículo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batería</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio/min</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($vehicles)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-lg font-semibold">No hay vehículos registrados</p>
                                        <a href="/admin/vehicles/create" class="text-blue-600 hover:text-blue-700 font-medium">+ Crear el primero</a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?= $vehicle['id'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        <?= htmlspecialchars($vehicle['license_plate'] ?? $vehicle['plate'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($vehicle['brand']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($vehicle['model']) ?> (<?= $vehicle['year'] ?>)</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php
                                        $types = [
                                            'car' => '🚗 Coche',
                                            'bike' => '🚲 Bici',
                                            'scooter' => '🛴 Patinete',
                                            'motorcycle' => '🏍️ Moto'
                                        ];
                                        echo $types[$vehicle['vehicle_type']] ?? $vehicle['vehicle_type'];
                                        ?>
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
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-green-500 h-2 rounded-full" style="width: <?= $vehicle['battery_level'] ?>%"></div>
                                            </div>
                                            <span class="text-sm text-gray-600"><?= $vehicle['battery_level'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= number_format($vehicle['price_per_minute'], 2) ?>€
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="/admin/vehicles/<?= $vehicle['id'] ?>" class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                            👁️
                                        </a>
                                        <a href="/admin/vehicles/<?= $vehicle['id'] ?>/edit" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                            ✏️
                                        </a>
                                        <button onclick="confirmDelete(<?= $vehicle['id'] ?>, '<?= htmlspecialchars($vehicle['license_plate'] ?? $vehicle['plate']) ?>')" 
                                                class="text-red-600 hover:text-red-900" title="Eliminar">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($vehicles)): ?>
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Mostrando <span class="font-semibold"><?= count($vehicles) ?></span> vehículo(s)
                    </p>
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
function confirmDelete(id, plate) {
    if (confirm(`¿Estás seguro de eliminar el vehículo ${plate}?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/vehicles/${id}`;
        form.submit();
    }
}
</script>

<?php
// Incluir footer de admin
require_once __DIR__ . '/../admin-footer.php';
?>
