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
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browserLang, self::$availableLangs)) {
                return $browserLang;
            }
        }

        return $detectedLang;
    }
}
