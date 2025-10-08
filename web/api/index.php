<?php
/**
 * API Router
 * Main entry point for all API requests
 * Implements RESTful routing following 2025 best practices
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Include Composer autoload if available (for MongoDB client and other libs)
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// Include CORS configuration
require_once __DIR__ . '/config/cors.php';

// Include response utility
require_once __DIR__ . '/utils/Response.php';

// Get request method and URI
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string and decode URI
$requestUri = strtok($requestUri, '?');
$requestUri = urldecode($requestUri);

// Remove /api prefix if present
$requestUri = preg_replace('#^/api#', '', $requestUri);

// Remove trailing slash
$requestUri = rtrim($requestUri, '/');

// Parse the route
$routeParts = explode('/', trim($requestUri, '/'));

// Get resource and ID
$resource = $routeParts[0] ?? '';
$id = $routeParts[1] ?? null;
$action = $routeParts[2] ?? null;

try {
    // Route to appropriate controller
    switch ($resource) {
        case 'auth':
            require_once __DIR__ . '/controllers/AuthController.php';
            $controller = new AuthController();
            
            switch ($id) {
                case 'login':
                    if ($requestMethod === 'POST') {
                        $controller->login();
                    } else {
                        Response::methodNotAllowed(['POST']);
                    }
                    break;
                    
                case 'register':
                    if ($requestMethod === 'POST') {
                        $controller->register();
                    } else {
                        Response::methodNotAllowed(['POST']);
                    }
                    break;
                    
                case 'logout':
                    if ($requestMethod === 'POST') {
                        $controller->logout();
                    } else {
                        Response::methodNotAllowed(['POST']);
                    }
                    break;
                    
                case 'me':
                    if ($requestMethod === 'GET') {
                        $controller->me();
                    } else {
                        Response::methodNotAllowed(['GET']);
                    }
                    break;
                    
                case 'refresh':
                    if ($requestMethod === 'POST') {
                        $controller->refresh();
                    } else {
                        Response::methodNotAllowed(['POST']);
                    }
                    break;
                    
                case 'verify':
                    if ($requestMethod === 'GET') {
                        $controller->verify();
                    } else {
                        Response::methodNotAllowed(['GET']);
                    }
                    break;
                    
                default:
                    Response::notFound('Auth endpoint not found');
            }
            break;
            
        case 'users':
            require_once __DIR__ . '/controllers/UserController.php';
            $controller = new UserController();
            
            if (!$id) {
                // /api/users
                switch ($requestMethod) {
                    case 'GET':
                        $controller->index();
                        break;
                    case 'POST':
                        $controller->create();
                        break;
                    default:
                        Response::methodNotAllowed(['GET', 'POST']);
                }
            } else {
                // /api/users/{id}
                switch ($requestMethod) {
                    case 'GET':
                        $controller->show($id);
                        break;
                    case 'PUT':
                        $controller->update($id);
                        break;
                    case 'DELETE':
                        $controller->delete($id);
                        break;
                    default:
                        Response::methodNotAllowed(['GET', 'PUT', 'DELETE']);
                }
            }
            break;
            
        case 'vehicles':
            require_once __DIR__ . '/controllers/VehicleController.php';
            $controller = new VehicleController();
            
            if (!$id) {
                // /api/vehicles
                switch ($requestMethod) {
                    case 'GET':
                        $controller->index();
                        break;
                    case 'POST':
                        $controller->create();
                        break;
                    default:
                        Response::methodNotAllowed(['GET', 'POST']);
                }
            } elseif ($action === 'location') {
                // /api/vehicles/{id}/location
                if ($requestMethod === 'PATCH' || $requestMethod === 'PUT') {
                    $controller->updateLocation($id);
                } else {
                    Response::methodNotAllowed(['PATCH', 'PUT']);
                }
            } elseif ($action === 'status') {
                // /api/vehicles/{id}/status
                if ($requestMethod === 'PATCH' || $requestMethod === 'PUT') {
                    $controller->updateStatus($id);
                } else {
                    Response::methodNotAllowed(['PATCH', 'PUT']);
                }
            } else {
                // /api/vehicles/{id}
                switch ($requestMethod) {
                    case 'GET':
                        $controller->show($id);
                        break;
                    case 'PUT':
                        $controller->update($id);
                        break;
                    case 'DELETE':
                        $controller->delete($id);
                        break;
                    default:
                        Response::methodNotAllowed(['GET', 'PUT', 'DELETE']);
                }
            }
            break;
            
        case 'bookings':
            require_once __DIR__ . '/controllers/BookingController.php';
            $controller = new BookingController();
            
            if (!$id) {
                // /api/bookings
                switch ($requestMethod) {
                    case 'GET':
                        $controller->index();
                        break;
                    case 'POST':
                        $controller->create();
                        break;
                    default:
                        Response::methodNotAllowed(['GET', 'POST']);
                }
            } elseif ($action === 'complete') {
                // /api/bookings/{id}/complete
                if ($requestMethod === 'POST') {
                    $controller->complete($id);
                } else {
                    Response::methodNotAllowed(['POST']);
                }
            } elseif ($action === 'cancel') {
                // /api/bookings/{id}/cancel
                if ($requestMethod === 'POST') {
                    $controller->cancel($id);
                } else {
                    Response::methodNotAllowed(['POST']);
                }
            } else {
                // /api/bookings/{id}
                switch ($requestMethod) {
                    case 'GET':
                        $controller->show($id);
                        break;
                    case 'PUT':
                        $controller->update($id);
                        break;
                    case 'DELETE':
                        $controller->delete($id);
                        break;
                    default:
                        Response::methodNotAllowed(['GET', 'PUT', 'DELETE']);
                }
            }
            break;
            
        case 'sensors':
            require_once __DIR__ . '/controllers/SensorController.php';
            $controller = new SensorController();
            
            if (!$id) {
                // /api/sensors
                switch ($requestMethod) {
                    case 'GET':
                        $controller->getAllSensorData();
                        break;
                    case 'POST':
                        $controller->insertSensorData();
                        break;
                    default:
                        Response::methodNotAllowed(['GET', 'POST']);
                }
            } elseif ($id === 'cleanup') {
                // /api/sensors/cleanup
                if ($requestMethod === 'DELETE') {
                    $controller->cleanupOldData();
                } else {
                    Response::methodNotAllowed(['DELETE']);
                }
            } elseif ($action === 'average') {
                // /api/sensors/{vehicle_id}/average
                if ($requestMethod === 'GET') {
                    $controller->getAverageSensorData($id);
                } else {
                    Response::methodNotAllowed(['GET']);
                }
            } else {
                // /api/sensors/{vehicle_id}
                if ($requestMethod === 'GET') {
                    $controller->getVehicleSensorData($id);
                } else {
                    Response::methodNotAllowed(['GET']);
                }
            }
            break;
            
        case 'logs':
            require_once __DIR__ . '/controllers/SensorController.php';
            $controller = new SensorController();
            
            // /api/logs
            switch ($requestMethod) {
                case 'GET':
                    $controller->getSystemLogs();
                    break;
                case 'POST':
                    $controller->insertSystemLog();
                    break;
                default:
                    Response::methodNotAllowed(['GET', 'POST']);
            }
            break;
            
        case 'health':
            // Health check endpoint
            if ($requestMethod === 'GET') {
                require_once __DIR__ . '/config/database.php';
                
                $health = [
                    'status' => 'ok',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'version' => '1.0.0'
                ];
                
                try {
                    $connections = testConnections();
                    $health['database'] = $connections;
                } catch (Exception $e) {
                    $health['database'] = ['error' => 'Connection failed'];
                }
                
                Response::success($health, 'API is healthy');
            } else {
                Response::methodNotAllowed(['GET']);
            }
            break;
            
        case '':
            // Root endpoint - API information
            if ($requestMethod === 'GET') {
                Response::success([
                    'name' => 'VoltiaCar API',
                    'version' => '1.0.0',
                    'description' => 'RESTful API for VoltiaCar platform',
                    'endpoints' => [
                        'auth' => '/api/auth/{login|register|logout|me|refresh|verify}',
                        'users' => '/api/users',
                        'vehicles' => '/api/vehicles',
                        'bookings' => '/api/bookings',
                        'sensors' => '/api/sensors',
                        'logs' => '/api/logs',
                        'health' => '/api/health'
                    ],
                    'documentation' => 'https://github.com/carsharing/api-docs'
                ], 'Welcome to Carsharing API');
            } else {
                Response::methodNotAllowed(['GET']);
            }
            break;
            
        default:
            Response::notFound('Endpoint not found');
    }
    
} catch (Exception $e) {
    // Handle uncaught exceptions
    error_log("Uncaught exception: " . $e->getMessage());
    Response::handleException($e, false); // Set to true for debug mode
}
