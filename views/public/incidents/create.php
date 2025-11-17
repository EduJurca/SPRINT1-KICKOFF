<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('incident.title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <!-- accessibility.css removed -->

</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-4xl bg-white p-6 rounded-2xl shadow-lg">

        <header class="w-full mb-6 grid grid-cols-3 items-center">
            <div class="text-left">
                <a href="/dashboard"
                    class="text-[#1565C0] font-semibold hover:underline"><?php echo __('profile.back'); ?></a>
            </div>
            <h1 class="text-center text-2xl font-bold text-gray-900"><?php echo __('incident.title'); ?></h1>
            <div class="flex justify-end">
                <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
            </div>
        </header>

        <div class="max-w-2xl mx-auto">
            <form action="/report-incident" method="POST" class="space-y-6">
                <div>
                    <label for="type"
                        class="block text-sm text-gray-900 font-semibold mb-2"><?php echo __('incident.type'); ?></label>
                    <select id="type" name="type"
                        class="w-full px-4 py-2 bg-[#F5F5F5] text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]">
                        <option value=""><?php echo __('incident.type_select'); ?></option>
                        <option value="mechanical"><?php echo __('incident.type_mechanical'); ?></option>
                        <option value="electrical"><?php echo __('incident.type_electrical'); ?></option>
                        <option value="other"><?php echo __('incident.type_other'); ?></option>
                    </select>
                </div>

                <div>
                    <label for="description"
                        class="block text-sm text-gray-900 font-semibold mb-2"><?php echo __('incident.description'); ?></label>
                    <textarea id="description" name="description" rows="4"
                        placeholder="<?php echo __('incident.placeholder_description'); ?>"
                        class="w-full px-4 py-2 bg-[#F5F5F5] text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0] resize-none"></textarea>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit"
                        class="px-6 py-3 bg-[#1565C0] hover:bg-[#0D47A1] text-white font-semibold rounded-lg transition-colors duration-300 shadow-md">
                        <?php echo __('incident.submit'); ?>
                    </button>
                </div>
            </form>
        </div>

    </div>

</body>

</html>