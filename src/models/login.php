<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://127.0.0.1:5501');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'msg' => 'Username and password required']);
    exit();
}

$conn = new mysqli('mariadb', 'simsuser', 'Putamare123', 'simsdb');
$conn->set_charset('utf8mb4');

$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id']; // <-- aquí se guarda la sesión
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Password incorrect']);
    }
} else {
    echo json_encode(['success' => false, 'msg' => 'User not found']);
}
?>