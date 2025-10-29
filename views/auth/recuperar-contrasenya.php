<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('password_reset.title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/toast.js"></script>
    <style>
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-inner w-full max-w-sm flex flex-col justify-center">
        <img src="../../../../images/logo.png" alt="<?php echo __('password_reset.logo_alt'); ?>" class="h-40 w-40 rounded-full mx-auto">
        <h1 class="text-2xl font-bold text-center text-gray-900 mb-6"><?php echo __('password_reset.heading'); ?></h1>
        <form id="resetForm" autocomplete="off">
            <div class="mb-6">
                <label for="email" class="block text-gray-900 font-semibold mb-2"><?php echo __('password_reset.email_label'); ?></label>
                <input type="text" id="email" name="email"
                    class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]"
                    placeholder="<?php echo __('password_reset.email_placeholder'); ?>" required>
            </div>
            <button type="submit" id="resetBtn"
                class="w-full bg-[#1565C0] text-white font-semibold py-3 px-6 rounded-lg hover:opacity-90 transition-opacity duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                <?php echo __('password_reset.send_button'); ?>
            </button>
        </form>
        <a href="/login" class="mt-4 text-[#1565C0] text-center font-semibold hover:underline block"><?php echo __('password_reset.back_to_login'); ?></a>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resetForm = document.getElementById('resetForm');
            const emailInput = document.getElementById('email');
            const resetBtn = document.getElementById('resetBtn');

            resetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = emailInput.value.trim();

                if (!email) {
                    showToast('<?php echo __('password_reset.enter_email'); ?>', 'error');
                    return;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showToast('<?php echo __('password_reset.enter_valid_email'); ?>', 'error');
                    return;
                }

                showToast('<?php echo __('password_reset.email_sent'); ?>', 'success', 4000);
                emailInput.value = '';
            });
        });
    </script>
</body>
</html>