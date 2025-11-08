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

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
                <tr>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['type']) ?>
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
                                <?php 
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'in_progress' => 'bg-blue-100 text-blue-800',
                                    'resolved' => 'bg-green-100 text-green-800'
                                ];
                                $statusLabels = [
                                    'pending' => 'Pendent',
                                    'in_progress' => 'En Progrés',
                                    'resolved' => 'Resolta'
                                ];
                                $color = $statusColors[$incident['status']] ?? 'bg-gray-100 text-gray-800';
                                $label = $statusLabels[$incident['status']] ?? $incident['status'];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $color ?>">
                                    <?= $label ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-gray-600 hover:text-indigo-900 mr-3" title="Veure detalls">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (Permissions::can('incidents.edit')): ?>
                                    <a href="/admin/incidents/<?= $incident['id'] ?>/edit" class="text-blue-600 hover:text-blue-900 mr-3" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (Permissions::can('incidents.resolve') && $incident['status'] !== 'resolved'): ?>
                                    <form method="POST" action="/admin/incidents/<?= $incident['id'] ?>/resolve" class="inline js-confirm" data-confirm-message="Segur que vols marcar aquesta incidència com resolta?">
                                        <button type="submit" class="text-green-600 hover:text-green-900 mr-3" title="Marcar com resolta">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if (Permissions::can('incidents.delete')): ?>
                                    <form method="POST" action="/admin/incidents/<?= $incident['id'] ?>" class="inline js-confirm" data-confirm-message="Segur que vols eliminar aquesta incidència?">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php

?>
<script src="/assets/js/toast.js"></script>
<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<?php if (!empty($_SESSION['success'])): ?>
    <script>window.Toast && window.Toast.success(<?php echo json_encode($_SESSION['success']); ?>, 5000);</script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <script>window.Toast && window.Toast.error(<?php echo json_encode($_SESSION['error']); ?>, 5000);</script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>