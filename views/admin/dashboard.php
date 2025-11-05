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
        'change' => 'Incidències registrades',
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

                
                <div class="inline-flex gap-1 bg-gray-100 p-0.5 rounded-lg mb-6 shadow-md">
                    <button class="tab-button px-3 py-1.5 rounded-md text-sm bg-blue-900 text-white hover:bg-blue-700 hover:text-gray-100 transition-colors" data-active="true">
                        Vista General
                    </button>
                    <button class="tab-button px-3 py-1.5 rounded-md text-sm text-gray-600 hover:bg-blue-700 hover:text-gray-100 transition-colors">
                        Estadístiques
                    </button>
                </div>
                
                <!-- Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <?php foreach ($metrics as $metric): ?>
                    <div class="bg-gray-100 rounded-xl p-6 shadow-md">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm text-gray-900 font-medium"><?php echo $metric['title']; ?></span>
                            <span class="text-xl"><?php echo $metric['icon']; ?></span>
                        </div>
                        <div class="text-3xl font-bold mb-1"><?php echo $metric['value']; ?></div>
                        <div class="text-xs text-blue-700"><?php echo $metric['change']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Dashboard Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Chart -->
                    <div class="bg-gray-100 rounded-xl p-6 shadow-md col-span-4">
                        <h2 class="text-lg font-semibold mb-6">Vista</h2>
                        <div class="flex items-end justify-between h-[300px] w-full">
                            <?php foreach ($chartData as $month => $value): ?>
                            <div class="flex flex-col items-center gap-2 h-full flex-1">
                                <div class="w-4/5 bg-gray-200 rounded-t hover:bg-white transition-all cursor-pointer" 
                                     style="height: <?php echo ($value / $maxValue) * 100; ?>%;"></div>
                                <div class="text-xs text-gray-600 mt-auto"><?php echo $month; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

<?php require_once VIEWS_PATH . '/admin/admin-footer.php'; ?>