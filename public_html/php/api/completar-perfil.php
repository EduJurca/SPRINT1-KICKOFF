<?php
require_once __DIR__ . '/../controllers/ProfileController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080'); 
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $profile = ProfileController::getProfile($user_id);
    echo json_encode($profile ? ['success'=>true,'data'=>$profile] : ['success'=>false,'message'=>'Usuario no encontrado']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $allowedFields = ['fullname', 'phone', 'birthdate', 'address', 'sex'];
    $data = [];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) $data[$field] = $input[$field];
    }
    $updated = ProfileController::updateProfile($user_id, $data);
    echo json_encode($updated ? ['success'=>true,'message'=>'Perfil actualizado correctamente'] : ['success'=>false,'message'=>'Error al actualizar el perfil']);
} else {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Método no permitido']);
}