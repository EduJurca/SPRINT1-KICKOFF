<?php
/**
 * Session Check Endpoint
 * Verifica si el usuario tiene una sesión activa
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Depuración: mostrar todas las variables de sesión
$sessionData = [
    'session_id' => session_id(),
    'session_status' => session_status(),
    'session_data' => $_SESSION,
    'is_logged_in' => isset($_SESSION['user_id']),
    'cookie_params' => session_get_cookie_params()
];

echo json_encode([
    'success' => true,
    'session' => $sessionData
]);
?>
