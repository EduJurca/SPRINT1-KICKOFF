<?php
/**
 * Vista: Charging Station Details
 * Detailed view of a single charging station with map
 */

$title = ($station['name'] ?? 'Charging Station') . ' - VoltiaCar';
$pageTitle = $station['name'] ?? 'Charging Station';

require_once __DIR__ . '/../public/layouts/header.php';

// Clear any session messages
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
#station-map {
    height: 400px;
    width: 100%;
    border-radius: 8px;
}

.info-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.info-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #E5E7EB;
}

.info-row:last-child {
    border-bottom: none;
}

.info-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 18px;
}

.info-icon.blue { background-color: #DBEAFE; color: #1E40AF; }
.info-icon.green { background-color: #D1FAE5; color: #065F46; }
.info-icon.purple { background-color: #E9D5FF; color: #6B21A8; }
.info-icon.orange { background-color: #FED7AA; color: #9A3412; }
.info-icon.red { background-color: #FEE2E2; color: #991B1B; }
.info-icon.gray { background-color: #F3F4F6; color: #374151; }

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

.slot-indicator {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.slot {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
}

.slot.available {
    background-color: #D1FAE5;
    color: #065F46;
    border: 2px solid #10B981;
}

.slot.occupied {
    background-color: #FEE2E2;
    color: #991B1B;
    border: 2px solid #EF4444;
}
</style>

<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="text-sm text-gray-600">
            <a href="/charging-stations" class="hover:text-blue-600">
                <i class="fas fa-map"></i> Charging Stations Map
            </a>
            <span class="mx-2">/</span>
            <span class="text-gray-900"><?= htmlspecialchars($station['name']) ?></span>
        </nav>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-charging-station text-blue-600"></i>
                    <?= htmlspecialchars($station['name']) ?>
                </h1>
                <p class="text-gray-600">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($station['address']) ?>, <?= htmlspecialchars($station['city']) ?>
                    <?php if (!empty($station['postal_code'])): ?>
                        (<?= htmlspecialchars($station['postal_code']) ?>)
                    <?php endif; ?>
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <a href="/charging-stations" 
                   class="bg-blue-600 hover:bg-blue-700 text-white hover:text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition">
                    <i class="fas fa-arrow-left"></i>
                    Back to Map
                </a>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="/admin/charging-stations/<?= $station['id'] ?>/edit" 
                   class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition text-center">
                    <i class="fas fa-edit"></i>
                    Edit Station
                </a>
                <?php endif; ?>
                <div>
                <?php
                $statusClass = "status-{$station['status']}";
                $statusText = str_replace('_', ' ', strtoupper($station['status']));
                ?>
                <span class="status-badge <?= $statusClass ?>">
                    <i class="fas fa-circle text-xs"></i> <?= $statusText ?>
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Map -->
            <div class="info-card">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-map text-blue-600"></i> Location
                </h2>
                <div id="station-map"></div>
                <div class="mt-4 flex gap-2">
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $station['latitude'] ?>,<?= $station['longitude'] ?>" 
                       target="_blank"
                       class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 hover:text-white transition text-center">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                    <a href="https://www.openstreetmap.org/?mlat=<?= $station['latitude'] ?>&mlon=<?= $station['longitude'] ?>&zoom=15" 
                       target="_blank"
                       class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition text-center">
                        <i class="fas fa-external-link-alt"></i> Open in OSM
                    </a>
                </div>
            </div>

            <!-- Description -->
            <?php if (!empty($station['description'])): ?>
            <div class="info-card">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-blue-600"></i> Description
                </h2>
                <p class="text-gray-700"><?= nl2br(htmlspecialchars($station['description'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Charging Slots Visualization -->
            <div class="info-card">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-plug text-purple-600"></i> Charging Slots
                </h2>
                <div class="mb-4">
                    <p class="text-gray-700 mb-2">
                        <span class="font-bold text-green-600 text-2xl"><?= $station['available_slots'] ?></span>
                        <span class="text-gray-600"> of </span>
                        <span class="font-bold text-2xl"><?= $station['total_slots'] ?></span>
                        <span class="text-gray-600"> slots available</span>
                    </p>
                </div>
                <div class="slot-indicator">
                    <?php 
                    $available = (int)$station['available_slots'];
                    $total = (int)$station['total_slots'];
                    $occupied = $total - $available;
                    
                    // Show available slots
                    for ($i = 1; $i <= $available; $i++): ?>
                        <div class="slot available">
                            <i class="fas fa-plug"></i>
                        </div>
                    <?php endfor; ?>
                    
                    <!-- Show occupied slots -->
                    <?php for ($i = 1; $i <= $occupied; $i++): ?>
                        <div class="slot occupied">
                            <i class="fas fa-car"></i>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Station Details -->
            <div class="info-card">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-info text-blue-600"></i> Details
                </h2>
                
                <div class="space-y-0">
                    <div class="info-row">
                        <div class="info-icon purple">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Power</p>
                            <p class="font-semibold text-gray-900">50 kW</p>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon blue">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Operator</p>
                            <p class="font-semibold text-gray-900"><?= htmlspecialchars($station['operator'] ?? 'VoltiaCar') ?></p>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon green">
                            <i class="fas fa-plug"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Available Slots</p>
                            <p class="font-semibold text-gray-900"><?= $station['available_slots'] ?> / <?= $station['total_slots'] ?></p>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon orange">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Coordinates</p>
                            <p class="font-semibold text-gray-900 text-sm">
                                <?= number_format($station['latitude'], 6) ?>,<br>
                                <?= number_format($station['longitude'], 6) ?>
                            </p>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon gray">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Last Updated</p>
                            <p class="font-semibold text-gray-900"><?= date('d/m/Y H:i', strtotime($station['updated_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="info-card">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-bolt text-yellow-600"></i> Actions
                </h2>
                
                <?php if ($station['status'] === 'active' && $station['available_slots'] > 0): ?>
                    <a href="/booking/create?station_id=<?= $station['id'] ?>" 
                       class="block w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center font-semibold mb-3">
                        <i class="fas fa-calendar-check"></i> Book Charging Slot
                    </a>
                <?php else: ?>
                    <button disabled 
                            class="block w-full px-4 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed text-center font-semibold mb-3">
                        <i class="fas fa-times-circle"></i> 
                        <?= $station['status'] !== 'active' ? 'Station Not Available' : 'No Slots Available' ?>
                    </button>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="info-card bg-blue-50 border border-blue-200">
                <p class="text-blue-800 mb-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Login required</strong>
                </p>
                <p class="text-blue-700 text-sm mb-4">
                    You need to be logged in to book a charging slot.
                </p>
                <a href="/login?redirect=/charging-stations/<?= $station['id'] ?>" 
                   class="block w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 hover:text-white transition text-center">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
            <?php endif; ?>

            <!-- Safety Info -->
            <div class="info-card bg-yellow-50 border border-yellow-200">
                <h3 class="font-bold text-yellow-900 mb-2">
                    <i class="fas fa-exclamation-triangle"></i> Safety Information
                </h3>
                <ul class="text-sm text-yellow-800 space-y-1">
                    <li><i class="fas fa-check text-xs"></i> Always park in designated areas</li>
                    <li><i class="fas fa-check text-xs"></i> Follow charging instructions</li>
                    <li><i class="fas fa-check text-xs"></i> Report any issues immediately</li>
                    <li><i class="fas fa-check text-xs"></i> Do not leave vehicle unattended</li>
                </ul>
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

<?php require_once __DIR__ . '/../public/layouts/footer.php'; ?>
