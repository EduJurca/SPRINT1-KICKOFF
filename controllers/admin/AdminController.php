<?php

require_once MODELS_PATH . '/User.php';
require_once MODELS_PATH . '/Booking.php';
require_once MODELS_PATH . '/Vehicle.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

/**
 * Admin Dashboard Controller
 * Responsable solo del dashboard principal y estadísticas del panel de admin
 * Las rutas específicas (vehículos, usuarios, incidencias) se delegan a sus controladores especializados
 */
class AdminController {
    private $db;
    private $userModel;
    private $bookingModel;
    private $vehicleModel;
    
    public function __construct() {
        AuthController::requireAdmin();
        $this->db = Database::getMariaDBConnection();
        $this->userModel = new User();
        $this->bookingModel = new Booking($this->db);
        $this->vehicleModel = new Vehicle($this->db);
    }
    
    public function dashboard() {
        $totalUsers = $this->getTotalUsers();
        $totalVehicles = $this->getTotalVehicles();
        $totalBookings = $this->getTotalBookings();
        $totalRevenue = $this->getTotalRevenue();
        $monthlyBookings = $this->getMonthlyBookings();
        $recentUsers = $this->getRecentUsers(5);
        
        return Router::view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalVehicles' => $totalVehicles,
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue,
            'monthlyBookings' => $monthlyBookings,
            'recentUsers' => $recentUsers,
            'pageTitle' => 'Dashboard'
        ]);
    }
    
    private function getTotalUsers() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM users");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    private function getTotalVehicles() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM vehicles");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    private function getTotalBookings() {
        return $this->bookingModel->getTotalActiveBookings();
    }
    
    private function getTotalRevenue() {
        return $this->bookingModel->getCurrentMonthRevenue();
    }
    
    private function getMonthlyBookings() {
        return $this->bookingModel->getMonthlyBookingsStats();
    }
    
    private function getRecentUsers($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT u.username, u.email, r.name as role_name 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.created_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }

    /**
     * Mostrar configuració d'admin
     */
    public function showSettings() {
        require_once PUBLIC_PATH . '/php/admin/settings.php';
    }
}

