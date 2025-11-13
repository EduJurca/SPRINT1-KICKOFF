document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    const tabButtons = document.querySelectorAll('.tab-button');
    
    // Funcionalidad del menú de notificaciones
    const notificationButton = document.getElementById('notificationButton');
    const notificationMenu = document.getElementById('notificationMenu');
    let isNotificationMenuOpen = false;

    if (notificationButton && notificationMenu) {
        notificationButton.addEventListener('click', function(e) {
            e.stopPropagation();
            isNotificationMenuOpen = !isNotificationMenuOpen;
            notificationMenu.classList.toggle('hidden');
        });
    }

    // Perfil dropdown
    const profileButton = document.getElementById('profileButton');
    const profileMenu = document.getElementById('profileMenu');
    let isProfileMenuOpen = false;

    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', function(e) {
            e.stopPropagation();
            isProfileMenuOpen = !isProfileMenuOpen;
            profileMenu.classList.toggle('hidden');
        });
    }

    // Cerrar menús cuando se hace clic fuera
    document.addEventListener('click', function(e) {
        if (isNotificationMenuOpen && notificationMenu && !notificationMenu.contains(e.target) && !notificationButton.contains(e.target)) {
            notificationMenu.classList.add('hidden');
            isNotificationMenuOpen = false;
        }
        if (isProfileMenuOpen && profileMenu && !profileMenu.contains(e.target) && !profileButton.contains(e.target)) {
            profileMenu.classList.add('hidden');
            isProfileMenuOpen = false;
        }
    });
    
    // Funcionalidad para enlaces del sidebar
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            navLinks.forEach(nl => {
                nl.classList.remove('bg-blue-900', 'text-white');
                nl.classList.add('text-gray-900');
                nl.removeAttribute('data-active');
            });
            
            this.classList.add('bg-blue-900', 'text-white');
            this.classList.remove('text-gray-900');
            this.setAttribute('data-active', 'true');
        });
    });
    
    // Funcionalidad para botones de tabs
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            tabButtons.forEach(btn => {
                btn.classList.remove('bg-blue-900', 'text-white');
                btn.classList.add('text-gray-600');
                btn.removeAttribute('data-active');
            });

            this.classList.add('bg-blue-900', 'text-white');
            this.classList.remove('text-gray-600');
            this.setAttribute('data-active', 'true');
        });
    });

    // Sidebar (mobile) - toggle, backdrop, Escape key, aria-expanded
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const mobileSidebarClose = document.getElementById('mobileSidebarClose');

    function openSidebar() {
        if (!adminSidebar) return;
        adminSidebar.classList.remove('hidden');
        adminSidebar.classList.add('flex');
        sidebarBackdrop && sidebarBackdrop.classList.remove('hidden');
        if (mobileMenuButton) mobileMenuButton.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        if (!adminSidebar) return;
        adminSidebar.classList.add('hidden');
        adminSidebar.classList.remove('flex');
        sidebarBackdrop && sidebarBackdrop.classList.add('hidden');
        if (mobileMenuButton) mobileMenuButton.setAttribute('aria-expanded', 'false');
    }

    function toggleSidebar() {
        if (!adminSidebar) return;
        if (adminSidebar.classList.contains('hidden')) openSidebar();
        else closeSidebar();
    }

    // Ensure initial aria state
    if (mobileMenuButton && !mobileMenuButton.hasAttribute('aria-expanded')) {
        mobileMenuButton.setAttribute('aria-expanded', 'false');
    }

    mobileMenuButton && mobileMenuButton.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleSidebar();
    });

    sidebarBackdrop && sidebarBackdrop.addEventListener('click', function() {
        closeSidebar();
    });

    mobileSidebarClose && mobileSidebarClose.addEventListener('click', function(e) {
        e.stopPropagation();
        closeSidebar();
    });

    // Close sidebar with Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            if (adminSidebar && !adminSidebar.classList.contains('hidden')) {
                closeSidebar();
            }
        }
    });
});