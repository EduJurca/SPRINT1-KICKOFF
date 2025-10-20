<?php
// --- CORS and content type headers ---
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:8080");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// --- Stop preflight requests early ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// --- Load controller (this file must not produce any output before headers) ---
require_once __DIR__ . '/../controllers/AuthController.php';

// --- Parse JSON input ---
$input = json_decode(file_get_contents("php://input"), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// --- Call login and send JSON response ---
$result = AuthController::login($username, $password);
echo json_encode($result);
