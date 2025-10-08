/**
 * Authentication Module
 * Handles user authentication, token management, and session handling
 */

class Auth {
  constructor() {
    this.apiBaseUrl = '/api'; // Will be configured for Docker environment
    this._initialized = false;
    this._authChecking = false;
    this._adminChecking = false;
    
    // Load from localStorage
    this.loadFromStorage();
    
    // Debug info available in console if needed
  }

  /**
   * Load authentication data from localStorage
   */
  loadFromStorage() {
    try {
      this.token = localStorage.getItem('authToken');
      const userStr = localStorage.getItem('user');
      this.user = userStr ? JSON.parse(userStr) : null;
      
      // Token and user loaded from localStorage
    } catch (error) {
      console.error('Error loading from storage:', error);
      this.token = null;
      this.user = null;
    }
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return !!this.token && !!this.user;
  }

  /**
   * Get current user
   */
  getCurrentUser() {
    return this.user;
  }

  /**
   * Get authentication token
   */
  getToken() {
    return this.token;
  }

  /**
   * Login user with email and password
   */
  async login(email, password) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, password })
      });

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Login failed');
      }

      const data = await response.json();
      
      // Store token and user data
      this.token = data.token;
      this.user = data.user;
      localStorage.setItem('authToken', this.token);
      localStorage.setItem('user', JSON.stringify(this.user));

      // Dispatch login event
      window.dispatchEvent(new CustomEvent('userLoggedIn', { 
        detail: { user: this.user } 
      }));

      return { success: true, user: this.user };
    } catch (error) {
      console.error('Login error:', error);
      return { success: false, error: error.message };
    }
  }

  /**
   * Register new user
   */
  async register(userData) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/auth/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
      });

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Registration failed');
      }

      const data = await response.json();
      
      // Store token and user data
      this.token = data.token;
      this.user = data.user;
      localStorage.setItem('authToken', this.token);
      localStorage.setItem('user', JSON.stringify(this.user));

      // Dispatch registration event
      window.dispatchEvent(new CustomEvent('userRegistered', { 
        detail: { user: this.user } 
      }));

      return { success: true, user: this.user };
    } catch (error) {
      console.error('Registration error:', error);
      return { success: false, error: error.message };
    }
  }

  /**
   * Logout user
   */
  async logout() {
    try {
      // Call backend logout endpoint if we have a token
      if (this.token) {
        try {
          await fetch(`${this.apiBaseUrl}/auth/logout`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${this.token}`
            }
          });
        } catch (e) {
          // Backend logout failed, but continue with local logout
          console.warn('Backend logout failed:', e);
        }
      }
      
      // Clear local storage
      localStorage.removeItem('authToken');
      localStorage.removeItem('user');
      
      // Clear instance variables
      this.token = null;
      this.user = null;
      
      // Clear any auth checking flags
      this._authChecking = false;
      this._adminChecking = false;

      // Dispatch logout event
      window.dispatchEvent(new CustomEvent('userLoggedOut'));

      // Small delay to ensure everything is cleared
      setTimeout(() => {
        // Redirect to home page
        window.location.href = './index.html';
      }, 100);
      
    } catch (error) {
      console.error('Logout error:', error);
      // Force logout even if there was an error
      localStorage.clear();
      window.location.href = './index.html';
    }
  }

  /**
   * Check if user has specific role
   */
  hasRole(role) {
    return this.user && this.user.role === role;
  }

  /**
   * Check if user is admin
   */
  isAdmin() {
    return this.hasRole('admin');
  }

  /**
   * Protect page - redirect if not authenticated
   */
  async requireAuth() {
    // If we already have valid token and user, check with backend
    if (this.token && this.user) {
      await this.restoreSession();
    }
    
    if (!this.isAuthenticated()) {
      // Only redirect if we're not already on login page
      const currentPath = window.location.pathname;
      
      if (!currentPath.includes('login.html') && !currentPath.includes('index.html')) {
        window.location.href = './login.html';
        return false;
      }
      return false;
    }
    
    return true;
  }

  /**
   * Require admin role - redirect if not admin
   */
  async requireAdmin() {
    // Prevent multiple simultaneous calls
    if (this._adminChecking) {
      while (this._adminChecking) {
        await new Promise(resolve => setTimeout(resolve, 100));
      }
      return this.isAuthenticated() && this.isAdmin();
    }
    
    this._adminChecking = true;
    
    try {
      // First, try to restore session from backend
      await this.restoreSession();
      
      if (!this.isAuthenticated()) {
        if (!window.location.pathname.includes('login.html')) {
          window.location.href = './login.html';
        }
        return false;
      }
      if (!this.isAdmin()) {
        if (!window.location.pathname.includes('dashboard.html')) {
          window.location.href = './dashboard.html';
        }
        return false;
      }
      return true;
    } finally {
      this._adminChecking = false;
    }
  }

/**
   * Clear session without redirect
   */
  clearSession() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('user');
    this.token = null;
    this.user = null;
  }

  /**
   * Validate and restore session from backend
   */
  async restoreSession() {
    console.log('Restoring session, current token:', this.token ? 'exists' : 'none');
    
    if (!this.token) {
      console.log('No token found, checking localStorage');
      return;
    }
    
    try {
      console.log('Verifying token with backend...');
      const res = await fetch(`${this.apiBaseUrl}/auth/verify`, {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.token}`
        }
      });
      
      if (res.ok) {
        const data = await res.json();
        console.log('Backend response:', data);
        
        if (data && data.data && data.data.user) {
          this.user = data.data.user;
          localStorage.setItem('user', JSON.stringify(this.user));
          console.log('Session restored successfully for user:', this.user.email);
        } else {
          console.log('Invalid response structure from backend');
        }
      } else {
        console.log('Token verification failed, status:', res.status);
        // Token invalid or expired
        this.clearSession();
      }
    } catch (e) {
      console.warn('Session verification failed:', e);
      this.clearSession();
    }
  }

  /**
   * Make authenticated API request
   */
  async apiRequest(endpoint, options = {}) {
    const headers = {
      'Content-Type': 'application/json',
      ...options.headers
    };

    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }

    try {
      const response = await fetch(`${this.apiBaseUrl}${endpoint}`, {
        ...options,
        headers
      });

      // Handle unauthorized
      if (response.status === 401) {
        // Clear session and redirect to login
        this.clearSession();
        window.dispatchEvent(new CustomEvent('userLoggedOut'));
        window.location.href = './login.html';
        throw new Error('Unauthorized');
      }

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'API request failed');
      }

      return await response.json();
    } catch (error) {
      console.error('API request error:', error);
      throw error;
    }
  }

  /**
   * Update user profile
   */
  async updateProfile(userData) {
    try {
      const data = await this.apiRequest('/user/profile', {
        method: 'PUT',
        body: JSON.stringify(userData)
      });

      // Update stored user data
      this.user = { ...this.user, ...data.user };
      localStorage.setItem('user', JSON.stringify(this.user));

      return { success: true, user: this.user };
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Initialize auth UI elements
   */
  async initUI() {
    // First, verify/restore session with backend
    await this.restoreSession();

    // Update navigation based on auth state AFTER restoring session
    this.updateNavigation();

    // Display user info
    this.updateUserDisplay();

    // Setup logout buttons
    document.querySelectorAll('[data-logout]').forEach(button => {
      button.addEventListener('click', async (e) => {
        e.preventDefault();
        
        // Show loading state
        const originalText = button.textContent;
        button.textContent = 'Logging out...';
        button.disabled = true;
        
        try {
          await this.logout();
        } catch (error) {
          console.error('Logout failed:', error);
          // Reset button state if logout fails
          button.textContent = originalText;
          button.disabled = false;
        }
      });
    });

    // Update nav on login/logout events
    window.addEventListener('userLoggedIn', () => {
      this.updateNavigation();
      this.updateUserDisplay();
    });
    window.addEventListener('userLoggedOut', () => {
      this.updateNavigation();
      this.updateUserDisplay();
    });
  }

  /**
   * Update navigation based on authentication state
   */
  updateNavigation() {
    const authLinks = document.querySelectorAll('[data-auth-required]');
    const guestLinks = document.querySelectorAll('[data-guest-only]');
    const adminLinks = document.querySelectorAll('[data-admin-only]');

    console.log('Updating navigation, authenticated:', this.isAuthenticated());
    console.log('Found auth links:', authLinks.length);
    console.log('Found guest links:', guestLinks.length);

    if (this.isAuthenticated()) {
      authLinks.forEach(el => {
        el.classList.remove('hidden');
        console.log('Showing auth element:', el);
      });
      guestLinks.forEach(el => {
        el.classList.add('hidden');
        console.log('Hiding guest element:', el);
      });
      
      if (this.isAdmin()) {
        adminLinks.forEach(el => el.classList.remove('hidden'));
      } else {
        adminLinks.forEach(el => el.classList.add('hidden'));
      }
    } else {
      authLinks.forEach(el => {
        el.classList.add('hidden');
        console.log('Hiding auth element:', el);
      });
      guestLinks.forEach(el => {
        el.classList.remove('hidden');
        console.log('Showing guest element:', el);
      });
      adminLinks.forEach(el => el.classList.add('hidden'));
    }
  }

  /**
   * Update user display information
   */
  updateUserDisplay() {
    if (this.isAuthenticated()) {
      document.querySelectorAll('[data-user-name]').forEach(element => {
        element.textContent = this.user.full_name || this.user.name || this.user.email;
        console.log('Updated user name:', element.textContent);
      });

      document.querySelectorAll('[data-user-email]').forEach(element => {
        element.textContent = this.user.email;
      });
    } else {
      document.querySelectorAll('[data-user-name]').forEach(element => {
        element.textContent = 'User';
      });
    }
  }
}

// Create global instance
const auth = new Auth();

// Auto-initialize UI when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => auth.initUI());
} else {
  auth.initUI();
}

// Export for use in other modules
/* eslint-disable no-undef */
if (typeof module !== 'undefined' && module.exports) {
  module.exports = auth;
}
/* eslint-enable no-undef */
