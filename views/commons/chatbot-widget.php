<?php
/**
 * 💬 Widget de Chat Flotante
 * Se incluye en todas las páginas para acceso rápido al chatbot
 */

// Solo mostrar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    return;
}
?>

<!-- Botón flotante del chat -->
<button id="chat-toggle" class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 z-50 flex items-center justify-center" aria-label="<?php echo __('chat.open_assistant'); ?>">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
    </svg>
    <span id="chat-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">1</span>
</button>

<!-- Ventana del chat (oculta por defecto) -->
<div id="chat-widget" class="fixed bottom-24 right-6 w-96 bg-white rounded-lg shadow-2xl z-50 hidden flex-col" style="height: 500px; max-height: calc(100vh - 150px);">
    <!-- Header del chat -->
    <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-sm"><?php echo __('chat.title'); ?></h3>
                <p class="text-xs text-blue-100"><?php echo __('chat.subtitle'); ?></p>
            </div>
        </div>
        <button id="chat-close" class="text-white hover:bg-blue-700 rounded p-1 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Mensajes del chat -->
    <div id="widget-chat-messages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-4">
        <!-- Mensaje de bienvenida -->
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <p class="text-sm text-gray-800"><?php echo __('chat.welcome_message'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicador de escritura -->
    <div id="widget-typing-indicator" class="hidden px-4 py-2">
        <div class="flex items-center space-x-2 text-gray-500">
            <div class="flex space-x-1">
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            </div>
            <span class="text-xs"><?php echo __('chat.typing'); ?></span>
        </div>
    </div>

    <!-- Input del chat -->
    <div class="border-t border-gray-200 p-4 bg-white rounded-b-lg">
        <form id="widget-chat-form" class="flex items-center space-x-2">
            <input 
                type="text" 
                id="widget-chat-input" 
                placeholder="<?php echo __('chat.input_placeholder'); ?>" 
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                maxlength="1000"
                autocomplete="off"
            >
            <button 
                type="submit" 
                id="widget-send-button"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </form>
        <p class="text-xs text-gray-500 mt-2">
            <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <?php echo __('chat.info_message'); ?>
        </p>
    </div>
</div>

<!-- Estilos adicionales para el widget -->
<style>
#chat-widget {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

#chat-toggle {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

#chat-toggle:hover {
    transform: scale(1.05);
}

@media (max-width: 640px) {
    #chat-widget {
        position: fixed;
        bottom: 0;
        right: 0;
        left: 0;
        width: 100%;
        height: 70vh;
        max-height: 70vh;
        border-radius: 16px 16px 0 0;
    }
    
    #chat-toggle {
        bottom: 20px;
        right: 20px;
    }
}

/* Animación del badge */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

#chat-badge {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<!-- Script del widget -->
<script>
// Translated error messages
const CHAT_ERRORS = {
    noResponse: <?php echo json_encode(__('chat.error_no_response')); ?>,
    connection: <?php echo json_encode(__('chat.error_connection')); ?>
};

document.addEventListener('DOMContentLoaded', function() {
    const chatToggle = document.getElementById('chat-toggle');
    const chatWidget = document.getElementById('chat-widget');
    const chatClose = document.getElementById('chat-close');
    const chatForm = document.getElementById('widget-chat-form');
    const chatInput = document.getElementById('widget-chat-input');
    const sendButton = document.getElementById('widget-send-button');
    const messagesContainer = document.getElementById('widget-chat-messages');
    const typingIndicator = document.getElementById('widget-typing-indicator');

    // Toggle del widget
    chatToggle.addEventListener('click', function() {
        chatWidget.classList.toggle('hidden');
        chatWidget.classList.toggle('flex');
        if (!chatWidget.classList.contains('hidden')) {
            chatInput.focus();
            scrollToBottom();
        }
    });

    // Cerrar widget
    chatClose.addEventListener('click', function() {
        chatWidget.classList.add('hidden');
        chatWidget.classList.remove('flex');
    });

    // Enviar mensaje
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;

        // Deshabilitar input
        chatInput.disabled = true;
        sendButton.disabled = true;

        // Añadir mensaje del usuario
        addMessage(message, 'user');
        chatInput.value = '';

        // Mostrar indicador de escritura
        typingIndicator.classList.remove('hidden');
        scrollToBottom();

        try {
            const response = await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            typingIndicator.classList.add('hidden');

            if (response.ok && data.success) {
                addMessage(data.message, 'bot');
            } else {
                let errorMsg = data.error || CHAT_ERRORS.noResponse;
                if (data.details && data.details.error && data.details.error.message) {
                    errorMsg += ': ' + data.details.error.message;
                }
                addMessage('❌ Error: ' + errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            typingIndicator.classList.add('hidden');
            addMessage('❌ ' + CHAT_ERRORS.connection, 'error');
        } finally {
            chatInput.disabled = false;
            sendButton.disabled = false;
            chatInput.focus();
            scrollToBottom();
        }
    });

    function addMessage(text, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start space-x-3';

        if (type === 'user') {
            messageDiv.classList.add('flex-row-reverse', 'space-x-reverse');
            messageDiv.innerHTML = `
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 text-right">
                    <div class="inline-block bg-blue-600 text-white rounded-lg p-3 max-w-xs shadow-sm">
                        <p class="text-sm">${escapeHtml(text)}</p>
                    </div>
                </div>
            `;
        } else if (type === 'bot') {
            messageDiv.innerHTML = `
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-lg p-3 max-w-xs shadow-sm">
                        <p class="text-sm text-gray-800">${escapeHtml(text)}</p>
                    </div>
                </div>
            `;
        } else if (type === 'error') {
            messageDiv.innerHTML = `
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="bg-red-100 border-l-4 border-red-500 rounded-lg p-3 max-w-xs">
                        <p class="text-sm text-red-700">${escapeHtml(text)}</p>
                    </div>
                </div>
            `;
        }

        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
