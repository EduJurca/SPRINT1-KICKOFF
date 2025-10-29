<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('accessibility.title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .phone-frame {
            border: 12px solid #212121;
            border-radius: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            padding: 10px;
            background-color: #212121;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="phone-frame w-full max-w-sm h-[667px] flex items-center justify-center">
        <div class="bg-white p-8 rounded-2xl shadow-inner w-full h-full flex flex-col">
            <header class="flex items-center justify-between mb-6">
                <a href="/dashboard" class="text-[#1565C0] text-sm font-semibold hover:underline"><?php echo __('accessibility.back'); ?></a>
                <h1 class="text-2xl font-bold text-gray-900 flex-1 text-center"><?php echo __('accessibility.heading'); ?></h1>
            </header>

            <main class="flex-1 overflow-y-auto space-y-6">
                <section class="bg-[#F5F5F5] p-4 rounded-lg">
                    <h2 class="font-bold text-lg mb-2"><?php echo __('accessibility.text_size'); ?></h2>
                    <p class="text-sm text-gray-600 mb-4"><?php echo __('accessibility.text_size_description'); ?></p>
                    <div class="flex items-center space-x-4">
                        <button class="flex-1 bg-gray-300 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-300" aria-label="<?php echo __('accessibility.decrease_text'); ?>">
                            <span class="text-xs">A-</span>
                        </button>
                        <button class="flex-1 bg-gray-300 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-300" aria-label="<?php echo __('accessibility.reset_text'); ?>">
                            <span class="text-base">A</span>
                        </button>
                        <button class="flex-1 bg-gray-300 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-300" aria-label="<?php echo __('accessibility.increase_text'); ?>">
                            <span class="text-xl">A+</span>
                        </button>
                    </div>
                </section>

                <section class="bg-[#F5F5F5] p-4 rounded-lg">
                    <h2 class="font-bold text-lg mb-2"><?php echo __('accessibility.contrast'); ?></h2>
                    <p class="text-sm text-gray-600 mb-4"><?php echo __('accessibility.contrast_description'); ?></p>
                    <div class="flex justify-between items-center space-x-4">
                        <button class="flex-1 bg-white border border-gray-300 py-2 rounded-lg hover:bg-gray-100 transition-colors duration-300" aria-label="<?php echo __('accessibility.normal_contrast'); ?>"><?php echo __('accessibility.normal_contrast'); ?></button>
                        <button class="flex-1 bg-black text-white py-2 rounded-lg hover:bg-gray-800 transition-colors duration-300" aria-label="<?php echo __('accessibility.high_contrast'); ?>"><?php echo __('accessibility.high_contrast'); ?></button>
                    </div>
                </section>

                <section class="bg-[#F5F5F5] p-4 rounded-lg">
                    <h2 class="font-bold text-lg mb-2"><?php echo __('accessibility.font_type'); ?></h2>
                    <p class="text-sm text-gray-600 mb-4"><?php echo __('accessibility.font_type_description'); ?></p>
                    <div class="flex justify-between items-center space-x-4">
                        <button class="flex-1 bg-gray-300 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-300" aria-label="<?php echo __('accessibility.default_font'); ?>"><?php echo __('accessibility.default_font'); ?></button>
                        <button class="flex-1 bg-gray-300 py-2 rounded-lg font-mono hover:bg-gray-400 transition-colors duration-300" aria-label="<?php echo __('accessibility.monospace_font'); ?>"><?php echo __('accessibility.monospace_font'); ?></button>
                    </div>
                </section>

                <section class="bg-[#F5F5F5] p-4 rounded-lg">
                    <h2 class="font-bold text-lg mb-2"><?php echo __('accessibility.screen_reader'); ?></h2>
                    <p class="text-sm text-gray-600 mb-4"><?php echo __('accessibility.screen_reader_description'); ?></p>
                    <div class="flex items-center">
                        <input
type="checkbox"
id="screenreader-mode"
class="h-5 w-5 rounded-full text-[#1565C0] focus:ring-[#1565C0]"
>
                        <label for="screenreader-mode" class="text-gray-900 ml-2"><?php echo __('accessibility.screen_reader_mode'); ?></label>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>