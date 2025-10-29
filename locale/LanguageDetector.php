<?php

class LanguageDetector
{
    private static $defaultLang = 'ca';
    private static $availableLangs = ['en', 'ca'];

    public static function detect($uri)
    {
        $detectedLang = self::$defaultLang;
        $uriPath = parse_url($uri, PHP_URL_PATH);

        foreach (self::$availableLangs as $lang) {
            if (strpos($uriPath, '/' . $lang . '/') === 0 || $uriPath === '/' . $lang) {
                $detectedLang = $lang;
                $_SESSION['lang'] = $lang;
                return $detectedLang;
            }
        }

        if (isset($_SESSION['user']) && isset($_SESSION['user']['lang'])) {
            return $_SESSION['user']['lang'];
        }

        if (isset($_SESSION['lang'])) {
            return $_SESSION['lang'];
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = self::parseAcceptLanguageHeader($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if ($browserLang) {
                return $browserLang;
            }
        }

        return $detectedLang;
    }

    /**
     * Parse the Accept-Language header and return the best matching language
     * that is supported by the application.
     *
     * @param string $acceptLanguage The Accept-Language header value
     * @return string|null The best matching language or null if none found
     */
    private static function parseAcceptLanguageHeader($acceptLanguage)
    {
        if (empty($acceptLanguage)) {
            return null;
        }

        $languages = [];

        // Split by comma and parse each language tag
        $acceptLanguageParts = explode(',', $acceptLanguage);

        foreach ($acceptLanguageParts as $part) {
            $part = trim($part);

            // Parse language tag and quality value
            // Format: language-tag[;q=quality-value]
            if (strpos($part, ';q=') !== false) {
                list($langTag, $qualityPart) = explode(';q=', $part, 2);
                $quality = (float) $qualityPart;
            } else {
                $langTag = $part;
                $quality = 1.0; // Default quality if not specified
            }

            $langTag = trim($langTag);

            // Extract the primary language code (first 2-3 characters before hyphen)
            // Handle cases like 'en-US', 'zh-CN', 'ca', 'en'
            if (strpos($langTag, '-') !== false) {
                $primaryLang = substr($langTag, 0, strpos($langTag, '-'));
            } else {
                $primaryLang = $langTag;
            }

            // Only consider 2-character language codes
            if (strlen($primaryLang) === 2) {
                $languages[] = [
                    'lang' => $primaryLang,
                    'quality' => $quality
                ];
            }
        }

        // Sort by quality value (highest first)
        usort($languages, function($a, $b) {
            return $b['quality'] <=> $a['quality'];
        });

        // Return the first supported language with the highest quality
        foreach ($languages as $language) {
            if (in_array($language['lang'], self::$availableLangs)) {
                return $language['lang'];
            }
        }

        return null;
    }
}
