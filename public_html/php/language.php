<?php
/**
 * Language Handler Class
 * Manages multi-language support for VoltiaCar application
 * Supports: Catalan (ca), Spanish (es), English (en)
 */

class Language {
    private $lang;
    private $translations;
    private $defaultLang = 'ca';
    private $supportedLangs = ['ca', 'es', 'en'];
    
    /**
     * Constructor
     * @param string $lang Language code (ca, es, en)
     */
    public function __construct($lang = null) {
        // Determine language from parameter, session, cookie, or browser
        $this->lang = $this->detectLanguage($lang);
        
        // Load translations
        $this->loadTranslations();
    }
    
    /**
     * Detect the user's preferred language
     * Priority: parameter > session > cookie > browser > default
     */
    private function detectLanguage($lang = null) {
        // 1. Check parameter
        if ($lang && in_array($lang, $this->supportedLangs)) {
            return $lang;
        }
        
        // 2. Check session
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $this->supportedLangs)) {
            return $_SESSION['lang'];
        }
        
        // 3. Check cookie
        if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $this->supportedLangs)) {
            return $_COOKIE['lang'];
        }
        
        // 4. Check browser language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browserLang, $this->supportedLangs)) {
                return $browserLang;
            }
        }
        
        // 5. Return default
        return $this->defaultLang;
    }
    
    /**
     * Load translations from JSON file
     */
    private function loadTranslations() {
        // Determine the correct path to language files
        $langDir = __DIR__ . '/../lang';
        
        // If lang directory doesn't exist at that path, try alternative paths
        if (!is_dir($langDir)) {
            $langDir = __DIR__ . '/lang';
        }
        if (!is_dir($langDir)) {
            $langDir = $_SERVER['DOCUMENT_ROOT'] . '/lang';
        }
        
        $file = $langDir . '/' . $this->lang . '.json';
        
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $this->translations = json_decode($json, true);
        } else {
            // Fallback to default language
            $file = $langDir . '/' . $this->defaultLang . '.json';
            if (file_exists($file)) {
                $json = file_get_contents($file);
                $this->translations = json_decode($json, true);
            } else {
                $this->translations = [];
            }
        }
    }
    
    /**
     * Get translation for a key
     * @param string $key Translation key
     * @param string $default Default value if key not found
     * @return string Translated text
     */
    public function get($key, $default = '') {
        if (isset($this->translations[$key])) {
            return $this->translations[$key];
        }
        return $default ?: $key;
    }
    
    /**
     * Get translation (alias for get)
     */
    public function t($key, $default = '') {
        return $this->get($key, $default);
    }
    
    /**
     * Get current language code
     * @return string Current language code
     */
    public function getCurrentLang() {
        return $this->lang;
    }
    
    /**
     * Set language
     * @param string $lang Language code
     * @return bool Success status
     */
    public function setLanguage($lang) {
        if (in_array($lang, $this->supportedLangs)) {
            $this->lang = $lang;
            $this->loadTranslations();
            
            // Save to session
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['lang'] = $lang;
            }
            
            // Save to cookie (30 days)
            setcookie('lang', $lang, time() + (30 * 24 * 60 * 60), '/');
            
            return true;
        }
        return false;
    }
    
    /**
     * Get all supported languages
     * @return array Supported language codes
     */
    public function getSupportedLanguages() {
        return $this->supportedLangs;
    }
    
    /**
     * Get language name
     * @param string $code Language code
     * @return string Language name
     */
    public function getLanguageName($code = null) {
        $code = $code ?: $this->lang;
        
        $names = [
            'ca' => 'Català',
            'es' => 'Español',
            'en' => 'English'
        ];
        
        return $names[$code] ?? $code;
    }
    
    /**
     * Get all translations (for JavaScript)
     * @return array All translations
     */
    public function getAllTranslations() {
        return $this->translations;
    }
    
    /**
     * Export translations as JSON (for JavaScript)
     * @return string JSON string
     */
    public function toJSON() {
        return json_encode($this->translations, JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Helper function to get Language instance
 * @return Language
 */
function getLang() {
    static $instance = null;
    if ($instance === null) {
        $instance = new Language();
    }
    return $instance;
}

/**
 * Helper function to translate text
 * @param string $key Translation key
 * @param string $default Default value
 * @return string Translated text
 */
function __($key, $default = '') {
    return getLang()->get($key, $default);
}

/**
 * Helper function to echo translated text
 * @param string $key Translation key
 * @param string $default Default value
 */
function _e($key, $default = '') {
    echo getLang()->get($key, $default);
}
?>