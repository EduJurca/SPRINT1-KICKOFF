<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

$mysqli = new mysqli('mariadb', 'simsuser', 'Putamare123', 'simsdb');

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $mysqli->prepare("SELECT fullname, dni, phone, birthdate, address, sex FROM users WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
        exit();
    }
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'JSON inválido']);
        exit();
    }

    $fullname = isset($input['fullname']) ? trim($input['fullname']) : null;
    $dni = isset($input['dni']) ? trim($input['dni']) : null;
    $phone = isset($input['phone']) ? trim($input['phone']) : null;
    $birthdate = isset($input['birthdate']) ? trim($input['birthdate']) : null;
    $address = isset($input['address']) ? trim($input['address']) : null;
    $sex = isset($input['sex']) ? trim($input['sex']) : null;

    $stmt = $mysqli->prepare("UPDATE users SET fullname = ?, dni = ?, phone = ?, birthdate = ?, address = ?, sex = ? WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
        exit();
    }
    $stmt->bind_param('ssssssi', $fullname, $dni, $phone, $birthdate, $address, $sex, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil']);
    }
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$mysqli->close();
?>