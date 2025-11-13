<?php
// Admin sidebar extracted from admin-header
?>
<aside id="adminSidebar" class="hidden md:flex md:flex-col md:w-60 lg:w-72 xl:w-80 bg-sky-50 shadow-lg fixed md:static inset-y-0 left-0 z-50 md:z-auto transition-all duration-300 min-h-screen overflow-hidden flex-col">
    <!-- Header -->
    <div class="px-3 sm:px-4 py-3 sm:py-4 flex items-start justify-between border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
            <img src="/assets/images/logo.png" alt="Voltacar Logo" class="w-10 sm:w-12 h-10 sm:h-12 flex-shrink-0 object-contain">
            <span class="hidden lg:block text-sm font-semibold text-gray-900 truncate">Voltacar</span>
        </div>
        <!-- Close button visible on mobile -->
        <button id="mobileSidebarClose" type="button" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-300 active:bg-gray-400 transition-colors flex-shrink-0 ml-2" aria-label="Cerrar menú">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-0">
        <!-- General Section -->
        <div class="mb-3 sm:mb-6 px-2 sm:px-4">
            <div class="px-1 sm:px-0 py-2 text-xs uppercase text-gray-500 font-semibold tracking-widest">General</div>
            <a href="/admin/dashboard" class="nav-link group flex items-center gap-2 sm:gap-3 px-2 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm rounded-lg transition-all duration-200 hover:bg-blue-100 hover:text-blue-900 <?php echo ($currentPage ?? '') === 'dashboard' ? 'bg-blue-900 text-white hover:bg-blue-900' : 'text-gray-700'; ?>" <?php echo ($currentPage ?? '') === 'dashboard' ? 'data-active="true"' : ''; ?>>
                <img src="/assets/images/dashboard.png" alt="Dashboard" class="w-4 h-4 opacity-100 flex-shrink-0"> 
                <span class="truncate">Dashboard</span>
            </a>
        </div>
        
        <!-- Pages Section -->
        <div class="mb-3 sm:mb-6 px-2 sm:px-4">
            <div class="px-1 sm:px-0 py-2 text-xs uppercase text-gray-500 font-semibold tracking-widest">Pages</div>
            
            <a href="/admin/users" class="nav-link group flex items-center gap-2 sm:gap-3 px-2 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm rounded-lg transition-all duration-200 hover:bg-blue-100 hover:text-blue-900 <?php echo ($currentPage ?? '') === 'users' ? 'bg-blue-900 text-white hover:bg-blue-900' : 'text-gray-700'; ?>" <?php echo ($currentPage ?? '') === 'users' ? 'data-active="true"' : ''; ?>>
                <i class="fa fa-users w-4 h-4 flex-shrink-0 text-center"></i> 
                <span class="truncate">Usuaris</span>
            </a>
            
            <a href="/admin/charging-stations" class="nav-link group flex items-center gap-2 sm:gap-3 px-2 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm rounded-lg transition-all duration-200 hover:bg-blue-100 hover:text-blue-900 <?php echo ($currentPage ?? '') === 'charging-stations' ? 'bg-blue-900 text-white hover:bg-blue-900' : 'text-gray-700'; ?>" <?php echo ($currentPage ?? '') === 'charging-stations' ? 'data-active="true"' : ''; ?>>
                <i class="fa fa-charging-station w-4 h-4 flex-shrink-0 text-center"></i> 
                <span class="truncate">Punts de càrrega</span>
            </a>
            
            <a href="/admin/vehicles" class="nav-link group flex items-center gap-2 sm:gap-3 px-2 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm rounded-lg transition-all duration-200 hover:bg-blue-100 hover:text-blue-900 <?php echo ($currentPage ?? '') === 'vehicles' ? 'bg-blue-900 text-white hover:bg-blue-900' : 'text-gray-700'; ?>" <?php echo ($currentPage ?? '') === 'vehicles' ? 'data-active="true"' : ''; ?>>
                <i class="fa fa-car w-4 h-4 flex-shrink-0 text-center"></i> 
                <span class="truncate">Vehicles</span>
            </a>
            
            <a href="/admin/incidents" class="nav-link group flex items-center gap-2 sm:gap-3 px-2 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm rounded-lg transition-all duration-200 hover:bg-blue-100 hover:text-blue-900 <?php echo ($currentPage ?? '') === 'incidents' ? 'bg-blue-900 text-white hover:bg-blue-900' : 'text-gray-700'; ?>" <?php echo ($currentPage ?? '') === 'incidents' ? 'data-active="true"' : ''; ?>>
                <i class="fa fa-flag w-4 h-4 flex-shrink-0 text-center"></i> 
                <span class="truncate">Incidencies</span>
            </a>
        </div>
    </nav>
    
    <!-- Footer (spacer) -->
    <div class="h-4 sm:h-6 flex-shrink-0"></div>
</aside>
