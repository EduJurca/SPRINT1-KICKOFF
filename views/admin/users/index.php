<?php 
$currentPage = 'users';
require_once VIEWS_PATH . '/admin/admin-header.php'; 
?>

<div class="p-4 md:p-6 lg:p-10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 md:mb-8">
        <h1 class="text-xl md:text-2xl font-semibold">Gestió d'Usuaris</h1>
        <div class="w-full md:w-auto">
            <?php if (can('users.create')): ?>
            <a href="/admin/users/create" class="block md:inline-block text-center bg-blue-700 hover:bg-blue-800 text-white px-4 md:px-6 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-plus mr-2"></i>Nou Usuari
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Missatges -->
    <?php showMessages(); ?>

    <!-- Cerca -->
    <div class="flex flex-col gap-2 bg-gray-100 p-2 md:p-3 lg:p-4 rounded-lg mb-6 shadow-md">
        <form method="GET" action="/admin/users" class="flex flex-col md:flex-row gap-2">
            <input 
                type="text" 
                name="search" 
                placeholder="Cercar per username, email o nom..." 
                value="<?= htmlspecialchars($search ?? '') ?>"
                class="flex-1 px-3 md:px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 bg-white text-sm"
            >
            <button type="submit" class="px-4 md:px-6 py-2 rounded-md text-sm bg-gray-900 text-white hover:bg-gray-700 transition-colors whitespace-nowrap">
                <i class="fas fa-search"></i> Cercar
            </button>
            <?php if (!empty($search)): ?>
                <a href="/admin/users" class="px-4 md:px-6 py-2 rounded-md text-sm text-gray-600 hover:bg-gray-200 transition-colors text-center">
                    Netejar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Taula (responsive: oculta en móvil, visible md+) -->
    <div class="hidden md:block bg-gray-100 rounded-xl p-4 md:p-6 shadow-md overflow-x-auto">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom Complet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Creació</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accions</th>
                    </tr>
                </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No s'han trobat usuaris
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                <?= htmlspecialchars($user['username']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= htmlspecialchars($user['fullname'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $roleColors = [
                                    'SuperAdmin' => 'bg-purple-100 text-purple-800',
                                    'Treballador' => 'bg-blue-100 text-blue-800',
                                    'Client' => 'bg-green-100 text-green-800'
                                ];
                                $roleName = $user['role_name'] ?? 'Client';
                                $colorClass = $roleColors[$roleName] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $colorClass ?>">
                                    <?= htmlspecialchars($roleName) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if (can('users.edit')): ?>
                                <a href="/admin/users/edit?id=<?= $user['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <?php endif; ?>
                                
                                <?php if (can('users.delete') && $user['id'] != 1): ?>
                                    <form method="POST" action="/admin/users/delete" class="inline" 
                                          onsubmit="return confirm('Segur que vols eliminar aquest usuari?');">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i> Eliminar
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

    <!-- Cards para móvil (visible solo en xs/sm, oculta md+) -->
    <div class="md:hidden">
        <?php if (empty($users)): ?>
            <div class="bg-gray-100 rounded-xl p-4 shadow-md text-center text-gray-500">
                No s'han trobat usuaris
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($users as $user): ?>
                    <div class="bg-gray-100 rounded-lg p-4 shadow-sm space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-semibold text-sm"><?= htmlspecialchars($user['username']) ?></div>
                                <div class="text-xs text-gray-600"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                            <?php
                            $roleColors = [
                                'SuperAdmin' => 'bg-purple-100 text-purple-800',
                                'Treballador' => 'bg-blue-100 text-blue-800',
                                'Client' => 'bg-green-100 text-green-800'
                            ];
                            $roleName = $user['role_name'] ?? 'Client';
                            $colorClass = $roleColors[$roleName] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $colorClass ?>">
                                <?= htmlspecialchars($roleName) ?>
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">
                            <strong>Nom:</strong> <?= htmlspecialchars($user['fullname'] ?? '-') ?><br>
                            <strong>Data:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                        </div>
                        <div class="flex gap-2 pt-2 border-t border-gray-200">
                            <?php if (can('users.edit')): ?>
                            <a href="/admin/users/edit?id=<?= $user['id'] ?>" 
                               class="flex-1 text-center text-blue-600 hover:text-blue-900 text-xs font-medium">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <?php endif; ?>
                            
                            <?php if (can('users.delete') && $user['id'] != 1): ?>
                                <form method="POST" action="/admin/users/delete" class="flex-1"
                                      onsubmit="return confirm('Segur que vols eliminar aquest usuari?');">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="w-full text-red-600 hover:text-red-900 text-xs font-medium">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginació -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-4 flex justify-center overflow-x-auto">
            <nav class="flex gap-1 md:gap-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="/admin/users?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="px-2 md:px-4 py-2 rounded text-xs md:text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once VIEWS_PATH . '/admin/admin-footer.php'; ?>
