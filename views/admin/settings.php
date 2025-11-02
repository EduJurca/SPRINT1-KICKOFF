<?php
/**
 * Vista: Configuració del Sistema
 * Panel de configuración y ajustes del sistema
 */

// La autenticación ya se verifica en AdminController::requireAdmin()

// Configuración de la vista
$title = $title ?? 'Configuració - Panel d\'Administració';
$pageTitle = $pageTitle ?? 'Configuració del Sistema';
$currentPage = $currentPage ?? 'settings';

// Incluir el header de admin
require_once __DIR__ . '/admin-header.php';
?>

<!-- Contenido principal -->
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Configuració del Sistema</h2>
    <p class="text-gray-600 mt-1">Gestiona la configuració general de l'aplicació</p>
</div>

<!-- Pestañas de configuración -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button class="tab-button active px-6 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600">
                General
            </button>
            <button class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                Preus
            </button>
            <button class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                Notificacions
            </button>
            <button class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                Integrations
            </button>
        </nav>
    </div>
</div>

<!-- Configuración General -->
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informació de l'aplicació</h3>
        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom de l'aplicació</label>
                <input type="text" value="VoltiaCar" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descripció</label>
                <textarea rows="3" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">Sistema de mobilitat sostenible per a Tarragona</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Correu de contacte</label>
                    <input type="email" value="info@voltiacar.cat" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telèfon de contacte</label>
                    <input type="tel" value="+34 977 123 456" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Preus i tarifes</h3>
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preu per minut (€)</label>
                    <input type="number" step="0.01" value="0.25" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descompte Premium (%)</label>
                    <input type="number" value="20" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preu Premium mensual (€)</label>
                    <input type="number" step="0.01" value="29.99" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Límits i restriccions</h3>
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Temps màxim de reserva (min)</label>
                    <input type="number" value="480" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bateria mínima per reservar (%)</label>
                    <input type="number" value="20" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="require_license" checked 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="require_license" class="ml-2 text-sm text-gray-700">
                    Requerir carnet de conduir verificat per reservar
                </label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="maintenance_mode" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="maintenance_mode" class="ml-2 text-sm text-gray-700">
                    Mode manteniment (deshabilitar reserves noves)
                </label>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Notificacions</h3>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium">Notificacions per email</p>
                    <p class="text-sm text-gray-500">Enviar notificacions als usuaris per correu electrònic</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium">Notificacions push</p>
                    <p class="text-sm text-gray-500">Enviar notificacions push als dispositius mòbils</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium">Alertes d'administrador</p>
                    <p class="text-sm text-gray-500">Rebre alertes sobre problemes del sistema</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="flex justify-end space-x-3">
        <button class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel·lar
        </button>
        <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-save mr-2"></i>Guardar Canvis
        </button>
    </div>
</div>

<?php
// Incluir el footer de admin
require_once __DIR__ . '/admin-footer.php';
?>
