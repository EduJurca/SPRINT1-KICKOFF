<?php
/**
 * Layout: Admin Header
 * Header específico para el panel de administración
 */

if (!isset($title)) {
    $title = 'Admin Panel - VoltaCar';
}
$bodyClass = $bodyClass ?? 'bg-gray-50';
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- UserWay Accessibility Widget -->
    <script>
        (function(d){
            var s = d.createElement("script");
            s.setAttribute("data-account","RrwQjeYdrh");
            s.src = "https://cdn.userway.org/widget.js";
            (d.body || d.head).appendChild(s);
        })(document);
    </script>
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ((array)$additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?php echo htmlspecialchars($bodyClass); ?>">
    
    <!-- Sidebar de navegación -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#1565C0] text-white flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-blue-700">
                <h1 class="text-2xl font-bold">SIMS Admin</h1>
                <p class="text-sm text-blue-200 mt-1">Panel de Control</p>
            </div>
            
            <!-- Navegación -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-3">
                    <li>
                        <a href="/admin/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors <?php echo $currentPage === 'dashboard' ? 'bg-blue-700' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/users" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors <?php echo $currentPage === 'users' ? 'bg-blue-700' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>Usuaris</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/vehicles" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors <?php echo $currentPage === 'vehicles' ? 'bg-blue-700' : ''; ?>">
                            <i class="fas fa-car"></i>
                            <span>Vehicles</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/bookings" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors <?php echo $currentPage === 'bookings' ? 'bg-blue-700' : ''; ?>">
                            <i class="fas fa-calendar-check"></i>
                            <span>Reserves</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/incidencies" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors <?php echo $currentPage === 'incidencies' ? 'bg-blue-700' : ''; ?>">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Incidències</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/settings" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors <?php echo $currentPage === 'settings' ? 'bg-blue-700' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Configuració</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Usuario admin y logout -->
            <div class="p-4 border-t border-blue-700">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-blue-200">Administrador</p>
                    </div>
                </div>
                <a href="/logout" class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg transition-colors text-sm justify-center">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Tancar Sessió</span>
                </a>
            </div>
        </aside>
        
        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></h2>
                    <div class="flex items-center gap-4">
                        <!-- Notificaciones -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        <!-- Buscar -->
                        <div class="relative">
                            <input type="text" placeholder="Buscar..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Contenido scrolleable -->
            <main class="flex-1 overflow-y-auto p-6">
