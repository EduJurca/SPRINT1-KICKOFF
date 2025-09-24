<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

// Connection to MariaDB
$conn = new mysqli('mariadb', 'simsuser', 'Putamare123', 'simsdb');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'msg' => 'Database connection error.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username'], $data['password'], $data['email'])) {
    echo json_encode(['success' => false, 'msg' => 'Missing information.']);
    exit();
}

$username = $data['username'];
$email = $data['email'];

// Check if the user or email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'msg' => 'The username or email already exists.']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

$password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
$dni = $data['dni'] ?? null;
$phone = $data['phone'] ?? null;
$fecha_nacimiento = $data['fecha_nacimiento'] ?? null;

// Insert the user
$stmt = $conn->prepare("INSERT INTO users (username, dni, phone, fecha_nacimiento, email, password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $username, $dni, $phone, $fecha_nacimiento, $email, $password_hash);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'msg' => 'User registered successfully.']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Registration error.']);
}
$stmt->close();
$conn->close();
?>