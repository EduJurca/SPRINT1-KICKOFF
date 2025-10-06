<?php
/**
 * Vehicle Search Page
 * Advanced search with filters for vehicle type, price, location, battery, and accessibility
 */

$pageTitle = 'VoltiaCar - Cercar Vehicles';
$additionalCSS = [];

// Include header
include_once __DIR__ . '/../../php/components/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                <?php echo $lang->get('cercar_vehicles', 'Cercar Vehicles'); ?>
            </h1>
            <p class="text-gray-600 mt-2">
                <?php echo $lang->get('utilitza_filtres_trobar', 'Utilitza els filtres per trobar el vehicle perfecte'); ?>
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Search Filters Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">
                        <?php echo $lang->get('filtres', 'Filtres'); ?>
                    </h2>
                    
                    <form id="search-form" class="space-y-6">
                        <!-- Vehicle Type Filter -->
                        <div>
                            <label for="vehicle_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo $lang->get('tipus_vehicle', 'Tipus de Vehicle'); ?>
                            </label>
                            <select 
                                id="vehicle_type" 
                                name="vehicle_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            >
                                <option value=""><?php echo $lang->get('tots', 'Tots'); ?></option>
                                <option value="electric"><?php echo $lang->get('electric', 'Elèctric'); ?></option>
                                <option value="hybrid"><?php echo $lang->get('hibrid', 'Híbrid'); ?></option>
                                <option value="compact"><?php echo $lang->get('compacte', 'Compacte'); ?></option>
                                <option value="suv"><?php echo $lang->get('suv', 'SUV'); ?></option>
                            </select>
                        </div>

                        <!-- Price Range Filter -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo $lang->get('rang_preu', 'Rang de Preu (€/min)'); ?>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <input 
                                        type="number" 
                                        id="min_price" 
                                        name="min_price"
                                        placeholder="Min"
                                        step="0.01"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                                    >
                                </div>
                                <div>
                                    <input 
                                        type="number" 
                                        id="max_price" 
                                        name="max_price"
                                        placeholder="Max"
                                        step="0.01"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo $lang->get('ubicacio', 'Ubicació'); ?>
                            </label>
                            <button 
                                type="button" 
                                id="use-my-location"
                                class="w-full px-3 py-2 bg-blue-50 border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium"
                            >
                                📍 <?php echo $lang->get('usar_ubicacio_actual', 'Usar la meva ubicació'); ?>
                            </button>
                            <input type="hidden" id="lat" name="lat">
                            <input type="hidden" id="lng" name="lng">
                            <div id="location-status" class="mt-2 text-xs text-gray-600"></div>
                        </div>

                        <!-- Max Distance Filter -->
                        <div>
                            <label for="max_distance" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo $lang->get('distancia_maxima', 'Distància Màxima (km)'); ?>
                            </label>
                            <input 
                                type="number" 
                                id="max_distance" 
                                name="max_distance"
                                placeholder="5"
                                step="0.5"
                                min="0.5"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            >
                        </div>

                        <!-- Date/Time Range Filter -->
                        <div>
                            <label for="start_datetime" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo $lang->get('data_inici', 'Data d\'Inici'); ?>
                            </label>
                            <input 
                                type="datetime-local" 
                                id="start_datetime" 
                                name="start_datetime"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green text-sm"
                            >
                        </div>

                        <div>
                            <label for="end_datetime" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo $lang->get('data_fi', 'Data de Fi'); ?>
                            </label>
                            <input 
                                type="datetime-local" 
                                id="end_datetime" 
                                name="end_datetime"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green text-sm"
                            >
                        </div>

                        <!-- Battery Level Filter -->
                        <div>
                            <label for="min_battery" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo $lang->get('bateria_minima', 'Bateria Mínima (%)'); ?>
                            </label>
                            <input 
                                type="number" 
                                id="min_battery" 
                                name="min_battery"
                                placeholder="0"
                                min="0"
                                max="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            >
                        </div>

                        <!-- Accessibility Filter -->
                        <div>
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="accessibility" 
                                    name="accessibility"
                                    value="true"
                                    class="mr-3 h-5 w-5 text-primary-green focus:ring-primary-green border-gray-300 rounded"
                                >
                                <span class="text-sm font-semibold text-gray-700">
                                    ♿ <?php echo $lang->get('nomes_accessibles', 'Només vehicles accessibles'); ?>
                                </span>
                            </label>
                        </div>

                        <!-- Sort By Filter -->
                        <div>
                            <label for="sort_by" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo $lang->get('ordenar_per', 'Ordenar per'); ?>
                            </label>
                            <select 
                                id="sort_by" 
                                name="sort_by"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            >
                                <option value="battery"><?php echo $lang->get('bateria', 'Bateria'); ?></option>
                                <option value="price"><?php echo $lang->get('preu', 'Preu'); ?></option>
                                <option value="distance"><?php echo $lang->get('distancia', 'Distància'); ?></option>
                            </select>
                        </div>

                        <!-- Search Button -->
                        <button 
                            type="submit" 
                            class="w-full bg-primary-green hover:bg-primary-green-dark text-white font-bold py-3 rounded-lg transition-colors duration-300 shadow-md hover:shadow-lg"
                        >
                            🔍 <?php echo $lang->get('cercar', 'Cercar'); ?>
                        </button>

                        <!-- Reset Button -->
                        <button 
                            type="reset" 
                            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 rounded-lg transition-colors duration-300"
                        >
                            <?php echo $lang->get('netejar_filtres', 'Netejar Filtres'); ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Search Results Section -->
            <div class="lg:col-span-3">
                <div class="mb-6 flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <?php echo $lang->get('resultats', 'Resultats'); ?>
                    </h2>
                    <div id="results-count" class="text-gray-600 font-medium"></div>
                </div>

                <!-- Results Container -->
                <div id="search-results">
                    <div class="bg-white rounded-lg shadow-md p-12 text-center">
                        <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">
                            <?php echo $lang->get('utilitza_filtres_cercar', 'Utilitza els filtres per cercar vehicles'); ?>
                        </h3>
                        <p class="text-gray-500">
                            <?php echo $lang->get('selecciona_criteris_cerca', 'Selecciona els teus criteris de cerca i fes clic a "Cercar"'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include booking.js for search functionality -->
<script src="../../js/booking.js"></script>

<!-- Additional Search Page Scripts -->
<script>
// Geolocation functionality
document.getElementById('use-my-location')?.addEventListener('click', function() {
    const statusDiv = document.getElementById('location-status');
    const button = this;
    
    button.disabled = true;
    button.innerHTML = '⏳ <?php echo $lang->get('obtenint_ubicacio', 'Obtenint ubicació...'); ?>';
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('lat').value = position.coords.latitude;
                document.getElementById('lng').value = position.coords.longitude;
                
                statusDiv.innerHTML = '✓ <?php echo $lang->get('ubicacio_obtinguda', 'Ubicació obtinguda correctament'); ?>';
                statusDiv.className = 'mt-2 text-xs text-green-600';
                
                button.disabled = false;
                button.innerHTML = '✓ <?php echo $lang->get('ubicacio_configurada', 'Ubicació configurada'); ?>';
                button.className = 'w-full px-3 py-2 bg-green-50 border border-green-300 text-green-700 rounded-lg text-sm font-medium';
            },
            function(error) {
                statusDiv.innerHTML = '✗ <?php echo $lang->get('error_ubicacio', 'Error obtenint la ubicació'); ?>';
                statusDiv.className = 'mt-2 text-xs text-red-600';
                
                button.disabled = false;
                button.innerHTML = '📍 <?php echo $lang->get('usar_ubicacio_actual', 'Usar la meva ubicació'); ?>';
            }
        );
    } else {
        statusDiv.innerHTML = '✗ <?php echo $lang->get('geolocalitzacio_no_suportada', 'Geolocalització no suportada'); ?>';
        statusDiv.className = 'mt-2 text-xs text-red-600';
        
        button.disabled = false;
        button.innerHTML = '📍 <?php echo $lang->get('usar_ubicacio_actual', 'Usar la meva ubicació'); ?>';
    }
});

// Update results count
function updateResultsCount(count) {
    const countDiv = document.getElementById('results-count');
    if (countDiv) {
        if (count === 0) {
            countDiv.textContent = '<?php echo $lang->get('cap_resultat', 'Cap resultat'); ?>';
        } else if (count === 1) {
            countDiv.textContent = '1 <?php echo $lang->get('vehicle_trobat', 'vehicle trobat'); ?>';
        } else {
            countDiv.textContent = count + ' <?php echo $lang->get('vehicles_trobats', 'vehicles trobats'); ?>';
        }
    }
}

// Override the displaySearchResults to update count
const originalDisplaySearchResults = window.bookingManager.displaySearchResults.bind(window.bookingManager);
window.bookingManager.displaySearchResults = function(vehicles) {
    updateResultsCount(vehicles.length);
    originalDisplaySearchResults(vehicles);
};

// Language Switcher Script
document.getElementById('languageSwitcher')?.addEventListener('change', function() {
    fetch('../../php/api/set-language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ lang: this.value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
});

// Logout functionality
document.getElementById('logoutBtn')?.addEventListener('click', function() {
    fetch('../../php/api/logout.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '../../index.php';
        }
    });
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../../php/components/footer.php';
?>
