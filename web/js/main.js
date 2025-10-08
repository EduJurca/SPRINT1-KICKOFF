/**
 * Main Application Module
 * Handles general app initialization and navigation
 */

class App {
  constructor() {
    this.initialized = false;
    this.currentPage = this.getCurrentPage();
  }

  /**
   * Initialize the application
   */
  async init() {
    if (this.initialized) return;

    // Setup mobile menu
    this.setupMobileMenu();

    // Setup smooth scrolling
    this.setupSmoothScroll();

    // Setup accessibility features
    this.setupAccessibility();

    // Setup notifications
    this.setupNotifications();

    // Page-specific initialization
    await this.initPageSpecific();

    this.initialized = true;
  }

  /**
   * Get current page name
   */
  getCurrentPage() {
    const path = window.location.pathname;
    const page = path.split('/').pop() || 'index.html';
    return page.replace('.html', '');
  }

  /**
   * Setup mobile menu toggle
   */
  setupMobileMenu() {
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (menuButton && mobileMenu) {
      menuButton.addEventListener('click', () => {
        const isExpanded = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', !isExpanded);
        mobileMenu.classList.toggle('hidden');
      });

      // Close menu when clicking outside
      document.addEventListener('click', (e) => {
        if (!menuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
          menuButton.setAttribute('aria-expanded', 'false');
          mobileMenu.classList.add('hidden');
        }
      });
    }
  }

  /**
   * Setup smooth scrolling for anchor links
   */
  setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#') return;

        e.preventDefault();
        const target = document.querySelector(href);
        
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });

          // Update focus for accessibility
          target.setAttribute('tabindex', '-1');
          target.focus();
        }
      });
    });
  }

  /**
   * Setup accessibility features
   */
  setupAccessibility() {
    // Skip to main content link
    const skipLink = document.getElementById('skip-to-main');
    if (skipLink) {
      skipLink.addEventListener('click', (e) => {
        e.preventDefault();
        const main = document.querySelector('main');
        if (main) {
          main.setAttribute('tabindex', '-1');
          main.focus();
        }
      });
    }

    // Keyboard navigation for dropdowns
    document.querySelectorAll('[role="button"]').forEach(button => {
      button.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          button.click();
        }
      });
    });

    // Focus visible for keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        document.body.classList.add('keyboard-navigation');
      }
    });

    document.addEventListener('mousedown', () => {
      document.body.classList.remove('keyboard-navigation');
    });
  }

  /**
   * Setup notification system
   */
  setupNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notification-container')) {
      const container = document.createElement('div');
      container.id = 'notification-container';
      container.className = 'fixed top-4 right-4 z-50 space-y-2';
      container.setAttribute('aria-live', 'polite');
      container.setAttribute('aria-atomic', 'true');
      document.body.appendChild(container);
    }
  }

  /**
   * Show notification
   */
  showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notification-container');
    if (!container) return;

    const colors = {
      success: 'bg-green-500',
      error: 'bg-red-500',
      warning: 'bg-yellow-500',
      info: 'bg-blue-500'
    };

    const icons = {
      success: '✓',
      error: '✕',
      warning: '⚠',
      info: 'ℹ'
    };

    const notification = document.createElement('div');
    notification.className = `${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 min-w-[300px] transform transition-all duration-300`;
    notification.setAttribute('role', 'alert');
    
    notification.innerHTML = `
      <span class="text-xl font-bold">${icons[type]}</span>
      <span class="flex-1">${message}</span>
      <button class="text-white hover:text-gray-200 font-bold" aria-label="Close notification">✕</button>
    `;

    // Add close button functionality
    const closeButton = notification.querySelector('button');
    closeButton.addEventListener('click', () => {
      notification.style.opacity = '0';
      notification.style.transform = 'translateX(100%)';
      setTimeout(() => notification.remove(), 300);
    });

    container.appendChild(notification);

    // Auto-remove after duration
    if (duration > 0) {
      setTimeout(() => {
        if (notification.parentNode) {
          notification.style.opacity = '0';
          notification.style.transform = 'translateX(100%)';
          setTimeout(() => notification.remove(), 300);
        }
      }, duration);
    }
  }

  /**
   * Page-specific initialization
   */
  async initPageSpecific() {
    switch (this.currentPage) {
      case 'index':
        await this.initIndexPage();
        break;
      case 'login':
        await this.initLoginPage();
        break;
      case 'register':
        this.initRegisterPage();
        break;
      case 'dashboard':
        await this.initDashboardPage();
        break;
      case 'map':
        await this.initMapPage();
        break;
      case 'admin/index':
      case 'admin/users':
      case 'admin/vehicles':
        await this.initAdminPages();
        break;
    }
  }

  /**
   * Initialize index page
   */
  async initIndexPage() {
    console.log('Initializing index page');
    // Index page is public but should show auth state
    // No redirection needed - just update navigation
  }

  /**
   * Initialize login page
   */
  async initLoginPage() {
    const loginForm = document.getElementById('login-form');
    /* eslint-disable no-undef */
    // If already authenticated after session verification, redirect away
    await auth.restoreSession();
    if (auth.isAuthenticated()) {
      window.location.href = './dashboard.html';
      return;
    }
    /* eslint-enable no-undef */

    if (loginForm) {
      /* eslint-disable no-undef */
      loginForm.addEventListener('validSubmit', async () => {
        const formData = validator.getFormData(loginForm);
        const result = await auth.login(formData.email, formData.password);
        
        if (result.success) {
          this.showNotification('Login successful!', 'success');
          setTimeout(() => {
            window.location.href = './dashboard.html';
          }, 1000);
        } else {
          this.showNotification(result.error || 'Login failed', 'error');
        }
      });
      /* eslint-enable no-undef */
    }
  }

  /**
   * Initialize register page
   */
  initRegisterPage() {
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
      /* eslint-disable no-undef */
      registerForm.addEventListener('validSubmit', async () => {
        const formData = validator.getFormData(registerForm);
        const result = await auth.register(formData);
        
        if (result.success) {
          this.showNotification('Registration successful!', 'success');
          setTimeout(() => {
            window.location.href = './dashboard.html';
          }, 1000);
        } else {
          this.showNotification(result.error || 'Registration failed', 'error');
        }
      });
      /* eslint-enable no-undef */
    }
  }

  /**
   * Initialize dashboard page
   */
  async initDashboardPage() {
    // Require authentication
    /* eslint-disable no-undef */
    if (!(await auth.requireAuth())) return;
    /* eslint-enable no-undef */

    // Load user bookings
    await this.loadUserBookings();

    // Load available vehicles
    await this.loadAvailableVehicles();

    // Load and display user statistics
    await this.loadUserStats();
  }

  /**
   * Initialize map page
   */
  async initMapPage() {
    // Note: Map page can be viewed by both authenticated and non-authenticated users
    // But logged-in users get additional features
    
    // Initialize map when Leaflet is loaded
    /* eslint-disable no-undef */
    if (typeof L !== 'undefined') {
      // Create VehicleMap class if it doesn't exist
      if (typeof VehicleMap === 'undefined') {
        // Simple fallback map initialization
        this.initBasicMap();
      } else {
        const vehicleMap = new VehicleMap('map');
        vehicleMap.init();
        
        // Store globally for access
        window.vehicleMap = vehicleMap;
      }
    }
    /* eslint-enable no-undef */
    
    // Setup filter buttons if authenticated
    /* eslint-disable no-undef */
    if (auth.isAuthenticated()) {
      this.setupMapFilters();
    }
    /* eslint-enable no-undef */
  }

  /**
   * Initialize admin pages
   */
  async initAdminPages() {
    // Require admin authentication
    /* eslint-disable no-undef */
    if (!(await auth.requireAdmin())) return;
    /* eslint-enable no-undef */

    // Load admin-specific data
    await this.loadAdminData();
  }

  /**
   * Initialize basic map fallback
   */
  initBasicMap() {
    try {
      const map = L.map('map').setView([40.4168, -3.7038], 13);
      
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map);
      
      // Add a sample marker
      L.marker([40.4168, -3.7038]).addTo(map)
        .bindPopup('VoltiaCar - Madrid Center')
        .openPopup();
        
      window.basicMap = map;
    } catch (error) {
      console.error('Error initializing basic map:', error);
    }
  }

  /**
   * Setup map filters for authenticated users
   */
  setupMapFilters() {
    const filterButtons = document.querySelectorAll('[data-filter]');
    
    filterButtons.forEach(button => {
      button.addEventListener('click', (e) => {
        const filter = e.target.getAttribute('data-filter');
        
        // Update active button
        filterButtons.forEach(btn => {
          btn.classList.remove('bg-blue-600', 'text-white');
          btn.classList.add('bg-gray-200', 'text-gray-700');
        });
        
        e.target.classList.remove('bg-gray-200', 'text-gray-700');
        e.target.classList.add('bg-blue-600', 'text-white');
        
        // Apply filter if map exists
        if (window.vehicleMap && typeof window.vehicleMap.filterVehicles === 'function') {
          window.vehicleMap.filterVehicles(filter);
        } else if (window.basicMap) {
          console.log('Filter selected:', filter);
        }
      });
    });
  }

  /**
   * Load user bookings
   */
  async loadUserBookings() {
    try {
      /* eslint-disable no-undef */
      const response = await auth.apiRequest('/bookings');
      /* eslint-enable no-undef */
      
      if (response && response.data) {
        this.displayUserBookings(response.data);
      }
    } catch (error) {
      console.error('Error loading user bookings:', error);
      this.showNotification('Error loading bookings', 'error');
    }
  }

  /**
   * Load available vehicles
   */
  async loadAvailableVehicles() {
    try {
      /* eslint-disable no-undef */
      const response = await auth.apiRequest('/vehicles?status=available');
      /* eslint-enable no-undef */
      
      if (response && response.data) {
        this.displayAvailableVehicles(response.data);
      }
    } catch (error) {
      console.error('Error loading available vehicles:', error);
      this.showNotification('Error loading vehicles', 'error');
    }
  }

  /**
   * Load admin data
   */
  async loadAdminData() {
    try {
      /* eslint-disable no-undef */
      const [users, vehicles, bookings] = await Promise.all([
        auth.apiRequest('/users'),
        auth.apiRequest('/vehicles'),
        auth.apiRequest('/bookings')
      ]);
      /* eslint-enable no-undef */
      
      this.displayAdminStats({
        users: users.data || [],
        vehicles: vehicles.data || [],
        bookings: bookings.data || []
      });
    } catch (error) {
      console.error('Error loading admin data:', error);
      this.showNotification('Error loading admin data', 'error');
    }
  }

  /**
   * Load user statistics
   */
  async loadUserStats() {
    try {
      /* eslint-disable no-undef */
      const response = await auth.apiRequest('/bookings');
      /* eslint-enable no-undef */
      
      if (response && response.data) {
        const bookings = response.data;
        const activeBookings = bookings.filter(b => b.status === 'active').length;
        const completedBookings = bookings.filter(b => b.status === 'completed').length;
        const totalSpent = bookings
          .filter(b => b.status === 'completed' && b.total_cost)
          .reduce((sum, b) => sum + parseFloat(b.total_cost), 0);
        
        this.updateDashboardStats({
          activeBookings,
          completedBookings,
          totalSpent
        });
      }
    } catch (error) {
      console.error('Error loading user stats:', error);
      // Continue with default values if stats fail to load
    }
  }

  /**
   * Update dashboard statistics with real data
   */
  updateDashboardStats(stats) {
    // Update active bookings
    const activeBookingEl = document.querySelector('[data-stat="active-bookings"]');
    if (activeBookingEl) {
      activeBookingEl.textContent = stats.activeBookings;
    }

    // Update total trips
    const totalTripsEl = document.querySelector('[data-stat="total-trips"]');
    if (totalTripsEl) {
      totalTripsEl.textContent = stats.completedBookings;
    }

    // Update total spent
    const totalSpentEl = document.querySelector('[data-stat="total-spent"]');
    if (totalSpentEl) {
      totalSpentEl.textContent = this.formatCurrency(stats.totalSpent);
    }
  }

  /**
   * Display user bookings
   */
  displayUserBookings(bookings) {
    const activeBookingSection = document.querySelector('[data-section="active-booking"]');
    const pastBookingsTable = document.querySelector('[data-section="past-bookings"] tbody');
    
    // Find active booking
    const activeBooking = bookings.find(b => b.status === 'active');
    
    if (activeBooking && activeBookingSection) {
      this.renderActiveBooking(activeBooking, activeBookingSection);
    }
    
    // Show past bookings
    const pastBookings = bookings.filter(b => b.status !== 'active');
    
    if (pastBookingsTable) {
      this.renderPastBookings(pastBookings, pastBookingsTable);
    }
  }

  /**
   * Render active booking
   */
  renderActiveBooking(booking, container) {
    const html = `
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="flex items-start space-x-4 mb-4 md:mb-0">
          <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">${booking.vehicle_make} ${booking.vehicle_model}</h3>
            <p class="text-gray-600 mb-2">
              <span class="font-semibold">Location</span>: ${booking.vehicle_location || 'Location updating...'}
            </p>
            <div class="flex items-center space-x-4 text-sm">
              <span class="flex items-center text-gray-600">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Start Time: ${this.formatDate(booking.start_time)}
              </span>
              <span class="flex items-center text-green-600">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17 6V4c0-1.1-.9-2-2-2H9c-1.1 0-2 .9-2 2v2H2v14h20V6h-5zm-6-2h2v2h-2V4zM2 8h20v2H2V8z"/>
                </svg>
                Battery: ${booking.vehicle_battery || 'Unknown'}%
              </span>
            </div>
          </div>
        </div>
        <div class="flex flex-col space-y-2">
          <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors" onclick="app.completeBooking('${booking.id}')">
            End Trip
          </button>
          <a href="./map.html" class="bg-gray-200 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors text-center">
            View on Map
          </a>
        </div>
      </div>
    `;
    
    container.innerHTML = html;
  }

  /**
   * Render past bookings
   */
  renderPastBookings(bookings, tableBody) {
    const html = bookings.map(booking => `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="font-medium text-gray-900">${booking.vehicle_make} ${booking.vehicle_model}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
          ${this.formatDate(booking.start_time)}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
          ${booking.end_time ? this.formatDate(booking.end_time) : 'N/A'}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
          ${booking.total_cost ? this.formatCurrency(booking.total_cost) : 'N/A'}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="px-2 py-1 text-xs font-semibold rounded-full ${
            booking.status === 'completed' ? 'bg-green-100 text-green-800' :
            booking.status === 'cancelled' ? 'bg-red-100 text-red-800' :
            'bg-yellow-100 text-yellow-800'
          }">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span>
        </td>
      </tr>
    `).join('');
    
    tableBody.innerHTML = html;
  }

  /**
   * Display available vehicles
   */
  displayAvailableVehicles(vehicles) {
    const container = document.querySelector('[data-section="available-vehicles"] .grid');
    
    if (!container) return;
    
    const html = vehicles.slice(0, 3).map(vehicle => `
      <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
        <div class="bg-gradient-to-br from-blue-100 to-blue-200 h-40 flex items-center justify-center">
          <svg class="w-24 h-24 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
          </svg>
        </div>
        <div class="p-4">
          <h3 class="text-lg font-bold text-gray-900 mb-2">${vehicle.make} ${vehicle.model}</h3>
          <div class="space-y-2 text-sm text-gray-600 mb-4">
            <p class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              ${vehicle.location || 'Location updating...'}
            </p>
            <p class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17 6V4c0-1.1-.9-2-2-2H9c-1.1 0-2 .9-2 2v2H2v14h20V6h-5zm-6-2h2v2h-2V4zM2 8h20v2H2V8z"/>
              </svg>
              ${vehicle.battery_level || 'Unknown'}% - ${vehicle.range || 'Unknown'} km
            </p>
            <p class="text-lg font-bold text-blue-600">${this.formatCurrency(vehicle.price_per_hour)}<span class="text-sm font-normal text-gray-600">/hour</span></p>
          </div>
          <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors" onclick="app.bookVehicle('${vehicle.id}')">
            Book Now
          </button>
        </div>
      </div>
    `).join('');
    
    container.innerHTML = html;
  }

  /**
   * Complete a booking
   */
  async completeBooking(bookingId) {
    try {
      /* eslint-disable no-undef */
      await auth.apiRequest(`/bookings/${bookingId}/complete`, { method: 'POST' });
      /* eslint-enable no-undef */
      
      this.showNotification('Trip completed successfully!', 'success');
      // Reload page data
      await this.loadUserBookings();
      await this.loadUserStats();
    } catch (error) {
      console.error('Error completing booking:', error);
      this.showNotification('Error completing trip', 'error');
    }
  }

  /**
   * Book a vehicle
   */
  async bookVehicle(vehicleId) {
    try {
      const startTime = new Date().toISOString();
      
      /* eslint-disable no-undef */
      await auth.apiRequest('/bookings', {
        method: 'POST',
        body: JSON.stringify({
          vehicle_id: vehicleId,
          start_time: startTime
        })
      });
      /* eslint-enable no-undef */
      
      this.showNotification('Vehicle booked successfully!', 'success');
      // Reload page data
      await this.loadUserBookings();
      await this.loadAvailableVehicles();
      await this.loadUserStats();
    } catch (error) {
      console.error('Error booking vehicle:', error);
      this.showNotification(error.message || 'Error booking vehicle', 'error');
    }
  }

  /**
   * Format date for display
   */
  formatDate(dateString) {
    const date = new Date(dateString);
    /* eslint-disable no-undef */
    return date.toLocaleDateString(i18n.getCurrentLanguage(), {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
    /* eslint-enable no-undef */
  }

  /**
   * Format currency
   */
  formatCurrency(amount, currency = 'EUR') {
    /* eslint-disable no-undef */
    return new Intl.NumberFormat(i18n.getCurrentLanguage(), {
      style: 'currency',
      currency: currency
    }).format(amount);
    /* eslint-enable no-undef */
  }
}

// Create global instance
const app = new App();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', async () => await app.init());
} else {
  (async () => await app.init())();
}

// Export for use in other modules
/* eslint-disable no-undef */
if (typeof module !== 'undefined' && module.exports) {
  module.exports = app;
}
/* eslint-enable no-undef */
