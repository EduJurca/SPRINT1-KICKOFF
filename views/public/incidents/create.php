<?php
/**
 * 📝 Reportar Incidencia - Vista pública para clientes
 */

$pageTitle = "Reportar Incidencia";
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="max-w-3xl mx-auto py-8">
    <div class="bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-4">Reporta una incidencia</h1>

        <?php if (isset($error)): ?>
            <div class="mb-4 text-red-700 bg-red-50 p-3 rounded"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="/report-incident" method="POST" class="space-y-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Tipo *</label>
                <select id="type" name="type" required class="mt-1 block w-full border rounded p-2">
                    <option value="">Seleccionar...</option>
                    <option value="mechanical">Mecánica</option>
                    <option value="electrical">Eléctrica</option>
                    <option value="other">Otra</option>
                </select>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Descripción *</label>
                <textarea id="description" name="description" rows="4" required class="mt-1 block w-full border rounded p-2" placeholder="Describe el problema"></textarea>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notas (opcional)</label>
                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border rounded p-2" placeholder="Información adicional"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Enviar incidencia</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php';
