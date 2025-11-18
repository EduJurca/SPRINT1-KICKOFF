<?php

define('ROOT_PATH', __DIR__ . '/../');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('DATABASE_PATH', ROOT_PATH . '/database');
define('CORE_PATH', ROOT_PATH . '/core');
define('LOCALE_PATH', ROOT_PATH . '/locale');
define('LANG_PATH', ROOT_PATH . '/lang');
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/assets');

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// Configuració de l'aplicació
define('APP_NAME', 'SIMS - Sistema Intelligent de Mobilitat Sostenible');
define('APP_VERSION', '1.0.0');
