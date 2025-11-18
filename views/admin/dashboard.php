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
    'Gen' => 0,
    'Feb' => 0,
    'Mar' => 0,
    'Abr' => 0,
    'Mai' => 0,
    'Jun' => 0,
    'Jul' => 0,
    'Ago' => 0,
    'Set' => 0,
    'Oct' => 0,
    'Nov' => 0,
    'Des' => 0
];
$maxValue = max($chartData) ?: 1;

$recentActivity = $recentUsers ?? [];

$pageTitle = __('admin.menu.dashboard');
$currentPage = 'dashboard';

require_once VIEWS_PATH . '/admin/admin-header.php';
?>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900"><?php echo __('admin.menu.dashboard'); ?></h2>
                    <p class="text-sm text-gray-600 mt-1"><?php echo __('admin.dashboard.description'); ?></p>
                </div>
            </div>
        </div>
        <div class="inline-flex gap-1 bg-gray-100 p-0.5 rounded-lg mb-6 shadow-md">
            <button
                class="tab-button px-3 py-1.5 rounded-md text-sm bg-blue-900 text-white hover:bg-blue-700 hover:text-gray-100 transition-colors"
                data-active="true">
                <?php echo __('admin.dashboard.tabs.overview'); ?>
            </button>
            <button
                class="tab-button px-3 py-1.5 rounded-md text-sm text-gray-600 hover:bg-blue-700 hover:text-gray-100 transition-colors">
                <?php echo __('admin.dashboard.tabs.statistics'); ?>
            </button>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php foreach ($metrics as $key => $metric): ?>
                <?php
                    $titleKey = 'admin.dashboard.metrics.' . $key . '.title';
                    $changeKey = 'admin.dashboard.metrics.' . $key . '.change';
                    $translatedTitle = __($titleKey);
                    $translatedChange = __($changeKey);
                    $displayTitle = $translatedTitle === $titleKey ? $metric['title'] : $translatedTitle;
                    $displayChange = $translatedChange === $changeKey ? $metric['change'] : $translatedChange;
                ?>
                <div class="bg-gray-100 rounded-xl p-6 shadow-md">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm text-gray-900 font-medium"><?php echo $displayTitle; ?></span>
                        <span class="text-xl"><?php echo $metric['icon']; ?></span>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $metric['value']; ?></div>
                    <div class="text-xs text-blue-700"><?php echo $displayChange; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Chart -->
            <div class="bg-gray-100 rounded-xl p-6 shadow-md col-span-4">
                <h2 class="text-lg font-semibold mb-2"><?php echo __('admin.dashboard.overview_title'); ?></h2>
                <?php
                    $chartLabelKey = 'admin.dashboard.chart_label';
                    $chartLabel = __($chartLabelKey);
                    if ($chartLabel === $chartLabelKey) { $chartLabel = 'Bookings'; }
                ?>
                <p class="text-sm text-gray-600 mb-4"><?php echo $chartLabel; ?></p>
                <div class="flex items-end justify-between h-[300px] w-full">
                    <?php foreach ($chartData as $month => $value): ?>
                        <?php $pct = ($maxValue > 0) ? (($value / $maxValue) * 100) : 0; ?>
                        <div class="flex flex-col items-center gap-2 h-full flex-1" aria-label="<?php echo htmlspecialchars($month . ': ' . $value); ?>">
                            <div class="text-sm text-gray-700 mb-1 font-medium"><?php echo $value; ?></div>
                            <div class="w-4/5 bg-gray-200 rounded-t hover:bg-white transition-all cursor-pointer" title="<?php echo $value; ?>"
                                style="height: <?php echo $pct; ?>%;"></div>
                            <div class="text-xs text-gray-600 mt-auto"><?php echo $month; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/admin/admin-footer.php'; ?>