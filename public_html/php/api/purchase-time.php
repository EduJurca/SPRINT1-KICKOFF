<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// ============================
// 1️⃣ VALIDACIÓN INICIAL DE LA PETICIÓN
// ============================

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

// ============================
// 2️⃣ CLASE: UserRepository
// ============================

class UserRepository {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function getUserById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT balance, minute_balance FROM users WHERE id = ?");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function updateUser(int $id, float $newBalance, int $newMinutes): bool {
        $stmt = $this->db->prepare("UPDATE users SET balance = ?, minute_balance = ? WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('dii', $newBalance, $newMinutes, $id);
        return $stmt->execute();
    }
}

// ============================
// 3️⃣ CLASE: PurchaseValidator
// ============================

class PurchaseValidator {
    private array $validOptions = [
        10  => 1.5,
        30  => 4.0,
        60  => 7.5,
        120 => 14.0,
        180 => 20.0,
        240 => 25.0
    ];

    public function validateInput(array $input): ?array {
        $minutes = isset($input['minutes']) ? intval($input['minutes']) : 0;
        $price   = isset($input['price']) ? floatval($input['price']) : 0.0;

        if ($minutes <= 0 || $price <= 0) {
            return ['message' => 'Dades de compra invàlides'];
        }

        if (!isset($this->validOptions[$minutes]) || (float)$this->validOptions[$minutes] !== $price) {
            return ['message' => 'Dades manipulades o no vàlides'];
        }

        return null; // todo correcto
    }
}

// ============================
// 4️⃣ CLASE: PurchaseService
// ============================

class PurchaseService {
    private UserRepository $userRepo;
    private PurchaseValidator $validator;

    public function __construct(UserRepository $userRepo, PurchaseValidator $validator) {
        $this->userRepo = $userRepo;
        $this->validator = $validator;
    }

    public function processPurchase(int $userId, array $input): array {
        // Validar datos del POST
        $validationError = $this->validator->validateInput($input);
        if ($validationError) {
            http_response_code(400);
            return ['success' => false, 'message' => $validationError['message']];
        }

        $minutes = intval($input['minutes']);
        $price   = floatval($input['price']);

        // Obtener usuario
        $user = $this->userRepo->getUserById($userId);
        if (!$user) {
            http_response_code(404);
            return ['success' => false, 'message' => 'Usuari no trobat'];
        }

        // Comprobar saldo suficiente
        if ((float)$user['balance'] < $price) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Saldo insuficient'];
        }

        // Calcular nuevos valores
        $newBalance = (float)$user['balance'] - $price;
        $newMinutes = (int)$user['minute_balance'] + $minutes;

        // Actualizar usuario
        $success = $this->userRepo->updateUser($userId, $newBalance, $newMinutes);

        if (!$success) {
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
}

// ============================
// 5️⃣ INICIALIZACIÓN Y EJECUCIÓN
// ============================

$db = DatabaseMariaDB::getConnection();
$userRepo = new UserRepository($db);
$validator = new PurchaseValidator();
$service = new PurchaseService($userRepo, $validator);

$response = $service->processPurchase((int)$_SESSION['user_id'], $_POST);
echo json_encode($response);
