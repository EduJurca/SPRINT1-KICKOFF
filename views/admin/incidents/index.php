<?php
require_once __DIR__ . '/../admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestió d'Incidents</h1>
        <a href="/admin/incidents/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Crear Nou Incident
        </a>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            Incident creat correctament.
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipus</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripció</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creador</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignat a</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Creació</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($incidents)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No hi ha incidents registrats.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($incidents as $incident): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($incident['id']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['type']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                <?php echo htmlspecialchars($incident['description']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['creator_name'] ?? 'Desconegut'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['assignee_name'] ?? 'No assignat'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['created_at']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pendent
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-gray-600 hover:text-indigo-900 mr-3">Veure</a>
                                <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Editar</a>
                                <a href="#" class="text-red-600 hover:text-red-900">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../admin-footer.php';
?>
