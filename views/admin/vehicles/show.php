<?php
/**
 * 👁️ Vista: Detalle del Vehículo (Admin)
 * Muestra toda la información de un vehículo específico
 */

require_once __DIR__ . '/../admin-header.php';

$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
?>
<style> .leaflet-control-zoom { 
            display: none !important;
        } </style>
<!-- Leaflet CSS para mapas -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        <?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?>
                    </h2>
                    <?php if (!empty($vehicle['license_plate'])): ?>
                        <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($vehicle['license_plate']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-3 mt-3 sm:mt-0">
                    <!-- Volver al listado (secundario) -->
                    <a href="/admin/vehicles" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <?= __('admin.vehicles.back') ?>
                    </a>

                    <!-- Editar (botón grande) -->
                    <a href="/admin/vehicles/<?= $vehicle['id'] ?>/edit" class="bg-[#1565C0] hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-md hover:shadow-lg" title="<?= __('admin.vehicles.buttons.edit') ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.414 6.586a2 2 0 112.828 2.828L12 18l-4 1 1-4 8.414-8.414z"/>
                        </svg>
                        <?= __('admin.vehicles.buttons.edit') ?>
                    </a>

                    <!-- Eliminar (botón grande y rojo) -->
                    <button type="button" onclick="confirmDelete()" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-md hover:shadow-lg" title="<?= __('admin.vehicles.buttons.delete') ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                        </svg>
                        <?= __('admin.vehicles.buttons.delete') ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mensaje de éxito -->
        <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-semibold"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tarjetas de información -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            
            <!-- Estado -->
            <div class="bg-gray-100 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500"><?= __('admin.vehicles.cards.status') ?></h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
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
            <div class="bg-gray-100 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500"><?= __('admin.vehicles.cards.battery') ?></h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900"><?= $vehicle['battery_level'] ?? $vehicle['battery'] ?? 'N/A' ?>%</p>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                    <div class="<?= ($vehicle['battery_level'] ?? $vehicle['battery'] ?? 0) > 50 ? 'bg-green-500' : (($vehicle['battery_level'] ?? $vehicle['battery'] ?? 0) > 20 ? 'bg-yellow-500' : 'bg-red-500') ?> h-2.5 rounded-full transition-all" style="width: <?= $vehicle['battery_level'] ?? $vehicle['battery'] ?? 0 ?>%"></div>
                </div>
            </div>

            <!-- Precio -->
            <div class="bg-gray-100 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500"><?= __('admin.vehicles.cards.price') ?></h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($vehicle['price_per_minute'], 2) ?>€</p>
            </div>
        </div>

        <!-- Información detallada -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Información del vehículo -->
            <div class="bg-gray-100 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?= __('admin.vehicles.info.vehicle_information') ?>
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500"><?= __('admin.vehicles.labels.plate') ?></dt>
                        <dd class="text-sm text-gray-900 font-semibold"><?= htmlspecialchars($vehicle['license_plate'] ?? $vehicle['plate'] ?? 'N/A') ?></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500"><?= __('admin.vehicles.labels.brand') ?></dt>
                        <dd class="text-sm text-gray-900"><?= htmlspecialchars($vehicle['brand']) ?></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500"><?= __('admin.vehicles.labels.model') ?></dt>
                        <dd class="text-sm text-gray-900"><?= htmlspecialchars($vehicle['model']) ?></dd>
                    </div>
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-500"><?= __('admin.vehicles.labels.year') ?></dt>
                        <dd class="text-sm text-gray-900"><?= $vehicle['year'] ?></dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-sm font-medium text-gray-500"><?= __('admin.vehicles.labels.is_accessible') ?></dt>
                        <dd class="text-sm text-gray-900">
                            <?= $vehicle['is_accessible'] ? 'Sí' : 'No' ?>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Ubicación -->
            <div class="bg-gray-100 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Ubicación
                </h2>
                
                <?php 
                // Verificar si hay coordenadas válidas
                $lat = $vehicle['location']['lat'] ?? null;
                $lng = $vehicle['location']['lng'] ?? null;
                $hasValidCoords = $lat && $lng && $lat != 0 && $lng != 0;
                
                if ($hasValidCoords): 
                ?>
                    <!-- Mapa de OpenStreetMap -->
                    <div id="vehicle-map" style="height: 350px; border-radius: 8px;"></div>
                <?php else: ?>
                    <p class="text-sm text-gray-500 italic">No hay coordenadas disponibles para mostrar el mapa</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Imagen (si existe) -->
        <?php if (!empty($vehicle['image_url'])): ?>
            <div class="mt-6 bg-gray-100 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Imagen</h2>
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

<!-- Leaflet JS para mapas -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
        crossorigin=""></script>

<script>
const confirmTemplate = <?= json_encode(__('admin.vehicles.confirm_delete')) ?>;
function confirmDelete() {
    const plate = <?= json_encode(htmlspecialchars($vehicle['license_plate'] ?? $vehicle['plate'] ?? '')) ?>;
    const msg = confirmTemplate.replace(':plate', plate);
    if (confirm(msg)) {
        document.getElementById('deleteForm').submit();
    }
}

// Inicializar mapa de OpenStreetMap
<?php 
$lat = $vehicle['location']['lat'] ?? null;
$lng = $vehicle['location']['lng'] ?? null;
$hasValidCoords = $lat && $lng && $lat != 0 && $lng != 0;

if ($hasValidCoords): 
?>
document.addEventListener('DOMContentLoaded', function() {
    const lat = <?= $lat ?>;
    const lng = <?= $lng ?>;
    
    console.log('Inicializando mapa con coordenadas:', lat, lng);
    
    // Crear el mapa centrado en la ubicación del vehículo
    const map = L.map('vehicle-map').setView([lat, lng], 15);
    
    // Agregar tiles de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        minZoom: 10,
        maxZoom: 18
    }).addTo(map);
    
    // Crear icono personalizado para el vehículo
    const vehicleIcon = L.divIcon({
        html: '<div style="background-color: #3b82f6; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
        className: 'custom-marker',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
    
    // Agregar marcador del vehículo
    const marker = L.marker([lat, lng], { icon: vehicleIcon }).addTo(map);
    
    // Popup con información del vehículo (sin emoticonos)
    marker.bindPopup(`
        <div style="text-align: center; padding: 5px;">
            <strong style="font-size: 1.1em;"><?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?></strong><br>
            <span style="font-size: 0.9em; color: #666;"><?= htmlspecialchars($vehicle['license_plate'] ?? $vehicle['plate'] ?? '') ?></span><br>
            <span style="font-size: 0.85em; color: #888;">${lat.toFixed(6)}, ${lng.toFixed(6)}</span>
        </div>
    `).openPopup();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
