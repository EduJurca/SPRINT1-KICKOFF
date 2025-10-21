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
            // 🔹 Obtener vehículos desde MariaDB con todos los campos necesarios
            $stmt = $db->prepare("
                SELECT 
                    v.id,
                    v.plate as license_plate,
                    v.brand,
                    v.model,
                    v.year,
                    v.battery_level,
                    v.latitude,
                    v.longitude,
                    v.status,
                    v.vehicle_type,
                    v.is_accessible,
                    v.accessibility_features,
                    v.price_per_minute,
                    v.image_url
                FROM vehicles v
                WHERE v.status != 'maintenance'
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
                
                // Debug: verificar si tiene coordenadas de MariaDB
                $hasCoords = isset($vehicle['latitude']) && isset($vehicle['longitude']);
                
                if ($mongoAvailable && isset($mongoIndex[$plate])) {
                    $carData = $mongoIndex[$plate];
                    $vehicle['status'] = $carData['status'] ?? $vehicle['status'] ?? 'available';
                    $vehicle['battery'] = $carData['battery_level'] ?? $vehicle['battery_level'] ?? 85;
                    
                    // Convertir location de MongoDB a formato esperado
                    if (isset($carData['location']['coordinates'])) {
                        $vehicle['location'] = [
                            'lng' => $carData['location']['coordinates'][0],
                            'lat' => $carData['location']['coordinates'][1]
                        ];
                    } else {
                        // Usar ubicación de MariaDB si está disponible
                        $vehicle['location'] = [
                            'lat' => $hasCoords ? (float)$vehicle['latitude'] : 40.7117 + (rand(-100, 100) / 10000),
                            'lng' => $hasCoords ? (float)$vehicle['longitude'] : 0.5783 + (rand(-100, 100) / 10000)
                        ];
                    }
                    $vehicle['last_updated'] = $carData['last_updated'] ?? null;
                    $vehicle['is_accessible'] = (bool)($carData['is_accessible'] ?? $vehicle['is_accessible'] ?? false);
                } else {
                    // Valores por defecto si MongoDB no está disponible - USAR SIEMPRE MariaDB
                    $vehicle['status'] = $vehicle['status'] ?? 'available';
                    $vehicle['battery'] = $vehicle['battery_level'] ?? rand(60, 100);
                    
                    // SIEMPRE usar ubicación de MariaDB si existe
                    if ($hasCoords) {
                        $vehicle['location'] = [
                            'lat' => (float)$vehicle['latitude'],
                            'lng' => (float)$vehicle['longitude']
                        ];
                    } else {
                        // Fallback a Amposta centro si no hay coordenadas
                        $vehicle['location'] = [
                            'lat' => 40.7117 + (rand(-100, 100) / 10000),
                            'lng' => 0.5783 + (rand(-100, 100) / 10000)
                        ];
                    }
                    
                    $vehicle['last_updated'] = date('Y-m-d H:i:s');
                    $vehicle['is_accessible'] = (bool)($vehicle['is_accessible'] ?? (rand(0, 10) > 8));
                }
                
                // Asegurar que todos los campos necesarios existen
                $vehicle['type'] = $vehicle['vehicle_type'] ?? 'car';
                $vehicle['price_per_minute'] = (float)($vehicle['price_per_minute'] ?? 0.35);
                $vehicle['image_url'] = $vehicle['image_url'] ?? '/images/default-car.jpg';
                
                // Limpiar campos duplicados de la base de datos
                unset($vehicle['latitude']);
                unset($vehicle['longitude']);
                unset($vehicle['battery_level']);
                unset($vehicle['vehicle_type']);
            }

            echo json_encode([
                'success' => true,
                'data' => $vehicles,
                'count' => count($vehicles),
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
