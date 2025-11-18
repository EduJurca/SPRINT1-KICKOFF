<?php require_once VIEWS_PATH . '/admin/admin-header.php'; ?>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Editar Usuari</h2>
                    <p class="text-sm text-gray-600 mt-1">Modifica la informació de l'usuari</p>
                </div>
                <a href="/admin/users" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Tornar al llistat
                </a>
            </div>
        </div>

        <!-- Missatges d'error -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold"><?= htmlspecialchars($_SESSION['error']) ?></p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Formulari -->
        <div class="bg-gray-100 rounded-lg shadow-md p-8">
            <form method="POST" action="/admin/users/update" class="space-y-6" id="editUserForm" novalidate>
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                
                <!-- Informació Bàsica -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informació Bàsica</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="<?= htmlspecialchars($user['username']) ?>"
                                required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                            >
                            <p id="error-username" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?= htmlspecialchars($user['email']) ?>"
                                required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                            >
                            <p id="error-email" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>

                        <!-- Nom Complet -->
                        <div>
                            <label for="fullname" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nom Complet
                            </label>
                            <input 
                                type="text" 
                                id="fullname" 
                                name="fullname"
                                value="<?= htmlspecialchars($user['fullname'] ?? '') ?>"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                            >
                        </div>

                        <!-- Telèfon -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                Telèfon
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone"
                                value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                            >
                        </div>

                        <!-- Rol -->
                        <div class="md:col-span-2">
                            <label for="role_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Rol <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="role_id" 
                                name="role_id"
                                required
                                <?= $user['id'] == 1 ? 'disabled' : '' ?>
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"
                            >
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['name']) ?>
                                        <?php if (!empty($role['description'])): ?>
                                            - <?= htmlspecialchars($role['description']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($user['id'] == 1): ?>
                                <p class="text-xs text-gray-500 mt-1">El SuperAdmin principal no pot canviar de rol</p>
                                <input type="hidden" name="role_id" value="1">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Botons d'acció -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="/admin/users" 
                       class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold transition-all shadow-sm hover:shadow-md">
                        Cancel·lar
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-[#1565C0] hover:bg-blue-700 text-white rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar Canvis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editUserForm');
    const requiredMsg = '<?php echo addslashes(__('form.validations.required_field')); ?>';
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Username
        const username = document.getElementById('username');
        const errorUsername = document.getElementById('error-username');
        if (!username.value.trim()) {
            errorUsername.textContent = requiredMsg;
            errorUsername.classList.remove('hidden');
            username.classList.add('border-red-500');
            isValid = false;
        } else {
            errorUsername.classList.add('hidden');
            username.classList.remove('border-red-500');
        }
        
        // Email
        const email = document.getElementById('email');
        const errorEmail = document.getElementById('error-email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim()) {
            errorEmail.textContent = requiredMsg;
            errorEmail.classList.remove('hidden');
            email.classList.add('border-red-500');
            isValid = false;
        } else if (!emailRegex.test(email.value.trim())) {
            errorEmail.textContent = 'Correu no vàlid';
            errorEmail.classList.remove('hidden');
            email.classList.add('border-red-500');
            isValid = false;
        } else {
            errorEmail.classList.add('hidden');
            email.classList.remove('border-red-500');
        }
        
        if (!isValid) {
            e.preventDefault();
            const firstError = form.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
    
    // Netejar errors quan l'usuari escriu
    ['username', 'email'].forEach(fieldName => {
        const field = document.getElementById(fieldName);
        const errorEl = document.getElementById(`error-${fieldName}`);
        field.addEventListener('input', function() {
            errorEl.classList.add('hidden');
            field.classList.remove('border-red-500');
        });
    });
});
</script>

<?php require_once VIEWS_PATH . '/admin/admin-footer.php'; ?>
