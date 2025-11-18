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

    private static function parseAcceptLanguageHeader($acceptLanguage)
    {
        if (empty($acceptLanguage)) {
            return null;
        }

        $languages = [];
        $acceptLanguageParts = explode(',', $acceptLanguage);

        foreach ($acceptLanguageParts as $part) {
            $part = trim($part);

            if (strpos($part, ';q=') !== false) {
                list($langTag, $qualityPart) = explode(';q=', $part, 2);
                $quality = (float) $qualityPart;
            } else {
                $langTag = $part;
                $quality = 1.0;
            }

            $langTag = trim($langTag);

            if (strpos($langTag, '-') !== false) {
                $primaryLang = substr($langTag, 0, strpos($langTag, '-'));
            } else {
                $primaryLang = $langTag;
            }

            if (strlen($primaryLang) === 2) {
                $languages[] = [
                    'lang' => $primaryLang,
                    'quality' => $quality
                ];
            }
        }

        usort($languages, function($a, $b) {
            return $b['quality'] <=> $a['quality'];
        });

        foreach ($languages as $language) {
            if (in_array($language['lang'], self::$availableLangs)) {
                return $language['lang'];
            }
        }

        return null;
    }
}
