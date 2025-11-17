<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('vehicle.page_title'); ?></title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

    <link rel="stylesheet" href="/assets/css/accessibility.css">
    <link rel="stylesheet" href="/assets/css/vehicle-claim-modal.css">

    <script src="/assets/css/tailwind.config.js"></script>
    <script>
        (function (d) {
            var s = d.createElement("script");
            s.setAttribute("data-account", "<?php echo getenv('USERWAY_ACCOUNT_ID'); ?>");
            s.src = "https://cdn.userway.org/widget.js";
            (d.body || d.head).appendChild(s);
        })(document);
    </script>
    <style>
        #map,
        #map-desktop {
            height: 100%;
            width: 100%;
            z-index: 1;
        }

        .leaflet-control-zoom {
            display: none !important;
        }
    </style>
</head>


<body class="flex items-center justify-center min-h-screen">

    <div class="w-full h-screen flex items-start justify-center">
        <div class="w-full h-full flex flex-col relative">
            <main class="flex-1 relative overflow-hidden">
                <div id="map" class="absolute inset-0 w-full h-full"></div>
                <header class="relative flex items-center justify-center p-10 shadow-sm flex-shrink-0 z-20">
                    <div class="absolute left-4">
                        <img src="/assets/images/logo.png" class="h-14 w-14 bg-gray-50 rounded-full object-cover border-2 border-gray-200"
                            alt="<?php echo __(key: 'vehicle.logo_alt'); ?>">
                    </div>
                </header>

                <div id="vehicles-drawer"
                    class="absolute top-0 right-0 h-full w-1/2 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-10 flex flex-col">
                    <div class="flex justify-between items-center p-4 border-b border-gray-200 flex-shrink-0">
                        <h2 class="text-lg font-bold text-gray-900"><?php echo __(key: 'vehicle.nearby_vehicles'); ?>
                        </h2>
                        <div class="flex items-center gap-2">
                            <button id="toggle-vehicles"
                                class="flex items-center bg-gray-200 p-2 rounded-lg text-gray-700 hover:bg-gray-300 transition-colors duration-300">
                                <img src="/assets/images/discapacidad.png"
                                    alt="<?php echo __('vehicle.accessible_vehicles_alt'); ?>" class="h-5 w-5">
                            </button>
                            <button id="close-drawer" class="text-gray-600 hover:text-gray-900">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Listas de vehículos con scroll -->
                    <div class="flex-1 overflow-y-auto px-3 py-2"
                        style="overflow-y: scroll; -webkit-overflow-scrolling: touch;">
                        <ul id="normal-list" class="space-y-3">
                            <li class="bg-gray-100 p-3 rounded-lg shadow-sm text-center text-gray-500 text-sm">
                                <?php echo __('vehicle.loading_vehicles'); ?>
                            </li>
                        </ul>

                        <ul id="special-list" class="space-y-3 hidden">
                            <li class="bg-gray-100 p-3 rounded-lg shadow-sm text-center text-gray-500 text-sm">
                                <?php echo __('vehicle.loading_accessible_vehicles'); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Botón para abrir modal de reserva -->
    <button id="reserve-btn" class="fixed bottom-24 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg shadow-lg transition-all duration-300 z-40 flex items-center justify-center font-semibold">
        <p class="uppercase">    
             Reserva  
        </p>
    </button>

    <!-- Modal de reserva de vehículos -->
    <div id="reserve-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-11/12 max-h-[80vh] overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Reservar Vehicle</h2>
                <button id="close-reserve-modal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-car- text-xl mb-1"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto flex-1">
                <div id="reserve-vehicles-list" class="space-y-3">
                    <div class="text-center text-gray-500">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-2">Carregant vehicles...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div id="claim-modal"
        class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 opacity-0 invisible transition-all duration-300 ease-in-out"
        style="display:none;">
        <div
            class="bg-white rounded-xl shadow-2xl max-w-md w-11/12 max-h-[90vh] overflow-y-auto transform scale-90 translate-y-4 transition-all duration-300 ease-in-out">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                    <span class="text-2xl"><i class="fas fa-car text-sm"></i></span>
                    <span><?php echo __('vehicle.confirm_claim'); ?></span>
                </h2>
                <button
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors text-2xl leading-none"
                    id="claim-modal-close">✕</button>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-5" id="vehicle-info">
                    <?php echo __('vehicle.loading'); ?>
                </div>

                <!-- Selector de tiempo de alquiler -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?php echo __('vehicle.rental_duration'); ?></label>
                    <select id="rental-duration" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <option value="30"><?php echo __('vehicle.rental_30min'); ?></option>
                        <option value="60"><?php echo __('vehicle.rental_1h'); ?></option>
                        <option value="90"><?php echo __('vehicle.rental_1h30'); ?></option>
                        <option value="120"><?php echo __('vehicle.rental_2h'); ?></option>
                        <option value="180"><?php echo __('vehicle.rental_3h'); ?></option>
                        <option value="240"><?php echo __('vehicle.rental_4h'); ?></option>
                        <option value="360"><?php echo __('vehicle.rental_6h'); ?></option>
                        <option value="480"><?php echo __('vehicle.rental_8h'); ?></option>
                        <option value="720"><?php echo __('vehicle.rental_12h'); ?></option>
                        <option value="1440"><?php echo __('vehicle.rental_24h'); ?></option>
                    </select>
                </div>

                <!-- Resumen de costos -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-5">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700"><?php echo __('vehicle.price_per_minute'); ?></span>
                            <span class="font-semibold text-gray-900" id="price-per-minute">€0.00/min</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700"><?php echo __('vehicle.selected_time'); ?></span>
                            <span class="font-semibold text-gray-900" id="selected-duration">30 minuts</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700"><?php echo __('vehicle.time_cost'); ?></span>
                            <span class="font-semibold text-gray-900" id="time-cost">€0.00</span>
                        </div>
                        <div class="border-t border-blue-300 pt-2 mt-2"></div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700"><?php echo __('vehicle.unlock_fee'); ?></span>
                            <span class="font-semibold text-gray-900">€0.50</span>
                        </div>
                        <div class="border-t-2 border-blue-400 pt-2 mt-2"></div>
                        <div class="flex justify-between">
                            <span class="font-bold text-gray-900"><?php echo __('vehicle.total'); ?></span>
                            <span class="font-black text-blue-600 text-xl" id="total-cost">€0.50</span>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4 mb-5 flex items-start gap-3">
                    <div class="text-2xl flex-shrink-0">⚠️</div>
                    <div class="flex-1">
                        <div class="font-bold text-orange-800 mb-1 text-base">
                            <?php echo __('vehicle.unlock_cost'); ?>
                        </div>
                        <div class="text-orange-700 text-sm leading-relaxed">
                            <?php echo __('vehicle.payment_info'); ?>
                        </div>
                    </div>
                </div>

                <p class="text-center text-gray-500 text-sm mt-4">
                    <?php echo __('vehicle.terms_acceptance'); ?>
                </p>
            </div>
            <div class="p-5 border-t border-gray-200 flex gap-3">
                <button
                    class="flex-1 py-3 px-6 rounded-lg font-bold text-base border-none cursor-pointer bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                    id="claim-modal-cancel"><?php echo __('vehicle.cancel'); ?></button>
                <button
                    class="flex-1 py-3 px-6 rounded-lg font-bold text-base border-none cursor-pointer bg-blue-600 text-white hover:bg-blue-700 transition-colors"
                    id="claim-modal-confirm"><?php echo __('vehicle.accept_and_claim'); ?></button>
            </div>
        </div>
    </div>

    <div id="vehicle-details-overlay"
        class="fixed inset-0 bg-black bg-opacity-50 z-40 transition-opacity duration-300 opacity-0"
        style="display:none;"></div>
    <div id="vehicle-details-modal"
        class="fixed left-0 right-0 bottom-0 z-50 transform translate-y-full transition-transform duration-300"
        style="display:none; max-height: 80vh;">
        <div class="max-w-3xl mx-auto px-4 pb-20">
            <div class="bg-white rounded-t-2xl shadow-2xl flex flex-col" style="max-height: 80vh;">
                <div class="overflow-y-auto flex-1">
                    <div class="vehicle-details-content"></div>

                    <div class="px-4 pb-4">
                        <h4 class="text-sm font-semibold mt-3 mb-2 sticky top-0 bg-white py-2">Vehicles propers</h4>
                        <ul id="nearby-vehicles-list" class="divide-y bg-gray-100 rounded-xl divide-gray-100 mb-4"></ul>
                    </div>
                </div>

                <div class="p-3 border-t text-center bg-white flex-shrink-0">
                    <button id="close-vehicle-details"
                        class="text-gray-500 hover:text-gray-700 font-medium">Tancar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/toast.js"></script>
    <script src="/assets/js/confirm-modal.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/vehicles.js"></script>
    <script src="/assets/js/vehicle-claim-modal.js"></script>
    <script src="/assets/js/localitzar-vehicle.js"></script>

    <script>
        // Helper function to calculate distance
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the Earth in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }
        
        // Modal de reserva
        document.getElementById('reserve-btn').addEventListener('click', function() {
            const modal = document.getElementById('reserve-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            loadAllVehiclesForReserve();
        });

        document.getElementById('close-reserve-modal').addEventListener('click', function() {
            const modal = document.getElementById('reserve-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        // Cerrar al hacer clic fuera del modal
        document.getElementById('reserve-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                this.classList.remove('flex');
            }
        });

        async function loadAllVehiclesForReserve() {
            const container = document.getElementById('reserve-vehicles-list');
            
            try {
                const response = await fetch('/api/vehicles');
                const data = await response.json();
                
                if (data.success && data.vehicles) {
                    // Get user location
                    let userLocation = null;
                    if (window.VehicleLocator && window.VehicleLocator.userLocation) {
                        userLocation = window.VehicleLocator.userLocation;
                    }
                    
                    // Calculate distances and sort
                    const vehiclesWithDistance = data.vehicles.map(vehicle => {
                        if (userLocation && vehicle.location) {
                            const distance = calculateDistance(
                                userLocation.lat, 
                                userLocation.lng, 
                                vehicle.location.lat, 
                                vehicle.location.lng
                            );
                            return { ...vehicle, distance };
                        }
                        return vehicle;
                    });
                    
                    // Sort by distance
                    vehiclesWithDistance.sort((a, b) => {
                        if (a.distance && b.distance) return a.distance - b.distance;
                        if (a.distance) return -1;
                        if (b.distance) return 1;
                        return 0;
                    });
                    
                    container.innerHTML = '';
                    
                    vehiclesWithDistance.forEach(vehicle => {
                        const vehicleCard = document.createElement('div');
                        vehicleCard.className = 'bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer';
                        vehicleCard.onclick = function() {
                            // Cerrar modal de reserva
                            document.getElementById('reserve-modal').classList.add('hidden');
                            document.getElementById('reserve-modal').classList.remove('flex');
                            // Mostrar detalles del vehículo
                            if (window.VehicleLocator) {
                                window.VehicleLocator.showVehicleDetails(vehicle.id);
                            }
                        };
                        
                        const distanceText = vehicle.distance 
                            ? `<p class="text-xs text-gray-400 flex items-center"><i class="fas fa-map-marker-alt mr-1"></i>${vehicle.distance.toFixed(2)} km</p>`
                            : '';
                        
                        vehicleCard.innerHTML = `
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="${vehicle.image_url}" alt="${vehicle.model}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">${vehicle.model || 'Vehicle'}</h3>
                                        <p class="text-sm text-gray-500">${vehicle.plate || vehicle.license_plate || 'N/A'}</p>
                                        <p class="text-xs text-gray-400">${vehicle.battery || 0}% bateria</p>
                                        ${distanceText}
                                    </div>
                                </div>
                                <div class="text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        `;
                        container.appendChild(vehicleCard);
                    });
                } else {
                    container.innerHTML = '<p class="text-center text-gray-500">No hi ha vehicles disponibles</p>';
                }
            } catch (error) {
                console.error('Error loading vehicles:', error);
                container.innerHTML = '<p class="text-center text-red-500">Error carregant vehicles</p>';
            }
        }
    </script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>

</html>