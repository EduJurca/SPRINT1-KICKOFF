<?php
/**
 * 📋 Constants - Definicions globals del projecte
 * Aquest fitxer conté totes les constants utilitzades a l'aplicació
 */

// Definir constants de directoris del projecte
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('DATABASE_PATH', ROOT_PATH . '/database');
define('CORE_PATH', ROOT_PATH . '/core');
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public_html');

// Configuració d'errors (poden ser sobreescrits per l'entorn)
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// Configuració de l'aplicació
define('APP_NAME', 'SIMS - Sistema Intelligent de Mobilitat Sostenible');
define('APP_VERSION', '1.0.0');
