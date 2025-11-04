<?php

require_once MODELS_PATH . '/User.php';
require_once MODELS_PATH . '/Booking.php';
require_once MODELS_PATH . '/Vehicle.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

class AdminController {
    private $userModel;
    private $bookingModel;
    private $vehicleModel;
    
    public function __construct() {
        $db = Database::getMariaDBConnection();
        $this->userModel = new User();
        $this->bookingModel = new Booking($db);
        $this->vehicleModel = new Vehicle($db);
    }
    
    public function dashboard() {
        AuthController::requireAdmin();
        
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
        $db = Database::getMariaDBConnection();
        $result = $db->query("SELECT COUNT(*) as total FROM users");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    private function getTotalVehicles() {
        $db = Database::getMariaDBConnection();
        $result = $db->query("SELECT COUNT(*) as total FROM vehicles");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    private function getTotalBookings() {
        $db = Database::getMariaDBConnection();
        $result = $db->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'active'");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    private function getTotalRevenue() {
        $db = Database::getMariaDBConnection();
        $result = $db->query("
            SELECT SUM(total_cost) as revenue 
            FROM bookings 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
            AND status IN ('completed', 'active')
        ");
        $row = $result->fetch_assoc();
        return $row['revenue'] ?? 0;
    }
    
    private function getMonthlyBookings() {
        $db = Database::getMariaDBConnection();
        
        $months = ['Gen', 'Feb', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Des'];
        $data = array_fill_keys($months, 0);
        
        $result = $db->query("
            SELECT MONTH(created_at) as month, COUNT(*) as count 
            FROM bookings 
            WHERE YEAR(created_at) = YEAR(CURRENT_DATE())
            GROUP BY MONTH(created_at)
        ");
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $monthIndex = (int)$row['month'] - 1;
                if ($monthIndex >= 0 && $monthIndex < 12) {
                    $data[$months[$monthIndex]] = (int)$row['count'];
                }
            }
        }
        
        return $data;
    }
    
    private function getRecentUsers($limit = 5) {
        $db = Database::getMariaDBConnection();
        $stmt = $db->prepare("
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
    
    public function vehicles() {
        AuthController::requireAdmin();
        
        $db = Database::getMariaDBConnection();
        $search = $_GET['search'] ?? '';
        
        if (!empty($search)) {
            $stmt = $db->prepare("
                SELECT v.*, 
                       (SELECT COUNT(*) FROM bookings WHERE vehicle_id = v.id AND status = 'active') as active_bookings
                FROM vehicles v
                WHERE v.brand LIKE ? OR v.model LIKE ? OR v.license_plate LIKE ?
                ORDER BY v.id DESC
            ");
            $searchTerm = "%$search%";
            $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $db->query("
                SELECT v.*, 
                       (SELECT COUNT(*) FROM bookings WHERE vehicle_id = v.id AND status = 'active') as active_bookings
                FROM vehicles v
                ORDER BY v.id DESC
            ");
        }
        
        $vehicles = [];
        while ($row = $result->fetch_assoc()) {
            $vehicles[] = $row;
        }
        
        return Router::view('admin.vehicles', [
            'vehicles' => $vehicles,
            'search' => $search,
            'pageTitle' => 'Vehicles',
            'currentPage' => 'vehicles'
        ]);
    }
    
    public function bookings() {
        AuthController::requireAdmin();
        
        $db = Database::getMariaDBConnection();
        $status = $_GET['status'] ?? '';
        
        $query = "
            SELECT b.*, 
                   u.username, 
                   v.brand, 
                   v.model, 
                   v.license_plate
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN vehicles v ON b.vehicle_id = v.id
        ";
        
        if (!empty($status)) {
            $stmt = $db->prepare($query . " WHERE b.status = ? ORDER BY b.created_at DESC LIMIT 100");
            $stmt->bind_param('s', $status);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $db->query($query . " ORDER BY b.created_at DESC LIMIT 100");
        }
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return Router::view('admin.bookings', [
            'bookings' => $bookings,
            'status' => $status,
            'pageTitle' => 'Reserves',
            'currentPage' => 'bookings'
        ]);
    }
}
