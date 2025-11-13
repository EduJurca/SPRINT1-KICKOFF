// Mobile Sidebar Handler - Responsive and Touch-Friendly
(function() {
    'use strict';
    
    console.log('📱 sidebar-mobile.js: Starting');
    
    // Wait for DOM to be ready
    function waitForElements() {
        const sidebar = document.getElementById('adminSidebar');
        const button = document.getElementById('mobileMenuButton');
        const backdrop = document.getElementById('sidebarBackdrop');
        const closeBtn = document.getElementById('mobileSidebarClose');
        
        // If elements not found, retry after short delay
        if (!sidebar || !button) {
            console.warn('📱 sidebar-mobile.js: Elements not ready, retrying...');
            setTimeout(waitForElements, 100);
            return;
        }
        
        initializeSidebar(sidebar, button, backdrop, closeBtn);
    }
    
    function initializeSidebar(sidebar, button, backdrop, closeBtn) {
        console.log('📱 sidebar-mobile.js: Elements found and initializing');
        
        // State
        let isOpen = false;
        
        // Check if we're on mobile (under md breakpoint which is 768px)
        function isMobile() {
            return window.innerWidth < 768;
        }
        
        // Get current display state
        function getSidebarDisplay() {
            if (isMobile()) {
                return sidebar.style.display || 'none';
            } else {
                // On desktop, check computed style
                const computed = window.getComputedStyle(sidebar);
                return computed.display;
            }
        }
        
        // Initialize: hide sidebar on mobile, show on desktop
        function initialize() {
            const mobile = isMobile();
            console.log('📱 sidebar-mobile.js: Initializing, isMobile=' + mobile);
            
            if (mobile) {
                sidebar.style.display = 'none';
                isOpen = false;
                console.log('📱 sidebar-mobile.js: Mobile mode - sidebar hidden');
            } else {
                // Desktop: remove inline style to let Tailwind handle it
                sidebar.style.display = '';
                isOpen = false;
                console.log('📱 sidebar-mobile.js: Desktop mode - sidebar visible');
            }
            
            // Hide backdrop on desktop
            if (backdrop) {
                backdrop.style.display = isMobile() ? 'none' : 'none';
            }
        }
        
        // Open sidebar (mobile only)
        function open() {
            if (!isMobile()) {
                console.log('📱 sidebar-mobile.js: Not mobile, skipping open');
                return;
            }
            
            console.log('📱 sidebar-mobile.js: Opening sidebar');
            isOpen = true;
            sidebar.style.display = 'flex';
            sidebar.style.zIndex = '50';
            
            if (backdrop) {
                backdrop.classList.add('visible');
                backdrop.style.display = 'block';
            }
            
            document.body.style.overflow = 'hidden';
            button.setAttribute('aria-expanded', 'true');
            console.log('📱 sidebar-mobile.js: Sidebar opened');
        }
        
        // Close sidebar
        function close() {
            console.log('📱 sidebar-mobile.js: Closing sidebar');
            sidebar.style.display = 'none';
            
            if (backdrop) {
                backdrop.classList.remove('visible');
                backdrop.style.display = 'none';
            }
            
            document.body.style.overflow = 'auto';
            isOpen = false;
            button.setAttribute('aria-expanded', 'false');
            console.log('📱 sidebar-mobile.js: Sidebar closed');
        }
        
        // Toggle
        function toggle() {
            console.log('📱 sidebar-mobile.js: Toggle called, isOpen=' + isOpen);
            if (isOpen) close();
            else open();
        }
        
        // Attach event listeners to button
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('📱 sidebar-mobile.js: Button click detected');
            toggle();
        });
        
        // Touch support for button
        button.addEventListener('touchstart', function(e) {
            // Don't prevent default for touch - let it be a normal click too
            console.log('📱 sidebar-mobile.js: Button touch detected');
        });
        
        // Prevent click from propagating to backdrop
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Backdrop click to close
        if (backdrop) {
            backdrop.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('📱 sidebar-mobile.js: Backdrop clicked');
                close();
            });
        }
        
        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('📱 sidebar-mobile.js: Close button clicked');
                close();
            });
        }
        
        // Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isOpen) {
                console.log('📱 sidebar-mobile.js: Escape key pressed');
                close();
            }
        });
        
        // Handle window resize - reinitialize when crossing breakpoint
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                console.log('📱 sidebar-mobile.js: Window resize detected');
                initialize();
            }, 150);
        });
        
        // Initialize on load
        initialize();
        
        // Set initial aria-expanded state
        button.setAttribute('aria-expanded', 'false');
        
        console.log('✅ sidebar-mobile.js: Initialized successfully');
    }
    
    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', waitForElements);
    } else {
        waitForElements();
    }
    
})();
