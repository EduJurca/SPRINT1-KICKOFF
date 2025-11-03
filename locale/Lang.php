<?php

class Lang
{
    private static $currentLang = 'ca';
    private static $translations = [];
    private static $availableLangs = ['en', 'ca'];

    public static function init($lang = null)
    {
        if ($lang && in_array($lang, self::$availableLangs)) {
            self::$currentLang = $lang;
        }

        $langFile = __DIR__ . '/../lang/' . self::$currentLang . '.php';
        
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        } else {
            error_log("Language file not found: " . $langFile . " for language: " . self::$currentLang);
            self::$translations = [];
        }
    }

    public static function get($key, $params = [])
    {
        $keys = explode('.', $key);
        $translation = self::$translations;

        foreach ($keys as $k) {
            if (isset($translation[$k])) {
                $translation = $translation[$k];
            } else {
                return $key;
            }
        }

        if (!empty($params) && is_string($translation)) {
            foreach ($params as $paramKey => $paramValue) {
                $translation = str_replace(':' . $paramKey, $paramValue, $translation);
            }
        }

        return $translation;
    }

    public static function current()
    {
        return self::$currentLang;
    }

    public static function available()
    {
        return self::$availableLangs;
    }

    public static function url($path = '')
    {
        $path = ltrim($path, '/');
        return '/' . self::$currentLang . '/' . $path;
    }

    public static function switchUrl($url, $newLang)
    {
        foreach (self::$availableLangs as $lang) {
            if (strpos($url, '/' . $lang . '/') === 0) {
                return str_replace('/' . $lang . '/', '/' . $newLang . '/', $url);
            }
        }
        return '/' . $newLang . '/' . ltrim($url, '/');
    }
}

function __($key, $params = [])
{
    return Lang::get($key, $params);
}
