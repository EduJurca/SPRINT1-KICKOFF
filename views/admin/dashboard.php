<?php
// Dades reals del controlador
$currentUser = [
    'name' => $auth['username'] ?? 'Admin',
    'email' => $_SESSION['email'] ?? 'admin@voltiacar.com',
    'role_name' => $auth['role_name'] ?? 'Admin',
    'initials' => strtoupper(substr($auth['username'] ?? 'AD', 0, 2))
];

$metrics = [
    'users' => [
        'title' => 'Total Usuaris',
        'value' => $totalUsers ?? '0',
        'change' => 'Usuaris registrats',
        'icon' => '<i class="fa fa-users text-[#1565C0]"></i>'
    ],
    'vehicles' => [
        'title' => 'Vehicles',
        'value' => $totalVehicles ?? '0',
        'change' => 'Vehicles disponibles',
        'icon' => '<i class="fa fa-car text-[#1565C0]"></i>'
    ],
    'incidents' => [
        'title' => 'Incidències',
        'value' => $totalIncidents ?? '0',
        'change' => 'Incidències actives',
        'icon' => '<i class="fa fa-exclamation-triangle text-[#1565C0]"></i>'
    ],
    'revenue' => [
        'title' => 'Ingressos',
        'value' => '€' . number_format($totalRevenue ?? 0, 2),
        'change' => 'Aquest mes',
        'icon' => '<i class="fa fa-money-bill text-[#1565C0]"></i>'
    ]
];

$chartData = $monthlyBookings ?? [
    'Gen' => 0, 'Feb' => 0, 'Mar' => 0, 'Abr' => 0,
    'Mai' => 0, 'Jun' => 0, 'Jul' => 0, 'Ago' => 0,
    'Set' => 0, 'Oct' => 0, 'Nov' => 0, 'Des' => 0
];
$maxValue = max($chartData) ?: 1;

$recentActivity = $recentUsers ?? [];

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

require_once VIEWS_PATH . '/admin/admin-header.php';
?>

                <!-- Dashboard title: moved from navbar -->
                <div class="mb-4 px-4 md:px-0">
                    <h1 class="text-xl md:text-2xl font-semibold"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                </div>

                <div class="inline-flex gap-1 bg-gray-100 p-0.5 rounded-lg mb-6 shadow-md mx-4 md:mx-0">
                    <button class="tab-button px-2 md:px-3 py-1.5 rounded-md text-xs md:text-sm bg-blue-900 text-white hover:bg-blue-700 hover:text-gray-100 transition-colors" data-tab="general" data-active="true">
                        Vista General
                    </button>
                    <button class="tab-button px-2 md:px-3 py-1.5 rounded-md text-xs md:text-sm text-gray-600 hover:bg-blue-700 hover:text-gray-100 transition-colors" data-tab="stats">
                        Estadístiques
                    </button>
                </div>
                
                <!-- Tab 1: Vista General -->
                <div id="tab-general" class="tab-content">
                    <!-- Metrics: 1 col mobile, 2 md, 4 lg -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-8 px-4 md:px-0">
                        <?php foreach ($metrics as $metric): ?>
                        <div class="bg-gray-100 rounded-xl p-4 md:p-6 shadow-md">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs md:text-sm text-gray-900 font-medium"><?php echo $metric['title']; ?></span>
                                <span class="text-lg md:text-xl"><?php echo $metric['icon']; ?></span>
                            </div>
                            <div class="text-2xl md:text-3xl font-bold mb-1"><?php echo $metric['value']; ?></div>
                            <div class="text-xs text-blue-700"><?php echo $metric['change']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Dashboard Grid: Chart -->
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6 px-4 md:px-0">
                        <!-- Chart -->
                        <div class="bg-gray-100 rounded-xl p-4 md:p-6 shadow-md col-span-4 overflow-x-auto">
                            <h2 class="text-base md:text-lg font-semibold mb-4 md:mb-6">Reserves Mensuals</h2>
                            <div class="flex items-end justify-between h-[250px] md:h-[350px] w-full gap-1 md:gap-2 pb-4 overflow-x-auto">
                                <?php foreach ($chartData as $month => $value): ?>
                                <div class="flex flex-col items-center gap-1 md:gap-2 h-full flex-1 min-w-max md:min-w-0">
                                    <div class="w-full md:w-4/5 bg-gradient-to-t from-blue-400 to-blue-600 rounded-t hover:from-blue-500 hover:to-blue-700 transition-all cursor-pointer shadow-sm group relative" 
                                         style="height: <?php echo ($value / $maxValue) * 100; ?>%;" 
                                         title="<?php echo $month; ?>: <?php echo $value; ?> reserves">
                                        <!-- Tooltip amb valor al hover -->
                                        <div class="hidden group-hover:block absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                                            <?php echo $value; ?>
                                        </div>
                                    </div>
                                    <div class="text-xs md:text-sm text-gray-700 font-medium text-center break-words"><?php echo substr($month, 0, 3); ?></div>
                                    <div class="text-xs text-gray-500 text-center"><?php echo $value; ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Legenda -->
                            <div class="mt-6 pt-4 border-t border-gray-300 flex items-center justify-center gap-4 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 bg-gradient-to-t from-blue-400 to-blue-600 rounded-sm"></div>
                                    <span class="text-xs md:text-sm text-gray-600">Reserves</span>
                                </div>
                                <div class="text-xs md:text-sm text-gray-600">
                                    Total: <strong><?php echo array_sum($chartData); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Estadístiques -->
                <div id="tab-stats" class="tab-content hidden px-4 md:px-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-8">
                        <!-- Estadístiques detallades -->
                        <div class="bg-white rounded-xl p-4 md:p-6 shadow-md border border-gray-200">
                            <h3 class="text-base md:text-lg font-semibold mb-4 text-gray-900">Resumen d'Usuaris</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Usuaris Totals</span>
                                    <span class="text-lg font-bold text-blue-600"><?php echo $totalUsers ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Usuaris Nous (30 dies)</span>
                                    <span class="text-lg font-bold text-green-600"><?php echo $newUsersMonth ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-600">Usuaris Actius</span>
                                    <span class="text-lg font-bold text-yellow-600"><?php echo $activeUsers ?? '0'; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-4 md:p-6 shadow-md border border-gray-200">
                            <h3 class="text-base md:text-lg font-semibold mb-4 text-gray-900">Resumen de Vehicles</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Vehicles Totals</span>
                                    <span class="text-lg font-bold text-blue-600"><?php echo $totalVehicles ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Vehicles Disponibles</span>
                                    <span class="text-lg font-bold text-green-600"><?php echo $availableVehicles ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-600">Vehicles en Ús</span>
                                    <span class="text-lg font-bold text-orange-600"><?php echo $vehiclesInUse ?? '0'; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-4 md:p-6 shadow-md border border-gray-200">
                            <h3 class="text-base md:text-lg font-semibold mb-4 text-gray-900">Resumen de Reserves</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Reserves Actives</span>
                                    <span class="text-lg font-bold text-blue-600"><?php echo $activeBookings ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Reserves Avui</span>
                                    <span class="text-lg font-bold text-green-600"><?php echo $todayBookings ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-600">Reserves Pendents</span>
                                    <span class="text-lg font-bold text-yellow-600"><?php echo $pendingBookings ?? '0'; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-4 md:p-6 shadow-md border border-gray-200">
                            <h3 class="text-base md:text-lg font-semibold mb-4 text-gray-900">Resumen de Incidències</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Incidències Totals</span>
                                    <span class="text-lg font-bold text-blue-600"><?php echo $totalIncidents ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Pendents de Resoldre</span>
                                    <span class="text-lg font-bold text-red-600"><?php echo $pendingIncidents ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-600">Resueltes (30 dies)</span>
                                    <span class="text-lg font-bold text-green-600"><?php echo $resolvedIncidentsMonth ?? '0'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<?php require_once VIEWS_PATH . '/admin/admin-footer.php'; ?>