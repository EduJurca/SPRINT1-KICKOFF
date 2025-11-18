<!DOCTYPE html>
<html lang="ca">

<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="/assets/images/favicon.png" type="image/png">
        <link rel="apple-touch-icon" href="/assets/images/favicon.png">
        <title><?php echo __('incident.title'); ?></title>
        <script src="https://cdn.tailwindcss.com"></script>

        <link rel="stylesheet" href="/assets/css/main.css">

        <style>
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            #scrollShadow.active {
                opacity: 1;
                box-shadow: inset 0px 10px 15px -5px rgba(0,128,0,0.3);
            }
            .error-msg { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; }
        </style>

</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-sm md:max-w-3xl lg:max-w-4xl h-[667px] md:h-auto flex items-center justify-center">
        <div class="bg-white p-5 rounded-2xl shadow-inner w-full h-full flex flex-col relative space-y-6">
            <header class="grid grid-cols-3 items-center mb-6 w-full">
                <div class="text-left">
                    <a href="/dashboard" class="text-[#1565C0] text-sm font-semibold"><?php echo __('profile.back'); ?></a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 text-center"><?php echo __('incident.title'); ?></h1>
                <div class="flex justify-end">
                    <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
                </div>
            </header>

            <div class="relative flex-1 overflow-hidden">

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form id="incidentForm" action="/report-incident" method="POST" class="overflow-y-auto no-scrollbar space-y-4 pr-1 h-full">
                    <div>
                        <label for="type" class="block text-gray-900 font-semibold mb-2"><?php echo __('incident.type'); ?></label>
                        <select id="type" name="type" class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg border-0 shadow-none appearance-none focus:outline-none focus:ring-2 focus:ring-[#1565C0] focus:border-transparent">
                            <option value=""><?php echo __('incident.type_select'); ?></option>
                            <option value="technical"><?php echo __('incident.type_technical'); ?></option>
                            <option value="maintenance"><?php echo __('incident.type_maintenance'); ?></option>
                            <option value="user_complaint"><?php echo __('incident.type_user_complaint'); ?></option>
                            <option value="accident"><?php echo __('incident.type_accident'); ?></option>
                            <option value="other"><?php echo __('incident.type_other'); ?></option>
                        </select>
                        <div class="error-msg" id="error-type"></div>
                    </div>
                    <div>
                        <label for="description" class="block text-gray-900 font-semibold mb-2"><?php echo __('incident.description'); ?></label>
                        <textarea id="description" name="description" rows="6" placeholder="<?php echo __('incident.placeholder_description'); ?>" class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg border-0 shadow-none focus:outline-none focus:ring-2 focus:ring-[#1565C0] resize-none focus:border-transparent placeholder-gray-400"></textarea>
                        <div class="error-msg" id="error-description"></div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="w-full md:w-auto bg-[#1565C0] text-white py-3 px-6 rounded-lg font-semibold">
                            <?php echo __('incident.submit'); ?>
                        </button>
                    </div>
                </form>

                <div id="scrollShadow" class="pointer-events-none absolute bottom-0 left-0 w-full h-10 bg-gradient-to-t from-green-500 via-transparent to-transparent opacity-0 transition-opacity duration-300 rounded-b-2xl"></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('incidentForm');
        const scrollShadow = document.getElementById('scrollShadow');

        function updateShadow() {
            if (form.scrollHeight > form.clientHeight && form.scrollTop + form.clientHeight < form.scrollHeight - 1) {
                scrollShadow.style.opacity = '1';
            } else {
                scrollShadow.style.opacity = '0';
            }
        }
        form.addEventListener('scroll', updateShadow);
        updateShadow();

        form.addEventListener('submit', function(e) {
            ['type','description'].forEach(f => { document.getElementById('error-' + f).textContent = ''; });

            const type = form.type.value;
            const description = form.description.value.trim();
            let valid = true;

            if (!type) {
                document.getElementById('error-type').textContent = '<?php echo addslashes(__('form.validations.required_field')); ?>';
                valid = false;
            }
            if (description.length < 10) {
                document.getElementById('error-description').textContent = '<?php echo addslashes(__('form.validations.required_field')); ?>';
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    });
    </script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>