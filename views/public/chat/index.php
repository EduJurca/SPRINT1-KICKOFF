<?php
/**
 * 💬 Vista: Chat con IA
 * Interfaz de chatbot para asistencia al usuario
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/chat.css">

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"><?= __('chat.title') ?></h1>
                        <p class="text-sm text-gray-500 mt-1"><?= __('chat.subtitle') ?></p>
                    </div>
                    <a href="/dashboard" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <?= __('chat.back') ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col" style="height: 600px;">
            
            <!-- Messages Area -->
            <div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-4">
                <!-- Welcome Message -->
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="bg-gray-100 rounded-lg p-4">
                            <p class="text-sm text-gray-800"><?= __('chat.welcome_message') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t border-gray-200 p-4 bg-gray-50">
                <form id="chat-form" class="flex items-center space-x-3">
                    <input 
                        type="text" 
                        id="chat-input" 
                        placeholder="<?= __('chat.input_placeholder') ?>"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        maxlength="1000"
                        required
                    >
                    <button 
                        type="submit" 
                        id="send-button"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <?= __('chat.send') ?>
                    </button>
                </form>
                
                <!-- Typing Indicator (hidden by default) -->
                <div id="typing-indicator" class="hidden mt-3 flex items-center space-x-2 text-sm text-gray-500">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                    </div>
                    <span><?= __('chat.typing') ?></span>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-blue-700"><?= __('chat.info_message') ?></p>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/chat.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
