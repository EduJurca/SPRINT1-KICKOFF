<?php
/**
 * Vista: Crear Nova Incidència
 * Formulari per afegir una nova incidència al sistema
 */

$currentPage = 'incidents';

require_once __DIR__ . '/../admin-header.php';

// Obtenir errors i dades antigues
$errors = $_SESSION['errors'] ?? [];
$oldData = $_SESSION['old_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_data']);
?>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900"><?php echo __("incident.create_title"); ?></h2>
                    <p class="text-sm text-gray-600 mt-1"><?php echo __("incident.create_heading"); ?></p>
                </div>
                <a href="/admin/incidents" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <?php echo __("actions.back"); ?>
                </a>
            </div>
        </div>

        <!-- Mostrar errores -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold mb-2">Errors de validació:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="bg-gray-100 rounded-lg shadow-md p-8">
            <form id="incident-create-form" action="/admin/incidents/create" method="POST" class="space-y-6" novalidate>
                
                <!-- Información Básica -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informació de la Incidència</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo __("incident.label_type"); ?> <span class="text-red-500">*</span>
                            </label>
                            <select id="type" name="type" data-required="true" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value=""><?php echo __("incident.type_select"); ?></option>
                                <option value="technical" <?= ($oldData['type'] ?? '') === 'technical' ? 'selected' : '' ?>><?php echo __("incident.type_technical"); ?></option>
                                <option value="maintenance" <?= ($oldData['type'] ?? '') === 'maintenance' ? 'selected' : '' ?>><?php echo __("incident.type_maintenance"); ?></option>
                                <option value="user_complaint" <?= ($oldData['type'] ?? '') === 'user_complaint' ? 'selected' : '' ?>><?php echo __("incident.type_user_complaint"); ?></option>
                                <option value="accident" <?= ($oldData['type'] ?? '') === 'accident' ? 'selected' : '' ?>><?php echo __("incident.type_accident"); ?></option>
                                <option value="other" <?= ($oldData['type'] ?? '') === 'other' ? 'selected' : '' ?>><?php echo __("incident.type_other"); ?></option>
                            </select>
                        </div>

                        <div>
                            <label for="incident_assignee" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo __("incident.label_assignee"); ?>
                            </label>
                            <select id="incident_assignee" name="incident_assignee"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value="">-</option>
                                <?php foreach ($workers as $worker): ?>
                                    <option value="<?php echo $worker['id']; ?>" <?= ($oldData['incident_assignee'] ?? '') == $worker['id'] ? 'selected' : '' ?>>
                                        <?php echo htmlspecialchars($worker['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Descripció i Notes -->
                <div class="pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Detalls</h2>
                    <div class="space-y-6">
                        
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo __("incident.description"); ?> <span class="text-red-500">*</span>
                            </label>
                            <textarea id="description" name="description" rows="4" data-required="true" required
                                      placeholder="<?php echo __("incident.placeholder_description"); ?>"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"><?= htmlspecialchars($oldData['description'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo __("incident.notes"); ?>
                            </label>
                            <textarea id="notes" name="notes" rows="3"
                                      placeholder="<?php echo __("incident.placeholder_notes"); ?>"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"><?= htmlspecialchars($oldData['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="/admin/incidents" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all shadow-sm hover:shadow-md">
                        <?php echo __("actions.cancel"); ?>
                    </a>
                    <button type="submit" class="px-6 py-3 bg-[#1565C0] hover:bg-blue-700 text-white rounded-lg font-semibold transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php echo __("incident.submit"); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>

<script>
(function(){
    const form = document.getElementById('incident-create-form');
    if (!form) return;
    const requiredSelector = '[data-required]';

    function clearError(el){
        const next = el.parentNode.querySelector('.field-error');
        if(next) next.remove();
        el.removeAttribute('aria-invalid');
    }

    function showError(el, msg){
        clearError(el);
        el.setAttribute('aria-invalid', 'true');
        const err = document.createElement('p');
        err.className = 'field-error text-red-600 text-sm mt-1';
        err.setAttribute('role','alert');
        err.textContent = msg;
        el.parentNode.appendChild(err);
    }

    function validateField(el){
        clearError(el);
        const tag = el.tagName.toLowerCase();
        const val = (el.value || '').toString().trim();
        if (el.hasAttribute('data-required')){
            if (val === ''){
                showError(el, '<?php echo __("form.validations.required_field"); ?>');
                return false;
            }
        }
        return true;
    }

    form.addEventListener('submit', function(e){
        let valid = true;
        const fields = form.querySelectorAll(requiredSelector);
        fields.forEach(function(f){ if(!validateField(f)) valid = false; });
        if (!valid){
            e.preventDefault();
            const firstErr = form.querySelector('.field-error');
            if (firstErr){ firstErr.scrollIntoView({behavior:'smooth', block:'center'}); }
        }
    });

    form.addEventListener('input', function(ev){
        const t = ev.target; if (t && t.hasAttribute && t.hasAttribute('data-required')) validateField(t);
    });

    form.addEventListener('blur', function(ev){
        const t = ev.target; if (t && t.hasAttribute && t.hasAttribute('data-required')) validateField(t);
    }, true);

})();
</script>