<?php
/**
 * Vista: Create Charging Station
 * Form to add a new charging station
 */

// Check if user is admin
// if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
//     header('Location: /login');
//     exit;
// }

$title = 'Add Charging Station - Admin Panel';
$pageTitle = 'Add Charging Station';
$currentPage = 'charging-stations';

require_once __DIR__ . '/../admin-header.php';
?>

<!-- Error Messages -->
<?php if (isset($_SESSION['errors'])): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    <ul class="list-disc list-inside">
        <?php foreach ($_SESSION['errors'] as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php unset($_SESSION['errors']); endif; ?>

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">
        <i class="fas fa-plus-circle text-green-600"></i>
        Add New Charging Station
    </h1>
    <a href="/admin/charging-stations" 
       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
        <i class="fas fa-arrow-left"></i>
        Back to List
    </a>
</div>

<!-- Form -->
<div class="bg-white rounded-lg shadow p-6">
    <form action="/admin/charging-stations/store" method="POST" class="space-y-6">
        
        <!-- Basic Information -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                <i class="fas fa-info-circle text-blue-600"></i>
                Basic Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Station Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Amposta Centre Station">
                </div>
                
                <!-- Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                        Address <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="address" 
                           name="address" 
                           required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Placa d'Espanya, 1">
                </div>
                
                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                        City <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="city" 
                           name="city" 
                           required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Amposta">
                </div>
                
                <!-- Postal Code -->
                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                        Postal Code
                    </label>
                    <input type="text" 
                           id="postal_code" 
                           name="postal_code" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., 43870">
                </div>
                
                <!-- Operator -->
                <div class="md:col-span-2">
                    <label for="operator" class="block text-sm font-medium text-gray-700 mb-1">
                        Operator
                    </label>
                    <input type="text" 
                           id="operator" 
                           name="operator" 
                           value="VoltiaCar"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., VoltiaCar">
                </div>
            </div>
        </div>
        
        <!-- Location (GPS) -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                <i class="fas fa-map-marker-alt text-red-600"></i>
                Location (GPS Coordinates)
            </h2>
            
            <!-- Interactive Map -->
            <div class="mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                    <p class="text-blue-800 text-sm flex items-start gap-2">
                        <i class="fas fa-info-circle mt-0.5"></i>
                        <span><strong>Click on the map</strong> to select the exact location of the charging station. The coordinates will be filled automatically.</span>
                    </p>
                </div>
                
                <!-- Map Container -->
                <div id="location-map" class="w-full h-96 rounded-lg border-2 border-gray-300 mb-4"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Latitude -->
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">
                        Latitude <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="latitude" 
                           name="latitude" 
                           step="0.000001" 
                           min="-90" 
                           max="90" 
                           required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Click on map to set">
                    <p class="text-xs text-gray-500 mt-1">Between -90 and 90 (click map or type)</p>
                </div>
                
                <!-- Longitude -->
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">
                        Longitude <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="longitude" 
                           name="longitude" 
                           step="0.000001" 
                           min="-180" 
                           max="180" 
                           required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Click on map to set">
                    <p class="text-xs text-gray-500 mt-1">Between -180 and 180 (filled by map)</p>
                </div>
            </div>
        </div>
        
        <!-- Capacity -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                <i class="fas fa-plug text-purple-600"></i>
                Capacity & Power
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Total Slots -->
                <div>
                    <label for="total_slots" class="block text-sm font-medium text-gray-700 mb-1">
                        Total Charging Slots <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="total_slots" 
                           name="total_slots" 
                           min="1" 
                           value="4" 
                           required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           onchange="syncAvailableSlots()">
                </div>
                
                <!-- Available Slots -->
                <div>
                    <label for="available_slots" class="block text-sm font-medium text-gray-700 mb-1">
                        Available Slots <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="available_slots" 
                           name="available_slots" 
                           min="0" 
                           value="4" 
                           required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Power (Fixed) -->
                <div>
                    <label for="power_kw" class="block text-sm font-medium text-gray-700 mb-1">
                        Power
                    </label>
                    <input type="text" 
                           id="power_kw" 
                           name="power_kw" 
                           value="50 kW" 
                           readonly 
                           disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                    <p class="text-xs text-gray-500 mt-1">Fixed at 50 kW</p>
                </div>
            </div>
        </div>
        
        <!-- Status -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                <i class="fas fa-toggle-on text-green-600"></i>
                Status
            </h2>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                    Station Status <span class="text-red-500">*</span>
                </label>
                <select id="status" 
                        name="status" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="active">Active</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="out_of_service">Out of Service</option>
                </select>
            </div>
        </div>
        
        <!-- Description -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                <i class="fas fa-align-left text-gray-600"></i>
                Additional Information
            </h2>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description (Optional)
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Additional information about this charging station..."></textarea>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="/admin/charging-stations" 
               class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <i class="fas fa-save"></i>
                Create Station
            </button>
        </div>
    </form>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Interactive Map for Location Selection
let map;
let marker;

function initLocationMap() {
    // Default center: Amposta
    const defaultLat = 40.7089;
    const defaultLng = 0.5783;
    
    // Initialize map
    map = L.map('location-map').setView([defaultLat, defaultLng], 13);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add click event to map
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        // Update form fields
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
        
        // Remove old marker if exists
        if (marker) {
            map.removeLayer(marker);
        }
        
        // Add new marker
        marker = L.marker([lat, lng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        }).addTo(map);
        
        // Add popup
        marker.bindPopup(`
            <strong>Selected Location</strong><br>
            Lat: ${lat.toFixed(6)}<br>
            Lng: ${lng.toFixed(6)}
        `).openPopup();
    });
}

// Sync available slots with total slots
function syncAvailableSlots() {
    const totalSlots = document.getElementById('total_slots').value;
    const availableSlots = document.getElementById('available_slots');
    if (parseInt(availableSlots.value) > parseInt(totalSlots)) {
        availableSlots.value = totalSlots;
    }
    availableSlots.max = totalSlots;
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    initLocationMap();
    syncAvailableSlots();
});
</script>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
