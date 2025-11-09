<?php


$pageTitle = __("incident.create_title");
require_once __DIR__ . '/../admin-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-blue-600 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-white"><?php echo __("incident.create_title"); ?></h1>
                <p class="text-blue-100 mt-1"><?php echo __("incident.create_heading"); ?></p>
            </div>

            <div class="p-6">
                <?php if (isset($error)): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800"><?php echo __("incident.error_title"); ?></h3>
                                <p class="text-sm text-red-700 mt-1"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form id="incident-create-form" action="/admin/incidents/create" method="POST" class="space-y-6" novalidate>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo __("incident.label_type"); ?> *
                            </label>
                            <select id="type" name="type" data-required="true"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value=""><?php echo __("incident.type_select"); ?></option>
                                <option value="mechanical"><?php echo __("incident.type_mechanical"); ?></option>
                                <option value="electrical"><?php echo __("incident.type_electrical"); ?></option>
                                <option value="other"><?php echo __("incident.type_other"); ?></option>
                            </select>
                        </div>

                        <div>
                            <label for="incident_assignee" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo __("incident.label_assignee"); ?>
                            </label>
                            <select id="incident_assignee" name="incident_assignee"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-</option>
                                <?php foreach ($workers as $worker): ?>
                                    <option value="<?php echo $worker['id']; ?>">
                                        <?php echo htmlspecialchars($worker['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo __("incident.description"); ?> *
                        </label>
                        <textarea id="description" name="description" rows="4" data-required="true"
                                  placeholder="<?php echo __("incident.placeholder_description"); ?>"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo __("incident.notes"); ?>
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="<?php echo __("incident.placeholder_notes"); ?>"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="/admin/incidents"
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <?php echo __("actions.cancel"); ?>
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <?php echo __("incident.submit"); ?>
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