<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

function get_user_by_id(mysqli $db, int $id): ?array {
    $stmt = $db->prepare("SELECT balance, minute_balance FROM users WHERE id = ?");
    
    if (!$stmt) return null;
    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) return null;
    $res = $stmt->get_result();
    
    return $res ? $res->fetch_assoc() : null;
}

function update_user(mysqli $db, int $id, float $newBalance, int $newMinutes): bool {
    $stmt = $db->prepare("UPDATE users SET balance = ?, minute_balance = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param('dii', $newBalance, $newMinutes, $id);
    return $stmt->execute();
}

function validate_purchase_input(array $input): ?array {
    $validOptions = [
        10  => 1.5,
        30  => 4.0,
        60  => 7.5,
        120 => 14.0,
        180 => 20.0,
        240 => 25.0
    ];

    $minutes = isset($input['minutes']) ? intval($input['minutes']) : 0;
    $price   = isset($input['price']) ? floatval($input['price']) : 0.0;

    if ($minutes <= 0 || $price <= 0) {
        return ['message' => 'Dades de compra invàlides'];
    }

    if (!isset($validOptions[$minutes]) || (float)$validOptions[$minutes] !== $price) {
        return ['message' => 'Dades manipulades o no vàlides'];
    }

    return null;
}


function process_purchase(mysqli $db, int $userId, array $input): array {
    $validationError = validate_purchase_input($input);
    if ($validationError) {
        http_response_code(400);
        return ['success' => false, 'message' => $validationError['message']];
    }

    $minutes = intval($input['minutes']);
    $price   = floatval($input['price']);

    $user = get_user_by_id($db, $userId);
    if (!$user) {
        http_response_code(404);
        return ['success' => false, 'message' => 'Usuari no trobat'];
    }

    if ((float)$user['balance'] < $price) {
        http_response_code(400);
        return ['success' => false, 'message' => 'Saldo insuficient'];
    }

    $newBalance = (float)$user['balance'] - $price;
    $newMinutes = (int)$user['minute_balance'] + $minutes;

    $succes = update_user($db, $userId, $newBalance, $newMinutes);
    if (!$succes) {
        http_response_code(500);
        return ['success' => false, 'message' => 'Error en actualitzar l’usuari'];
    }

    return [
        'success' => true,
        'message' => 'Compra realitzada correctament',
        'data' => [
            'new_balance' => $newBalance,
            'new_minutes' => $newMinutes
        ]
    ];
}


$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true);

$db = DatabaseMariaDB::getConnection();
$response = process_purchase($db, (int)$_SESSION['user_id'], $input);
echo json_encode($response);
