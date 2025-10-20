if (!document.getElementById('toast-container')) {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.setAttribute('aria-live', 'polite');
    container.className = 'fixed top-5 right-5 space-y-2 z-50';

    if (document.body) {
        document.body.appendChild(container);
    } else {
        window.addEventListener('DOMContentLoaded', () => document.body.appendChild(container));
    }
}

window.showToast = function(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const bgColors = {
        success: 'bg-green-500',
        alert: 'bg-blue-500',
        warning: 'bg-orange-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };

    const bgColor = bgColors[type] || bgColors.info;

    const toast = document.createElement('div');
    toast.setAttribute('role', 'status');
    toast.className = `max-w-xs px-4 py-2 rounded shadow-lg text-white ${bgColor} transform translate-x-6 opacity-0 transition-all duration-300 ease-out`;
    toast.style.willChange = 'transform, opacity';

    toast.innerText = message;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-6', 'opacity-0');
        toast.classList.add('translate-x-0', 'opacity-100');
    });

    const hide = () => {
        toast.classList.remove('translate-x-0', 'opacity-100');
        toast.classList.add('translate-x-6', 'opacity-0');
        setTimeout(() => {
            try { toast.remove(); } catch (e) { }
        }, 350);
    };

    const timer = setTimeout(hide, duration);

    toast.addEventListener('click', () => {
        clearTimeout(timer);
        hide();
    });
};
