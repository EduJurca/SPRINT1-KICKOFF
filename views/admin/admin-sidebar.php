<?php
// Admin sidebar extracted from admin-header
?>
<aside id="adminSidebar" class="hidden md:flex md:flex-col w-60 bg-sky-50 shadow-lg fixed md:static inset-y-0 left-0 z-50 md:z-auto flex-col transition-all duration-200">
    <div class="px-4 py-5 flex items-center justify-between md:justify-start">
        <div class="flex items-center gap-3">
            <img src="/assets/images/logo.png" alt="Voltacar Logo" class="w-12 h-12">
        </div>
        <!-- Close button visible on mobile -->
        <button id="mobileSidebarClose" class="md:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100" aria-label="Cerrar menú">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
    </div>
    
    <nav class="flex-1 overflow-y-auto px-0">
        <div class="mb-6 px-4">
            <div class="px-0 py-2 text-xs uppercase text-gray-600 font-semibold">General</div>
            <a href="/admin/dashboard" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-[#1565C0] hover:text-gray-100 <?php echo ($currentPage ?? '') === 'dashboard' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'dashboard' ? 'data-active="true"' : ''; ?>>
                <img src="/assets/images/dashboard.png" alt="Dashboard" class="w-4 h-4 opacity-100"> 
                Dashboard
            </a>
        </div>
        
        <div class="mb-6 px-4">
            <div class="px-0 py-2 text-xs uppercase text-gray-600 font-semibold">Pages</div>
            <a href="/admin/users" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'users' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'users' ? 'data-active="true"' : ''; ?>>
                <i class="fa fa-users"></i> Usuaris
            </a>
            <a href="/admin/charging-stations" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'charging-stations' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'charging-stations' ? 'data-active="true"' : ''; ?>>
                <i class="fa fa-charging-station"></i> Punts de càrrega
            </a>
            <a href="/admin/vehicles" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'vehicles' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'vehicles' ? 'data-active="true"' : ''; ?>>
                <i class="fa fa-car"></i> Vehicles
            </a>
            <a href="/admin/incidents" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 hover:text-gray-100 <?php echo ($currentPage ?? '') === 'incidents' ? 'bg-blue-900 text-white' : 'text-gray-900'; ?>" <?php echo ($currentPage ?? '') === 'incidents' ? 'data-active="true"' : ''; ?>>
                <i class="fa fa-flag"></i> Incidencies
            </a>
        </div>
    </nav>
</aside>
