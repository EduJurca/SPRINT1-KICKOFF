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
        <aside class="w-60 bg-sky-50 flex flex-col shadow-lg">
            <!-- Logo -->
            <div class="px-4 py-5">
                <div class="flex items-center gap-3">
                    <img src="/assets/images/logo.png" alt="Voltacar Logo" class="w-8 h-8">
                <div>
                    <h1 class="text-2xl font-bold tracking-wide">VoltiaCar</h1>
                    <p class="text-sm text-[#00C853] mt-1">Panell d'administració</p>
                </div>
            </div>
            
            <!-- Navegación -->
                        <nav class="flex-1">
                <div class="mb-6">
                    <div class="px-4 py-2 text-xs uppercase text-gray-600 font-semibold">General</div>
                    <a href="/admin/dashboard" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm text-gray-900 hover:bg-blue-700 hover:text-gray-100 <?php echo $currentPage === 'dashboard' ? 'bg-blue-900 text-white' : ''; ?>">
                        <img src="/assets/images/dashboard.png" alt="Dashboard" class="w-4 h-4 opacity-100"> 
                        Dashboard
                    </a>
                </div>
                
                <div class="mb-6">
                    <div class="px-4 py-2 text-xs uppercase text-gray-600 font-semibold">Pages</div>
                    <a href="/admin/users" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm text-gray-900 hover:bg-blue-700 hover:text-gray-100 <?php echo $currentPage === 'users' ? 'bg-blue-900 text-white' : ''; ?>">
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
                    <span>Tanca sessió</span>
                </a>
            </div>
        </aside>
        
        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="bg-[#0A2342] border-b border-[#00C853] px-8 py-5 w-full">
                <div class="flex items-center justify-between w-full">
                    <h2 class="text-2xl font-bold text-white tracking-wide"><?php echo htmlspecialchars($pageTitle ?? 'Inici'); ?></h2>
                    <div class="flex items-center gap-6">
                        <!-- Notificacions -->
                        <button class="relative p-2 text-[#00C853] hover:text-white transition-colors">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        <!-- Cercar -->
                        <div class="relative">
                            <input type="text" placeholder="Cercar..." class="pl-10 pr-4 py-2 border border-[#00C853] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00C853] bg-[#102A43] text-white placeholder:text-[#00C853]">
                            <i class="fas fa-search absolute left-3 top-3 text-[#00C853]"></i>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Contenido scrolleable -->
            <main class="flex-1 overflow-y-auto p-6">
