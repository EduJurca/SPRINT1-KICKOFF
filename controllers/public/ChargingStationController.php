<?php
/**
 * ChargingStationController
 * Manages charging stations CRUD and public views
 */

require_once MODELS_PATH . '/ChargingStation.php';

class ChargingStationController {
    private $stationModel;
    
    public function __construct() {
        $db = Database::getMariaDBConnection();
        $this->stationModel = new ChargingStation($db);
    }
    
    // ==========================================
    // ADMIN - CRUD METHODS
    // ==========================================
    
    /**
     * Display list of all charging stations (admin)
     */
    public function index() {
        // Check if user is admin
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: /login');
            exit;
        }
        
        $stations = $this->stationModel->getAllStations();
        
        Router::view('admin.charging.index', [
            'stations' => $stations,
            'totalStations' => count($stations)
        ]);
    }
    
    /**
     * Show form to create new station
     */
    public function create() {
        // Check if user is admin
        // if (!isset($_SESSION['user_id'])) {
        //     header('Location: /login');
        //     exit;
        // }
        
        Router::view('admin.charging.create');
    }
    
    /**
     * Store new charging station
     */
    public function store() {
        // Check if user is admin
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: /login');
            exit;
        }
        
        // Get POST data
        $data = [
            'name' => $_POST['name'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? null,
            'latitude' => floatval($_POST['latitude'] ?? 0),
            'longitude' => floatval($_POST['longitude'] ?? 0),
            'total_slots' => intval($_POST['total_slots'] ?? 4),
            'available_slots' => intval($_POST['available_slots'] ?? 4),
            'power_kw' => 50, // Fixed at 50kW
            'status' => $_POST['status'] ?? 'active',
            'operator' => $_POST['operator'] ?? 'VoltiaCar',
            'description' => $_POST['description'] ?? null
        ];
        
        // Validate data
        $errors = $this->validateStationData($data);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /admin/charging-stations/create');
            exit;
        }
        
        // Create station
        $result = $this->stationModel->createStation($data);
        
        if ($result) {
            $_SESSION['success'] = 'Charging station created successfully!';
            header('Location: /admin/charging-stations');
        } else {
            $_SESSION['error'] = 'Failed to create charging station';
            header('Location: /admin/charging-stations/create');
        }
        exit;
    }
    
    /**
     * Show form to edit station
     */
    public function edit($id) {
        // Check if user is admin
        // if (!isset($_SESSION['user_id'])) {
        //     header('Location: /login');
        //     exit;
        // }
        
        $station = $this->stationModel->getStationById($id);
        
        if (!$station) {
            $_SESSION['error'] = 'Station not found';
            header('Location: /admin/charging-stations');
            exit;
        }
        
        Router::view('admin.charging.edit', [
            'station' => $station
        ]);
    }
    
    /**
     * Update charging station
     */
    public function update($id) {
        // Check if user is admin
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: /login');
            exit;
        }
        
        // Get POST data
        $data = [
            'name' => $_POST['name'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? null,
            'latitude' => floatval($_POST['latitude'] ?? 0),
            'longitude' => floatval($_POST['longitude'] ?? 0),
            'total_slots' => intval($_POST['total_slots'] ?? 4),
            'available_slots' => intval($_POST['available_slots'] ?? 4),
            'power_kw' => 50, // Fixed at 50kW
            'status' => $_POST['status'] ?? 'active',
            'operator' => $_POST['operator'] ?? 'VoltiaCar',
            'description' => $_POST['description'] ?? null
        ];
        
        // Validate data
        $errors = $this->validateStationData($data);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /admin/charging-stations/' . $id . '/edit');
            exit;
        }
        
        // Update station
        $result = $this->stationModel->updateStation($id, $data);
        
        if ($result) {
            $_SESSION['success'] = 'Charging station updated successfully!';
            header('Location: /admin/charging-stations');
        } else {
            $_SESSION['error'] = 'Failed to update charging station';
            header('Location: /admin/charging-stations/' . $id . '/edit');
        }
        exit;
    }
    
    /**
     * Delete charging station
     */
    public function delete($id) {
        // Check if user is admin
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: /login');
            exit;
        }
        
        $result = $this->stationModel->deleteStation($id);
        
        if ($result) {
            $_SESSION['success'] = 'Charging station deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete charging station';
        }
        
        header('Location: /admin/charging-stations');
        exit;
    }
    
    // ==========================================
    // PUBLIC - MAP AND API METHODS
    // ==========================================
    
    /**
     * Show interactive map with all stations
     */
    public function showMap() {
        $stations = $this->stationModel->getAllStations();
        
        Router::view('charging.map', [
            'stations' => $stations
        ]);
    }
    
    /**
     * Get all stations as JSON (for AJAX/map)
     */
    public function getStationsJSON() {
        $stations = $this->stationModel->getAllStations();
        
        // Process stations for map display
        foreach ($stations as &$station) {
            $station['latitude'] = floatval($station['latitude']);
            $station['longitude'] = floatval($station['longitude']);
            $station['available_slots'] = intval($station['available_slots']);
            $station['total_slots'] = intval($station['total_slots']);
            $station['power_kw'] = intval($station['power_kw']);
        }
        
        return Router::json([
            'success' => true,
            'stations' => $stations
        ], 200);
    }
    
    /**
     * Get station details by ID (public view)
     */
    public function getStationDetails($id) {
        $station = $this->stationModel->getStationById($id);
        
        if (!$station) {
            $_SESSION['error'] = 'Charging station not found';
            header('Location: /charging-stations');
            exit;
        }
        
        Router::view('charging.details', [
            'station' => $station
        ]);
    }
    
    // ==========================================
    // VALIDATION
    // ==========================================
    
    /**
     * Validate station data
     * 
     * @param array $data Station data to validate
     * @return array Array of error messages
     */
    private function validateStationData($data) {
        $errors = [];
        
        // Name required
        if (empty($data['name'])) {
            $errors[] = "Name is required";
        }
        
        // Address required
        if (empty($data['address'])) {
            $errors[] = "Address is required";
        }
        
        // City required
        if (empty($data['city'])) {
            $errors[] = "City is required";
        }
        
        // Latitude valid (-90 to 90)
        if ($data['latitude'] < -90 || $data['latitude'] > 90) {
            $errors[] = "Invalid latitude (must be between -90 and 90)";
        }
        
        // Longitude valid (-180 to 180)
        if ($data['longitude'] < -180 || $data['longitude'] > 180) {
            $errors[] = "Invalid longitude (must be between -180 and 180)";
        }
        
        // Total slots positive
        if ($data['total_slots'] <= 0) {
            $errors[] = "Total slots must be positive";
        }
        
        // Available slots can't exceed total
        if ($data['available_slots'] > $data['total_slots']) {
            $errors[] = "Available slots cannot exceed total slots";
        }
        
        // Available slots can't be negative
        if ($data['available_slots'] < 0) {
            $errors[] = "Available slots cannot be negative";
        }
        
        return $errors;
    }
}
