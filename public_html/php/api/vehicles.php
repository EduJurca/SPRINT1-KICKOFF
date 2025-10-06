<?php
/**
 * Vehicles API Endpoint
 * Handles vehicle-related operations: fetch available vehicles, search, etc.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    $db = DatabaseMariaDB::getConnection();
    
    switch ($action) {
        case 'available':
            // Get all available vehicles
            $stmt = $db->prepare("
                SELECT 
                    v.id,
                    v.model,
                    v.license_plate,
                    v.battery_level as battery,
                    v.latitude,
                    v.longitude,
                    v.status,
                    v.vehicle_type,
                    v.is_accessible,
                    v.accessibility_features
                FROM vehicles v
                WHERE v.status = 'available'
                ORDER BY v.battery_level DESC
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehicles = [];
            while ($row = $result->fetch_assoc()) {
                $vehicles[] = [
                    'id' => (int)$row['id'],
                    'model' => $row['model'],
                    'license_plate' => $row['license_plate'],
                    'battery' => (int)$row['battery'],
                    'location' => [
                        'lat' => (float)$row['latitude'],
                        'lng' => (float)$row['longitude']
                    ],
                    'status' => $row['status'],
                    'type' => $row['vehicle_type'],
                    'is_accessible' => (bool)$row['is_accessible'],
                    'accessibility_features' => $row['accessibility_features'] ? json_decode($row['accessibility_features'], true) : []
                ];
            }
            
            echo json_encode([
                'success' => true,
                'vehicles' => $vehicles
            ]);
            break;
            
        case 'nearby':
            // Get nearby vehicles based on user location
            $lat = floatval($_GET['lat'] ?? 0);
            $lng = floatval($_GET['lng'] ?? 0);
            $radius = floatval($_GET['radius'] ?? 5); // Default 5km radius
            $vehicle_type = $_GET['type'] ?? null;
            $min_battery = intval($_GET['min_battery'] ?? 0);
            $accessible_only = isset($_GET['accessible']) && $_GET['accessible'] === 'true';
            
            if ($lat == 0 || $lng == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Location coordinates required'
                ]);
                exit();
            }
            
            // Build query with filters
            $query = "
                SELECT 
                    v.id,
                    v.model,
                    v.license_plate,
                    v.battery_level as battery,
                    v.latitude,
                    v.longitude,
                    v.status,
                    v.vehicle_type,
                    v.is_accessible,
                    v.accessibility_features,
                    v.price_per_minute,
                    v.image_url,
                    (6371 * acos(cos(radians(?)) * cos(radians(v.latitude)) * 
                    cos(radians(v.longitude) - radians(?)) + sin(radians(?)) * 
                    sin(radians(v.latitude)))) AS distance
                FROM vehicles v
                WHERE v.status = 'available'
            ";
            
            $params = [$lat, $lng, $lat];
            $types = 'ddd';
            
            // Add vehicle type filter
            if ($vehicle_type) {
                $query .= " AND v.vehicle_type = ?";
                $params[] = $vehicle_type;
                $types .= 's';
            }
            
            // Add battery filter
            if ($min_battery > 0) {
                $query .= " AND v.battery_level >= ?";
                $params[] = $min_battery;
                $types .= 'i';
            }
            
            // Add accessibility filter
            if ($accessible_only) {
                $query .= " AND v.is_accessible = 1";
            }
            
            $query .= " HAVING distance <= ?";
            $params[] = $radius;
            $types .= 'd';
            
            $query .= " ORDER BY distance ASC, v.battery_level DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehicles = [];
            while ($row = $result->fetch_assoc()) {
                $vehicles[] = [
                    'id' => (int)$row['id'],
                    'model' => $row['model'],
                    'license_plate' => $row['license_plate'],
                    'battery' => (int)$row['battery'],
                    'location' => [
                        'lat' => (float)$row['latitude'],
                        'lng' => (float)$row['longitude']
                    ],
                    'distance' => round((float)$row['distance'], 2),
                    'status' => $row['status'],
                    'type' => $row['vehicle_type'],
                    'is_accessible' => (bool)$row['is_accessible'],
                    'accessibility_features' => $row['accessibility_features'] ? json_decode($row['accessibility_features'], true) : [],
                    'price_per_minute' => (float)$row['price_per_minute'],
                    'image_url' => $row['image_url']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'vehicles' => $vehicles,
                'count' => count($vehicles),
                'user_location' => [
                    'lat' => $lat,
                    'lng' => $lng
                ],
                'filters' => [
                    'radius' => $radius,
                    'type' => $vehicle_type,
                    'min_battery' => $min_battery,
                    'accessible_only' => $accessible_only
                ]
            ]);
            break;
            
        case 'details':
            // Get details of a specific vehicle
            $vehicle_id = intval($_GET['id'] ?? 0);
            
            if ($vehicle_id == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Vehicle ID required'
                ]);
                exit();
            }
            
            $stmt = $db->prepare("
                SELECT 
                    v.id,
                    v.model,
                    v.license_plate,
                    v.battery_level as battery,
                    v.latitude,
                    v.longitude,
                    v.status,
                    v.vehicle_type,
                    v.is_accessible,
                    v.accessibility_features,
                    v.price_per_minute,
                    v.image_url
                FROM vehicles v
                WHERE v.id = ?
            ");
            $stmt->bind_param('i', $vehicle_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $vehicle = $result->fetch_assoc();
            
            if ($vehicle) {
                echo json_encode([
                    'success' => true,
                    'vehicle' => [
                        'id' => (int)$vehicle['id'],
                        'model' => $vehicle['model'],
                        'license_plate' => $vehicle['license_plate'],
                        'battery' => (int)$vehicle['battery'],
                        'location' => [
                            'lat' => (float)$vehicle['latitude'],
                            'lng' => (float)$vehicle['longitude']
                        ],
                        'status' => $vehicle['status'],
                        'type' => $vehicle['vehicle_type'],
                        'is_accessible' => (bool)$vehicle['is_accessible'],
                        'accessibility_features' => $vehicle['accessibility_features'] ? json_decode($vehicle['accessibility_features'], true) : [],
                        'price_per_minute' => (float)$vehicle['price_per_minute'],
                        'image_url' => $vehicle['image_url']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Vehicle not found'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
