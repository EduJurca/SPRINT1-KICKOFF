<?php
/**
 * Vista: Charging Stations Map
 * Interactive map showing all charging stations using OpenStreetMap + Leaflet
 */

// Clear any error messages that might be in session BEFORE loading header
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

$title = 'Mapa d\'estacions de càrrega - VoltiaCar';
$pageTitle = 'Estacions de càrrega';
$currentPage = 'charging';

require_once __DIR__ . '/../admin-header.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
#map {
    height: 500px;
    width: 100%;
    border-radius: 8px;
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
</style>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Mapa d'Estacions de Càrrega</h2>
                    <p class="text-sm text-gray-600 mt-1">Troba l'estació de càrrega més propera</p>
                </div>
                <a href="/admin/charging-stations" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Tornar al Llistat
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-gray-100 rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total</p>
                        <p id="total-stations" class="text-2xl font-bold text-gray-900">-</p>
                    </div>
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-gray-100 rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Actives</p>
                        <p id="active-stations" class="text-2xl font-bold text-gray-900">-</p>
                    </div>
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-gray-100 rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Manteniment</p>
                        <p id="maintenance-stations" class="text-2xl font-bold text-gray-900">-</p>
                    </div>
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-gray-100 rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Fora de Servei</p>
                        <p id="outofservice-stations" class="text-2xl font-bold text-gray-900">-</p>
                    </div>
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-gray-100 rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Slots Disponibles</p>
                        <p id="total-slots" class="text-2xl font-bold text-gray-900">-</p>
                    </div>
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
                </div>

        <!-- Filters -->
        <div class="bg-gray-100 rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="filter-city" class="block text-sm font-semibold text-gray-700 mb-2">Ciutat</label>
                    <select id="filter-city" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                        <option value="">Totes les Ciutats</option>
                    </select>
                </div>

                <div>
                    <label for="filter-status" class="block text-sm font-semibold text-gray-700 mb-2">Estat</label>
                    <select id="filter-status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                        <option value="">Tots els Estats</option>
                        <option value="active">Activa</option>
                        <option value="maintenance">Manteniment</option>
                        <option value="out_of_service">Fora de Servei</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button onclick="resetFilters()" class="w-full px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reiniciar Filtres
                    </button>
                </div>
            </div>
        </div>

        <!-- Map Container -->
        <div class="bg-gray-100 rounded-lg shadow-md p-4">
            <div id="map"></div>
        </div>

        <!-- Legend -->
        <div class="bg-gray-100 rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Llegenda</h3>
            <div class="flex flex-wrap gap-6">
                <div class="flex items-center gap-2">
                    <div style="width: 20px; height: 20px; background-color: #10B981; border-radius: 50%; border: 2px solid white;"></div>
                    <span class="text-sm text-gray-700">Estació Activa</span>
                </div>
                <div class="flex items-center gap-2">
                    <div style="width: 20px; height: 20px; background-color: #F59E0B; border-radius: 50%; border: 2px solid white;"></div>
                    <span class="text-sm text-gray-700">En Manteniment</span>
                </div>
                <div class="flex items-center gap-2">
                    <div style="width: 20px; height: 20px; background-color: #EF4444; border-radius: 50%; border: 2px solid white;"></div>
                    <span class="text-sm text-gray-700">Fora de Servei</span>
                </div>
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
        alert('Error en carregar les estacions de càrrega. Si us plau, intenta-ho de nou.');
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
    const statusMap = {
        active: 'Activa',
        maintenance: 'Manteniment',
        out_of_service: 'Fora de Servei'
    };
    const statusText = statusMap[station.status] || station.status;

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
                    <span>${station.available_slots} / ${station.total_slots} places disponibles</span>
                </div>
                <div class="station-popup-row">
                    <i class="fas fa-bolt"></i>
                    <span>${station.power_kw || '—'} kW</span>
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
                <i class="fas fa-info-circle"></i> Veure detalls
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

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
