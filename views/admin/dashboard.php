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
        'icon' => '👥'
    ],
    'vehicles' => [
        'title' => 'Vehicles',
        'value' => $totalVehicles ?? '0',
        'change' => 'Vehicles disponibles',
        'icon' => '�'
    ],
    'bookings' => [
        'title' => 'Reserves',
        'value' => $totalBookings ?? '0',
        'change' => 'Reserves actives',
        'icon' => '�'
    ],
    'revenue' => [
        'title' => 'Ingressos',
        'value' => '€' . number_format($totalRevenue ?? 0, 2),
        'change' => 'Aquest mes',
        'icon' => '�'
    ]
];

$chartData = $monthlyBookings ?? [
    'Gen' => 0, 'Feb' => 0, 'Mar' => 0, 'Abr' => 0,
    'Mai' => 0, 'Jun' => 0, 'Jul' => 0, 'Ago' => 0,
    'Set' => 0, 'Oct' => 0, 'Nov' => 0, 'Des' => 0
];
$maxValue = max($chartData) ?: 1;

$recentActivity = $recentUsers ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($currentUser['name']); ?> - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-white text-black leading-normal">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-60 bg-sky-50 flex flex-col shadow-lg">
            <div class="px-4 py-5">
                <div class="flex items-center gap-3">
                    <img src="/assets/images/logo.png" alt="Voltacar Logo" class="w-8 h-8">
                </div>
            </div>
            
            <nav class="flex-1">
                <div class="mb-6">
                    <div class="px-4 py-2 text-xs uppercase text-gray-600 font-semibold">General</div>
                    <a href="#" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 bg-blue-900 text-white" data-active="true">
                        <img src="/assets/images/dashboard.png" alt="Dashboard" class="w-4 h-4 opacity-100"> 
                        Dashboard
                    </a>
                </div>
                
                <div class="mb-6">
                    <div class="px-4 py-2 text-xs uppercase text-gray-600 font-semibold">Pages</div>
                    <a href="/admin/users" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm text-gray-900 hover:bg-blue-700 hover:text-gray-100">
                        <i class="fa fa-users"></i> Usuaris
                    </a>
                    <a href="#" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm text-gray-900 hover:bg-blue-700 hover:text-gray-100">
                        <i class="fa fa-charging-station"></i> Punts de carrega
                    </a>
                    <a href="#" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm text-gray-900 hover:bg-blue-700 hover:text-gray-100">
                        <i class="fa fa-car"></i> Vehicles
                    </a>
                    <a href="#" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm text-gray-900 hover:bg-blue-700 hover:text-gray-100">
                        <i class="fa fa-flag"></i> Incidencies
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <div class="p-10">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-semibold">Dashboard</h1>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center text-xs font-semibold text-white">
                            <?php echo htmlspecialchars($currentUser['initials']); ?>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium"><?php echo htmlspecialchars($currentUser['name']); ?></h4>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-2 bg-gray-100 p-1 rounded-lg mb-6 shadow-md">
                    <button class="tab-button px-4 py-2 rounded-md text-sm bg-gray-900 text-white transition-colors" data-active="true">
                        Vista General
                    </button>
                    <button class="tab-button px-4 py-2 rounded-md text-sm text-gray-600 hover:bg-gray-900 hover:text-white transition-colors">
                        Estadístiques
                    </button>
                    <button class="tab-button px-4 py-2 rounded-md text-sm text-gray-600 hover:bg-gray-900 hover:text-white transition-colors">
                        Incidències
                    </button>
                    <button class="tab-button px-4 py-2 rounded-md text-sm text-gray-600 hover:bg-gray-900 hover:text-white transition-colors">
                        Notificacions
                    </button>
                </div>
                
                <!-- Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <?php foreach ($metrics as $metric): ?>
                    <div class="bg-gray-100 rounded-xl p-6 shadow-md">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm text-gray-900 font-medium"><?php echo $metric['title']; ?></span>
                            <span class="text-lg opacity-50"><?php echo $metric['icon']; ?></span>
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
                        <h2 class="text-lg font-semibold mb-6">Overview</h2>
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
        </main>
    </div>
    
    <script>
        // Manejar estado activo para enlaces del sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link');
            const tabButtons = document.querySelectorAll('.tab-button');
            
            // Funcionalidad para enlaces del sidebar
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Remover estado activo de todos los enlaces
                    navLinks.forEach(nl => {
                        nl.classList.remove('bg-blue-900', 'text-white');
                        nl.classList.add('text-gray-900');
                        nl.removeAttribute('data-active');
                    });
                    
                    // Añadir estado activo al enlace clickeado
                    this.classList.add('bg-blue-900', 'text-white');
                    this.classList.remove('text-gray-900');
                    this.setAttribute('data-active', 'true');
                });
            });
            
            // Funcionalidad para botones de tabs
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remover estado activo de todos los botones
                    tabButtons.forEach(btn => {
                        btn.classList.remove('bg-gray-900', 'text-white');
                        btn.classList.add('text-gray-600');
                        btn.removeAttribute('data-active');
                    });
                    
                    // Añadir estado activo al botón clickeado
                    this.classList.add('bg-gray-900', 'text-white');
                    this.classList.remove('text-gray-600');
                    this.setAttribute('data-active', 'true');
                });
            });
        });
    </script>
</body>
</html>