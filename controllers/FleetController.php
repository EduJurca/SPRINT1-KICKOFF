<?php
/**
 * 🚗 FleetController - Gestió de Flota de Vehicles
 * 
 * Aquest controller gestiona la flota de vehicles.
 * Només accessible per MANAGERS i ADMINS.
 */

class FleetController {
    
    private $vehicleModel;
    private $bookingModel;
    
    public function __construct() {
        require_once __DIR__ . '/../models/Vehicle.php';
        require_once __DIR__ . '/../models/Booking.php';
        
        $this->vehicleModel = new Vehicle();
        $this->bookingModel = new Booking();
    }
    
    /**
     * Vista principal de gestió de flota
     * Només MANAGERS i ADMINS
     */
    public function index() {
      
        AuthController::requireRole('manager');
        
        // Obtenir tots els vehicles amb estadístiques
        $vehicles = $this->vehicleModel->getAllWithStats();
        
        Router::view('fleet.index', [
            'vehicles' => $vehicles,
            'title' => 'Gestió de Flota'
        ]);
    }
    
    /**
     * Afegir nou vehicle a la flota
     * Requereix permís 'add_vehicle' (Managers i Admins)
     */
    public function add() {
        AuthController::requirePermission('add_vehicle');
        
        // Si és GET, mostrar formulari
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            Router::view('fleet.add', [
                'title' => 'Afegir Vehicle'
            ]);
            return;
        }
        
        // Si és POST, processar formulari
        $data = [
            'brand' => $_POST['brand'] ?? '',
            'model' => $_POST['model'] ?? '',
            'license_plate' => $_POST['license_plate'] ?? '',
            'year' => $_POST['year'] ?? '',
            'color' => $_POST['color'] ?? '',
            'status' => 'available',
            'is_premium' => isset($_POST['is_premium']) ? 1 : 0
        ];
        
        // Validació
        if (empty($data['brand']) || empty($data['model']) || empty($data['license_plate'])) {
            $_SESSION['error'] = 'Tots els camps obligatoris han d\'estar omplerts';
            Router::redirect('/fleet/add');
            return;
        }
        
        $vehicleId = $this->vehicleModel->create($data);
        
        if ($vehicleId) {
            $_SESSION['success'] = 'Vehicle afegit correctament';
            Router::redirect('/fleet');
        } else {
            $_SESSION['error'] = 'Error al crear el vehicle';
            Router::redirect('/fleet/add');
        }
    }
    
    /**
     * Editar vehicle existent
     * Requereix permís 'edit_vehicle' (Managers i Admins)
     */
    public function edit($id) {
        AuthController::requirePermission('edit_vehicle');
        
        $vehicle = $this->vehicleModel->findById($id);
        
        if (!$vehicle) {
            $_SESSION['error'] = 'Vehicle no trobat';
            Router::redirect('/fleet');
            return;
        }
        
       
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            Router::view('fleet.edit', [
                'vehicle' => $vehicle,
                'title' => 'Editar Vehicle'
            ]);
            return;
        }
        
    
        $data = [
            'brand' => $_POST['brand'] ?? $vehicle['brand'],
            'model' => $_POST['model'] ?? $vehicle['model'],
            'year' => $_POST['year'] ?? $vehicle['year'],
            'color' => $_POST['color'] ?? $vehicle['color'],
            'is_premium' => isset($_POST['is_premium']) ? 1 : 0
        ];
        
        $success = $this->vehicleModel->update($id, $data);
        
        if ($success) {
            $_SESSION['success'] = 'Vehicle actualitzat correctament';
        } else {
            $_SESSION['error'] = 'Error al actualitzar el vehicle';
        }
        
        Router::redirect('/fleet');
    }
    
    /**
     * Desactivar/Activar vehicle
     * Requereix permís 'disable_vehicle' (Managers i Admins)
     */
    public function toggleStatus($id) {
        AuthController::requirePermission('disable_vehicle');
        
        $vehicle = $this->vehicleModel->findById($id);
        
        if (!$vehicle) {
            Router::json([
                'success' => false,
                'message' => 'Vehicle no trobat'
            ], 404);
            return;
        }
        
        $newStatus = $vehicle['status'] === 'available' ? 'maintenance' : 'available';
        $success = $this->vehicleModel->updateStatus($id, $newStatus);
        
        Router::json([
            'success' => $success,
            'message' => $success ? 'Estat actualitzat' : 'Error al actualitzar',
            'new_status' => $newStatus
        ]);
    }
    

    public function delete($id) {
        AuthController::requirePermission('delete_vehicle');
        
        $vehicle = $this->vehicleModel->findById($id);
        
        if (!$vehicle) {
            Router::json([
                'success' => false,
                'message' => 'Vehicle no trobat'
            ], 404);
            return;
        }
        
        // Comprovar si té reserves actives
        $activeBookings = $this->bookingModel->getActiveByVehicle($id);
        
        if (!empty($activeBookings)) {
            Router::json([
                'success' => false,
                'message' => 'No es pot eliminar un vehicle amb reserves actives'
            ], 400);
            return;
        }
        
        $success = $this->vehicleModel->softDelete($id);
        
        Router::json([
            'success' => $success,
            'message' => $success ? 'Vehicle eliminat' : 'Error al eliminar'
        ]);
    }
    
   
    public function stats() {
        AuthController::requirePermission('view_fleet_stats');
        
        $stats = [
            'total_vehicles' => $this->vehicleModel->count(),
            'available' => $this->vehicleModel->countByStatus('available'),
            'in_use' => $this->vehicleModel->countByStatus('in_use'),
            'maintenance' => $this->vehicleModel->countByStatus('maintenance'),
            'premium_vehicles' => $this->vehicleModel->countPremium(),
            'total_bookings_today' => $this->bookingModel->countToday(),
            'total_revenue_today' => $this->bookingModel->revenueToday(),
            'most_used_vehicles' => $this->vehicleModel->getMostUsed(5)
        ];
        
        Router::view('fleet.stats', [
            'stats' => $stats,
            'title' => 'Estadístiques de Flota'
        ]);
    }
    
    /**
     * Gestió de manteniment
     * Requereix permís 'manage_vehicle_maintenance' (Managers i Admins)
     */
    public function maintenance() {
        AuthController::requirePermission('manage_vehicle_maintenance');
        
        $maintenanceVehicles = $this->vehicleModel->getByStatus('maintenance');
        
        Router::view('fleet.maintenance', [
            'vehicles' => $maintenanceVehicles,
            'title' => 'Gestió de Manteniment'
        ]);
    }
    
    /**
     * Marcar vehicle com a en manteniment
     */
    public function setMaintenance($id) {
        AuthController::requirePermission('manage_vehicle_maintenance');
        
        $notes = $_POST['notes'] ?? 'Manteniment programat';
        
        $success = $this->vehicleModel->setMaintenance($id, $notes);
        
        Router::json([
            'success' => $success,
            'message' => $success ? 'Vehicle marcat per manteniment' : 'Error'
        ]);
    }
    
    /**
     * Completar manteniment i retornar vehicle a disponible
     */
    public function completeMaintenance($id) {
        AuthController::requirePermission('manage_vehicle_maintenance');
        
        $success = $this->vehicleModel->updateStatus($id, 'available');
        
        Router::json([
            'success' => $success,
            'message' => $success ? 'Manteniment completat' : 'Error'
        ]);
    }
}
