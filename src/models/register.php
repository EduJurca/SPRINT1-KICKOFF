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

// MariaDB connection with retry loop and utf8mb4 charset
$max_attempts = 10;
$attempt = 0;
$conn = null;
while ($attempt < $max_attempts) {
    $conn = @new mysqli('mariadb', 'simsuser', 'Putamare123', 'simsdb');
    if ($conn && !$conn->connect_error) {
        break;
    }
    usleep(500000); // wait 0.5 seconds
    $attempt++;
}
if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'msg' => 'Database connection error.']);
    exit();
}
$conn->set_charset('utf8mb4');

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
$nationality_id = $data['nationality_id'] ?? null;
$phone = $data['phone'] ?? null;
$birth_date = $data['fecha_nacimiento'] ?? null;
$iban = $data['iban'] ?? null;
$driver_license_photo = $data['driver_license_photo'] ?? null;
$minute_balance = 0;
$is_admin = 0;
$created_at = date('Y-m-d H:i:s');

// Insert the user
// Prepare statement with correct types and handle nulls for bind_param
$stmt = $conn->prepare("INSERT INTO users (username, nationality_id, phone, birth_date, email, password, iban, driver_license_photo, minute_balance, is_admin, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
// Use variables, and pass nulls as proper types
$nationality_id_param = isset($nationality_id) && $nationality_id !== '' ? $nationality_id : null;
$phone_param = isset($phone) && $phone !== '' ? $phone : null;
$birth_date_param = isset($birth_date) && $birth_date !== '' ? $birth_date : null;
$iban_param = isset($iban) && $iban !== '' ? $iban : null;
$driver_license_photo_param = isset($driver_license_photo) && $driver_license_photo !== '' ? $driver_license_photo : null;

// s: username, i: nationality_id, s: phone, s: birth_date, s: email, s: password, s: iban, s: driver_license_photo, i: minute_balance, i: is_admin, s: created_at
$stmt->bind_param(
    "sisssssssis",
    $username,
    $nationality_id_param,
    $phone_param,
    $birth_date_param,
    $email,
    $password_hash,
    $iban_param,
    $driver_license_photo_param,
    $minute_balance,
    $is_admin,
    $created_at
);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'msg' => 'User registered successfully.']);
} else {
    echo json_encode([
        'success' => false,
        'msg' => 'Registration error.',
        'error' => $stmt->error
    ]);
}
$stmt->close();
$conn->close();
?>