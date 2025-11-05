<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('vehicle.page_title'); ?></title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/accessibility.css">
    <link rel="stylesheet" href="/assets/css/localitzar-vehicle.css">
    <link rel="stylesheet" href="/assets/css/vehicle-claim-modal.css">

    <script src="/assets/css/tailwind.config.js"></script>
    <script>
        (function (d) {
            var s = d.createElement("script");
            s.setAttribute("data-account", "RrwQjeYdrh");
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

    <div class="mobile-view md:hidden w-full h-screen flex items-start justify-center">
        <div class="w-full h-full flex flex-col relative">
            <main class="flex-1 relative overflow-hidden">
                <div id="map" class="absolute inset-0 w-full h-full"></div>
                <header class="relative flex items-center justify-center p-10 shadow-sm flex-shrink-0 z-20">
                    <div class="absolute left-4">
                        <img src="/assets/images/logo.png" class="h-14 w-14 bg-gray-50 rounded-full object-cover"
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div id="claim-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 opacity-0 invisible transition-all duration-300 ease-in-out" style="display:none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-11/12 max-h-[90vh] overflow-y-auto transform scale-90 translate-y-4 transition-all duration-300 ease-in-out">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                    <span class="text-2xl"><i class="fas fa-car text-sm"></i></span>
                    <span><?php echo __('vehicle.confirm_claim'); ?></span>
                </h2>
                <button class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors text-2xl leading-none" id="claim-modal-close">✕</button>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-5" id="vehicle-info">
                    <?php echo __('vehicle.loading'); ?>
                </div>
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4 mb-5 flex items-start gap-3">
                    <div class="text-2xl flex-shrink-0">⚠️</div>
                    <div class="flex-1">
                        <div class="font-bold text-orange-800 mb-1 text-base">
                            <?php echo __('vehicle.unlock_cost'); ?>
                        </div>
                        <div class="text-orange-700 text-sm leading-relaxed">
                            <?php echo __('vehicle.unlock_fee_warning'); ?>
                        </div>
                    </div>
                </div>
                <div class="text-3xl font-black text-blue-600 text-center my-5">0.50€</div>
                <p class="text-center text-gray-500 text-sm mt-4">
                    <?php echo __('vehicle.terms_acceptance'); ?>
                </p>
            </div>
            <div class="p-5 border-t border-gray-200 flex gap-3">
                <button class="flex-1 py-3 px-6 rounded-lg font-bold text-base border-none cursor-pointer bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" id="claim-modal-cancel"><?php echo __('vehicle.cancel'); ?></button>
                <button class="flex-1 py-3 px-6 rounded-lg font-bold text-base border-none cursor-pointer bg-blue-600 text-white hover:bg-blue-700 transition-colors" id="claim-modal-confirm"><?php echo __('vehicle.accept_and_claim'); ?></button>
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
            <div class="bg-white rounded-t-2xl shadow-2xl overflow-hidden flex flex-col" style="max-height: 80vh;">
                <div class="vehicle-details-content flex-shrink-0"></div>

                <div class="px-4 pb-4 overflow-y-auto flex-1">
                    <h4 class="text-sm font-semibold mt-3 mb-2 sticky top-0 bg-white py-2">Vehicles propers</h4>
                    <ul id="nearby-vehicles-list" class="divide-y bg-gray-200 rounded-xl divide-gray-100 mb-4"></ul>
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
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/vehicles.js"></script>
    <script src="/assets/js/vehicle-claim-modal.js"></script>
    <script src="/assets/js/localitzar-vehicle.js"></script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>

</html>