// Sistema de notificaciones toast mejorado
const ToastNotification = {
    init() {
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.setAttribute('aria-live', 'polite');
            container.className = 'fixed top-5 right-5 space-y-3 z-[9999] w-96 max-w-[calc(100vw-2rem)]';

            if (document.body) {
                document.body.appendChild(container);
            } else {
                window.addEventListener('DOMContentLoaded', () => document.body.appendChild(container));
            }
        }
    },

    show(message, type = 'info', duration = 4000) {
        this.init();
        const container = document.getElementById('toast-container');
        if (!container) return;

        const icons = {
            success: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>`,
            error: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>`,
            warning: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                     </svg>`,
            info: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>`,
            alert: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                   </svg>`
        };

        const colorClasses = {
            success: 'bg-green-500 border-green-600',
            error: 'bg-red-500 border-red-600',
            warning: 'bg-orange-500 border-orange-600',
            info: 'bg-blue-500 border-blue-600',
            alert: 'bg-indigo-500 border-indigo-600'
        };

        const icon = icons[type] || icons.info;
        const colors = colorClasses[type] || colorClasses.info;

        const toast = document.createElement('div');
        toast.setAttribute('role', 'alert');
        toast.className = `${colors} border-l-4 rounded-lg shadow-2xl text-white p-4 transform translate-x-full opacity-0 transition-all duration-300 ease-out flex items-start space-x-3 cursor-pointer hover:shadow-xl`;
        toast.style.willChange = 'transform, opacity';

        toast.innerHTML = `
            <div class="flex-shrink-0 mt-0.5">
                ${icon}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium leading-relaxed break-words">${message}</p>
            </div>
            <button class="flex-shrink-0 ml-2 text-white hover:text-gray-200 transition-colors" aria-label="Cerrar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        });

        const hide = () => {
            toast.classList.remove('translate-x-0', 'opacity-100');
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                try { toast.remove(); } catch (e) { }
            }, 350);
        };

        const timer = setTimeout(hide, duration);

        toast.addEventListener('click', () => {
            clearTimeout(timer);
            hide();
        });

        const closeBtn = toast.querySelector('button');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                clearTimeout(timer);
                hide();
            });
        }
    }
};

// Inicializar al cargar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ToastNotification.init());
} else {
    ToastNotification.init();
}

// Función global para compatibilidad
window.showToast = (message, type = 'info', duration = 4000) => {
    ToastNotification.show(message, type, duration);
};

// Alias para notificaciones específicas
window.Toast = {
    success: (msg, duration) => ToastNotification.show(msg, 'success', duration),
    error: (msg, duration) => ToastNotification.show(msg, 'error', duration),
    warning: (msg, duration) => ToastNotification.show(msg, 'warning', duration),
    info: (msg, duration) => ToastNotification.show(msg, 'info', duration),
    alert: (msg, duration) => ToastNotification.show(msg, 'alert', duration)
};
