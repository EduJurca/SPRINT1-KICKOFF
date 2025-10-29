<?php
/**
 * 🎮 BookingController
 * Gestiona les reserves de vehicles
 */

require_once MODELS_PATH . '/Booking.php';
require_once MODELS_PATH . '/Vehicle.php';
require_once CONTROLLERS_PATH . '/AuthController.php';

class BookingController {
    private $bookingModel;
    private $vehicleModel;
    
    public function __construct() {
        $db = Database::getMariaDBConnection();
        $this->bookingModel = new Booking($db);
        $this->vehicleModel = new Vehicle($db);
    }
    
    /**
     * Llistar totes les reserves de l'usuari
     */
    public function index() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        $bookings = $this->bookingModel->getBookingHistory($userId, 20);
        
        return Router::json([
            'success' => true,
            'bookings' => $bookings
        ], 200);
    }
    
    /**
     * Mostrar una reserva específica
     */
    public function show($id) {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        $booking = $this->bookingModel->getBookingById($id);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }
        
        // Verificar que la reserva pertany a l'usuari
        if ($booking['user_id'] != $userId && !isset($_SESSION['is_admin'])) {
            return Router::json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        return Router::json([
            'success' => true,
            'booking' => $booking
        ], 200);
    }
    
    /**
     * Crear nova reserva
     */
    public function create() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['vehicle_id'])) {
            return Router::json([
                'success' => false,
                'message' => 'Vehicle ID is required'
            ], 400);
        }
        
        $vehicleId = $data['vehicle_id'];
        $unlockFee = $data['unlock_fee'] ?? 0.50;
        
        // Verificar que el vehicle està disponible
        if (!$this->vehicleModel->isAvailable($vehicleId)) {
            return Router::json([
                'success' => false,
                'message' => 'Vehicle not available'
            ], 400);
        }
        
        // Crear reserva
        $bookingId = $this->bookingModel->createBooking($userId, $vehicleId, $unlockFee);
        
        if ($bookingId) {
            // Actualitzar estat del vehicle
            $this->vehicleModel->updateStatus($vehicleId, 'in_use');
            
            return Router::json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking_id' => $bookingId
            ], 201);
        }
        
        return Router::json([
            'success' => false,
            'message' => 'Error creating booking'
        ], 500);
    }
    
    /**
     * Actualitzar reserva
     */
    public function update($id) {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $booking = $this->bookingModel->getBookingById($id);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }
        
        // Verificar que la reserva pertany a l'usuari
        if ($booking['user_id'] != $userId && !isset($_SESSION['is_admin'])) {
            return Router::json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Implementa la lògica d'actualització segons les teves necessitats
        
        return Router::json([
            'success' => true,
            'message' => 'Booking updated successfully'
        ], 200);
    }
    
    /**
     * Eliminar/Cancel·lar reserva
     */
    public function delete($id) {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        $booking = $this->bookingModel->getBookingById($id);
        
        if (!$booking) {
            return Router::json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }
        
        // Verificar que la reserva pertany a l'usuari
        if ($booking['user_id'] != $userId && !isset($_SESSION['is_admin'])) {
            return Router::json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Cancel·lar reserva
        if ($this->bookingModel->cancelBooking($id)) {
            // Si el vehicle estava en ús, alliberar-lo
            if ($booking['status'] === 'active') {
                $this->vehicleModel->updateStatus($booking['vehicle_id'], 'available');
            }
            
            return Router::json([
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ], 200);
        }
        
        return Router::json([
            'success' => false,
            'message' => 'Error cancelling booking'
        ], 500);
    }
}
