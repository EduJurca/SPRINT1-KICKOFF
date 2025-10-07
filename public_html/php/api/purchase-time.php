<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
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

try {
    $db = DatabaseMariaDB::getConnection();

    // Llegeix i valida dades del frontend (form POST)
    $minuts = isset($_POST['minuts']) ? intval($_POST['minuts']) : 0;
    $preu = isset($_POST['preu']) ? floatval($_POST['preu']) : 0.0;
    $user_id = (int)$_SESSION['user_id'];

    if ($minuts <= 0 || $preu <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Dades de compra invàlides'
        ]);
        exit();
    }

    // Opcional: comprovar que la combinació minuts/preu sigui vàlida
    $opcions_valides = [
        10 => 1.5,
        30 => 4.0,
        60 => 7.5,
        120 => 14.0,
        180 => 20.0,
        240 => 25.0
    ];

    if (!isset($opcions_valides[$minuts]) || (float)$opcions_valides[$minuts] !== $preu) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Dades manipulades o no vàlides'
        ]);
        exit();
    }

    // Obté saldo i minuts actuals de l’usuari (mysqli)
    $stmt = $db->prepare("SELECT saldo, minute_balance FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $db->error);
    }
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuari no trobat'
        ]);
        exit();
    }

    // Comprova si té prou saldo
    if ((float)$user['saldo'] < $preu) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Saldo insuficient'
        ]);
        exit();
    }

    // Calcula nous valors
    $nou_saldo = (float)$user['saldo'] - $preu;
    $nous_minuts = (int)$user['minute_balance'] + $minuts;

    // Actualitza usuari (mysqli)
    $update = $db->prepare("UPDATE users SET saldo = ?, minute_balance = ? WHERE id = ?");
    if (!$update) {
        throw new Exception('Prepare failed: ' . $db->error);
    }
    $update->bind_param('dii', $nou_saldo, $nous_minuts, $user_id);
    if (!$update->execute()) {
        throw new Exception('Execute failed: ' . $update->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Compra realitzada correctament',
        'data' => [
            'nou_saldo' => $nou_saldo,
            'nous_minuts' => $nous_minuts
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error intern: ' . $e->getMessage()
    ]);
}
?>
