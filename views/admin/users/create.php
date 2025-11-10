<?php require_once VIEWS_PATH . '/admin/admin-header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="mb-6">
        <a href="/admin/users" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Tornar al llistat
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-4">Crear Nou Usuari</h1>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="/admin/users/store">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contrasenya <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                    <p class="text-xs text-gray-500 mt-1">Mínim 6 caràcters</p>
                </div>

                <!-- Nom Complet -->
                <div>
                    <label for="fullname" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom Complet
                    </label>
                    <input 
                        type="text" 
                        id="fullname" 
                        name="fullname"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- Telèfon -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Telèfon
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- Rol -->
                <div class="md:col-span-2">
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="role_id" 
                        name="role_id"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= $role['id'] == 3 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['name']) ?>
                                <?php if (!empty($role['description'])): ?>
                                    - <?= htmlspecialchars($role['description']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex gap-4 justify-end">
                <a href="/admin/users" 
                   class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg">
                    Cancel·lar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    <i class="fas fa-save mr-2"></i>Crear Usuari
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once VIEWS_PATH . '/admin/admin-footer.php'; ?>
