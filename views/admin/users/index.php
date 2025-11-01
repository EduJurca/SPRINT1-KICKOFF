<?php require_once VIEWS_PATH . '/admin/admin-header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestió d'Usuaris</h1>
        <?php if (can('users.create')): ?>
        <a href="/admin/users/create" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Nou Usuari
        </a>
        <?php endif; ?>
    </div>

    <!-- Missatges -->
    <?php showMessages(); ?>

    <!-- Cerca -->
    <div class="mb-4">
        <form method="GET" action="/admin/users" class="flex gap-2">
            <input 
                type="text" 
                name="search" 
                placeholder="Cercar per username, email o nom..." 
                value="<?= htmlspecialchars($search ?? '') ?>"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                <i class="fas fa-search"></i> Cercar
            </button>
            <?php if (!empty($search)): ?>
                <a href="/admin/users" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                    Netejar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Taula -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom Complet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Creació</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Accions</th>
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
                            <td class="px-6 py-4 whitespace-nowrap"><?= $user['id'] ?></td>
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

    <!-- Paginació -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-4 flex justify-center">
            <nav class="flex gap-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="/admin/users?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="px-4 py-2 rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once VIEWS_PATH . '/admin/admin-footer.php'; ?>
