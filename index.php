<?php

// Iniciar sessió
session_start();

// Carregar constants del projecte
require_once __DIR__ . '/config/constants.php';

// Configurar error reporting
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? 1 : 0);

// Carregar constants del projecte
require_once __DIR__ . '/config/constants.php';

// Carregar configuració de base de dades
require_once DATABASE_PATH . '/Database.php';
require_once LOCALE_PATH . '/Lang.php';
require_once LOCALE_PATH . '/LanguageDetector.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/helpers.php';
// Càrrega centralitzada de l'autorització i permisos per evitar múltiples includes
require_once CORE_PATH . '/Authorization.php';
require_once CORE_PATH . '/Permissions.php';

$uri = $_SERVER['REQUEST_URI'];
$detectedLang = LanguageDetector::detect($uri);
Lang::init($detectedLang);

require_once ROOT_PATH . '/routes/web.php';

$method = $_SERVER['REQUEST_METHOD'];
$uriPath = parse_url($uri, PHP_URL_PATH);

Router::dispatch($uriPath, $method);
