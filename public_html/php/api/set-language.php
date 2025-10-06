<?php
/**
 * Language Switcher API
 * Handles language switching requests
 */

session_start();
require_once __DIR__ . '/../language.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$lang_code = $input['lang'] ?? '';

$lang = new Language();

if ($lang->setLanguage($lang_code)) {
    echo json_encode([
        'success' => true,
        'message' => 'Language changed successfully',
        'lang' => $lang_code
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid language code'
    ]);
}
?>