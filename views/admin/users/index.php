<?php 
$currentPage = 'users';
require_once VIEWS_PATH . '/admin/admin-header.php'; 
?>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Gestió d'Usuaris</h2>
                    <p class="text-sm text-gray-600 mt-1">Gestiona els usuaris del sistema</p>
                </div>
                <?php if (can('users.create')): ?>
                <a href="/admin/users/create" class="bg-[#1565C0] hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nou Usuari
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Missatges -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm" role="alert">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-semibold"><?= htmlspecialchars($_SESSION['success']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm" role="alert">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-semibold"><?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Búsqueda Global -->
        <div class="bg-gray-100 rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="/admin/users" class="space-y-4">
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($search ?? '') ?>"
                            placeholder="Cercar per username, email o nom..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                        >
                    </div>
                    <button type="submit" class="bg-[#1565C0] hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Cercar
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="/admin/users" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm hover:shadow-md flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Netejar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Taula d'usuaris -->
        <div class="bg-gray-100 rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200">Username</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200">Nom Complet</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200">Rol</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200">Data Creació</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-200">Accions</th>
                        </tr>
                    </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-lg font-semibold text-gray-900 mb-1">No s'han trobat usuaris</p>
                                        <p class="text-sm text-gray-500 mb-4">Comença creant el primer usuari</p>
                                    </div>
                                    <?php if (can('users.create')): ?>
                                    <a href="/admin/users/create" class="text-[#1565C0] hover:text-blue-700 font-semibold inline-flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Crear el primer usuari
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"><?= $user['id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($user['fullname'] ?? '-') ?></div>
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
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $colorClass ?>">
                                        <?= htmlspecialchars($roleName) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (can('users.edit')): ?>
                                        <a href="/admin/users/edit?id=<?= $user['id'] ?>" class="text-gray-500 hover:text-blue-600 transition-colors p-2 hover:bg-gray-100 rounded-lg" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (can('users.delete') && $user['id'] != 1): ?>
                                            <button onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" 
                                                class="text-gray-500 hover:text-red-600 transition-colors p-2 hover:bg-gray-100 rounded-lg" title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        </div>
        
        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-gray-200 px-6 py-4 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm font-medium text-gray-700">
                        Mostrant <?= (($page - 1) * $perPage) + 1 ?> - <?= min($page * $perPage, $totalUsers) ?> de <?= $totalUsers ?> usuaris
                    </p>
                    
                    <nav class="flex items-center gap-2">
                        <!-- Botó Anterior -->
                        <a href="/admin/users?page=<?= max(1, $page - 1) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                           class="px-3 py-1.5 text-sm font-medium <?= $page <= 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?> bg-white border border-gray-300 rounded-lg transition-colors <?= $page <= 1 ? 'pointer-events-none' : '' ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        
                        <div class="flex items-center gap-1">
                            <?php
                            $range = 2;
                            $start = max(1, $page - $range);
                            $end = min($totalPages, $page + $range);
                            
                            if ($start > 1):
                            ?>
                                <a href="/admin/users?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                                   class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">1</a>
                                <?php if ($start > 2): ?>
                                    <span class="px-2 text-gray-500">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <a href="/admin/users?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                                   class="px-3 py-1.5 text-sm font-<?= $i === $page ? 'semibold text-white bg-[#1565C0]' : 'medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50' ?> rounded-lg transition-colors">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <span class="px-2 text-gray-500">...</span>
                                <?php endif; ?>
                                <a href="/admin/users?page=<?= $totalPages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                                   class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <?= $totalPages ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Botó Següent -->
                        <a href="/admin/users?page=<?= min($totalPages, $page + 1) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                           class="px-3 py-1.5 text-sm font-medium <?= $page >= $totalPages ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?> bg-white border border-gray-300 rounded-lg transition-colors <?= $page >= $totalPages ? 'pointer-events-none' : '' ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Formulari oculto para eliminar -->
<form id="deleteForm" method="POST" action="/admin/users/delete" style="display: none;">
    <input type="hidden" name="id" id="deleteUserId" value="">
</form>

<script>
function confirmDelete(id, username) {
    if (confirm('Segur que vols eliminar l\'usuari ' + username + '?')) {
        const form = document.getElementById('deleteForm');
        document.getElementById('deleteUserId').value = id;
        form.submit();
    }
}
</script>

<?php require_once VIEWS_PATH . '/admin/admin-footer.php'; ?>
