<?php
/**
 * Sensor Controller
 * Handles sensor data and system logs endpoints
 * Implements 2025 MongoDB best practices
 */

require_once __DIR__ . '/../models/Sensor.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Response.php';

class SensorController {
    private $sensorModel;
    
    public function __construct() {
        $this->sensorModel = new Sensor();
    }
    
    /**
     * Get sensor data by vehicle ID
     * GET /api/sensors/{vehicle_id}
     */
    public function getVehicleSensorData($vehicleId) {
        try {
            // Require authentication
            $currentUser = authenticate();
            
            // Get query parameters
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $skip = isset($_GET['skip']) ? (int)$_GET['skip'] : 0;
            $latest = isset($_GET['latest']) && $_GET['latest'] === 'true';
            $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
            $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
            
            // Validate parameters
            $limit = min(1000, max(1, $limit));
            $skip = max(0, $skip);
            
            // Get sensor data
            if ($latest) {
                $data = $this->sensorModel->getLatestByVehicle($vehicleId);
                
                if (!$data) {
                    Response::notFound('No sensor data found for this vehicle');
                    return;
                }
                
                Response::success($data, 'Latest sensor data retrieved successfully');
            } elseif ($startDate && $endDate) {
                // Validate dates
                if (!Validator::validateDate($startDate) || !Validator::validateDate($endDate)) {
                    Response::badRequest('Invalid date format. Use Y-m-d format');
                    return;
                }
                
                $data = $this->sensorModel->getByDateRange($vehicleId, $startDate, $endDate);
                Response::success($data, 'Sensor data retrieved successfully');
            } else {
                $data = $this->sensorModel->getHistoryByVehicle($vehicleId, $limit, $skip);
                $total = $this->sensorModel->countByVehicle($vehicleId);
                
                Response::paginated($data, $total, ($skip / $limit) + 1, $limit, 'Sensor data retrieved successfully');
            }
            
        } catch (Exception $e) {
            error_log("Get sensor data error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving sensor data');
        }
    }
    
    /**
     * Get average sensor data by vehicle
     * GET /api/sensors/{vehicle_id}/average
     */
    public function getAverageSensorData($vehicleId) {
        try {
            // Require authentication
            $currentUser = authenticate();
            
            // Get query parameters
            $hours = isset($_GET['hours']) ? (int)$_GET['hours'] : 24;
            
            // Validate parameters
            $hours = min(720, max(1, $hours)); // Max 30 days
            
            // Get average data
            $data = $this->sensorModel->getAverageByVehicle($vehicleId, $hours);
            
            if (!$data) {
                Response::notFound('No sensor data found for this vehicle');
                return;
            }
            
            Response::success($data, 'Average sensor data retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Get average sensor data error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving average sensor data');
        }
    }
    
    /**
     * Insert sensor data (technician or admin)
     * POST /api/sensors
     */
    public function insertSensorData() {
        try {
            // Require technician or admin role
            $currentUser = requireTechnicianOrAdmin();
            
            // Get request body
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                Response::badRequest('Invalid JSON data');
                return;
            }
            
            // Validate input
            $validation = Validator::validate($data, [
                'vehicle_id' => ['required' => true, 'type' => 'integer', 'min' => 1],
                'battery_level' => ['required' => false, 'type' => 'float', 'min' => 0, 'max' => 100],
                'fuel_level' => ['required' => false, 'type' => 'float', 'min' => 0, 'max' => 100],
                'speed' => ['required' => false, 'type' => 'float', 'min' => 0],
                'location_lat' => ['required' => false, 'type' => 'float', 'min' => -90, 'max' => 90],
                'location_lng' => ['required' => false, 'type' => 'float', 'min' => -180, 'max' => 180],
                'temperature' => ['required' => false, 'type' => 'float'],
                'tire_pressure' => ['required' => false, 'type' => 'float', 'min' => 0]
            ]);
            
            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }
            
            // Insert sensor data
            $insertedId = $this->sensorModel->insert($validation['data']);
            
            if (!$insertedId) {
                Response::serverError('Failed to insert sensor data');
                return;
            }
            
            Response::created(['id' => $insertedId], 'Sensor data inserted successfully');
            
        } catch (Exception $e) {
            error_log("Insert sensor data error: " . $e->getMessage());
            Response::serverError('An error occurred while inserting sensor data');
        }
    }
    
    /**
     * Get system logs (admin only)
     * GET /api/logs
     */
    public function getSystemLogs() {
        try {
            // Require admin role
            $currentUser = requireAdmin();
            
            // Get query parameters
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $skip = isset($_GET['skip']) ? (int)$_GET['skip'] : 0;
            $level = isset($_GET['level']) ? $_GET['level'] : null;
            
            // Validate parameters
            $limit = min(1000, max(1, $limit));
            $skip = max(0, $skip);
            
            // Validate level if provided
            if ($level && !in_array($level, ['info', 'warning', 'error'])) {
                Response::badRequest('Invalid level value. Must be: info, warning, or error');
                return;
            }
            
            // Get logs
            $logs = $this->sensorModel->getSystemLogs($limit, $skip, $level);
            $total = $this->sensorModel->countLogs($level);
            
            Response::paginated($logs, $total, ($skip / $limit) + 1, $limit, 'System logs retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Get system logs error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving system logs');
        }
    }
    
    /**
     * Insert system log (admin only)
     * POST /api/logs
     */
    public function insertSystemLog() {
        try {
            // Require admin role
            $currentUser = requireAdmin();
            
            // Get request body
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                Response::badRequest('Invalid JSON data');
                return;
            }
            
            // Validate input
            $validation = Validator::validate($data, [
                'level' => ['required' => true, 'type' => 'enum', 'values' => ['info', 'warning', 'error']],
                'message' => ['required' => true, 'type' => 'string', 'min_length' => 1, 'max_length' => 1000],
                'action' => ['required' => false, 'type' => 'string', 'max_length' => 100]
            ]);
            
            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }
            
            // Add user and IP information
            $logData = $validation['data'];
            $logData['user_id'] = $currentUser['id'];
            $logData['ip_address'] = getClientIP();
            
            // Insert log
            $insertedId = $this->sensorModel->insertLog($logData);
            
            if (!$insertedId) {
                Response::serverError('Failed to insert system log');
                return;
            }
            
            Response::created(['id' => $insertedId], 'System log inserted successfully');
            
        } catch (Exception $e) {
            error_log("Insert system log error: " . $e->getMessage());
            Response::serverError('An error occurred while inserting system log');
        }
    }
    
    /**
     * Get all sensor data (admin only)
     * GET /api/sensors
     */
    public function getAllSensorData() {
        try {
            // Require admin role
            $currentUser = requireAdmin();
            
            // Get query parameters
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $skip = isset($_GET['skip']) ? (int)$_GET['skip'] : 0;
            
            // Validate parameters
            $limit = min(1000, max(1, $limit));
            $skip = max(0, $skip);
            
            // Get all sensor data
            $data = $this->sensorModel->getAll($limit, $skip);
            
            Response::success($data, 'All sensor data retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Get all sensor data error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving sensor data');
        }
    }
    
    /**
     * Delete old sensor data (admin only)
     * DELETE /api/sensors/cleanup
     */
    public function cleanupOldData() {
        try {
            // Require admin role
            $currentUser = requireAdmin();
            
            // Get query parameters
            $daysOld = isset($_GET['days']) ? (int)$_GET['days'] : 90;
            
            // Validate parameters
            $daysOld = min(365, max(30, $daysOld)); // Between 30 and 365 days
            
            // Delete old data
            $deletedCount = $this->sensorModel->deleteOldData($daysOld);
            
            // Log cleanup
            $this->sensorModel->insertLog([
                'level' => 'info',
                'message' => "Cleaned up {$deletedCount} old sensor data records (older than {$daysOld} days)",
                'user_id' => $currentUser['id'],
                'action' => 'cleanup_sensor_data',
                'ip_address' => getClientIP()
            ]);
            
            Response::success([
                'deleted_count' => $deletedCount,
                'days_old' => $daysOld
            ], 'Old sensor data cleaned up successfully');
            
        } catch (Exception $e) {
            error_log("Cleanup sensor data error: " . $e->getMessage());
            Response::serverError('An error occurred while cleaning up sensor data');
        }
    }
}
