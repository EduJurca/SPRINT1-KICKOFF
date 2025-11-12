<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('admin.page_title'); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../css/custom.css">
    <link rel="stylesheet" href="../../css/administrar-vehicle.css">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="mobile-view md:hidden">
        <div class="bg-white p-5 rounded-2xl shadow-inner w-full max-w-sm flex flex-col relative">
            <header class="grid grid-cols-3 items-center mb-6 w-full">
                <div class="text-left">
                  <a href="/dashboard" class="text-[#1565C0] font-semibold hover:underline"><?php echo __('admin.back'); ?></a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 text-center"><?php echo __('admin.control_vehicle'); ?></h1>
                <div class="flex justify-end items-center gap-2">
                    <button id="release-vehicle-btn-mobile" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded-lg text-xs transition-colors" title="Finalitzar Reserva">
                        <?php echo __('admin.finish'); ?>
                    </button>
                    <img src="/assets/images/logo.png" alt="<?php echo __('admin.logo_alt'); ?>" class="h-10 w-10">
                </div>
            </header>

            <!-- Parte superior fixa: Estat del Servei y Bateria en fila -->
            <div class="mb-4 sticky top-0 z-10 bg-white p-2 rounded-lg shadow-sm flex flex-row justify-between items-center gap-2">
                <div class="pushable green flex-1">
                    <span class="shadow"></span>
                    <span class="edge"></span>
                    <span class="front p-2 flex flex-col items-center text-sm">
                        <span class="text-base font-semibold"><?php echo __('admin.status'); ?></span>
                        <span class="text-white text-sm font-semibold" data-vehicle-status><?php echo __('admin.operational'); ?></span>
                    </span>
                </div>
                <div class="pushable flex-1">
                    <span class="shadow"></span>
                    <span class="edge"
                        style="background: linear-gradient(to left, hsl(200, 70%, 40%) 0%, hsl(200, 70%, 50%) 8%, hsl(200, 70%, 50%) 92%, hsl(200, 70%, 40%) 100%);"></span>
                    <span class="front p-2 flex flex-col items-center text-sm" style="background: hsl(200, 70%, 50%);">
                        <span class="text-base font-semibold"><?php echo __('admin.battery'); ?></span>
                        <div class="w-full bg-gray-300 rounded-lg h-6 overflow-hidden mt-1">
                            <div class="h-full" data-battery-bar style="width: 80%; background-color: #00C853;">
                                <div class="h-full flex items-center justify-center text-white font-bold text-sm" data-battery-text>80%</div>
                            </div>
                        </div>
                    </span>
                </div>
            </div>

            <!-- Contenido desplazable horizontal -->
            <main class="flex-1 overflow-x-auto no-scrollbar flex snap-x snap-mandatory">
                <section class="flex-none w-full px-2 sm:px-4 flex flex-col justify-center snap-start">
                    <h2 class="text-xl font-bold text-gray-900 mb-4"><?php echo __('admin.vehicle_controls'); ?></h2>
                    <div class="grid grid-cols-2 gap-4 sm:gap-6 p-2">
                        <button class="pushable yellow h-32" data-control="engine">
                            <span class="shadow"></span>
                            <span class="edge"></span>
                            <span class="front flex-1 flex flex-col items-center justify-center text-base p-2">
                                <img src="/assets/images/engegar.png" alt="<?php echo __('admin.start_stop_alt'); ?>" class="h-12 w-12 mb-2">
                                <span><?php echo __('admin.start_stop'); ?></span>
                            </span>
                        </button>
                        <button class="pushable h-32" data-control="horn">
                            <span class="shadow"></span>
                            <span class="edge"></span>
                            <span class="front flex-1 flex flex-col items-center justify-center text-base p-2">
                                <img src="/assets/images/claxon.png" alt="<?php echo __('admin.activate_horn'); ?>" class="h-12 w-12 mb-2">
                                <?php echo __('admin.activate_horn'); ?>
                            </span>
                        </button>
                        <button class="pushable h-32" data-control="lights">
                            <span class="shadow"></span>
                            <span class="edge"></span>
                            <span class="front flex-1 flex flex-col items-center justify-center text-base p-2">
                                <img src="/assets/images/llums.png" alt="<?php echo __('admin.activate_lights'); ?>" class="h-12 w-12 mb-2">
                                <?php echo __('admin.activate_lights'); ?>
                            </span>
                        </button>
                        <button class="pushable h-32" data-control="doors">
                            <span class="shadow"></span>
                            <span class="edge"></span>
                            <span class="front flex-1 flex flex-col items-center justify-center text-base p-2">
                                <img src="/assets/images/portes.png" alt="<?php echo __('admin.lock_doors'); ?>" class="h-12 w-12 mb-2">
                                <?php echo __('admin.lock_doors'); ?>
                            </span>
                        </button>
                    </div>
                </section>

                <section class="flex-none w-full px-2 sm:px-4 flex flex-col justify-center snap-start">
                    <h2 class="text-xl font-bold text-gray-900 mb-4"><?php echo __('admin.vehicle_info'); ?></h2>
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center space-x-3">
                                <img src="/assets/images/bateria.png" alt="<?php echo __('admin.battery_alt'); ?>" class="h-8 w-8">
                                <div>
                                    <p class="text-sm text-gray-600"><?php echo __('admin.battery_level'); ?></p>
                                    <p class="text-lg font-semibold text-gray-900" id="battery-level">85%</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <img src="/assets/images/velocitat.png" alt="<?php echo __('admin.speed_alt'); ?>" class="h-8 w-8">
                                <div>
                                    <p class="text-sm text-gray-600"><?php echo __('admin.current_speed'); ?></p>
                                    <p class="text-lg font-semibold text-gray-900" id="current-speed">0 km/h</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <img src="/assets/images/distancia.png" alt="<?php echo __('admin.distance_alt'); ?>" class="h-8 w-8">
                                <div>
                                    <p class="text-sm text-gray-600"><?php echo __('admin.distance_traveled'); ?></p>
                                    <p class="text-lg font-semibold text-gray-900" id="distance-traveled">0 km</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <img src="/assets/images/temps.png" alt="<?php echo __('admin.time_alt'); ?>" class="h-8 w-8">
                                <div>
                                    <p class="text-sm text-gray-600"><?php echo __('admin.time_used'); ?></p>
                                    <p class="text-lg font-semibold text-gray-900" id="time-used">0 min</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="flex-none w-full px-2 sm:px-4 flex flex-col justify-center snap-start">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Històric de Càrrega</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="bg-[#F5F5F5] p-3 rounded-lg">Càrrega completada: 12/09/2025</li>
                        <li class="bg-[#F5F5F5] p-3 rounded-lg">Càrrega completada: 08/09/2025</li>
                        <li class="bg-[#F5F5F5] p-3 rounded-lg">Càrrega completada: 05/09/2025</li>
                    </ul>
                </section>
            </main>
            <!-- Pagination dots (mobile only) -->
            <div class="dots flex justify-center gap-2 mt-4 md:hidden">
                <span class="dot w-3 h-3 rounded-full bg-gray-400"></span>
                <span class="dot w-3 h-3 rounded-full bg-gray-400"></span>
                <span class="dot w-3 h-3 rounded-full bg-gray-400"></span>
            </div>
        </div>
    </div>

    <div class="desktop-view hidden md:block bg-white p-8 rounded-2xl shadow-inner w-full max-w-5xl mx-auto">
        <header class="grid grid-cols-3 items-center mb-6 w-full">
            <div class="text-left">
                <a href="/dashboard" class="text-[#1565C0] text-sm font-semibold">Tornar</a>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 text-center">Controlar Vehicle</h2>
            <div class="flex justify-end items-center gap-3">
                <button id="release-vehicle-btn-desktop" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-colors" title="Finalitzar Reserva">
                    Finalitzar Reserva
                </button>
                <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
            </div>
        </header>

        <div class="mb-6 sticky top-0 z-10 bg-white p-4 rounded-lg shadow-sm flex flex-row justify-center items-center gap-8">
            <div class="pushable flex-1 max-w-xs">
                <span class="shadow"></span>
                <span class="edge"
                    style="background: linear-gradient(to left, hsl(140, 80%, 35%) 0%, hsl(140, 80%, 45%) 8%, hsl(140, 80%, 45%) 92%, hsl(140, 80%, 35%) 100%);"></span>
                <span class="front p-3 flex flex-col items-center text-base" style="background: hsl(140, 80%, 45%);">
                    <span class="font-semibold"><?php echo __('admin.status'); ?></span>
                    <span class="text-white font-semibold h-8 mt-2" data-vehicle-status><?php echo __('admin.operational'); ?></span>
                </span>
            </div>
            <div class="pushable flex-1 max-w-xs">
                <span class="shadow"></span>
                <span class="edge"
                    style="background: linear-gradient(to left, hsl(200, 70%, 40%) 0%, hsl(200, 70%, 50%) 8%, hsl(200, 70%, 50%) 92%, hsl(200, 70%, 40%) 100%);"></span>
                <span class="front p-3 flex flex-col items-center text-base" style="background: hsl(200, 70%, 50%);">
                    <span class="font-semibold"><?php echo __('admin.battery'); ?></span>
                    <div class="w-full bg-gray-300 rounded-lg h-8 overflow-hidden mt-2">
                        <div class="h-full" data-battery-bar style="width: 80%; background-color: #00C853;">
                            <div class="h-full flex items-center justify-center text-white font-bold text-base" data-battery-text>80%</div>
                        </div>
                    </div>
                </span>
            </div>
        </div>

        <main class="flex flex-row gap-8">
            <section class="flex-1 flex flex-col">
                <h2 class="text-2xl font-bold text-gray-900 mb-6"><?php echo __('admin.vehicle_controls'); ?></h2>
                <div class="grid grid-cols-2 gap-8">
                    <button class="pushable yellow h-40" data-control="engine">
                        <span class="shadow"></span>
                        <span class="edge"></span>
                        <span class="front flex flex-col items-center justify-center text-lg p-4">
                            <img src="/assets/images/engegar.png" alt="<?php echo __('admin.start_stop_alt'); ?>" class="h-16 w-16 mb-3">
                            <span><?php echo __('admin.start_stop'); ?></span>
                        </span>
                    </button>
                    <button class="pushable h-40" data-control="horn">
                        <span class="shadow"></span>
                        <span class="edge"></span>
                        <span class="front flex flex-col items-center justify-center text-lg p-4">
                            <img src="/assets/images/claxon.png" alt="<?php echo __('admin.activate_horn'); ?>" class="h-16 w-16 mb-3">
                            <?php echo __('admin.activate_horn'); ?>
                        </span>
                    </button>
                    <button class="pushable h-40" data-control="lights">
                        <span class="shadow"></span>
                        <span class="edge"></span>
                        <span class="front flex flex-col items-center justify-center text-lg p-4">
                            <img src="/assets/images/llums.png" alt="<?php echo __('admin.activate_lights'); ?>" class="h-16 w-16 mb-3">
                            <?php echo __('admin.activate_lights'); ?>
                        </span>
                    </button>
                    <button class="pushable h-40" data-control="doors">
                        <span class="shadow"></span>
                        <span class="edge"></span>
                        <span class="front flex flex-col items-center justify-center text-lg p-4">
                            <img src="/assets/images/portes.png" alt="<?php echo __('admin.lock_doors'); ?>" class="h-16 w-16 mb-3">
                            <?php echo __('admin.lock_doors'); ?>
                        </span>
                    </button>
                </div>
            </section>

            <!-- Information and Location section -->
            <section class="flex-1 flex flex-col">
                <h2 class="text-2xl font-bold text-gray-900 mb-6"><?php echo __('admin.info_location'); ?></h2>
                <div class="bg-[#F5F5F5] p-6 rounded-lg mb-6">
                    <p class="text-gray-900 font-semibold text-xl mb-2"><?php echo __('admin.license_plate'); ?> <span class="font-normal" data-vehicle-license><?php echo __('admin.loading'); ?>...</span></p>
                    <p class="text-gray-700 text-lg"><?php echo __('admin.model'); ?> <span class="font-normal" data-vehicle-model><?php echo __('admin.loading'); ?>...</span></p>
                </div>
                <div class="flex-1 w-full rounded-lg relative" style="min-height: 320px;">
                    <div id="vehicle-map-desktop" style="height: 100%; width: 100%; border-radius: 0.5rem;"></div>
                </div>
            </section>

            <!-- Charging History section -->
            <section class="flex-1 flex flex-col">
                <h3 class="text-xl font-bold text-gray-900 mb-4"><?php echo __('admin.charging_history'); ?></h3>
                <ul class="space-y-4 text-base">
                    <li class="bg-[#F5F5F5] p-4 rounded-lg"><?php echo __('admin.charge_completed'); ?>: 12/09/2025</li>
                    <li class="bg-[#F5F5F5] p-4 rounded-lg"><?php echo __('admin.charge_completed'); ?>: 08/09/2025</li>
                    <li class="bg-[#F5F5F5] p-4 rounded-lg"><?php echo __('admin.charge_completed'); ?>: 05/09/2025</li>
                </ul>
            </section>
        </main>
    </div>

    <!-- Modal de Confirmación para Finalizar Reserva -->
        <div id="release-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4" style="z-index: 9999;">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo __('admin.confirm_release'); ?></h3>
            <p class="text-gray-700 mb-6"><?php echo __('admin.release_warning'); ?></p>
            <div class="flex space-x-3">
                <button id="confirm-release" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition-colors">
                    <?php echo __('admin.confirm'); ?>
                </button>
                <button id="cancel-release" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition-colors">
                    <?php echo __('admin.cancel'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <!-- Application Scripts -->
    <script src="/assets/js/toast.js"></script>
    <script src="/assets/js/confirm-modal.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/vehicles.js"></script>
    <script src="/assets/js/administrar-vehicle.js"></script>

    <a href="/resum-projecte"
        class="fixed bottom-10 right-10 block bg-[#1565C0] text-white p-4 rounded-full shadow-lg hover:bg-[#1151a3] transition-colors duration-300 z-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
    </a>
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>

</html>