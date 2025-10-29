<?php
/**
 * 🚪 Front Controller - Punt d'entrada principal de l'aplicació
 * Totes les peticions passen per aquí i es dirigeixen al Router
 */

// Iniciar sessió
session_start();

// Carregar constants del projecte
require_once __DIR__ . '/config/constants.php';

// Configurar error reporting
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? 1 : 0);

// Carregar configuració de base de dades
require_once DATABASE_PATH . '/Database.php';

// Carregar el Router
require_once CORE_PATH . '/Router.php';

// Carregar les rutes
require_once ROOT_PATH . '/routes/web.php';

// Obtenir la URI sol·licitada
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Eliminar query string de la URI
$uri = parse_url($uri, PHP_URL_PATH);

// Executar el router
Router::dispatch($uri, $method);
