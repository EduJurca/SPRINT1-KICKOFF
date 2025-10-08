<?php
/**
 * Vehicle Controller
 * Handles vehicle management endpoints
 * Implements 2025 RBAC best practices
 */

require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Response.php';

class VehicleController {
    private $vehicleModel;
    
    public function __construct() {
        $this->vehicleModel = new Vehicle();
    }
    
    /**
     * Get all vehicles
     * GET /api/vehicles
     */
    public function index() {
        try {
            // Optional authentication
            $currentUser = optionalAuthenticate();
            
            // Get query parameters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            $lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
            $lng = isset($_GET['lng']) ? (float)$_GET['lng'] : null;
            $radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 5;
            
            // Validate pagination
            $page = max(1, $page);
            $perPage = min(100, max(1, $perPage));
            
            // Validate status if provided
            if ($status && !in_array($status, ['available', 'in_use', 'maintenance', 'unavailable'])) {
                Response::badRequest('Invalid status value');
                return;
            }
            
            // Get vehicles
            if ($lat && $lng) {
                // Find nearby vehicles
                $vehicles = $this->vehicleModel->findNearby($lat, $lng, $radius);
                $total = count($vehicles);
            } elseif ($search) {
                // Search vehicles
                $vehicles = $this->vehicleModel->search($search, $page, $perPage);
                $total = $this->vehicleModel->count($status);
            } else {
                // Get all vehicles
                $vehicles = $this->vehicleModel->getAll($page, $perPage, $status);
                $total = $this->vehicleModel->count($status);
            }
            
            Response::paginated($vehicles, $total, $page, $perPage, 'Vehicles retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Get vehicles error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving vehicles');
        }
    }
    
    /**
     * Get vehicle by ID
     * GET /api/vehicles/{id}
     */
    public function show($id) {
        try {
            // Optional authentication
            $currentUser = optionalAuthenticate();
            
            // Get vehicle
            $vehicle = $this->vehicleModel->findById($id);
            
            if (!$vehicle) {
                Response::notFound('Vehicle not found');
                return;
            }
            
            Response::success($vehicle, 'Vehicle retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Get vehicle error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving vehicle');
        }
    }
    
    /**
     * Create vehicle (technician or admin)
     * POST /api/vehicles
     */
    public function create() {
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
                'model' => ['required' => true, 'type' => 'string', 'min_length' => 2, 'max_length' => 100],
                'brand' => ['required' => true, 'type' => 'string', 'min_length' => 2, 'max_length' => 100],
                'license_plate' => ['required' => true, 'type' => 'string', 'min_length' => 2, 'max_length' => 20],
                'status' => ['required' => false, 'type' => 'enum', 'values' => ['available', 'in_use', 'maintenance', 'unavailable']],
                'location_lat' => ['required' => false, 'type' => 'float', 'min' => -90, 'max' => 90],
                'location_lng' => ['required' => false, 'type' => 'float', 'min' => -180, 'max' => 180],
                'price_per_hour' => ['required' => true, 'type' => 'float', 'min' => 0]
            ]);
            
            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }
            
            // Check if license plate already exists
            if ($this->vehicleModel->findByLicensePlate($validation['data']['license_plate'])) {
                Response::conflict('License plate already exists');
                return;
            }
            
            // Create vehicle
            $vehicleId = $this->vehicleModel->create($validation['data']);
            
            if (!$vehicleId) {
                Response::serverError('Failed to create vehicle');
                return;
            }
            
            // Get created vehicle
            $vehicle = $this->vehicleModel->findById($vehicleId);
            
            // Log creation
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'info',
                'message' => "Vehicle created: {$vehicle['license_plate']}",
                'user_id' => $currentUser['id'],
                'action' => 'create_vehicle',
                'ip_address' => getClientIP()
            ]);
            
            Response::created($vehicle, 'Vehicle created successfully');
            
        } catch (Exception $e) {
            error_log("Create vehicle error: " . $e->getMessage());
            
            if ($e->getCode() == 409) {
                Response::conflict($e->getMessage());
            } else {
                Response::serverError('An error occurred while creating vehicle');
            }
        }
    }
    
    /**
     * Update vehicle (technician or admin)
     * PUT /api/vehicles/{id}
     */
    public function update($id) {
        try {
            // Require technician or admin role
            $currentUser = requireTechnicianOrAdmin();
            
            // Get request body
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                Response::badRequest('Invalid JSON data');
                return;
            }
            
            // Check if vehicle exists
            $vehicle = $this->vehicleModel->findById($id);
            if (!$vehicle) {
                Response::notFound('Vehicle not found');
                return;
            }
            
            // Build validation rules
            $rules = [];
            
            if (isset($data['model'])) {
                $rules['model'] = ['required' => true, 'type' => 'string', 'min_length' => 2, 'max_length' => 100];
            }
            
            if (isset($data['brand'])) {
                $rules['brand'] = ['required' => true, 'type' => 'string', 'min_length' => 2, 'max_length' => 100];
            }
            
            if (isset($data['license_plate'])) {
                $rules['license_plate'] = ['required' => true, 'type' => 'string', 'min_length' => 2, 'max_length' => 20];
            }
            
            if (isset($data['status'])) {
                $rules['status'] = ['required' => true, 'type' => 'enum', 'values' => ['available', 'in_use', 'maintenance', 'unavailable']];
            }
            
            if (isset($data['location_lat'])) {
                $rules['location_lat'] = ['required' => true, 'type' => 'float', 'min' => -90, 'max' => 90];
            }
            
            if (isset($data['location_lng'])) {
                $rules['location_lng'] = ['required' => true, 'type' => 'float', 'min' => -180, 'max' => 180];
            }
            
            if (isset($data['price_per_hour'])) {
                $rules['price_per_hour'] = ['required' => true, 'type' => 'float', 'min' => 0];
            }
            
            // Validate input
            if (!empty($rules)) {
                $validation = Validator::validate($data, $rules);
                
                if (!$validation['valid']) {
                    Response::validationError($validation['errors']);
                    return;
                }
                
                // Check if license plate is already taken by another vehicle
                if (isset($validation['data']['license_plate']) && $validation['data']['license_plate'] !== $vehicle['license_plate']) {
                    if ($this->vehicleModel->findByLicensePlate($validation['data']['license_plate'])) {
                        Response::conflict('License plate already in use');
                        return;
                    }
                }
                
                // Update vehicle
                $success = $this->vehicleModel->update($id, $validation['data']);
                
                if (!$success) {
                    Response::serverError('Failed to update vehicle');
                    return;
                }
            }
            
            // Get updated vehicle
            $updatedVehicle = $this->vehicleModel->findById($id);
            
            // Log update
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'info',
                'message' => "Vehicle updated: {$updatedVehicle['license_plate']}",
                'user_id' => $currentUser['id'],
                'action' => 'update_vehicle',
                'ip_address' => getClientIP()
            ]);
            
            Response::success($updatedVehicle, 'Vehicle updated successfully');
            
        } catch (Exception $e) {
            error_log("Update vehicle error: " . $e->getMessage());
            
            if ($e->getCode() == 409) {
                Response::conflict($e->getMessage());
            } else {
                Response::serverError('An error occurred while updating vehicle');
            }
        }
    }
    
    /**
     * Delete vehicle (admin only)
     * DELETE /api/vehicles/{id}
     */
    public function delete($id) {
        try {
            // Require admin role
            $currentUser = requireAdmin();
            
            // Check if vehicle exists
            $vehicle = $this->vehicleModel->findById($id);
            if (!$vehicle) {
                Response::notFound('Vehicle not found');
                return;
            }
            
            // Delete vehicle
            $success = $this->vehicleModel->delete($id);
            
            if (!$success) {
                Response::serverError('Failed to delete vehicle');
                return;
            }
            
            // Log deletion
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'warning',
                'message' => "Vehicle deleted: {$vehicle['license_plate']}",
                'user_id' => $currentUser['id'],
                'action' => 'delete_vehicle',
                'ip_address' => getClientIP()
            ]);
            
            Response::success(null, 'Vehicle deleted successfully');
            
        } catch (Exception $e) {
            error_log("Delete vehicle error: " . $e->getMessage());
            Response::serverError('An error occurred while deleting vehicle');
        }
    }
    
    /**
     * Update vehicle location
     * PATCH /api/vehicles/{id}/location
     */
    public function updateLocation($id) {
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
                'location_lat' => ['required' => true, 'type' => 'float', 'min' => -90, 'max' => 90],
                'location_lng' => ['required' => true, 'type' => 'float', 'min' => -180, 'max' => 180]
            ]);
            
            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }
            
            // Check if vehicle exists
            $vehicle = $this->vehicleModel->findById($id);
            if (!$vehicle) {
                Response::notFound('Vehicle not found');
                return;
            }
            
            // Update location
            $success = $this->vehicleModel->updateLocation(
                $id,
                $validation['data']['location_lat'],
                $validation['data']['location_lng']
            );
            
            if (!$success) {
                Response::serverError('Failed to update vehicle location');
                return;
            }
            
            // Get updated vehicle
            $updatedVehicle = $this->vehicleModel->findById($id);
            
            Response::success($updatedVehicle, 'Vehicle location updated successfully');
            
        } catch (Exception $e) {
            error_log("Update vehicle location error: " . $e->getMessage());
            Response::serverError('An error occurred while updating vehicle location');
        }
    }
    
    /**
     * Update vehicle status
     * PATCH /api/vehicles/{id}/status
     */
    public function updateStatus($id) {
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
                'status' => ['required' => true, 'type' => 'enum', 'values' => ['available', 'in_use', 'maintenance', 'unavailable']]
            ]);
            
            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }
            
            // Check if vehicle exists
            $vehicle = $this->vehicleModel->findById($id);
            if (!$vehicle) {
                Response::notFound('Vehicle not found');
                return;
            }
            
            // Update status
            $success = $this->vehicleModel->updateStatus($id, $validation['data']['status']);
            
            if (!$success) {
                Response::serverError('Failed to update vehicle status');
                return;
            }
            
            // Get updated vehicle
            $updatedVehicle = $this->vehicleModel->findById($id);
            
            Response::success($updatedVehicle, 'Vehicle status updated successfully');
            
        } catch (Exception $e) {
            error_log("Update vehicle status error: " . $e->getMessage());
            Response::serverError('An error occurred while updating vehicle status');
        }
    }
}
