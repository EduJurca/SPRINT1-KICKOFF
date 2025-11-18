<?php
/**
 * Vista: Create Charging Station
 * Form to add a new charging station
 */

require_once __DIR__ . '/../admin-header.php';

// Obtener errores y datos antiguos
$errors = $_SESSION['errors'] ?? [];
$oldData = $_SESSION['old_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_data']);
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
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Nova Estació de Càrrega</h2>
                    <p class="text-sm text-gray-600 mt-1">Afegeix una nova estació de càrrega al sistema</p>
                </div>
                <a href="/admin/charging-stations" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Tornar
                </a>
            </div>
        </div>

        <!-- Mostrar errores -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold mb-2">Errors de validació:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="bg-gray-100 rounded-lg shadow-md p-8">
            <form id="createChargingStationForm" action="/admin/charging-stations/store" method="POST" novalidate class="space-y-6">
                
                <!-- Información Básica -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informació Bàsica</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nom de l'Estació <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" required
                                   value="<?= htmlspecialchars($oldData['name'] ?? '') ?>"
                                   placeholder="p. ex., Estació Centre Amposta"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                            <p id="error-name" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                                Adreça <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="address" name="address" required
                                   value="<?= htmlspecialchars($oldData['address'] ?? '') ?>"
                                   placeholder="p. ex., Plaça d'Espanya, 1"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                            <p id="error-address" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                                Ciutat <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="city" name="city" required
                                   value="<?= htmlspecialchars($oldData['city'] ?? '') ?>"
                                   placeholder="p. ex., Amposta"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                            <p id="error-city" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="postal_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                Codi Postal
                            </label>
                            <input type="text" id="postal_code" name="postal_code"
                                   value="<?= htmlspecialchars($oldData['postal_code'] ?? '') ?>"
                                   placeholder="p. ex., 43870"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="operator" class="block text-sm font-semibold text-gray-700 mb-2">
                                Operador
                            </label>
                            <input type="text" id="operator" name="operator"
                                   value="<?= htmlspecialchars($oldData['operator'] ?? 'VoltiaCar') ?>"
                                   placeholder="p. ex., VoltiaCar"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                        </div>
                    </div>
                </div>
                
                <!-- Ubicación -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Ubicació (Coordenades GPS)</h2>
                    
                    <!-- Mapa interactivo -->
                    <div class="mb-4">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                            <p class="text-sm text-blue-700">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Fes clic al mapa per seleccionar la ubicació de l'estació de càrrega
                            </p>
                        </div>
                        <div id="location-map" style="height: 400px; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label for="latitude" class="block text-sm font-semibold text-gray-700 mb-2">
                                Latitud <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="latitude" name="latitude"
                                   value="<?= htmlspecialchars($oldData['latitude'] ?? '40.7089') ?>"
                                   step="0.000001" min="-90" max="90"
                                   required readonly
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                            <p class="text-xs text-gray-500 mt-1">Fes clic al mapa per establir</p>
                        </div>
                        
                        <div>
                            <label for="longitude" class="block text-sm font-semibold text-gray-700 mb-2">
                                Longitud <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="longitude" name="longitude"
                                   value="<?= htmlspecialchars($oldData['longitude'] ?? '0.5783') ?>"
                                   step="0.000001" min="-180" max="180"
                                   required readonly
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                            <p class="text-xs text-gray-500 mt-1">S'emplena automàticament amb el mapa</p>
                        </div>
                    </div>
                </div>
                
                <!-- Capacitat i Potència -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Capacitat i Potència</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <div>
                            <label for="total_slots" class="block text-sm font-semibold text-gray-700 mb-2">
                                Slots Totals <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="total_slots" name="total_slots" required
                                   value="<?= htmlspecialchars($oldData['total_slots'] ?? '4') ?>"
                                   min="1"
                                   onchange="syncAvailableSlots()"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                            <p id="error-total_slots" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="available_slots" class="block text-sm font-semibold text-gray-700 mb-2">
                                Slots Disponibles <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="available_slots" name="available_slots" required
                                   value="<?= htmlspecialchars($oldData['available_slots'] ?? '4') ?>"
                                   min="0"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                            <p id="error-available_slots" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="power_kw" class="block text-sm font-semibold text-gray-700 mb-2">
                                Potència
                            </label>
                            <input type="text" id="power_kw" name="power_kw"
                                   value="50 kW" readonly disabled
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                            <p class="text-xs text-gray-500 mt-1">Fixat a 50 kW</p>
                        </div>
                    </div>
                </div>

                <!-- Estat i Descripció -->
                <div class="pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Estat i Informació Addicional</h2>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                                Estat de l'Estació <span class="text-red-500">*</span>
                            </label>
                            <select id="status" name="status" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value="active" <?= ($oldData['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Activa</option>
                                <option value="maintenance" <?= ($oldData['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Manteniment</option>
                                <option value="out_of_service" <?= ($oldData['status'] ?? '') === 'out_of_service' ? 'selected' : '' ?>>Fora de Servei</option>
                            </select>
                            <p id="error-status" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                Descripció (Opcional)
                            </label>
                            <textarea id="description" name="description" rows="3"
                                      placeholder="Informació addicional sobre aquesta estació de càrrega..."
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"><?= htmlspecialchars($oldData['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="/admin/charging-stations" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all shadow-sm hover:shadow-md">
                        Cancel·lar
                    </a>
                    <button type="submit" class="px-6 py-3 bg-[#1565C0] hover:bg-blue-700 text-white rounded-lg font-semibold transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Crear Estació
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet CSS para mapas -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>

<!-- Leaflet JS para mapas -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
        crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtener coordenadas iniciales
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const initialLat = parseFloat(latInput.value) || 40.7089;
    const initialLng = parseFloat(lngInput.value) || 0.5783;
    
    // Crear el mapa centrado en Amposta
    const map = L.map('location-map').setView([initialLat, initialLng], 15);
    
    // Agregar tiles de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        minZoom: 10,
        maxZoom: 18
    }).addTo(map);
    
    // Crear un marcador inicial
    let marker = L.marker([initialLat, initialLng], {
        draggable: true
    }).addTo(map);
    
    // Actualizar coordenadas cuando se arrastra el marcador
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });
    
    // Agregar marcador al hacer clic en el mapa
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        // Mover el marcador a la nueva posición
        marker.setLatLng([lat, lng]);
        updateCoordinates(lat, lng);
        
        // Centrar el mapa en la nueva posición
        map.panTo([lat, lng]);
    });
    
    // Función para actualizar las coordenadas en los inputs
    function updateCoordinates(lat, lng) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        
        // Actualizar el popup del marcador
        marker.bindPopup(`
            <div style="text-align: center; padding: 5px;">
                <strong style="font-size: 1.1em;">Nova Ubicació</strong><br>
                <span style="font-size: 0.85em; color: #666;">
                    Lat: ${lat.toFixed(6)}<br>
                    Lng: ${lng.toFixed(6)}
                </span>
            </div>
        `).openPopup();
    }
    
    // Mostrar popup inicial
    updateCoordinates(initialLat, initialLng);
});

// Sync available slots with total slots
function syncAvailableSlots() {
    const totalSlots = document.getElementById('total_slots').value;
    const availableSlots = document.getElementById('available_slots');
    if (parseInt(availableSlots.value) > parseInt(totalSlots)) {
        availableSlots.value = totalSlots;
    }
    availableSlots.max = totalSlots;
}

// Form validation
const form = document.getElementById('createChargingStationForm');
const requiredMsg = '<?php echo addslashes(__('form.validations.required_field')); ?>';

form.addEventListener('submit', function(e) {
    let isValid = true;
    let firstError = null;

    // Required fields
    const requiredFields = ['name', 'address', 'city', 'total_slots', 'available_slots', 'status'];
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        const errorElement = document.getElementById(`error-${fieldName}`);
        
        if (!field.value.trim()) {
            isValid = false;
            errorElement.textContent = requiredMsg;
            errorElement.classList.remove('hidden');
            field.classList.add('border-red-500');
            if (!firstError) firstError = field;
        } else {
            errorElement.classList.add('hidden');
            field.classList.remove('border-red-500');
        }
    });

    if (!isValid) {
        e.preventDefault();
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
});

// Clear errors on input
['name', 'address', 'city', 'total_slots', 'available_slots', 'status'].forEach(fieldName => {
    const field = document.getElementById(fieldName);
    field.addEventListener('input', function() {
        const errorElement = document.getElementById(`error-${fieldName}`);
        errorElement.classList.add('hidden');
        field.classList.remove('border-red-500');
    });
});

</script>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
