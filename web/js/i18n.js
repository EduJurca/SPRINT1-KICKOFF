/**
 * Internationalization (i18n) Module
 * Handles language switching and translation loading
 */

class I18n {
  constructor() {
    this.currentLang = localStorage.getItem('language') || 'es';
    this.translations = {};
    this.initialized = false;
  }

  /**
   * Initialize i18n system
   */
  async init() {
    await this.loadTranslations(this.currentLang);
    this.updateLanguageSwitcher();
    this.translatePage();
    this.setupLanguageSwitcher();
    this.initialized = true;
  }

  /**
   * Load translation file for specified language
   */
  async loadTranslations(lang) {
    try {
      // Determine the correct path based on current location
      const pathSegments = window.location.pathname.split('/');
      const isInAdmin = pathSegments.includes('admin');
      const langPath = isInAdmin ? '../../lang/' : '../lang/';
      
      const response = await fetch(`${langPath}${lang}.json`);
      if (!response.ok) {
        throw new Error(`Failed to load translations for ${lang}`);
      }
      this.translations = await response.json();
      this.currentLang = lang;
      localStorage.setItem('language', lang);
    } catch (error) {
      console.error('Error loading translations:', error);
      // Fallback to Spanish if loading fails
      if (lang !== 'es') {
        await this.loadTranslations('es');
      }
    }
  }

  /**
   * Get translation by key path (e.g., 'nav.home')
   */
  t(keyPath) {
    const keys = keyPath.split('.');
    let value = this.translations;
    
    for (const key of keys) {
      if (value && typeof value === 'object' && key in value) {
        value = value[key];
      } else {
        console.warn(`Translation key not found: ${keyPath}`);
        return keyPath;
      }
    }
    
    return value;
  }

  /**
   * Translate all elements with data-i18n attribute
   */
  translatePage() {
    // Translate text content
    document.querySelectorAll('[data-i18n]').forEach(element => {
      const key = element.getAttribute('data-i18n');
      const translation = this.t(key);
      element.textContent = translation;
    });

    // Translate placeholders
    document.querySelectorAll('[data-i18n-placeholder]').forEach(element => {
      const key = element.getAttribute('data-i18n-placeholder');
      const translation = this.t(key);
      element.setAttribute('placeholder', translation);
    });

    // Translate aria-labels
    document.querySelectorAll('[data-i18n-aria]').forEach(element => {
      const key = element.getAttribute('data-i18n-aria');
      const translation = this.t(key);
      element.setAttribute('aria-label', translation);
    });

    // Translate title attributes
    document.querySelectorAll('[data-i18n-title]').forEach(element => {
      const key = element.getAttribute('data-i18n-title');
      const translation = this.t(key);
      element.setAttribute('title', translation);
    });
  }

  /**
   * Setup language switcher buttons
   */
  setupLanguageSwitcher() {
    document.querySelectorAll('[data-lang]').forEach(button => {
      button.addEventListener('click', async (e) => {
        e.preventDefault();
        const lang = button.getAttribute('data-lang');
        await this.switchLanguage(lang);
      });
    });
  }

  /**
   * Switch to a different language
   */
  async switchLanguage(lang) {
    if (lang === this.currentLang) return;
    
    await this.loadTranslations(lang);
    this.translatePage();
    this.updateLanguageSwitcher();
    
    // Dispatch custom event for other components to react
    window.dispatchEvent(new CustomEvent('languageChanged', { 
      detail: { language: lang } 
    }));
  }

  /**
   * Update active state of language switcher buttons
   */
  updateLanguageSwitcher() {
    document.querySelectorAll('[data-lang]').forEach(button => {
      const lang = button.getAttribute('data-lang');
      if (lang === this.currentLang) {
        button.classList.add('active', 'font-bold', 'text-blue-600');
        button.classList.remove('text-gray-600');
      } else {
        button.classList.remove('active', 'font-bold', 'text-blue-600');
        button.classList.add('text-gray-600');
      }
    });
  }

  /**
   * Get current language
   */
  getCurrentLanguage() {
    return this.currentLang;
  }
}

// Create global instance
const i18n = new I18n();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => i18n.init());
} else {
  i18n.init();
}

// Export for use in other modules
/* eslint-disable no-undef */
if (typeof module !== 'undefined' && module.exports) {
  module.exports = i18n;
}
/* eslint-enable no-undef */
