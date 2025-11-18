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

<script>
function switchTab(tabName) {
    document.getElementById('tab-general').style.display = 'none';
    document.getElementById('tab-estadistiques').style.display = 'none';
    document.getElementById('tab-' + tabName).style.display = 'block';
    
    const btnGeneral = document.getElementById('btn-general');
    const btnEstadistiques = document.getElementById('btn-estadistiques');
    
    btnGeneral.classList.remove('bg-blue-900', 'text-white');
    btnGeneral.classList.add('text-gray-600');
    btnEstadistiques.classList.remove('bg-blue-900', 'text-white');
    btnEstadistiques.classList.add('text-gray-600');
    
    if (tabName === 'general') {
        btnGeneral.classList.add('bg-blue-900', 'text-white');
        btnGeneral.classList.remove('text-gray-600');
    } else {
        btnEstadistiques.classList.add('bg-blue-900', 'text-white');
        btnEstadistiques.classList.remove('text-gray-600');
    }
}
</script>


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
        <button onclick="switchTab('general')" class="tab-button px-3 py-1.5 rounded-md text-sm bg-blue-900 text-white hover:bg-blue-700 hover:text-gray-100 transition-colors" id="btn-general">
                    <?php echo __('admin.dashboard.tabs.overview'); ?>
        </button>
        <button onclick="switchTab('estadistiques')" class="tab-button px-3 py-1.5 rounded-md text-sm text-gray-600 hover:bg-blue-700 hover:text-gray-100 transition-colors" id="btn-estadistiques">
                    <?php echo __('admin.dashboard.tabs.statistics'); ?>
        </button>
    </div>

<!-- Tab: Vista General -->
<div id="tab-general" class="tab-content-section">
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

<!-- Tab: Estadístiques -->
 
<div id="tab-estadistiques" class="tab-content-section" style="display:none;">
  
    <!-- Primera fila: 3 tarjetes -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- 1. Total Clients -->
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border-t-4 border-violet-500 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-violet-100 rounded-xl flex items-center justify-center">
                    <i class="fa fa-users text-violet-600 text-xl"></i>
                </div>
                <span class="text-xs font-bold text-violet-600 uppercase">Clients</span>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1"><?php echo $totalUsers ?? 0; ?></div>
            <div class="text-xs font-semibold text-gray-600 uppercase">Total Clients</div>
            <div class="text-xs text-gray-500 mt-2">Registrats</div>
        </div>

        <!-- 2. Total Vehicles -->
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border-t-4 border-blue-500 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fa fa-car text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-bold text-blue-600 uppercase">Vehicles</span>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1"><?php echo $totalVehicles ?? 0; ?></div>
            <div class="text-xs font-semibold text-gray-600 uppercase">Total Vehicles</div>
            <div class="text-xs text-emerald-600 mt-2 font-semibold">
                <?php echo isset($vehicleStats['active']) ? $vehicleStats['active'] : 'N/A'; ?> actius
            </div>
        </div>

        <!-- 3. Total Incidències -->
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border-t-4 border-red-500 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fa fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <span class="text-xs font-bold text-red-600 uppercase">Incidencies</span>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1"><?php echo $totalIncidents ?? 0; ?></div>
            <div class="text-xs font-semibold text-gray-600 uppercase">Incidències</div>
            <div class="text-xs text-gray-500 mt-2">Actives</div>
        </div>
    </div>

    <!-- Segona fila: 3 tarjetes -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- 4. CONSUM CARD - UPDATED -->
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border-t-4 border-cyan-500 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center">
                    <i class="fa fa-calendar text-cyan-600 text-xl"></i>
                </div>
                <span class="text-xs font-bold text-cyan-600 uppercase">Consum Energètic</span>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1"><?php echo number_format($energyThisMonth ?? 0, 2); ?> kWh</div>
            <div class="text-xs font-semibold text-gray-600 uppercase">Consum aquest mes</div>
            <div class="text-xs text-gray-500 mt-2">Basat en sessions de càrrega</div>
        </div>

        <!-- 5. NOUS CLIENTS -->
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border-t-4 border-emerald-500 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fa fa-users text-emerald-600 text-xl"></i>
                </div>
                <span class="text-xs font-bold text-emerald-600 uppercase">Nous Clients</span>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1"><?php echo $newClientsMonth ?? 0; ?></div>
            <div class="text-xs font-semibold text-gray-600 uppercase">Nous aquest mes</div>
            <div class="text-xs text-gray-500 mt-2">Clients amb rol=3</div>
        </div>

        <!-- 6. NOVES INCIDÈNCIES -->
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border-t-4 border-orange-500 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fa fa-exclamation-circle text-orange-600 text-xl"></i>
                </div>
                <span class="text-xs font-bold text-orange-600 uppercase">Noves Incidències</span>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1"><?php echo $newIncidentsMonth ?? 0; ?></div>
            <div class="text-xs font-semibold text-gray-600 uppercase">Aquest mes</div>
            <div class="text-xs text-gray-500 mt-2">Incidències creades en 30 dies</div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/admin/admin-footer.php'; ?>
