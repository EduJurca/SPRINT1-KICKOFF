<?php
// CORS headers
header("Access-Control-Allow-Origin: http://127.0.0.1:5501");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Preflight request
    exit(0);
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "User not logged in"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// MariaDB connection parameters
$db_host = "mariadb";
$db_user = "simsuser";
$db_pass = "Putamare123";
$db_name = "simsdb";

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

// Prepare statement to get user by user_id
$stmt = $mysqli->prepare("SELECT username, email, minute_balance, is_admin FROM users WHERE id = ?");
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);
    $mysqli->close();
    exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $user_id,
            "username" => $row['username'],
            "email" => $row['email'],
            "minute_balance" => $row['minute_balance'],
            "is_admin" => $row['is_admin']
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
}

$stmt->close();
$mysqli->close();
?>