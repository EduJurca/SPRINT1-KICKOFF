<?php
$currentPage = 'incidents';
$pageTitle = __("incident.edit_title");
require_once __DIR__ . '/../admin-header.php';
?>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900"><?php echo __("incident.edit_title"); ?> #<?= $incident['id'] ?></h2>
                    <p class="text-sm text-gray-600 mt-1"><?php echo __("incident.edit_heading"); ?></p>
                </div>
                <a href="/admin/incidents" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <?php echo __("incident.back"); ?>
                </a>
            </div>
        </div>

        <!-- Mostrar errores -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold"><?php echo __("incident.error_title"); ?></p>
                        <p class="mt-1"><?= htmlspecialchars($_SESSION['error']) ?></p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="bg-gray-100 rounded-lg shadow-md p-8">
            <form id="incident-edit-form" action="/admin/incidents/<?= $incident['id'] ?>/update" method="POST" class="space-y-6" novalidate>
                
                <!-- Información Básica -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informació Bàsica</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tipus -->
                        <div>
                            <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo __("incident.label_type"); ?> <span class="text-red-500">*</span>
                            </label>
                            <select id="type" name="type" data-required="true"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value="technical" <?= $incident['type'] === 'technical' ? 'selected' : '' ?>><?php echo __("incident.type_technical"); ?></option>
                                <option value="maintenance" <?= $incident['type'] === 'maintenance' ? 'selected' : '' ?>><?php echo __("incident.type_maintenance"); ?></option>
                                <option value="user_complaint" <?= $incident['type'] === 'user_complaint' ? 'selected' : '' ?>><?php echo __("incident.type_user_complaint"); ?></option>
                                <option value="accident" <?= $incident['type'] === 'accident' ? 'selected' : '' ?>><?php echo __("incident.type_accident"); ?></option>
                                <option value="other" <?= $incident['type'] === 'other' ? 'selected' : '' ?>><?php echo __("incident.type_other"); ?></option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo __("incident.label_status"); ?> <span class="text-red-500">*</span>
                            </label>
                            <select id="status" name="status" data-required="true"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value="pending" <?= $incident['status'] === 'pending' ? 'selected' : '' ?>><?php echo __("incident.status_pending"); ?></option>
                                <option value="in_progress" <?= $incident['status'] === 'in_progress' ? 'selected' : '' ?>><?php echo __("incident.status_in_progress"); ?></option>
                                <option value="resolved" <?= $incident['status'] === 'resolved' ? 'selected' : '' ?>><?php echo __("incident.status_resolved"); ?></option>
                            </select>
                        </div>

                        <!-- Creador (NO editable) -->
                        <div>
                            <label for="creator" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo __("incident.label_creator"); ?>
                            </label>
                            <input type="text" 
                                   value="<?= htmlspecialchars($incident['creator_name'] ?? __("incident.unknown")) ?>"
                                   disabled
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-200 text-gray-600 cursor-not-allowed">
                        </div>

                        <!-- Assignat a -->
                        <div>
                            <label for="incident_assignee" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo __("incident.label_assignee"); ?>
                            </label>
                            <select id="incident_assignee" name="incident_assignee"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all">
                                <option value="">-</option>
                                <?php foreach ($workers as $worker): ?>
                                    <option value="<?= $worker['id'] ?>" <?= $incident['incident_assignee'] == $worker['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($worker['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Descripció i Notes -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Descripció i Notes</h2>
                    
                    <!-- Descripció -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            <?php echo __("incident.description"); ?> <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" rows="4" data-required="true"
                                  placeholder="<?php echo __("incident.placeholder_description"); ?>"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"><?= htmlspecialchars($incident['description']) ?></textarea>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                            <?php echo __("incident.notes"); ?>
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="<?php echo __("incident.placeholder_notes"); ?>"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1565C0] focus:border-transparent transition-all"><?= htmlspecialchars($incident['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                    <!-- Informació de resolució -->
                    <?php if ($incident['status'] === 'resolved'): ?>
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <h3 class="text-sm font-semibold text-green-800"><?php echo __("incident.resolved_title"); ?></h3>
                                    <p class="text-sm text-green-700 mt-1">
                                        <?php echo __("incident.resolved_by"); ?>: <strong><?= htmlspecialchars($incident['resolver_name'] ?? __("incident.unknown")) ?></strong><br>
                                        <?php echo __("incident.resolved_at"); ?>: <strong><?= $incident['resolved_at'] ? date('d/m/Y H:i', strtotime($incident['resolved_at'])) : __("incident.not_available") ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Botons -->
                    <div class="flex justify-between items-center pt-6">
                        <a href="/admin/incidents"
                           class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel·lar
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-6 py-2.5 text-sm font-semibold text-white bg-[#1565C0] rounded-lg hover:bg-blue-700 transition-all shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <?php echo __("actions.save"); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>

<script>
(function(){
    const form = document.getElementById('incident-edit-form');
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
