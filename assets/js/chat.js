/**
 * 💬 Chat.js - Gestión del chatbot con IA
 */

document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const sendButton = document.getElementById('send-button');
    const messagesContainer = document.getElementById('chat-messages');
    const typingIndicator = document.getElementById('typing-indicator');

    // Manejar envío del formulario
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        
        if (!message) {
            return;
        }

        // Deshabilitar input mientras se procesa
        chatInput.disabled = true;
        sendButton.disabled = true;

        // Añadir mensaje del usuario
        addMessage(message, 'user');
        
        // Limpiar input
        chatInput.value = '';

        // Mostrar indicador de escritura
        typingIndicator.classList.remove('hidden');

        try {
            // Enviar mensaje al servidor
            const response = await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();

            // Ocultar indicador de escritura
            typingIndicator.classList.add('hidden');

            if (response.ok && data.success) {
                // Añadir respuesta del bot
                addMessage(data.message, 'bot');
            } else {
                // Mostrar error con detalles en consola
                console.error('API Error:', data);
                let errorMsg = data.error || 'No se pudo obtener respuesta';
                if (data.details) {
                    console.error('Error details:', data.details);
                    if (data.details.error && data.details.error.message) {
                        errorMsg += ': ' + data.details.error.message;
                    }
                }
                addMessage('❌ Error: ' + errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            typingIndicator.classList.add('hidden');
            addMessage('❌ Error de conexión. Por favor, inténtalo de nuevo.', 'error');
        } finally {
            // Rehabilitar input
            chatInput.disabled = false;
            sendButton.disabled = false;
            chatInput.focus();
        }
    });

    /**
     * Añadir un mensaje al chat
     * @param {string} text - Texto del mensaje
     * @param {string} type - Tipo: 'user', 'bot', 'error'
     */
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
                    <div class="inline-block bg-blue-600 text-white rounded-lg p-4 max-w-md">
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
                    <div class="bg-gray-100 rounded-lg p-4 max-w-md">
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
                    <div class="bg-red-100 border-l-4 border-red-500 rounded-lg p-4 max-w-md">
                        <p class="text-sm text-red-700">${escapeHtml(text)}</p>
                    </div>
                </div>
            `;
        }

        messagesContainer.appendChild(messageDiv);
        
        // Scroll al último mensaje
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    /**
     * Escapar HTML para evitar XSS
     * @param {string} text - Texto a escapar
     * @returns {string} - Texto escapado
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
