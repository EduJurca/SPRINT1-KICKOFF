<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('vehicle.page_title'); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Leaflet CSS for maps -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >
    
    <!-- Custom CSS (use /assets paths so server serves correctly) -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/accessibility.css">
    <link rel="stylesheet" href="/assets/css/localitzar-vehicle.css">
    <link rel="stylesheet" href="/assets/css/vehicle-claim-modal.css">
    
    <!-- Tailwind Config -->
    <script src="/assets/css/tailwind.config.js"></script>
    
    <style>
        /* Map container styling */
        #map, #map-desktop {
            height: 100%;
            width: 100%;
            border-radius: 0.5rem;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    
    <div class="mobile-view md:hidden w-full h-screen flex items-start justify-center bg-gray-100">
        <div class="bg-white w-full h-full flex flex-col relative">
            <header class="relative flex items-center justify-center p-4 bg-white shadow-sm flex-shrink-0 z-20">
                <div class="absolute left-4">
                    <a href="/dashboard" class="text-[#1565C0] text-sm font-semibold"><?php echo __('vehicle.back'); ?></a>
                </div>
                <h1 class="text-lg font-bold text-gray-900 text-center leading-tight"><?php echo __('vehicle.locate_vehicles'); ?></h1>
                <div class="absolute right-4">
                    <img src="/assets/images/logo.png" alt="<?php echo __('vehicle.logo_alt'); ?>" class="h-8 w-8">
                </div>
            </header>

            <!-- Contenido principal -->
            <main class="flex-1 relative overflow-hidden">
                <!-- Mapa de vehículos disponibles (pantalla completa) -->
                <div id="map" class="absolute inset-0 w-full h-full"></div>

                <!-- Botón flotante para abrir el panel -->
                <button id="toggle-drawer" class="absolute bottom-6 right-6 z-10 bg-[#1565C0] text-white p-4 rounded-full shadow-lg hover:bg-[#0D47A1] transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Panel deslizante lateral derecho -->
                <div id="vehicles-drawer" class="absolute top-0 right-0 h-full w-1/2 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-10 flex flex-col">
                    <!-- Header del drawer -->
                    <div class="flex justify-between items-center p-4 border-b border-gray-200 flex-shrink-0">
                        <h2 class="text-lg font-bold text-gray-900"><?php echo __('vehicle.nearby_vehicles'); ?></h2>
                        <div class="flex items-center gap-2">
                            <button id="toggle-vehicles" class="flex items-center bg-gray-200 p-2 rounded-lg text-gray-700 hover:bg-gray-300 transition-colors duration-300">
                                <img src="/assets/images/discapacidad.png" alt="<?php echo __('vehicle.accessible_vehicles_alt'); ?>" class="h-5 w-5">
                            </button>
                            <button id="close-drawer" class="text-gray-600 hover:text-gray-900">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Listas de vehículos con scroll -->
                    <div class="flex-1 overflow-y-auto px-3 py-2" style="overflow-y: scroll; -webkit-overflow-scrolling: touch;">
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

    <div class="desktop-view hidden md:flex bg-white p-8 rounded-2xl shadow-inner w-full max-w-6xl mx-auto flex md:flex-row space-x-4 relative min-h-[667px]">
        <!-- Map container on the left -->
        <div class="flex-shrink-0 w-1/2 flex flex-col">
            <div class="flex items-center space-x-6 mb-6">
                <img
src="/assets/images/logo.png"
alt="<?php echo __('vehicle.logo_app_alt'); ?>"
class="h-12 w-12"
>
                <a href="/dashboard" class="text-[#1565C0] text-lg font-semibold hover:underline"><?php echo __('vehicle.back'); ?></a>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-6"><?php echo __('vehicle.locate_vehicles_title'); ?></h2>
            <div id="map-desktop" class="rounded-lg shadow-inner flex-shrink-0 w-full min-h-[400px] max-h-[400px]"></div>
        </div>
        <!-- Vehicles list container on the right -->
        <div class="flex-1 flex flex-col min-h-0">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900"><?php echo __('vehicle.nearby_vehicles'); ?></h2>
                <button id="toggle-vehicles-2" class="flex items-center bg-gray-200 p-2 rounded-lg text-gray-700 hover:bg-gray-300 transition-colors duration-300">
                    <img
src="/assets/images/discapacidad.png"
alt="<?php echo __('vehicle.accessible_vehicles_alt'); ?>"
class="h-6 w-6"
>
                </button>
            </div>
            <!-- Listas de vehículos con scroll -->
            <div class="flex-1 min-h-0 overflow-y-auto no-scrollbar">
                <ul id="normal-list-2" class="space-y-4">
                    <li class="bg-gray-100 p-4 rounded-lg shadow-sm text-center text-gray-500">
                        <?php echo __('vehicle.loading_vehicles'); ?>
                    </li>
                </ul>
                <ul id="special-list-2" class="space-y-4 hidden">
                    <li class="bg-gray-100 p-4 rounded-lg shadow-sm text-center text-gray-500">
                        <?php echo __('vehicle.loading_accessible_vehicles'); ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <!-- Vehicle Claim Modal (hidden by default, shown by JS when claiming) -->
    <div id="claim-modal" class="claim-modal-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);display:none;align-items:center;justify-content:center;z-index:9999;opacity:0;visibility:hidden;transition:opacity 0.3s ease,visibility 0.3s ease;">
        <div class="claim-modal-container" style="background:white;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.2);max-width:500px;width:90%;max-height:90vh;overflow-y:auto;transform:scale(0.9) translateY(20px);transition:transform 0.3s ease;">
            <div class="claim-modal-header" style="padding:24px;border-bottom:1px solid #E5E7EB;display:flex;align-items:center;justify-content:space-between;">
                <h2 class="claim-modal-title" style="font-size:20px;font-weight:700;color:#1F2937;display:flex;align-items:center;gap:12px;">
                    <span>🚗</span>
                    <span><?php echo __('vehicle.confirm_claim'); ?></span>
                </h2>
                <button class="claim-modal-close" id="claim-modal-close" style="background:none;border:none;color:#6B7280;cursor:pointer;padding:8px;border-radius:8px;font-size:24px;line-height:1;">✕</button>
            </div>
            <div class="claim-modal-content" style="padding:24px;">
                <div class="vehicle-info-card" id="vehicle-info" style="background:#F9FAFB;border-radius:12px;padding:16px;margin-bottom:20px;">
                    <?php echo __('vehicle.loading'); ?>
                </div>
                <div class="charge-warning" style="background:#FEF3C7;border:2px solid #F59E0B;border-radius:12px;padding:16px;margin-bottom:20px;display:flex;align-items:start;gap:12px;">
                    <div class="charge-warning-icon" style="font-size:24px;flex-shrink:0;">⚠️</div>
                    <div class="charge-warning-content" style="flex:1;">
                        <div class="charge-warning-title" style="font-weight:700;color:#92400E;margin-bottom:4px;font-size:16px;"><?php echo __('vehicle.unlock_cost'); ?></div>
                        <div class="charge-warning-text" style="color:#78350F;font-size:14px;line-height:1.5;"><?php echo __('vehicle.unlock_fee_warning'); ?></div>
                    </div>
                </div>
                <div class="charge-amount" style="font-size:28px;font-weight:800;color:#1565C0;text-align:center;margin:20px 0;">0.50€</div>
                <p style="text-align:center;color:#6B7280;font-size:14px;margin-top:16px;"><?php echo __('vehicle.terms_acceptance'); ?></p>
            </div>
            <div class="claim-modal-footer" style="padding:20px 24px;border-top:1px solid #E5E7EB;display:flex;gap:12px;">
                <button class="claim-modal-button claim-modal-button-cancel" id="claim-modal-cancel" style="flex:1;padding:12px 24px;border-radius:12px;font-weight:700;font-size:16px;border:none;cursor:pointer;background-color:#F3F4F6;color:#4B5563;"><?php echo __('vehicle.cancel'); ?></button>
                <button class="claim-modal-button claim-modal-button-confirm" id="claim-modal-confirm" style="flex:1;padding:12px 24px;border-radius:12px;font-weight:700;font-size:16px;border:none;cursor:pointer;background-color:#1565C0;color:white;"><?php echo __('vehicle.accept_and_claim'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- Application Scripts -->
    <script src="/assets/js/toast.js?v=11"></script>
    <script src="/assets/js/confirm-modal.js?v=11"></script>
    <script src="/assets/js/main.js?v=11"></script>
    <script src="/assets/js/auth.js?v=11"></script>
    <script src="/assets/js/vehicles.js?v=11"></script>
    <script src="/assets/js/vehicle-claim-modal.js?v=11"></script>
    <script src="/assets/js/localitzar-vehicle.js?v=11"></script>
</body>
</html>