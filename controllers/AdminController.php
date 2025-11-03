<?php
/**
 * 🛡️ AdminController
 * Gestiona el panell d'administració
 */

require_once CONTROLLERS_PATH . '/auth/AuthController.php';
require_once MODELS_PATH . '/User.php';

class AdminController {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Dashboard principal d'admin
     */
    public function dashboard() {
        // 🔐 Verificar autenticació i que sigui Staff (SuperAdmin o Treballador)
        $userId = AuthController::requireAuth();
        $roleId = $_SESSION['role_id'] ?? 3;
        
        if (!in_array($roleId, [1, 2])) {
            $_SESSION['error'] = 'Accés denegat. Només per personal autoritzat.';
            Router::redirect('/dashboard');
            exit;
        }
        
    
        $stats = $this->getStats();
        
        Router::view('admin.dashboard', [
            'stats' => $stats,
            'title' => 'Dashboard - Panel d\'Administració',
            'pageTitle' => 'Dashboard',
            'currentPage' => 'dashboard'
        ]);
    }
    
   
    public function users() {
        AuthController::requireAdmin();
        
        // Obtenir tots els usuaris
        $users = $this->userModel->getAll(100, 0);
        
        Router::view('admin.users.index', [
            'users' => $users,
            'title' => 'Usuaris - Panel d\'Administració',
            'pageTitle' => 'Gestió d\'Usuaris',
            'currentPage' => 'users'
        ]);
    }
    
    /**
     * Gestió de vehicles
     */
    public function vehicles() {
        AuthController::requireAdmin();
        
        // Obtenir vehicles de la base de dades
        require_once MODELS_PATH . '/Vehicle.php';
        $vehicleModel = new Vehicle();
        
        // Filtres
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';
        
        // Obtenir vehicles (sense filtres per ara, es pot ampliar)
        $db = Database::getMariaDBConnection();
        $query = "SELECT * FROM vehicles WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (plate LIKE ? OR brand LIKE ? OR model LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($status)) {
            $query .= " AND status = ?";
            $params[] = $status;
        }
        
        if (!empty($type)) {
            $query .= " AND type = ?";
            $params[] = $type;
        }
        
        $query .= " ORDER BY id DESC";
        
        $stmt = $db->prepare($query);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $vehicles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        Router::view('admin.vehicles', [
            'vehicles' => $vehicles,
            'title' => 'Vehicles - Panel d\'Administració',
            'pageTitle' => 'Gestió de Vehicles',
            'currentPage' => 'vehicles'
        ]);
    }
    
    /**
     * Gestió de reserves
     */
    public function bookings() {
        AuthController::requireAdmin();
        
    
        $db = Database::getMariaDBConnection();
        $query = "SELECT vu.*, u.username, v.plate as vehicle_plate 
                  FROM vehicle_usage vu 
                  LEFT JOIN users u ON vu.user_id = u.id 
                  LEFT JOIN vehicles v ON vu.vehicle_id = v.id 
                  ORDER BY vu.start_time DESC 
                  LIMIT 50";
        
        $result = $db->query($query);
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        
        // Calcular estadísticas
        $stats = [
            'active_bookings' => 0,
            'today_bookings' => 0,
            'pending_bookings' => 0,
            'completed_bookings' => 0
        ];
        
        foreach ($bookings as $booking) {
            if (empty($booking['end_time'])) {
                $stats['active_bookings']++;
            } else {
                $stats['completed_bookings']++;
            }
            
            if (date('Y-m-d', strtotime($booking['start_time'])) === date('Y-m-d')) {
                $stats['today_bookings']++;
            }
        }
        
        Router::view('admin.bookings', [
            'bookings' => $bookings,
            'stats' => $stats,
            'title' => 'Reserves - Panel d\'Administració',
            'pageTitle' => 'Gestió de Reserves',
            'currentPage' => 'bookings'
        ]);
    }
    
    /**
     * Incidències
     */
    public function incidencies() {
        AuthController::requireAdmin();
        
        Router::view('admin.incidencies', [
            'title' => 'Incidències - Panel d\'Administració',
            'pageTitle' => 'Gestió d\'Incidències',
            'currentPage' => 'incidencies'
        ]);
    }
    
    /**
     * Configuració (placeholder)
     */
    public function settings() {
        AuthController::requireAdmin();
        
        Router::view('admin.settings', [
            'title' => 'Configuració - Panel d\'Administració',
            'pageTitle' => 'Configuració del Sistema',
            'currentPage' => 'settings'
        ]);
    }
    
    /**

     * 
     * @return array Estadístiques
     */
    private function getStats() {
        
        $totalUsers = $this->userModel->count();
        
        $totalVehicles = 0;
        
       
        $activeBookings = 0;
        
        $monthlyRevenue = 0;
        
        
        $recentBookings = [];
        
        $popularVehicles = [];
        
        return [
            'total_users' => $totalUsers,
            'total_vehicles' => $totalVehicles,
            'active_bookings' => $activeBookings,
            'monthly_revenue' => $monthlyRevenue,
            'recent_bookings' => $recentBookings,
            'popular_vehicles' => $popularVehicles,
            
            // Percentatges de creixement (placeholder)
            'users_growth' => '+12%',
            'vehicles_growth' => '+5%',
            'bookings_growth' => '-3%',
            'revenue_growth' => '+18%'
        ];
    }
}
