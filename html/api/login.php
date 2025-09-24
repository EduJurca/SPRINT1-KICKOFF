<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Connexió a MariaDB
$conn = new mysqli('mariadb', 'simsuser', 'Putamare123', 'simsdb');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'msg' => 'Error de connexió a la base de dades.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'];
$password = $data['password'];

// Cerca l'usuari
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        echo json_encode(['success' => true, 'msg' => 'Login correcte.']);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Contrasenya incorrecta.']);
    }
} else {
    echo json_encode(['success' => false, 'msg' => 'Usuari no trobat.']);
}

$stmt->close();
$conn->close();
?>