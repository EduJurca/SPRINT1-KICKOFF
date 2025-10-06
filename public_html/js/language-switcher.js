/**
 * Language Switcher for VoltiaCar Application
 * Handles multi-language support on the client side
 * Supports: Catalan (ca), Spanish (es), English (en)
 */

class LanguageSwitcher {
    constructor() {
        this.translations = {};
        this.supportedLangs = ['ca', 'es', 'en'];
        this.langNames = {
            'ca': 'Català',
            'es': 'Español',
            'en': 'English'
        };
        this.currentLang = this.detectLanguage();
        
        this.init();
    }
    
    /**
     * Initialize language switcher
     */
    async init() {
        await this.loadTranslations(this.currentLang);
        this.createSwitcherUI();
        this.translatePage();
        this.setupEventListeners();
    }
    
    /**
     * Detect current language from localStorage, cookie, or browser
     */
    detectLanguage() {
        // Check localStorage
        const storedLang = localStorage.getItem('lang');
        if (storedLang && this.supportedLangs.includes(storedLang)) {
            return storedLang;
        }
        
        // Check cookie
        const cookieLang = this.getCookie('lang');
        if (cookieLang && this.supportedLangs.includes(cookieLang)) {
            return cookieLang;
        }
        
        // Check browser language
        const browserLang = navigator.language.substring(0, 2);
        if (this.supportedLangs.includes(browserLang)) {
            return browserLang;
        }
        
        // Default to Catalan
        return 'ca';
    }
    
    /**
     * Load translations from JSON file
     */
    async loadTranslations(lang) {
        try {
            // Determine the correct path to languages directory
            const pathPrefix = this.getLanguagesPath();
            const response = await fetch(`${pathPrefix}lang/${lang}.json`);
            
            if (response.ok) {
                this.translations = await response.json();
                this.currentLang = lang;
            } else {
                console.error(`Failed to load translations for ${lang}`);
                // Fallback to Catalan
                if (lang !== 'ca') {
                    await this.loadTranslations('ca');
                }
            }
        } catch (error) {
            console.error('Error loading translations:', error);
        }
    }
    
    /**
     * Get the correct path to languages directory based on current page location
     */
    getLanguagesPath() {
        const path = window.location.pathname;
        
        // If in root (index.html)
        if (path.endsWith('index.html') || path.endsWith('/')) {
            return './';
        }
        
        // If in pages/auth/
        if (path.includes('/pages/auth/')) {
            return '../../';
        }
        
        // If in pages/dashboard/, pages/profile/, pages/vehicle/, pages/accessibility/
        if (path.includes('/pages/')) {
            return '../../';
        }
        
        // If in admin/
        if (path.includes('/admin/')) {
            return '../';
        }
        
        // Default
        return './';
    }
    
    /**
     * Create language switcher UI
     */
    createSwitcherUI() {
        // Check if switcher already exists
        if (document.getElementById('language-switcher')) {
            return;
        }
        
        // Create switcher container
        const switcher = document.createElement('div');
        switcher.id = 'language-switcher';
        switcher.className = 'fixed top-4 right-4 z-50';
        
        // Create dropdown button
        const button = document.createElement('button');
        button.id = 'lang-button';
        button.className = 'bg-white text-gray-900 px-4 py-2 rounded-lg shadow-lg hover:bg-gray-100 transition-colors duration-300 flex items-center gap-2 font-semibold';
        button.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
            </svg>
            <span>${this.langNames[this.currentLang]}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        `;
        
        // Create dropdown menu
        const menu = document.createElement('div');
        menu.id = 'lang-menu';
        menu.className = 'hidden absolute top-full right-0 mt-2 bg-white rounded-lg shadow-lg overflow-hidden min-w-[150px]';
        
        // Add language options
        this.supportedLangs.forEach(lang => {
            const option = document.createElement('button');
            option.className = 'w-full text-left px-4 py-2 hover:bg-gray-100 transition-colors duration-200 font-semibold';
            option.dataset.lang = lang;
            option.textContent = this.langNames[lang];
            
            if (lang === this.currentLang) {
                option.classList.add('bg-blue-50', 'text-[#1565C0]');
            }
            
            menu.appendChild(option);
        });
        
        switcher.appendChild(button);
        switcher.appendChild(menu);
        document.body.appendChild(switcher);
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        const button = document.getElementById('lang-button');
        const menu = document.getElementById('lang-menu');
        
        if (button && menu) {
            // Toggle menu
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', () => {
                menu.classList.add('hidden');
            });
            
            // Language selection
            menu.querySelectorAll('button[data-lang]').forEach(option => {
                option.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const lang = option.dataset.lang;
                    await this.switchLanguage(lang);
                    menu.classList.add('hidden');
                });
            });
        }
    }
    
    /**
     * Switch to a different language
     */
    async switchLanguage(lang) {
        if (!this.supportedLangs.includes(lang) || lang === this.currentLang) {
            return;
        }
        
        // Load new translations
        await this.loadTranslations(lang);
        
        // Save preference
        localStorage.setItem('lang', lang);
        this.setCookie('lang', lang, 30);
        
        // Update UI
        this.updateSwitcherUI();
        this.translatePage();
        
        // Update HTML lang attribute
        document.documentElement.lang = lang;
        
        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('languageChanged', { detail: { lang } }));
    }
    
    /**
     * Update switcher UI after language change
     */
    updateSwitcherUI() {
        const button = document.getElementById('lang-button');
        const menu = document.getElementById('lang-menu');
        
        if (button) {
            const span = button.querySelector('span');
            if (span) {
                span.textContent = this.langNames[this.currentLang];
            }
        }
        
        if (menu) {
            menu.querySelectorAll('button[data-lang]').forEach(option => {
                const lang = option.dataset.lang;
                if (lang === this.currentLang) {
                    option.classList.add('bg-blue-50', 'text-[#1565C0]');
                } else {
                    option.classList.remove('bg-blue-50', 'text-[#1565C0]');
                }
            });
        }
    }
    
    /**
     * Translate all elements on the page
     */
    translatePage() {
        // Translate elements with data-i18n attribute
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.dataset.i18n;
            const translation = this.translations[key];
            
            if (translation) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    if (element.placeholder) {
                        element.placeholder = translation;
                    }
                } else {
                    element.textContent = translation;
                }
            }
        });
        
        // Translate elements with data-i18n-placeholder attribute
        document.querySelectorAll('[data-i18n-placeholder]').forEach(element => {
            const key = element.dataset.i18nPlaceholder;
            const translation = this.translations[key];
            
            if (translation) {
                element.placeholder = translation;
            }
        });
        
        // Translate elements with data-i18n-title attribute
        document.querySelectorAll('[data-i18n-title]').forEach(element => {
            const key = element.dataset.i18nTitle;
            const translation = this.translations[key];
            
            if (translation) {
                element.title = translation;
            }
        });
        
        // Translate elements with data-i18n-aria-label attribute
        document.querySelectorAll('[data-i18n-aria-label]').forEach(element => {
            const key = element.dataset.i18nAriaLabel;
            const translation = this.translations[key];
            
            if (translation) {
                element.setAttribute('aria-label', translation);
            }
        });
        
        // Translate elements with data-i18n-alt attribute
        document.querySelectorAll('[data-i18n-alt]').forEach(element => {
            const key = element.dataset.i18nAlt;
            const translation = this.translations[key];
            
            if (translation) {
                element.alt = translation;
            }
        });
    }
    
    /**
     * Get translation for a key
     */
    t(key, defaultValue = '') {
        return this.translations[key] || defaultValue || key;
    }
    
    /**
     * Get current language
     */
    getCurrentLanguage() {
        return this.currentLang;
    }
    
    /**
     * Get cookie value
     */
    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
        return null;
    }
    
    /**
     * Set cookie
     */
    setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/`;
    }
}

// Initialize language switcher when DOM is ready
let languageSwitcher;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        languageSwitcher = new LanguageSwitcher();
    });
} else {
    languageSwitcher = new LanguageSwitcher();
}

// Export for use in other scripts
window.LanguageSwitcher = LanguageSwitcher;
window.languageSwitcher = languageSwitcher;
