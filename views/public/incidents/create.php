<?php
// Use standard variables expected by header layout
$title = __('incident.title');
$showHeader = true;
require_once VIEWS_PATH . '/public/layouts/header.php';
?>

<div class="max-w-3xl mx-auto py-8">
    <div class="bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-4"><?php echo __('incident.title'); ?></h1>

        <?php if (isset($error)): ?>
            <div class="mb-4 text-red-700 bg-red-50 p-3 rounded"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="/report-incident" method="POST" class="space-y-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700"><?php echo __('incident.type'); ?> *</label>
                <select id="type" name="type" required class="mt-1 block w-full border rounded p-2">
                    <option value=""><?php echo __('incident.type_select'); ?></option>
                    <option value="mechanical"><?php echo __('incident.type_mechanical'); ?></option>
                    <option value="electrical"><?php echo __('incident.type_electrical'); ?></option>
                    <option value="other"><?php echo __('incident.type_other'); ?></option>
                </select>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700"><?php echo __('incident.description'); ?> *</label>
                <textarea id="description" name="description" rows="4" required class="mt-1 block w-full border rounded p-2" placeholder="<?php echo __('incident.placeholder_description'); ?>"></textarea>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700"><?php echo __('incident.notes'); ?> (<?php echo __('incident.optional'); ?>)</label>
                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border rounded p-2" placeholder="<?php echo __('incident.placeholder_notes'); ?>"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded"><?php echo __('incident.submit'); ?></button>
            </div>
        </form>
    </div>
</div>

<?php require_once VIEWS_PATH . '/public/layouts/footer.php';
