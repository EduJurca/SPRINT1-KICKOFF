<?php
if (!isset($_SESSION['success']))
    $_SESSION['success'] = null;
if (!isset($_SESSION['error']))
    $_SESSION['error'] = null;
if (!isset($_SESSION['warning']))
    $_SESSION['warning'] = null;
if (!isset($_SESSION['info']))
    $_SESSION['info'] = null;
if (!isset($_SESSION['alert']))
    $_SESSION['alert'] = null;

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoltiaCar</title>
    <link rel="icon" href="assets/images/logo.png" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/toast.js"></script>
    <style>
        /* Ensure sidebar link hover works consistently */
        .nav-link,
        .nav-link i,
        .nav-link svg {
            transition: background-color .2s ease, color .2s ease;
        }

        .nav-link i,
        .nav-link svg {
            color: inherit;
        }

        .nav-link:hover,
        .nav-link[data-active="true"]:hover {
            background-color: #1565C0 !important;
            /* Lighter blue for hover */
            color: #FFFFFF !important;
        }

        /* For active items, ensure icons inherit color */
        .nav-link[data-active="true"] i,
        .nav-link[data-active="true"] svg {
            color: #FFFFFF;
        }
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


    <!-- SIDEBAR MENU-->
    <div class="flex min-h-screen">
        <aside class="w-60 bg-sky-50 flex flex-col shadow-lg">
            <div class="px-4 py-5">
                <div class="flex items-center gap-3">
                    <img src="/assets/images/logo.png" alt="<?php echo __('home.logo_alt'); ?>" class="w-12 h-12">
                </div>
            </div>

            <nav class="flex-1">
                <div class="mb-6">
                    <div class="px-4 py-2 text-xs uppercase text-gray-600 font-semibold"><?php echo __('admin.menu.general'); ?></div>
                    <a href="/admin/dashboard"
                        class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'dashboard' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>"
                        <?php echo ($currentPage ?? '') === 'dashboard' ? 'data-active="true"' : ''; ?>>
                        <img src="/assets/images/dashboard.png" alt="Dashboard" class="w-4 h-4 opacity-100">
                        <?php echo __('admin.menu.dashboard'); ?>
                    </a>
                </div>

                <div class="mb-6">
                    <div class="px-4 py-2 text-xs uppercase text-gray-600 font-semibold"><?php echo __('admin.menu.pages'); ?></div>
                    <a href="/admin/users"
                        class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'users' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>"
                        <?php echo ($currentPage ?? '') === 'users' ? 'data-active="true"' : ''; ?>>
                        <i class="fa fa-users text-current"></i> <?php echo __('admin.menu.users'); ?>
                    </a>
                    <a href="/admin/charging-stations"
                        class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'charging-stations' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>"
                        <?php echo ($currentPage ?? '') === 'charging-stations' ? 'data-active="true"' : ''; ?>>
                        <i class="fa fa-charging-station text-current"></i> <?php echo __('admin.menu.charging_stations'); ?>
                    </a>
                    <a href="/admin/vehicles"
                        class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'vehicles' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>"
                        <?php echo ($currentPage ?? '') === 'vehicles' ? 'data-active="true"' : ''; ?>>
                        <i class="fa fa-car text-current"></i> <?php echo __('admin.menu.vehicles'); ?>
                    </a>
                    <a href="/admin/incidents"
                        class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'incidents' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>"
                        <?php echo ($currentPage ?? '') === 'incidents' ? 'data-active="true"' : ''; ?>>
                        <i class="fa fa-flag text-current"></i> <?php echo __('admin.menu.incidents'); ?>
                    </a>
                </div>
            </nav>
        </aside>


        <!-- NAVBAR -->
        <main class="flex-1 overflow-auto">
            <div class="p-10">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-semibold"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="flex items-center gap-2">
                                <button id="notificationButton" class="relative p-2 text-[#212121] hover:text-white hover:bg-[#00C853] rounded-lg transition-colors">
                                    <i class="fas fa-bell text-xl"></i>
                                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                                </button>
                                <form method="POST" action="/api/users/language" class="ml-5">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M7 2a1 1 0 011 1v1h3a1 1 0 110 2H9.578a18.87 18.87 0 01-1.724 4.78c.29.354.596.696.914 1.026a1 1 0 11-1.44 1.389c-.188-.196-.373-.396-.554-.6a19.098 19.098 0 01-3.107 3.567 1 1 0 01-1.334-1.49 17.087 17.087 0 003.13-3.733 18.992 18.992 0 01-1.487-2.494 1 1 0 111.79-.89c.234.47.489.928.764 1.372.417-.934.752-1.913.997-2.927H3a1 1 0 110-2h3V3a1 1 0 011-1zm6 6a1 1 0 01.894.553l2.991 5.982a.869.869 0 01.02.037l.99 1.98a1 1 0 11-1.79.895L15.383 16h-4.764l-.724 1.447a1 1 0 11-1.788-.894l.99-1.98.019-.038 2.99-5.982A1 1 0 0113 8zm-1.382 6h2.764L13 11.236 11.618 14z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <select name="language" onchange="this.form.submit()" aria-label="<?php echo __('profile.language'); ?>" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]">
                                            <option value="ca" <?= (isset($_SESSION['user']['lang']) && $_SESSION['user']['lang'] === 'ca') || !isset($_SESSION['user']['lang']) ? 'selected' : '' ?>>
                                                🇪🇸 Català
                                            </option>
                                            <option value="en" <?= (isset($_SESSION['user']['lang']) && $_SESSION['user']['lang'] === 'en') ? 'selected' : '' ?>>
                                                🇬🇧 English
                                            </option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div id="notificationMenu" class="hidden absolute left-0 mt-2 w-80 bg-white rounded-lg shadow-lg py-2 z-50">
                                <div class="px-4 py-2 border-b border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-900"><?php echo __('admin.notifications.title'); ?></h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <p class="text-sm text-gray-800"><?php echo __('admin.notifications.new_booking'); ?></p>
                                        <p class="text-xs text-gray-500 mt-1">Fa 5 minuts</p>
                                    </a>
                                    <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <p class="text-sm text-gray-800"><?php echo __('admin.notifications.new_user'); ?></p>
                                        <p class="text-xs text-gray-500 mt-1">Fa 30 minuts</p>
                                    </a>
                                    <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <p class="text-sm text-gray-800"><?php echo __('admin.notifications.incident_reported'); ?></p>
                                        <p class="text-xs text-gray-500 mt-1">Fa 1 hora</p>
                                    </a>
                                </div>
                                <div class="px-4 py-2 border-t border-gray-200">
                                    <a href="#" class="text-sm text-[#00C853] hover:text-[#008f3b] transition-colors"><?php echo __('admin.notifications.view_all'); ?></a>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <button id="profileButton" class="flex items-center gap-3 focus:outline-none"
                                aria-haspopup="true" aria-expanded="false">
                                <div
                                    class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center text-xs font-semibold text-white">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'AD', 0, 2)); ?>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span
                                        class="text-sm font-medium hidden sm:block"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                                    <span
                                        class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['email'] ?? 'admin@voltacar.com'); ?></span>
                                </div>
                                <i class="fas fa-caret-down ml-2 text-gray-500"></i>
                            </button>
                            <div id="profileMenu"
                                class="hidden absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50">
                                <form action="/logout" method="post" class="m-0">
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"><?php echo __('header.logout'); ?></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>