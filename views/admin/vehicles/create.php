<?php
/**
 * ➕ Vista: Crear Nuevo Vehículo (Admin)
 * Formulario para añadir un nuevo vehículo a la flota
 */

require_once __DIR__ . '/../admin-header.php';

// Obtener errores y datos antiguos
$errors = $_SESSION['errors'] ?? [];
$oldData = $_SESSION['old_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_data']);
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="/admin/vehicles" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver al listado
            </a>
            <h1 class="text-3xl font-bold text-gray-900">➕ Nuevo Vehículo</h1>
            <p class="mt-2 text-sm text-gray-600">Añade un nuevo vehículo a la flota</p>
        </div>

        <!-- Mostrar errores -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                <p class="font-semibold mb-2">❌ Errores de validación:</p>
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <form method="POST" action="/admin/vehicles" class="space-y-6">
                
                <!-- Información Básica -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">📝 Información Básica</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label for="plate" class="block text-sm font-medium text-gray-700 mb-2">
                                Matrícula <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="plate" name="plate" required
                                   value="<?= htmlspecialchars($oldData['plate'] ?? '') ?>"
                                   placeholder="1234ABC"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-2">
                                Año <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="year" name="year" required
                                   value="<?= htmlspecialchars($oldData['year'] ?? date('Y')) ?>"
                                   min="1900" max="<?= date('Y') + 1 ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">
                                Marca <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="brand" name="brand" required
                                   value="<?= htmlspecialchars($oldData['brand'] ?? '') ?>"
                                   placeholder="Tesla, Nissan, BMW..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700 mb-2">
                                Modelo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="model" name="model" required
                                   value="<?= htmlspecialchars($oldData['model'] ?? '') ?>"
                                   placeholder="Model 3, Leaf, i3..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Vehículo <span class="text-red-500">*</span>
                            </label>
                            <select id="vehicle_type" name="vehicle_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="car" <?= ($oldData['vehicle_type'] ?? '') === 'car' ? 'selected' : '' ?>>🚗 Coche</option>
                                <option value="bike" <?= ($oldData['vehicle_type'] ?? '') === 'bike' ? 'selected' : '' ?>>🚲 Bicicleta</option>
                                <option value="scooter" <?= ($oldData['vehicle_type'] ?? '') === 'scooter' ? 'selected' : '' ?>>🛴 Patinete</option>
                                <option value="motorcycle" <?= ($oldData['vehicle_type'] ?? '') === 'motorcycle' ? 'selected' : '' ?>>🏍️ Motocicleta</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado <span class="text-red-500">*</span>
                            </label>
                            <select id="status" name="status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="available" <?= ($oldData['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>✅ Disponible</option>
                                <option value="in_use" <?= ($oldData['status'] ?? '') === 'in_use' ? 'selected' : '' ?>>🔵 En uso</option>
                                <option value="charging" <?= ($oldData['status'] ?? '') === 'charging' ? 'selected' : '' ?>>🔋 Cargando</option>
                                <option value="maintenance" <?= ($oldData['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>🔧 Mantenimiento</option>
                                <option value="reserved" <?= ($oldData['status'] ?? '') === 'reserved' ? 'selected' : '' ?>>📌 Reservado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Características Técnicas -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">⚡ Características Técnicas</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label for="battery_level" class="block text-sm font-medium text-gray-700 mb-2">
                                Nivel de Batería (%)
                            </label>
                            <input type="number" id="battery_level" name="battery_level"
                                   value="<?= htmlspecialchars($oldData['battery_level'] ?? '100') ?>"
                                   min="0" max="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="price_per_minute" class="block text-sm font-medium text-gray-700 mb-2">
                                Precio por Minuto (€)
                            </label>
                            <input type="number" id="price_per_minute" name="price_per_minute"
                                   value="<?= htmlspecialchars($oldData['price_per_minute'] ?? '0.35') ?>"
                                   step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="is_accessible" name="is_accessible"
                                       <?= !empty($oldData['is_accessible']) ? 'checked' : '' ?>
                                       class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">
                                    ♿ Vehículo accesible (adaptado para personas con movilidad reducida)
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Ubicación -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">📍 Ubicación</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                                Latitud
                            </label>
                            <input type="number" id="latitude" name="latitude"
                                   value="<?= htmlspecialchars($oldData['latitude'] ?? '40.7117') ?>"
                                   step="0.000001"
                                   placeholder="40.7117"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                                Longitud
                            </label>
                            <input type="number" id="longitude" name="longitude"
                                   value="<?= htmlspecialchars($oldData['longitude'] ?? '0.5783') ?>"
                                   step="0.000001"
                                   placeholder="0.5783"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">
                                💡 Tip: Por defecto se usa la ubicación de Amposta. Puedes obtener coordenadas desde 
                                <a href="https://www.google.com/maps" target="_blank" class="text-blue-600 hover:text-blue-700">Google Maps</a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Imagen -->
                <div class="pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">🖼️ Imagen (opcional)</h2>
                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL de la Imagen
                        </label>
                        <input type="url" id="image_url" name="image_url"
                               value="<?= htmlspecialchars($oldData['image_url'] ?? '') ?>"
                               placeholder="https://ejemplo.com/imagen.jpg"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="mt-2 text-sm text-gray-500">
                            Introduce la URL de una imagen del vehículo (opcional)
                        </p>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="/admin/vehicles" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Crear Vehículo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
