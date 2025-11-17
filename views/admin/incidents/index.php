<?php
require_once __DIR__ . '/../admin-header.php';
?>

<div class="w-full px-4 md:px-6 lg:px-10 py-4 md:py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4 md:mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?php echo __('incident.incidents_management'); ?></h1>
        <a href="/admin/incidents/create" class="w-full md:w-auto text-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm md:text-base">
            <?php echo __('incident.button_create'); ?>
        </a>
    </div>

    <!-- Tabla (oculta en móvil) -->
    <div class="hidden md:block bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('form.labels.type'); ?></th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('form.labels.description'); ?></th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('form.labels.creator'); ?></th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('form.labels.assignee'); ?></th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('form.labels.creation_date'); ?></th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('form.labels.status'); ?></th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('form.labels.actions'); ?></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($incidents)): ?>
                    <tr>
                        <td colspan="7" class="px-4 md:px-6 py-4 text-center text-gray-500">
                            <?php echo __('no_registered_incidents'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($incidents as $incident): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['type']) ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-xs md:text-sm text-gray-900 max-w-xs truncate">
                                <?php echo htmlspecialchars($incident['description']); ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['creator_name'] ?? __('unknown')); ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['assignee_name'] ?? '-'); ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900">
                                <?php echo htmlspecialchars($incident['created_at']); ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <?php 
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'in_progress' => 'bg-blue-100 text-blue-800',
                                    'resolved' => 'bg-green-100 text-green-800'
                                ];
                                $statusLabels = [
                                    'pending' => __('incident.status_pending'),
                                    'in_progress' => __('incident.status_in_progress'),
                                    'resolved' => __('incident.status_resolved')
                                ];
                                $color = $statusColors[$incident['status']] ?? 'bg-gray-100 text-gray-800';
                                $label = $statusLabels[$incident['status']] ?? $incident['status'];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $color ?>">
                                    <?= $label ?>
                                </span>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm font-medium">
                                <?php if (Permissions::can('incidents.edit')): ?>
                                    <a href="/admin/incidents/<?= $incident['id'] ?>/edit" class="text-blue-600 hover:text-blue-900 mr-3" title="<?php echo __('actions.edit'); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (Permissions::can('incidents.resolve') && $incident['status'] !== 'resolved'): ?>
                                    <form method="POST" action="/admin/incidents/<?= $incident['id'] ?>/resolve" class="inline js-confirm" data-confirm-message="<?php echo __('confirm_resolve_incident'); ?>">
                                        <button type="submit" class="text-green-600 hover:text-green-900 mr-3" title="<?php echo __('incident.mark_as_resolved'); ?>">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if (Permissions::can('incidents.delete')): ?>
                                    <form method="POST" action="/admin/incidents/<?= $incident['id'] ?>" class="inline js-confirm" data-confirm-message="<?php echo __('confirm_delete_incident'); ?>">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="<?php echo __('actions.delete'); ?>">
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

    <!-- Cards para móvil (visible solo en xs/sm, oculta md+) -->
    <div class="md:hidden space-y-3">
        <?php if (empty($incidents)): ?>
            <div class="bg-white rounded-lg p-4 shadow-sm text-center text-gray-500">
                <p class="text-sm font-medium"><?php echo __('no_registered_incidents'); ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($incidents as $incident): ?>
                <div class="bg-white rounded-lg p-4 shadow-sm space-y-2">
                    <div class="flex justify-between items-start gap-2">
                        <div class="flex-1">
                            <div class="font-semibold text-sm"><?php echo htmlspecialchars($incident['type']); ?></div>
                            <div class="text-xs text-gray-600 truncate"><?php echo htmlspecialchars($incident['description']); ?></div>
                        </div>
                        <?php 
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'in_progress' => 'bg-blue-100 text-blue-800',
                            'resolved' => 'bg-green-100 text-green-800'
                        ];
                        $statusLabels = [
                            'pending' => __('incident.status_pending'),
                            'in_progress' => __('incident.status_in_progress'),
                            'resolved' => __('incident.status_resolved')
                        ];
                        $color = $statusColors[$incident['status']] ?? 'bg-gray-100 text-gray-800';
                        $label = $statusLabels[$incident['status']] ?? $incident['status'];
                        ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $color ?> whitespace-nowrap">
                            <?= $label ?>
                        </span>
                    </div>
                    <div class="text-xs text-gray-600 space-y-1 border-t border-gray-100 pt-2">
                        <div><strong>Creador:</strong> <?php echo htmlspecialchars($incident['creator_name'] ?? __('unknown')); ?></div>
                        <div><strong>Assignat a:</strong> <?php echo htmlspecialchars($incident['assignee_name'] ?? '-'); ?></div>
                        <div><strong>Data:</strong> <?php echo htmlspecialchars($incident['created_at']); ?></div>
                    </div>
                    <div class="flex gap-2 pt-2 border-t border-gray-100">
                        <?php if (Permissions::can('incidents.edit')): ?>
                            <a href="/admin/incidents/<?= $incident['id'] ?>/edit" class="flex-1 text-center text-blue-600 hover:text-blue-900 text-xs font-medium">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        <?php endif; ?>
                        <?php if (Permissions::can('incidents.resolve') && $incident['status'] !== 'resolved'): ?>
                            <form method="POST" action="/admin/incidents/<?= $incident['id'] ?>/resolve" class="flex-1 js-confirm" data-confirm-message="<?php echo __('confirm_resolve_incident'); ?>">
                                <button type="submit" class="w-full text-green-600 hover:text-green-900 text-xs font-medium">
                                    <i class="fas fa-check"></i> Resoldre
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if (Permissions::can('incidents.delete')): ?>
                            <form method="POST" action="/admin/incidents/<?= $incident['id'] ?>" class="flex-1 js-confirm" data-confirm-message="<?php echo __('confirm_delete_incident'); ?>">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="w-full text-red-600 hover:text-red-900 text-xs font-medium">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>