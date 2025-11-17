<?php
if (!isset($_SESSION['success'])) $_SESSION['success'] = null;
if (!isset($_SESSION['error'])) $_SESSION['error'] = null;
if (!isset($_SESSION['warning'])) $_SESSION['warning'] = null;
if (!isset($_SESSION['info'])) $_SESSION['info'] = null;
if (!isset($_SESSION['alert'])) $_SESSION['alert'] = null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?> - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <link rel="icon" href="assets/images/logo.png" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/toast.js"></script>
    <style>
        /* Ensure sidebar link hover works consistently */
        .nav-link, .nav-link i, .nav-link svg { transition: background-color .2s ease, color .2s ease; }
        .nav-link i, .nav-link svg { color: inherit; }
        .nav-link:hover, .nav-link[data-active="true"]:hover {
            background-color: #1565C0 !important; /* Lighter blue for hover */
            color: #FFFFFF !important;
        }
        /* For active items, ensure icons inherit color */
        .nav-link[data-active="true"] i, .nav-link[data-active="true"] svg { color: #FFFFFF; }
    </style>
</head>
<body class="font-sans bg-white text-black leading-normal">
    <?php if (!empty($_SESSION['success'])): ?>
        <script>window.Toast && window.Toast.success(<?php echo json_encode($_SESSION['success']); ?>, 5000);</script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <script>window.Toast && window.Toast.error(<?php echo json_encode($_SESSION['error']); ?>, 5000);</script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['warning'])): ?>
        <script>window.Toast && window.Toast.warning(<?php echo json_encode($_SESSION['warning']); ?>, 5000);</script>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['info'])): ?>
        <script>window.Toast && window.Toast.info(<?php echo json_encode($_SESSION['info']); ?>, 5000);</script>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['alert'])): ?>
        <script>window.Toast && window.Toast.alert(<?php echo json_encode($_SESSION['alert']); ?>, 5000);</script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>
    
    <div class="flex min-h-screen">
        <aside class="w-60 bg-sky-50 flex flex-col shadow-lg">
            <div class="px-4 py-5">
                <div class="flex items-center gap-3">
                    <img src="/assets/images/logo.png" alt="Voltacar Logo" class="w-12 h-12">
                </div>
            </div>
            
            <nav class="flex-1">
                <div class="mb-6">
                    <div class="px-4 py-2 text-xs uppercase text-gray-600 font-semibold">General</div>
                    <a href="/admin/dashboard" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'dashboard' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'dashboard' ? 'data-active="true"' : ''; ?>>
                        <img src="/assets/images/dashboard.png" alt="Dashboard" class="w-4 h-4 opacity-100"> 
                        Dashboard
                    </a>
                </div>
                
                <div class="mb-6">
                    <div class="px-4 py-2 text-xs uppercase text-gray-600 font-semibold">Pages</div>
                    <a href="/admin/users" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'users' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'users' ? 'data-active="true"' : ''; ?>>
                        <i class="fa fa-users text-current"></i> Usuaris
                    </a>
                    <a href="/admin/charging-stations" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'charging-stations' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'charging-stations' ? 'data-active="true"' : ''; ?>>
                        <i class="fa fa-charging-station text-current"></i> Punts de càrrega
                    </a>
                    <a href="/admin/vehicles" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'vehicles' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'vehicles' ? 'data-active="true"' : ''; ?>>
                        <i class="fa fa-car text-current"></i> Vehicles
                    </a>
                    <a href="/admin/incidents" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'incidents' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'incidents' ? 'data-active="true"' : ''; ?>>
                        <i class="fa fa-flag text-current"></i> Incidencies
                    </a>
                </div>
            </nav>
        </aside>
        
        <main class="flex-1 overflow-auto">
            <div class="p-10">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-semibold"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <button id="notificationButton" class="relative p-2 text-[#212121] hover:text-white hover:bg-[#00C853] rounded-lg transition-colors">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                            <div id="notificationMenu" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg py-2 z-50">
                                <div class="px-4 py-2 border-b border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-900">Notificacions</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <p class="text-sm text-gray-800">Nova reserva completada</p>
                                        <p class="text-xs text-gray-500 mt-1">Fa 5 minuts</p>
                                    </a>
                                    <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <p class="text-sm text-gray-800">Nou usuari registrat</p>
                                        <p class="text-xs text-gray-500 mt-1">Fa 30 minuts</p>
                                    </a>
                                    <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <p class="text-sm text-gray-800">Incidència reportada</p>
                                        <p class="text-xs text-gray-500 mt-1">Fa 1 hora</p>
                                    </a>
                                </div>
                                <div class="px-4 py-2 border-t border-gray-200">
                                    <a href="#" class="text-sm text-[#00C853] hover:text-[#008f3b] transition-colors">Veure totes</a>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <button id="profileButton" class="flex items-center gap-3 focus:outline-none" aria-haspopup="true" aria-expanded="false">
                                <div class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center text-xs font-semibold text-white">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'AD', 0, 2)); ?>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-sm font-medium hidden sm:block"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['email'] ?? 'admin@voltacar.com'); ?></span>
                                </div>
                                <i class="fas fa-caret-down ml-2 text-gray-500"></i>
                            </button>
                            <div id="profileMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50">
                                <form action="/logout" method="post" class="m-0">
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Tanca sessió</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>