<?php
/**
 * Vehicles API Endpoint
 * Combina datos de MariaDB y MongoDB
 * (usado para la página de "localizar vehículo")
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    http_response_code(200); 
    exit(); 
}

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

// 🔹 Conexiones
require_once __DIR__ . '/../core/DatabaseMariaDB.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    // Conexión a MariaDB
    $db = DatabaseMariaDB::getConnection();

    switch ($action) {
        case 'available':
        default:
            // 🔹 Obtener vehículos desde MariaDB
            $stmt = $db->prepare("
                SELECT 
                    v.id,
                    v.plate as license_plate,
                    v.brand,
                    v.model,
                    v.year
                FROM vehicles v
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            $vehicles = $result->fetch_all(MYSQLI_ASSOC);

            // 🔹 Intentar conexión a MongoDB para datos en tiempo real
            $mongoAvailable = false;
            $mongoIndex = [];
            
            try {
                // Verificar si el archivo de MongoDB existe y tiene las funciones necesarias
                $mongoConfigFile = __DIR__ . '/../../config/database.php';
                if (file_exists($mongoConfigFile)) {
                    // Verificar si el archivo vendor/autoload.php existe
                    $vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
                    if (file_exists($vendorAutoload)) {
                        require_once $mongoConfigFile;
                        if (function_exists('getMongoDB')) {
                            $mongo = getMongoDB();
                            $carsCollection = $mongo->cars;
                            $mongoCars = iterator_to_array($carsCollection->find());
                            
                            // Crear índice rápido por matrícula
                            foreach ($mongoCars as $car) {
                                $mongoIndex[$car['license_plate']] = $car;
                            }
                            $mongoAvailable = true;
                        }
                    }
                }
            } catch (Exception $mongoError) {
                // Si MongoDB no está disponible, continuamos sin él
                error_log("MongoDB not available: " . $mongoError->getMessage());
            }

            // 🔹 Combinar datos o usar valores por defecto
            foreach ($vehicles as &$vehicle) {
                $plate = $vehicle['license_plate'];
                
                if ($mongoAvailable && isset($mongoIndex[$plate])) {
                    $carData = $mongoIndex[$plate];
                    $vehicle['status'] = $carData['status'] ?? 'available';
                    $vehicle['battery'] = $carData['battery_level'] ?? 85;
                    
                    // Convertir location de MongoDB a formato esperado
                    if (isset($carData['location']['coordinates'])) {
                        $vehicle['location'] = [
                            'lng' => $carData['location']['coordinates'][0],
                            'lat' => $carData['location']['coordinates'][1]
                        ];
                    } else {
                        // Ubicación por defecto (Barcelona)
                        $vehicle['location'] = [
                            'lat' => 41.3851 + (rand(-100, 100) / 10000),
                            'lng' => 2.1734 + (rand(-100, 100) / 10000)
                        ];
                    }
                    $vehicle['last_updated'] = $carData['last_updated'] ?? null;
                    $vehicle['is_accessible'] = (bool)($carData['is_accessible'] ?? false);
                } else {
                    // Valores por defecto si MongoDB no está disponible
                    $vehicle['status'] = 'available';
                    $vehicle['battery'] = rand(20, 100);
                    $vehicle['location'] = [
                        'lat' => 41.3851 + (rand(-100, 100) / 10000),
                        'lng' => 2.1734 + (rand(-100, 100) / 10000)
                    ];
                    $vehicle['last_updated'] = date('Y-m-d H:i:s');
                    $vehicle['is_accessible'] = (rand(0, 10) > 8); // 20% de vehículos accesibles
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $vehicles,
                'mongodb_available' => $mongoAvailable
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
