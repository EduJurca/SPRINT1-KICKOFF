<?php
/**
 * 👁️ Vista: Detalle del Vehículo (Admin)
 * Muestra toda la información de un vehículo específico
 */

require_once __DIR__ . '/../admin-header.php';

$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="/admin/vehicles" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver al listado
            </a>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?>
                    </h1>
                    <p class="mt-2 text-lg text-gray-600">
                        Matrícula: <span class="font-semibold"><?= htmlspecialchars($vehicle['license_plate'] ?? $vehicle['plate'] ?? '') ?></span>
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="/admin/vehicles/<?= $vehicle['id'] ?>/edit" 
                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    <button onclick="confirmDelete()" 
                            class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <!-- Mensaje de éxito -->
        <?php if ($success): ?>
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                <p class="font-semibold">✅ <?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <!-- Tarjetas de información -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <!-- Estado -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Estado</h3>
                    <?php
                    $statusIcons = [
                        'available' => '✅',
                        'in_use' => '🔵',
                        'charging' => '🔋',
                        'maintenance' => '🔧',
                        'reserved' => '📌'
                    ];
                    echo $statusIcons[$vehicle['status']] ?? '❓';
                    ?>
                </div>
                <?php
                $statusColors = [
                    'available' => 'text-green-600',
                    'in_use' => 'text-blue-600',
                    'charging' => 'text-yellow-600',
                    'maintenance' => 'text-red-600',
                    'reserved' => 'text-purple-600'
                ];
                $statusNames = [
                    'available' => 'Disponible',
                    'in_use' => 'En uso',
                    'charging' => 'Cargando',
                    'maintenance' => 'Mantenimiento',
                    'reserved' => 'Reservado'
                ];
                $color = $statusColors[$vehicle['status']] ?? 'text-gray-600';
                $name = $statusNames[$vehicle['status']] ?? $vehicle['status'];
                ?>
                <p class="text-2xl font-bold <?= $color ?>"><?= $name ?></p>
            </div>

            <!-- Batería -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Batería</h3>
                    <span>🔋</span>
                </div>
                <p class="text-2xl font-bold text-gray-900"><?= $vehicle['battery_level'] ?? $vehicle['battery'] ?? 'N/A' ?>%</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: <?= $vehicle['battery_level'] ?? $vehicle['battery'] ?? 0 ?>%"></div>
                </div>
            </div>

            <!-- Precio -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Precio/minuto</h3>
                    <span>💰</span>
                </div>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($vehicle['price_per_minute'], 2) ?>€</p>
            </div>
        </div>

        <!-- Información detallada -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Información del vehículo -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Información del Vehículo
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                        <dd class="text-sm text-gray-900 font-semibold">#<?= $vehicle['id'] ?></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500">Matrícula</dt>
                        <dd class="text-sm text-gray-900 font-semibold"><?= htmlspecialchars($vehicle['license_plate'] ?? $vehicle['plate'] ?? 'N/A') ?></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500">Marca</dt>
                        <dd class="text-sm text-gray-900"><?= htmlspecialchars($vehicle['brand']) ?></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500">Modelo</dt>
                        <dd class="text-sm text-gray-900"><?= htmlspecialchars($vehicle['model']) ?></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500">Año</dt>
                        <dd class="text-sm text-gray-900"><?= $vehicle['year'] ?></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                        <dd class="text-sm text-gray-900">
                            <?php
                            $types = [
                                'car' => '🚗 Coche',
                                'bike' => '🚲 Bicicleta',
                                'scooter' => '🛴 Patinete',
                                'motorcycle' => '🏍️ Motocicleta'
                            ];
                            echo $types[$vehicle['vehicle_type']] ?? $vehicle['vehicle_type'];
                            ?>
                        </dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-sm font-medium text-gray-500">Accesible</dt>
                        <dd class="text-sm text-gray-900">
                            <?= $vehicle['is_accessible'] ? '♿ Sí' : '❌ No' ?>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Ubicación -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Ubicación
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500">Latitud</dt>
                        <dd class="text-sm text-gray-900 font-mono"><?= $vehicle['latitude'] ?? $vehicle['location']['lat'] ?? 'N/A' ?></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500">Longitud</dt>
                        <dd class="text-sm text-gray-900 font-mono"><?= $vehicle['longitude'] ?? $vehicle['location']['lng'] ?? 'N/A' ?></dd>
                    </div>
                </dl>
                
                <?php if (isset($vehicle['latitude']) && isset($vehicle['longitude'])): ?>
                    <div class="mt-4">
                        <a href="https://www.google.com/maps?q=<?= $vehicle['latitude'] ?>,<?= $vehicle['longitude'] ?>" 
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Ver en Google Maps
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Imagen (si existe) -->
        <?php if (!empty($vehicle['image_url'])): ?>
            <div class="mt-6 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">🖼️ Imagen</h2>
                <img src="<?= htmlspecialchars($vehicle['image_url']) ?>" 
                     alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>"
                     class="max-w-full h-auto rounded-lg">
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="deleteForm" method="POST" action="/admin/vehicles/<?= $vehicle['id'] ?>" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
function confirmDelete() {
    if (confirm('¿Estás seguro de eliminar este vehículo?\nEsta acción no se puede deshacer.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
