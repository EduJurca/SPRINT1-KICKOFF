<?php
/**
 * Vista: Dashboard de Administración
 * Panel principal con estadísticas y resumen del sistema
 */

// Verificar que el usuario sea administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

// Configuración de la vista
$title = 'Dashboard - Panel d\'Administració';
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// Incluir el header de admin
require_once __DIR__ . '/admin-header.php';
?>

<!-- Estadísticas principales -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Usuarios -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Usuaris</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">1,234</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-600 font-medium">+12%</span>
            <span class="text-gray-500 ml-2">vs mes anterior</span>
        </div>
    </div>
    
    <!-- Total Vehicles -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Vehicles</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">89</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-car text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-600 font-medium">+5%</span>
            <span class="text-gray-500 ml-2">vs mes anterior</span>
        </div>
    </div>
    
    <!-- Reserves actives -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Reserves Actives</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">45</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-check text-yellow-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-red-600 font-medium">-3%</span>
            <span class="text-gray-500 ml-2">vs setmana anterior</span>
        </div>
    </div>
    
    <!-- Ingressos -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Ingressos Mensuals</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">12.5K€</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-euro-sign text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-600 font-medium">+18%</span>
            <span class="text-gray-500 ml-2">vs mes anterior</span>
        </div>
    </div>
</div>

<!-- Gráficos y tablas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Reserves recents -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Reserves Recents</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">Joan García</p>
                        <p class="text-sm text-gray-500">Tesla Model 3 - 3 dies</p>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Confirmada</span>
                </div>
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">Maria López</p>
                        <p class="text-sm text-gray-500">BMW X5 - 1 setmana</p>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">Pendent</span>
                </div>
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">Pere Martínez</p>
                        <p class="text-sm text-gray-500">Audi A4 - 2 dies</p>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Confirmada</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vehicles més populars -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Vehicles Més Populars</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-900">Tesla Model 3</span>
                            <span class="text-sm font-medium text-gray-900">156 reserves</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-900">BMW X5</span>
                            <span class="text-sm font-medium text-gray-900">132 reserves</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 72%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-900">Audi A4</span>
                            <span class="text-sm font-medium text-gray-900">98 reserves</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 53%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-900">Mercedes C-Class</span>
                            <span class="text-sm font-medium text-gray-900">87 reserves</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 47%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accions ràpides -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Accions Ràpides</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="/admin/users/add" class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all">
            <i class="fas fa-user-plus text-blue-600 text-2xl"></i>
            <span class="font-medium text-gray-900">Afegir Usuari</span>
        </a>
        <a href="/admin/vehicles/add" class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-all">
            <i class="fas fa-car text-green-600 text-2xl"></i>
            <span class="font-medium text-gray-900">Afegir Vehicle</span>
        </a>
        <a href="/admin/bookings" class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-all">
            <i class="fas fa-calendar-alt text-yellow-600 text-2xl"></i>
            <span class="font-medium text-gray-900">Veure Reserves</span>
        </a>
        <a href="/admin/reports" class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all">
            <i class="fas fa-file-download text-purple-600 text-2xl"></i>
            <span class="font-medium text-gray-900">Exportar Dades</span>
        </a>
    </div>
</div>

<?php
// Incluir el footer de admin
require_once __DIR__ . '/admin-footer.php';
?>
