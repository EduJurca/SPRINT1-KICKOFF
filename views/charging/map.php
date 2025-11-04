<?php
/**
 * Vista: Charging Stations Map
 * Interactive map showing all charging stations using OpenStreetMap + Leaflet
 */

$title = 'Charging Stations Map - VoltiaCar';
$pageTitle = 'Charging Stations';
$currentPage = 'charging';

require_once __DIR__ . '/../admin/admin-header.php';

// Clear any error messages that might be in session
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
#map {
    height: 600px;
    width: 100%;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.leaflet-popup-content {
    margin: 12px;
    min-width: 250px;
}

.station-popup-header {
    font-size: 16px;
    font-weight: bold;
    color: #1565C0;
    margin-bottom: 8px;
}

.station-popup-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 14px;
}

.station-popup-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.station-popup-row i {
    width: 16px;
    color: #6B7280;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
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

.popup-button {
    display: block;
    width: 100%;
    margin-top: 12px;
    padding: 8px;
    background-color: #1565C0;
    color: white !important;
    text-align: center;
    border-radius: 6px;
    text-decoration: none;
    transition: background-color 0.2s;
}

.popup-button:hover {
    background-color: #0D47A1;
    color: white !important;
}

/* Custom marker icons */
.marker-icon {
    background-color: #10B981;
    width: 30px;
    height: 30px;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.marker-icon::after {
    content: '';
    position: absolute;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: white;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.marker-icon.maintenance {
    background-color: #F59E0B;
}

.marker-icon.out_of_service {
    background-color: #EF4444;
}
</style>

<!-- Header Section -->
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-start">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-charging-station"></i>
                Charging Stations Map
            </h1>
            <p class="text-gray-600">Find the nearest charging station for your electric vehicle</p>
        </div>
        
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <a href="/admin/charging-stations" 
           class="bg-gray-600 hover:bg-gray-700 text-white hover:text-white px-6 py-3 rounded-lg flex items-center gap-2 transition shadow-lg">
            <span class="text-xl">←</span>
            Back to List
        </a>
        <?php endif; ?>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Stations</p>
                    <p id="total-stations" class="text-2xl font-bold text-gray-900">-</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-charging-station text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Active</p>
                    <p id="active-stations" class="text-2xl font-bold text-green-600">-</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Maintenance</p>
                    <p id="maintenance-stations" class="text-2xl font-bold text-orange-600">-</p>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <i class="fas fa-tools text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Out of Service</p>
                    <p id="outofservice-stations" class="text-2xl font-bold text-red-600">-</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Available Slots</p>
                    <p id="total-slots" class="text-2xl font-bold text-purple-600">-</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <i class="fas fa-plug text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="filter-city" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-city"></i> City
                </label>
                <select id="filter-city" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Cities</option>
                </select>
            </div>

            <div>
                <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-toggle-on"></i> Status
                </label>
                <select id="filter-status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="out_of_service">Out of Service</option>
                </select>
            </div>

            <div class="flex items-end">
                <button onclick="resetFilters()" class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-redo"></i> Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="bg-white rounded-lg shadow p-4">
        <div id="map"></div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-lg shadow p-4 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">
            <i class="fas fa-info-circle text-blue-600"></i> Legend
        </h3>
        <div class="flex flex-wrap gap-6">
            <div class="flex items-center gap-2">
                <div style="width: 20px; height: 20px; background-color: #10B981; border-radius: 50%; border: 2px solid white;"></div>
                <span class="text-sm text-gray-700">Active Station</span>
            </div>
            <div class="flex items-center gap-2">
                <div style="width: 20px; height: 20px; background-color: #F59E0B; border-radius: 50%; border: 2px solid white;"></div>
                <span class="text-sm text-gray-700">Under Maintenance</span>
            </div>
            <div class="flex items-center gap-2">
                <div style="width: 20px; height: 20px; background-color: #EF4444; border-radius: 50%; border: 2px solid white;"></div>
                <span class="text-sm text-gray-700">Out of Service</span>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let markers = [];
let allStations = [];

// Initialize map
function initMap() {
    // Center on Amposta (Tarragona)
    map = L.map('map').setView([40.7089, 0.5780], 13);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Load stations
    loadStations();
}

// Load stations from API
async function loadStations() {
    try {
        const response = await fetch('/api/charging-stations');
        const data = await response.json();
        
        if (data.success) {
            allStations = data.stations;
            updateStats(allStations);
            populateCityFilter(allStations);
            displayStations(allStations);
        }
    } catch (error) {
        console.error('Error loading stations:', error);
        alert('Error loading charging stations. Please try again.');
    }
}

// Update statistics cards
function updateStats(stations) {
    const total = stations.length;
    const active = stations.filter(s => s.status === 'active').length;
    const maintenance = stations.filter(s => s.status === 'maintenance').length;
    const outofservice = stations.filter(s => s.status === 'out_of_service').length;
    const totalSlots = stations.reduce((sum, s) => sum + parseInt(s.available_slots), 0);

    document.getElementById('total-stations').textContent = total;
    document.getElementById('active-stations').textContent = active;
    document.getElementById('maintenance-stations').textContent = maintenance;
    document.getElementById('outofservice-stations').textContent = outofservice;
    document.getElementById('total-slots').textContent = totalSlots;
}

// Populate city filter dropdown
function populateCityFilter(stations) {
    const cities = [...new Set(stations.map(s => s.city))].sort();
    const select = document.getElementById('filter-city');
    
    cities.forEach(city => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        select.appendChild(option);
    });
}

// Display stations on map
function displayStations(stations) {
    // Clear existing markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];

    // Add markers for each station
    stations.forEach(station => {
        const marker = createMarker(station);
        markers.push(marker);
    });

    // Fit map to show all markers
    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

// Create marker for station
function createMarker(station) {
    const lat = parseFloat(station.latitude);
    const lng = parseFloat(station.longitude);

    // Custom icon based on status
    let iconColor = '#10B981'; // green for active
    if (station.status === 'maintenance') iconColor = '#F59E0B'; // orange
    if (station.status === 'out_of_service') iconColor = '#EF4444'; // red

    const icon = L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background-color: ${iconColor}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    // Create marker
    const marker = L.marker([lat, lng], { icon: icon }).addTo(map);

    // Create popup content
    const popupContent = createPopupContent(station);
    marker.bindPopup(popupContent);

    return marker;
}

// Create popup content
function createPopupContent(station) {
    const statusClass = `status-${station.status}`;
    const statusText = station.status.replace('_', ' ').toUpperCase();

    return `
        <div class="station-popup">
            <div class="station-popup-header">${station.name}</div>
            <div class="station-popup-info">
                <div class="station-popup-row">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>${station.address}, ${station.city}</span>
                </div>
                <div class="station-popup-row">
                    <i class="fas fa-plug"></i>
                    <span>${station.available_slots} / ${station.total_slots} slots available</span>
                </div>
                <div class="station-popup-row">
                    <i class="fas fa-bolt"></i>
                    <span>50 kW</span>
                </div>
                <div class="station-popup-row">
                    <i class="fas fa-building"></i>
                    <span>${station.operator || 'VoltiaCar'}</span>
                </div>
                <div class="station-popup-row">
                    <i class="fas fa-toggle-on"></i>
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
            </div>
            <a href="/charging-stations/${station.id}" class="popup-button">
                <i class="fas fa-info-circle"></i> View Details
            </a>
        </div>
    `;
}

// Filter stations
function filterStations() {
    const city = document.getElementById('filter-city').value;
    const status = document.getElementById('filter-status').value;

    let filtered = allStations;

    if (city) {
        filtered = filtered.filter(s => s.city === city);
    }

    if (status) {
        filtered = filtered.filter(s => s.status === status);
    }

    updateStats(filtered);
    displayStations(filtered);
}

// Reset filters
function resetFilters() {
    document.getElementById('filter-city').value = '';
    document.getElementById('filter-status').value = '';
    filterStations();
}

// Add event listeners
document.getElementById('filter-city').addEventListener('change', filterStations);
document.getElementById('filter-status').addEventListener('change', filterStations);

// Initialize on page load
document.addEventListener('DOMContentLoaded', initMap);
</script>

<?php require_once __DIR__ . '/../admin/admin-footer.php'; ?>
