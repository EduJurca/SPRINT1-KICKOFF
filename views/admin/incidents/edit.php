<?php
$pageTitle = "Editar Incidència";
require_once __DIR__ . '/../admin-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-blue-600 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-white">Editar Incidència #<?= $incident['id'] ?></h1>
                <p class="text-blue-100 mt-1">Modifica els detalls de la incidència</p>
            </div>

            <div class="p-6">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Error</h3>
                                <p class="text-sm text-red-700 mt-1"><?= htmlspecialchars($_SESSION['error']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form id="incident-edit-form" action="/admin/incidents/<?= $incident['id'] ?>/update" method="POST" class="space-y-6" novalidate>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tipus -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipus d'Incidència <span class="text-red-500">*</span>
                            </label>
                            <select id="type" name="type" data-required="true"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="mechanical" <?= $incident['type'] === 'mechanical' ? 'selected' : '' ?>>Mecànica</option>
                                <option value="electrical" <?= $incident['type'] === 'electrical' ? 'selected' : '' ?>>Elèctrica</option>
                                <option value="other" <?= $incident['type'] === 'other' ? 'selected' : '' ?>>Altra</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Estat <span class="text-red-500">*</span>
                            </label>
                            <select id="status" name="status" data-required="true"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending" <?= $incident['status'] === 'pending' ? 'selected' : '' ?>>Pendent</option>
                                <option value="in_progress" <?= $incident['status'] === 'in_progress' ? 'selected' : '' ?>>En Progrés</option>
                                <option value="resolved" <?= $incident['status'] === 'resolved' ? 'selected' : '' ?>>Resolta</option>
                            </select>
                        </div>

                        <!-- Creador (NO editable) -->
                        <div>
                            <label for="creator" class="block text-sm font-medium text-gray-700 mb-2">
                                Creat per
                            </label>
                            <input type="text" 
                                   value="<?= htmlspecialchars($incident['creator_name'] ?? 'Desconegut') ?>"
                                   disabled
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed">
                        </div>

                        <!-- Assignat a -->
                        <div>
                            <label for="incident_assignee" class="block text-sm font-medium text-gray-700 mb-2">
                                Assignat a
                            </label>
                            <select id="incident_assignee" name="incident_assignee"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Sense assignar</option>
                                <?php foreach ($workers as $worker): ?>
                                    <option value="<?= $worker['id'] ?>" <?= $incident['incident_assignee'] == $worker['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($worker['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Descripció -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripció <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" rows="4" data-required="true"
                                  placeholder="Descriu detalladament el problema..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($incident['description']) ?></textarea>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes Addicionals
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="Informació addicional, observacions..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($incident['notes'] ?? '') ?></textarea>
                    </div>

                    <!-- Informació de resolució -->
                    <?php if ($incident['status'] === 'resolved'): ?>
                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Incidència Resolta</h3>
                                    <p class="text-sm text-green-700 mt-1">
                                        Resolta per: <strong><?= htmlspecialchars($incident['resolver_name'] ?? 'Desconegut') ?></strong><br>
                                        Data: <strong><?= $incident['resolved_at'] ? date('d/m/Y H:i', strtotime($incident['resolved_at'])) : 'N/A' ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Botons -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <a href="/admin/incidents"
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i>Tornar
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>Guardar Canvis
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
                    showError(el, 'Aquest camp és obligatori');
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
