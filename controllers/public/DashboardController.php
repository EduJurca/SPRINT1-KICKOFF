<?php
/**
 * 🎮 DashboardController
 * Gestiona les dades del dashboard
 */

require_once MODELS_PATH . '/User.php';
require_once MODELS_PATH . '/Booking.php';
require_once MODELS_PATH . '/Vehicle.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

class DashboardController {
    private $userModel;
    private $bookingModel;
    private $vehicleModel;
    
    public function __construct() {
        $db = Database::getMariaDBConnection();
        $this->userModel = new User();
        $this->bookingModel = new Booking($db);
        $this->vehicleModel = new Vehicle($db);
    }
    
    /**
     * Mostrar pàgina de gestió amb dades renderitzades des del servidor
     */
    public function showGestio() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Obtenir informació de l'usuari
        $userInfo = $this->userModel->getUserInfo($userId);
        
        // Obtenir reserva activa si n'hi ha
        $activeBooking = $this->bookingModel->getActiveBooking($userId);
        
        // Obtenir historial de reserves recent
        $recentBookings = $this->bookingModel->getBookingHistory($userId, 5);
        
        // Passar totes les dades a la vista
        $data = array_merge(
            $userInfo ?? [],
            [
                'active_booking' => $activeBooking,
                'recent_bookings' => $recentBookings
            ]
        );
        
        return Router::view('public.dashboard.gestio', $data);
    }
    
    /**
     * Obtenir dades per a la pàgina de gestió (API - legacy)
     */
    public function getGestioData() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Obtenir informació de l'usuari
        $userInfo = $this->userModel->getUserInfo($userId);
        
        // Obtenir reserva activa si n'hi ha
        $activeBooking = $this->bookingModel->getActiveBooking($userId);
        
        // Obtenir historial de reserves recent
        $recentBookings = $this->bookingModel->getBookingHistory($userId, 5);
        
        return Router::json([
            'success' => true,
            'user' => $userInfo,
            'active_booking' => $activeBooking,
            'recent_bookings' => $recentBookings
        ], 200);
    }
    
    /**
     * Obtenir estadístiques del dashboard (només admin)
     */
    public function getStats() {
        // Requerir admin
        AuthController::requireAdmin();
        
        $db = Database::getMariaDBConnection();
        
        // Total d'usuaris
        $usersResult = $db->query("SELECT COUNT(*) as total FROM users");
        $totalUsers = $usersResult->fetch_assoc()['total'];
        
        // Total de vehicles
        $vehiclesResult = $db->query("SELECT COUNT(*) as total FROM vehicles");
        $totalVehicles = $vehiclesResult->fetch_assoc()['total'];
        
        // Vehicles disponibles
        $availableVehiclesResult = $db->query("SELECT COUNT(*) as total FROM vehicles WHERE status = 'available'");
        $availableVehicles = $availableVehiclesResult->fetch_assoc()['total'];
        
        // Total de reserves
        $bookingsResult = $db->query("SELECT COUNT(*) as total FROM bookings");
        $totalBookings = $bookingsResult->fetch_assoc()['total'];
        
        // Reserves actives
        $activeBookingsResult = $db->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'active'");
        $activeBookings = $activeBookingsResult->fetch_assoc()['total'];
        
        return Router::json([
            'success' => true,
            'stats' => [
                'total_users' => $totalUsers,
                'total_vehicles' => $totalVehicles,
                'available_vehicles' => $availableVehicles,
                'total_bookings' => $totalBookings,
                'active_bookings' => $activeBookings
            ]
        ], 200);
    }
}
