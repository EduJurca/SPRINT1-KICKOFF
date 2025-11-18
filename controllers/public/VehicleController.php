<?php
/**
 * 🎮 VehicleController
 * Gestiona la lògica relacionada amb vehicles
 */

require_once MODELS_PATH . '/Vehicle.php';
require_once MODELS_PATH . '/Booking.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

class VehicleController {
    private $vehicleModel;
    private $bookingModel;
    
    public function __construct() {
        $db = Database::getMariaDBConnection();
        $this->vehicleModel = new Vehicle($db);
        $this->bookingModel = new Booking($db);
    }
    
  
    public function getAvailableVehicles() {
        $vehicles = $this->vehicleModel->getAvailableVehicles();
        
       
        foreach ($vehicles as &$vehicle) {
            // Formatear ubicació
            if (isset($vehicle['latitude']) && isset($vehicle['longitude'])) {
                $vehicle['location'] = [
                    'lat' => (float)$vehicle['latitude'],
                    'lng' => (float)$vehicle['longitude']
                ];
            } else {
                $vehicle['location'] = [
                    'lat' => 40.7117 + (rand(-100, 100) / 10000),
                    'lng' => 0.5783 + (rand(-100, 100) / 10000)
                ];
            }
            
            // Assegurar camps necessaris
            $vehicle['status'] = $vehicle['status'] ?? 'available';
            $vehicle['battery'] = $vehicle['battery_level'] ?? rand(60, 100);
            $vehicle['type'] = $vehicle['vehicle_type'] ?? 'car';
            $vehicle['price_per_minute'] = (float)($vehicle['price_per_minute'] ?? 0.35);
            $vehicle['image_url'] = $vehicle['image_url'] ?? '/images/default-car.jpg';
            $vehicle['is_accessible'] = (bool)($vehicle['is_accessible'] ?? false);
            
            // Netejar camps duplicats
            unset($vehicle['latitude']);
            unset($vehicle['longitude']);
            unset($vehicle['battery_level']);
            unset($vehicle['vehicle_type']);
        }
        
        return Router::json([
            'success' => true,
            'vehicles' => $vehicles
        ], 200);
    }
    
  
    public function getVehicleById($id) {
        $vehicle = $this->vehicleModel->getVehicleById($id);
        
        if (!$vehicle) {
            return Router::json([
                'success' => false,
                'message' => 'Vehicle not found'
            ], 404);
        }
        
        return Router::json([
            'success' => true,
            'vehicle' => $vehicle
        ], 200);
    }
   
    public function claimVehicle() {
    
        $userId = AuthController::requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['vehicle_id'])) {
            return Router::json([
                'success' => false,
                'message' => 'Vehicle ID is required'
            ], 400);
        }
        
        $vehicleId = $data['vehicle_id'];
        
        error_log("=== VehicleController::claimVehicle START ===");
        error_log("Vehicle ID: $vehicleId");
        error_log("User ID: $userId");
        
        // Verificar que el vehicle existeix i està disponible
        $vehicle = $this->vehicleModel->getVehicleForClaim($vehicleId);
        
        if (!$vehicle) {
            error_log("ERROR: Vehicle not available or not found");
            return Router::json([
                'success' => false,
                'message' => __('vehicle.not_available')
            ], 404);
        }
        
        error_log("Vehicle found: " . json_encode($vehicle));
        
        // Verificar que l'usuari no té altre vehicle actiu
        $activeBooking = $this->bookingModel->getActiveBooking($userId);
        if ($activeBooking) {
            error_log("ERROR: User already has active booking: " . json_encode($activeBooking));
            return Router::json([
                'success' => false,
                'message' => __('vehicle.already_claimed')
            ], 400);
        }
        
        error_log("No active bookings for user");
        
        $duration = isset($data['duration']) ? (int)$data['duration'] : 30;
        
        // Actualitzar estat del vehicle
        error_log("Updating vehicle status to 'in_use'...");
        if (!$this->vehicleModel->updateStatus($vehicleId, 'in_use')) {
            error_log("ERROR: Failed to update vehicle status");
            return Router::json([
                'success' => false,
                'message' => 'Failed to update vehicle status'
            ], 500);
        }
        
        error_log("Vehicle status updated successfully");
        
        // Crear booking amb durada i càlcul de preu
        error_log("Creating booking...");
        $unlockFee = 0.50;
        $bookingId = $this->bookingModel->createBooking($userId, $vehicleId, $unlockFee, $duration);
        
        if (!$bookingId) {
            error_log("ERROR: Failed to create booking");
            // Revertir canvi d'estat si falla el booking
            $this->vehicleModel->updateStatus($vehicleId, 'available');
            return Router::json([
                'success' => false,
                'message' => 'Failed to create booking'
            ], 500);
        }
        
        error_log("SUCCESS: Booking created with ID: $bookingId");
        
        // Formatear ubicació
        if (isset($vehicle['latitude']) && isset($vehicle['longitude'])) {
            $vehicle['location'] = [
                'lat' => (float)$vehicle['latitude'],
                'lng' => (float)$vehicle['longitude']
            ];
        } else {
            $vehicle['location'] = [
                'lat' => 40.7117,
                'lng' => 0.5783
            ];
        }
        
        // Assegurar que el status és in_use
        $vehicle['status'] = 'in_use';
        
        // Netejar camps
        unset($vehicle['latitude']);
        unset($vehicle['longitude']);
        
        error_log("=== SUCCESS: Vehicle claimed successfully ===");
        
        return Router::json([
            'success' => true,
            'message' => 'Vehicle claimed successfully',
            'vehicle' => $vehicle,
            'unlock_fee' => $unlockFee,
            'booking_id' => $bookingId
        ], 200);
    }
    
    /**
     * Alliberar vehicle
     */
    public function releaseVehicle() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Buscar booking actiu
        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'No active vehicle'
            ], 404);
        }
        
        $vehicleId = $booking['vehicle_id'];
        
        // Actualitzar estat del vehicle
        if (!$this->vehicleModel->updateStatus($vehicleId, 'available')) {
            return Router::json([
                'success' => false,
                'message' => 'Failed to update vehicle status'
            ], 500);
        }
        
        // Completar booking
        if (!$this->bookingModel->completeBooking($vehicleId, $userId)) {
            return Router::json([
                'success' => false,
                'message' => 'Failed to complete booking'
            ], 500);
        }
        
        return Router::json([
            'success' => true,
            'message' => 'Vehicle released successfully'
        ], 200);
    }
    
    /**
     * Obtenir vehicle actual de l'usuari
     */
    public function getCurrentVehicle() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Buscar booking actiu
        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'No active vehicle'
            ], 404);
        }
        
        // Obtenir dades completes del vehicle
        $vehicle = $this->vehicleModel->getVehicleById($booking['vehicle_id']);
        
        if (!$vehicle) {
            return Router::json([
                'success' => false,
                'message' => 'Vehicle not found'
            ], 404);
        }
        
        // Assegurar que tots els camps necessaris estan presents
        $vehicle['id'] = $vehicle['id'] ?? $booking['vehicle_id'];
        $vehicle['battery'] = $vehicle['battery'] ?? 85;
        $vehicle['status'] = $vehicle['status'] ?? 'in_use';
        
        // Assegurar que location existeix
        if (!isset($vehicle['location'])) {
            $vehicle['location'] = [
                'lat' => 40.7117,
                'lng' => 0.5783
            ];
        }
        
        return Router::json([
            'success' => true,
            'vehicle' => $vehicle,
            'booking' => [
                'id' => $booking['id'],
                'start_datetime' => $booking['start_datetime'],
                'status' => $booking['status']
            ]
        ], 200);
    }
 
    public function search() {
        return $this->getAvailableVehicles();
    }

    public function bookVehicle() {
        return $this->claimVehicle();
    }
    

    /**
     * Activar botzina del vehicle
     */
    public function activateHorn() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Verificar que l'usuari té un vehicle actiu
        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'No active vehicle'
            ], 404);
        }
        
        // Mock: simular activació de botzina
        error_log("Horn activated for vehicle {$booking['vehicle_id']} by user $userId");
        
        return Router::json([
            'success' => true,
            'message' => 'Horn activated'
        ], 200);
    }
    
    /**
     * Activar llums del vehicle
     */
    public function activateLights() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Verificar que l'usuari té un vehicle actiu
        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'No active vehicle'
            ], 404);
        }
        
        // Mock: simular activació de llums
        error_log("Lights activated for vehicle {$booking['vehicle_id']} by user $userId");
        
        return Router::json([
            'success' => true,
            'message' => 'Lights activated'
        ], 200);
    }

    public function startEngine() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Verificar que l'usuari té un vehicle actiu
        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'No active vehicle'
            ], 404);
        }
        
        // Mock: simular arrencada del motor
        error_log("Engine started for vehicle {$booking['vehicle_id']} by user $userId");
        
        return Router::json([
            'success' => true,
            'message' => 'Engine started'
        ], 200);
    }
    
  
    public function stopEngine() {
  
        $userId = AuthController::requireAuth();
        
      
        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'No active vehicle'
            ], 404);
        }
        
     
        error_log("Engine stopped for vehicle {$booking['vehicle_id']} by user $userId");
        
        return Router::json([
            'success' => true,
            'message' => 'Engine stopped'
        ], 200);
    }
   
    public function lockDoors() {
     
        $userId = AuthController::requireAuth();
        
        
        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'No active vehicle'
            ], 404);
        }
        
      
        error_log("Doors locked for vehicle {$booking['vehicle_id']} by user $userId");
        
        return Router::json([
            'success' => true,
            'message' => 'Doors locked'
        ], 200);
    }
    
   
    public function unlockDoors() {
      
        $userId = AuthController::requireAuth();

        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'No active vehicle'
            ], 404);
        }
        
        error_log("Doors unlocked for vehicle {$booking['vehicle_id']} by user $userId");
        
        return Router::json([
            'success' => true,
            'message' => 'Doors unlocked'
        ], 200);
    }
}
