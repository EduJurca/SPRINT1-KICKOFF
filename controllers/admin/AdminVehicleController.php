<?php
/**
 * 🚗 Admin Vehicle Controller
 * Gestiona el CRUD de vehículos para administradores
 */

// require_once CONTROLLERS_PATH . '/auth/AuthController.php';
require_once MODELS_PATH . '/Vehicle.php';

class AdminVehicleController {
    private $vehicleModel;
    
    public function __construct() {
        // Verificar que el usuario es admin (implementar cuando esté listo el sistema de usuarios)
        // AuthController::requireAdmin();
        
        $this->vehicleModel = new Vehicle();
    }
    
    /**
     * INDEX - Listar todos los vehículos
     * Ruta: GET /admin/vehicles
     */
    public function index() {
        try {
            // Paginación
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;
            
            // Búsqueda global
            $search = trim($_GET['search'] ?? '');
            
            // Filtros avanzados
            $filters = [];
            if (isset($_GET['brand']) && trim($_GET['brand']) !== '') {
                $filters['brand'] = trim($_GET['brand']);
            }
            if (isset($_GET['model']) && trim($_GET['model']) !== '') {
                $filters['model'] = trim($_GET['model']);
            }
            if (isset($_GET['status']) && $_GET['status'] !== '') {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['is_accessible']) && $_GET['is_accessible'] !== '') {
                $filters['is_accessible'] = $_GET['is_accessible'];
            }
            if (isset($_GET['min_battery']) && $_GET['min_battery'] !== '') {
                $filters['min_battery'] = (int)$_GET['min_battery'];
            }
            
            // Obtener vehículos con paginación
            $vehicles = $this->vehicleModel->getAllVehicles($perPage, $offset, $search, $filters);
            $totalVehicles = $this->vehicleModel->countVehicles($search, $filters);
            $totalPages = max(1, ceil($totalVehicles / $perPage));
            
            // Asegurar que la página actual no exceda el total
            if ($page > $totalPages && $totalPages > 0) {
                $page = $totalPages;
                // Redirigir a la última página válida
                $queryParams = $_GET;
                $queryParams['page'] = $totalPages;
                $queryString = http_build_query($queryParams);
                return Router::redirect('/admin/vehicles?' . $queryString);
            }
            
            // Renderizar vista
            Router::view('admin.vehicles.index', [
                'vehicles' => $vehicles,
                'search' => $search,
                'filters' => $filters,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalVehicles' => $totalVehicles,
                'perPage' => $perPage
            ]);
            
        } catch (Exception $e) {
            error_log('Error in vehicle index: ' . $e->getMessage());
            Router::view('admin.vehicles.index', [
                'vehicles' => [],
                'search' => '',
                'filters' => [],
                'currentPage' => 1,
                'totalPages' => 1,
                'totalVehicles' => 0,
                'perPage' => 10,
                'error' => 'Error al cargar los vehículos'
            ]);
        }
    }
    
    /**
     * SHOW - Ver detalle de un vehículo
     * Ruta: GET /admin/vehicles/{id}
     */
    public function show($id) {
        try {
            $vehicle = $this->vehicleModel->getVehicleById($id);
            
            if (!$vehicle) {
                $_SESSION['error'] = 'Vehículo no encontrado';
                return Router::redirect('/admin/vehicles');
            }
            
            Router::view('admin.vehicles.show', [
                'vehicle' => $vehicle
            ]);
            
        } catch (Exception $e) {
            error_log('Error showing vehicle: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el vehículo';
            Router::redirect('/admin/vehicles');
        }
    }
    
    /**
     * ➕ CREATE - Mostrar formulario de crear
     * Ruta: GET /admin/vehicles/create
     */
    public function create() {
        Router::view('admin.vehicles.create');
    }
    
    /**
     * STORE - Guardar nuevo vehículo
     * Ruta: POST /admin/vehicles
     */
    public function store() {
        try {
            // Validar datos
            $data = [
                'plate' => $_POST['plate'] ?? '',
                'brand' => $_POST['brand'] ?? '',
                'model' => $_POST['model'] ?? '',
                'year' => (int)($_POST['year'] ?? date('Y')),
                'battery_level' => (int)($_POST['battery_level'] ?? 100),
                'latitude' => (float)($_POST['latitude'] ?? 40.7117),
                'longitude' => (float)($_POST['longitude'] ?? 0.5783),
                'status' => $_POST['status'] ?? 'available',
                'vehicle_type' => $_POST['vehicle_type'] ?? 'car',
                'is_accessible' => isset($_POST['is_accessible']) ? 1 : 0,
                'price_per_minute' => (float)($_POST['price_per_minute'] ?? 0.35),
                'image_url' => $_POST['image_url'] ?? null
            ];
            
            // Validar
            $errors = $this->vehicleModel->validate($data);
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old_data'] = $data;
                return Router::redirect('/admin/vehicles/create');
            }
            
            // Crear vehículo
            $vehicleId = $this->vehicleModel->create($data);
            
            if ($vehicleId) {
                $_SESSION['success'] = 'Vehículo creado correctamente';
                return Router::redirect('/admin/vehicles/' . $vehicleId);
            } else {
                $_SESSION['error'] = 'Error al crear el vehículo';
                return Router::redirect('/admin/vehicles/create');
            }
            
        } catch (Exception $e) {
            error_log('Error creating vehicle: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al crear el vehículo: ' . $e->getMessage();
            Router::redirect('/admin/vehicles/create');
        }
    }
    
    /**
     * EDIT - Mostrar formulario de editar
     * Ruta: GET /admin/vehicles/{id}/edit
     */
    public function edit($id) {
        try {
            $vehicle = $this->vehicleModel->getVehicleById($id); //crida al model
            
            if (!$vehicle) {
                $_SESSION['error'] = 'Vehículo no encontrado';
                return Router::redirect('/admin/vehicles');
            }
            
            Router::view('admin.vehicles.edit', [
                'vehicle' => $vehicle
            ]);
            
        } catch (Exception $e) {
            error_log('Error editing vehicle: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el vehículo';
            Router::redirect('/admin/vehicles');
        }
    }
    
    /**
     * UPDATE - Actualizar vehículo
     * Ruta: PUT /admin/vehicles/{id}
     */
    public function update($id) {
        try {
            // Obtener datos
            $data = [
                'plate' => $_POST['plate'] ?? '',
                'brand' => $_POST['brand'] ?? '',
                'model' => $_POST['model'] ?? '',
                'year' => (int)($_POST['year'] ?? date('Y')),
                'battery_level' => (int)($_POST['battery_level'] ?? 100),
                'latitude' => (float)($_POST['latitude'] ?? 40.7117),
                'longitude' => (float)($_POST['longitude'] ?? 0.5783),
                'status' => $_POST['status'] ?? 'available',
                'vehicle_type' => $_POST['vehicle_type'] ?? 'car',
                'is_accessible' => isset($_POST['is_accessible']) ? 1 : 0,
                'price_per_minute' => (float)($_POST['price_per_minute'] ?? 0.35),
                'image_url' => $_POST['image_url'] ?? null
            ];
            
            // Validar
            $errors = $this->vehicleModel->validate($data);
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old_data'] = $data;
                return Router::redirect('/admin/vehicles/' . $id . '/edit');
            }
            
            // Actualizar
            if ($this->vehicleModel->update($id, $data)) {
                $_SESSION['success'] = 'Vehículo actualizado correctamente';
                return Router::redirect('/admin/vehicles/' . $id);
            } else {
                $_SESSION['error'] = 'Error al actualizar el vehículo';
                return Router::redirect('/admin/vehicles/' . $id . '/edit');
            }
            
        } catch (Exception $e) {
            error_log('Error updating vehicle: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar el vehículo: ' . $e->getMessage();
            Router::redirect('/admin/vehicles/' . $id . '/edit');
        }
    }
    
    /**
     * DESTROY - Eliminar vehículo
     * Ruta: DELETE /admin/vehicles/{id}
     */
    public function destroy($id) {
        try {
            if ($this->vehicleModel->delete($id)) {
                $_SESSION['success'] = 'Vehículo eliminado correctamente';
            } else {
                $_SESSION['error'] = 'No se puede eliminar un vehículo en uso';
            }
            
        } catch (Exception $e) {
            error_log('Error deleting vehicle: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al eliminar el vehículo';
        }
        
        Router::redirect('/admin/vehicles');
    }
}
