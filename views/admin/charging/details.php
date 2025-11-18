<?php
/**
 * Vista: Charging Station Details
 * Detailed view of a single charging station with map
 */

// Clear any session messages BEFORE loading header
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

$title = ($station['name'] ?? 'Estació de càrrega') . ' - VoltiaCar';
$pageTitle = $station['name'] ?? 'Estació de càrrega';
$currentPage = 'charging';

require_once __DIR__ . '/../admin-header.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
#station-map {
    height: 100%;
    min-height: 250px;
    width: 100%;
    border-radius: 8px;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
}

.status-active {
    background-color: #D1FAE5;
    color: #065F46;
}

.status-maintenance {
    background-color: #FEF3C7;
    color: #92400E;
}

.status-out_of_service {
    background-color: #FEE2E2;
    color: #991B1B;
}
</style>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($station['name']) ?></h2>
                    <p class="text-sm text-gray-600 mt-1">
                        <?= htmlspecialchars($station['address']) ?>, <?= htmlspecialchars($station['city']) ?>
                        <?php if (!empty($station['postal_code'])): ?>
                            (<?= htmlspecialchars($station['postal_code']) ?>)
                        <?php endif; ?>
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="/admin/charging-stations" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Tornar al Llistat
                    </a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <a href="/admin/charging-stations/<?= $station['id'] ?>/edit" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-[#1565C0] rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6 flex flex-col">
                <!-- Map -->
                <div class="bg-gray-100 rounded-lg shadow-md p-6 flex flex-col flex-1 overflow-hidden">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ubicació</h3>
                    <div id="station-map" class="mb-4 flex-1"></div>
                    <div class="flex gap-3">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $station['latitude'] ?>,<?= $station['longitude'] ?>" 
                           target="_blank"
                           class="flex-1 px-4 py-2.5 bg-[#1565C0] text-white rounded-lg hover:bg-blue-700 transition-all text-center font-semibold shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                            Obtenir Direccions
                        </a>
                        <a href="https://www.openstreetmap.org/?mlat=<?= $station['latitude'] ?>&mlon=<?= $station['longitude'] ?>&zoom=15" 
                           target="_blank"
                           class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all text-center font-semibold shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            OpenStreetMap
                        </a>
                    </div>
                </div>

                <!-- Description -->
                <?php if (!empty($station['description'])): ?>
                <div class="bg-gray-100 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Descripció</h3>
                    <p class="text-gray-700"><?= nl2br(htmlspecialchars($station['description'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Charging Slots -->
                <div class="bg-gray-100 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Slots de Càrrega</h3>
                    <div class="mb-4">
                        <p class="text-gray-700 mb-4">
                            <span class="font-bold text-green-600 text-2xl"><?= $station['available_slots'] ?></span>
                            <span class="text-gray-600"> de </span>
                            <span class="font-bold text-2xl"><?= $station['total_slots'] ?></span>
                            <span class="text-gray-600"> disponibles</span>
                        </p>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <?php 
                        $available = (int)$station['available_slots'];
                        $total = (int)$station['total_slots'];
                        $occupied = $total - $available;
                        
                        // Show available slots
                        for ($i = 1; $i <= $available; $i++): ?>
                            <div class="w-10 h-10 bg-green-100 border-2 border-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        <?php endfor; ?>
                        
                        <!-- Show occupied slots -->
                        <?php for ($i = 1; $i <= $occupied; $i++): ?>
                            <div class="w-10 h-10 bg-red-100 border-2 border-red-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Station Details -->
                <div class="bg-gray-100 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalls de l'Estació</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 pb-4 border-b border-gray-200">
                            <svg class="w-6 h-6 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600">Estat</p>
                                <?php
                                $statusClass = "status-{$station['status']}";
                                $statusLabels = [
                                    'active' => 'Activa',
                                    'maintenance' => 'Manteniment',
                                    'out_of_service' => 'Fora de Servei'
                                ];
                                $statusText = $statusLabels[$station['status']] ?? strtoupper($station['status']);
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pb-4 border-b border-gray-200">
                            <svg class="w-6 h-6 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600">Potència</p>
                                <p class="font-semibold text-gray-900">50 kW</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pb-4 border-b border-gray-200">
                            <svg class="w-6 h-6 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600">Operador</p>
                                <p class="font-semibold text-gray-900"><?= htmlspecialchars($station['operator'] ?? 'VoltiaCar') ?></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pb-4 border-b border-gray-200">
                            <svg class="w-6 h-6 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600">Coordenades</p>
                                <p class="font-semibold text-gray-900 text-sm">
                                    <?= number_format($station['latitude'], 6) ?>,<br>
                                    <?= number_format($station['longitude'], 6) ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600">Última Actualització</p>
                                <p class="font-semibold text-gray-900"><?= date('d/m/Y H:i', strtotime($station['updated_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map centered on station
document.addEventListener('DOMContentLoaded', function() {
    const lat = <?= $station['latitude'] ?>;
    const lng = <?= $station['longitude'] ?>;
    
    const map = L.map('station-map').setView([lat, lng], 15);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Add marker for station
    const marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup(`
        <div style="padding: 8px;">
            <strong style="color: #1565C0;"><?= htmlspecialchars($station['name']) ?></strong><br>
            <span style="font-size: 12px;"><?= htmlspecialchars($station['address']) ?></span>
        </div>
    `).openPopup();
});
</script>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
