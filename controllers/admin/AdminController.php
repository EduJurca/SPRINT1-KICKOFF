<?php

require_once MODELS_PATH . '/User.php';
require_once MODELS_PATH . '/Booking.php';
require_once MODELS_PATH . '/Vehicle.php';
require_once MODELS_PATH . '/Incident.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

class AdminController {
    private $db;
    private $userModel;
    private $bookingModel;
    private $vehicleModel;
    private $incidentModel;
    
    public function __construct() {
        $this->db = Database::getMariaDBConnection();
        $this->userModel = new User();
        $this->bookingModel = new Booking($this->db);
        $this->vehicleModel = new Vehicle($this->db);
        $this->incidentModel = new Incident($this->db);
    }
    
    public function dashboard() {
        AuthController::requireAdmin();
        
        $totalUsers = $this->getTotalUsers();
        $totalVehicles = $this->getTotalVehicles();
        $totalBookings = $this->getTotalBookings();
        $totalRevenue = $this->getTotalRevenue();
        $totalIncidents = $this->incidentModel->getActiveIncidents();
        $monthlyBookings = $this->getMonthlyBookings();
        $recentUsers = $this->getRecentUsers(5);
        
        // Quick KPIs
        $energyThisMonth = $this->getEnergyConsumptionThisMonth();
        $newClientsMonth = $this->getNewClientsMonth();
        $newIncidentsMonth = $this->getNewIncidentsMonth();
        
        return Router::view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalVehicles' => $totalVehicles,
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue,
            'totalIncidents' => $totalIncidents,
            'monthlyBookings' => $monthlyBookings,
            'recentUsers' => $recentUsers,
            'energyThisMonth' => $energyThisMonth,
            'newClientsMonth' => $newClientsMonth,
            'newIncidentsMonth' => $newIncidentsMonth,
            'pageTitle' => 'Dashboard'
        ]);
    }
    
    private function getEnergyConsumptionThisMonth() {
        // Safely get energy consumed this month. If the table doesn't exist or query fails, return 0.0
        try {
            $check = $this->db->query("SHOW TABLES LIKE 'charging_sessions'");
            if (!$check || $check->num_rows === 0) {
                return 0.0;
            }

            $res = $this->db->query("SELECT SUM(IFNULL(energy_consumed_kwh,0)) as total_kwh FROM charging_sessions WHERE MONTH(start_time)=MONTH(CURRENT_DATE()) AND YEAR(start_time)=YEAR(CURRENT_DATE())");
            if ($res) {
                $row = $res->fetch_assoc();
                return (float)($row['total_kwh'] ?? 0);
            }
        } catch (\Throwable $e) {
            // Log could be added here. Fallback to 0.0
            return 0.0;
        }

        return 0.0;
    }

    private function getNewClientsMonth() {
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM users WHERE role_id = 3 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        if ($res) {
            $row = $res->fetch_assoc();
            return (int)($row['cnt'] ?? 0);
        }
        return 0;
    }

    private function getNewIncidentsMonth() {
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM incidents WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        if ($res) {
            $row = $res->fetch_assoc();
            return (int)($row['cnt'] ?? 0);
        }
        return 0;
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
        $result = $this->db->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'active'");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    private function getTotalRevenue() {
        $result = $this->db->query("
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
        
        $months = ['Gen', 'Feb', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Des'];
        $data = array_fill_keys($months, 0);
        
        $result = $this->db->query("
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
    
    public function vehicles() {
        AuthController::requireAdmin();
        
        $search = $_GET['search'] ?? '';
        
        if (!empty($search)) {
            $stmt = $this->db->prepare("
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
            $result = $this->db->query("
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
            $stmt = $this->db->prepare($query . " WHERE b.status = ? ORDER BY b.created_at DESC LIMIT 100");
            $stmt->bind_param('s', $status);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($query . " ORDER BY b.created_at DESC LIMIT 100");
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
