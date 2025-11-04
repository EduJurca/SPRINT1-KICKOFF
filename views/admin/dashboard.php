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
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <div class="app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon">⚡</div>
                <div class="logo-text">
                    <h3><?php echo htmlspecialchars($currentUser['name']); ?></h3>
                    <p><?php echo htmlspecialchars($currentUser['role_name']); ?></p>
                </div>
            </div>
            
            <nav>
                <div class="nav-section">
                    <div class="nav-label">General</div>
                    <a href="#" class="nav-item active">
                        <span>📊</span> Dashboard
                    </a>
                    <a href="#" class="nav-item">
                        <span>✓</span> Tasks
                    </a>
                    <a href="#" class="nav-item">
                        <span>⚙️</span> Apps
                    </a>
                    <a href="#" class="nav-item">
                        <span>💬</span> Chats
                        <span class="nav-badge">3</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span>👥</span> Users
                    </a>
                    <a href="#" class="nav-item">
                        <span>🔒</span> Secured by Clerk
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-label">Pages</div>
                    <a href="#" class="nav-item">
                        <span>🔐</span> Auth
                    </a>
                    <a href="#" class="nav-item">
                        <span>⚠️</span> Errors
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-label">Other</div>
                    <a href="#" class="nav-item">
                        <span>⚙️</span> Settings
                    </a>
                    <a href="#" class="nav-item">
                        <span>❓</span> Help Center
                    </a>
                </div>
            </nav>
            
            <div class="user-menu">
                <div class="user-avatar"><?php echo htmlspecialchars($currentUser['initials']); ?></div>
                <div class="user-info">
                    <h4><?php echo htmlspecialchars($currentUser['name']); ?></h4>
                    <p><?php echo htmlspecialchars($currentUser['email']); ?></p>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <nav class="nav-tabs">
                    <a href="#" class="active">Overview</a>
                    <a href="#">Customers</a>
                    <a href="#">Products</a>
                    <a href="#">Settings</a>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <span>🔍</span>
                        <input type="text" placeholder="Search">
                        <span style="font-size: 11px; color: #666;">⌘K</span>
                    </div>
                    <button class="icon-btn">🌙</button>
                    <button class="icon-btn">⚙️</button>
                    <div class="user-avatar"><?php echo htmlspecialchars($currentUser['initials']); ?></div>
                </div>
            </header>
            
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Dashboard</h1>
                    <button class="download-btn">Download</button>
                </div>
                
                <div class="tabs">
                    <button class="tab active">Overview</button>
                    <button class="tab">Analytics</button>
                    <button class="tab">Reports</button>
                    <button class="tab">Notifications</button>
                </div>
                
                <!-- Metrics -->
                <div class="metrics-grid">
                    <?php foreach ($metrics as $metric): ?>
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-title"><?php echo $metric['title']; ?></span>
                            <span class="metric-icon"><?php echo $metric['icon']; ?></span>
                        </div>
                        <div class="metric-value"><?php echo $metric['value']; ?></div>
                        <div class="metric-change"><?php echo $metric['change']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Dashboard Grid -->
                <div class="dashboard-grid">
                    <!-- Chart -->
                    <div class="card">
                        <h2 class="card-title">Overview</h2>
                        <div class="chart">
                            <?php foreach ($chartData as $month => $value): ?>
                            <div class="chart-bar-wrapper">
                                <div class="chart-bar" style="height: <?php echo ($value / $maxValue) * 100; ?>%;"></div>
                                <div class="chart-label"><?php echo $month; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Activitat Recent -->
                    <div class="card">
                        <div class="sales-header">
                            <h2 class="card-title">Activitat Recent</h2>
                        </div>
                        <p class="sales-subtitle"><?php echo count($recentActivity); ?> nous usuaris aquest mes.</p>
                        <div class="sales-list" style="margin-top: 24px;">
                            <?php if (!empty($recentActivity)): ?>
                                <?php foreach ($recentActivity as $user): ?>
                                <div class="sale-item">
                                    <div class="sale-avatar"><?php echo strtoupper(substr($user['username'] ?? 'U', 0, 2)); ?></div>
                                    <div class="sale-info">
                                        <div class="sale-name"><?php echo htmlspecialchars($user['username'] ?? 'Usuari'); ?></div>
                                        <div class="sale-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                                    </div>
                                    <div class="sale-amount" style="color: #00C853;">
                                        <?php echo htmlspecialchars($user['role_name'] ?? 'Client'); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="text-align: center; padding: 40px; color: #666;">
                                    <p>No hi ha activitat recent</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>