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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/toast.js"></script>
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
    <?php include __DIR__ . '/admin-sidebar.php'; ?>
    <!-- Backdrop for mobile sidebar -->
    <div id="sidebarBackdrop" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" aria-hidden="true" style="pointer-events: auto;"></div>
        
        <main class="flex-1 overflow-auto">
            <div class="p-10">
                <div class="flex items-center mb-8 sticky top-0 bg-white z-40 border-b border-gray-100 py-4 -mx-4 px-4 md:-mx-6 md:px-6 lg:-mx-10 lg:px-10">
                    <!-- Mobile hamburger -->
                    <button id="mobileMenuButton" class="md:hidden p-2 rounded-md mr-2 text-gray-700 hover:bg-gray-100" aria-label="Abrir menú">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex-1"></div>
                    <div class="flex items-center gap-3 md:gap-4">
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
                        <div class="relative" id="profileContainer">
                            <button id="profileButton" class="flex items-center gap-3 focus:outline-none hover:opacity-80 transition-opacity" aria-haspopup="true" aria-expanded="false">
                                <div class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center text-xs font-semibold text-white">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'AD', 0, 2)); ?>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-sm font-medium hidden sm:block"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                                    <span class="text-xs text-gray-500 hidden sm:block truncate" style="max-width:10rem"><?php echo htmlspecialchars($_SESSION['email'] ?? 'admin@voltacar.com'); ?></span>
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
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var btn = document.getElementById('mobileMenuButton');
                        var sidebar = document.getElementById('adminSidebar');
                        var backdrop = document.getElementById('sidebarBackdrop');
                        var closeBtn = document.getElementById('mobileSidebarClose');
                        var profileBtn = document.getElementById('profileButton');

                        function toggleSidebar() {
                            if (!sidebar) return;
                            var isHidden = sidebar.classList.contains('hidden');
                            if (isHidden) {
                                sidebar.classList.remove('hidden');
                                sidebar.classList.add('flex');
                                backdrop && backdrop.classList.remove('hidden');
                            } else {
                                sidebar.classList.add('hidden');
                                sidebar.classList.remove('flex');
                                backdrop && backdrop.classList.add('hidden');
                            }
                        }

                        function closeSidebar() {
                            if (!sidebar) return;
                            sidebar.classList.add('hidden');
                            sidebar.classList.remove('flex');
                            backdrop && backdrop.classList.add('hidden');
                        }

                        btn && btn.addEventListener('click', toggleSidebar);
                        backdrop && backdrop.addEventListener('click', closeSidebar);
                        closeBtn && closeBtn.addEventListener('click', closeSidebar);
                        
                        // Prevent profile button click from triggering sidebar close
                        profileBtn && profileBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                        });
                    });
                </script>