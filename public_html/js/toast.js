// toast.js
// Create a toast container if it doesn't exist
if (!document.getElementById('toast-container')) {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.setAttribute('aria-live', 'polite');
    container.className = 'fixed top-5 right-5 space-y-2 z-50';
    // If body isn't available yet (very early load), wait for DOMContentLoaded
    if (document.body) {
        document.body.appendChild(container);
    } else {
        window.addEventListener('DOMContentLoaded', () => document.body.appendChild(container));
    }
}

// showToast(message, type = 'info', duration = 3000)
window.showToast = function(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    let bgColor = 'bg-blue-500';
    if (type === 'success') bgColor = 'bg-green-500';
    if (type === 'warning') bgColor = 'bg-red-500';

    const toast = document.createElement('div');
    toast.setAttribute('role', 'status');
    toast.className = `max-w-xs px-4 py-2 rounded shadow-lg text-white ${bgColor} transform translate-x-6 opacity-0 transition-all duration-300 ease-out`;
    toast.style.willChange = 'transform, opacity';
    // Allow multi-line messages
    toast.innerText = message;

    container.appendChild(toast);

    // Trigger enter animation on next frame
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-6', 'opacity-0');
        toast.classList.add('translate-x-0', 'opacity-100');
    });

    // Hide after duration
    const hide = () => {
        // start exit animation
        toast.classList.remove('translate-x-0', 'opacity-100');
        toast.classList.add('translate-x-6', 'opacity-0');
        // remove after animation finished
        setTimeout(() => {
            try { toast.remove(); } catch (e) { /* ignore */ }
        }, 350);
    };

    const timer = setTimeout(hide, duration);

    // Allow click to dismiss early
    toast.addEventListener('click', () => {
        clearTimeout(timer);
        hide();
    });
};
