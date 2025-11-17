<?php

class Vehicle {
    private $db;
    
    public function __construct($dbConnection = null) {
        $this->db = $dbConnection ?? Database::getMariaDBConnection();
    }
    
    /**
     * Obtenir tots els vehicles disponibles
     * 
     * @return array Llista de vehicles
     */
    public function getAvailableVehicles() {
        $stmt = $this->db->prepare("
            SELECT 
                v.id,
                v.plate as license_plate,
                v.brand,
                v.model,
                v.year,
                v.battery_level,
                v.latitude,
                v.longitude,
                v.status,
                v.vehicle_type,
                v.is_accessible,
                v.accessibility_features,
                v.price_per_minute,
                v.image_url
            FROM vehicles v
            WHERE v.status != 'maintenance'
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtenir vehicle per ID
     * 
     * @param int $vehicleId ID del vehicle
     * @return array|null Dades del vehicle
     */
    public function getVehicleById($vehicleId) {
        $stmt = $this->db->prepare("
            SELECT 
                v.id,
                v.plate as license_plate,
                v.brand,
                v.model,
                v.year,
                v.battery_level as battery,
                v.latitude,
                v.longitude,
                v.status,
                v.vehicle_type,
                v.is_accessible,
                v.price_per_minute
            FROM vehicles v
            WHERE v.id = ?
        ");
        $stmt->bind_param('i', $vehicleId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $vehicle = $result->fetch_assoc(); //Todo:
        
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
        
        // Netejar camps duplicats
        unset($vehicle['latitude']);
        unset($vehicle['longitude']);
        
        return $vehicle;
    }
    
    /**
     * Comprovar si un vehicle està disponible
     * 
     * @param int $vehicleId ID del vehicle
     * @return bool True si està disponible
     */
    public function isAvailable($vehicleId) {
        $stmt = $this->db->prepare("
            SELECT status FROM vehicles WHERE id = ? AND status = 'available'
        ");
        $stmt->bind_param('i', $vehicleId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Actualitzar estat del vehicle
     * 
     * @param int $vehicleId ID del vehicle
     * @param string $status Nou estat
     * @return bool Èxit de l'operació
     */
    public function updateStatus($vehicleId, $status) {
        $stmt = $this->db->prepare("
            UPDATE vehicles 
            SET status = ?
            WHERE id = ?
        ");
        $stmt->bind_param('si', $status, $vehicleId);
        return $stmt->execute();
    }
    
    /**
     * Obtenir vehicle amb detalls complets per reclamar
     * 
     * @param int $vehicleId ID del vehicle
     * @return array|null Dades del vehicle
     */
    public function getVehicleForClaim($vehicleId) {
        $stmt = $this->db->prepare("
            SELECT 
                v.id,
                v.plate as license_plate,
                v.brand,
                v.model,
                v.year,
                v.battery_level as battery,
                v.latitude,
                v.longitude,
                v.status,
                v.vehicle_type,
                v.is_accessible,
                v.price_per_minute
            FROM vehicles v
            WHERE v.id = ? AND v.status = 'available'
        ");
        $stmt->bind_param('i', $vehicleId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Obtenir tots els vehicles
     * 
     * @return array Llista de tots els vehicles
     */
    public function getAllVehicles($limit = null, $offset = null, $search = '', $filters = []) {
        $query = "
            SELECT 
                v.id,
                v.plate as license_plate,
                v.brand,
                v.model,
                v.year,
                v.battery_level,
                v.latitude,
                v.longitude,
                v.status,
                v.is_accessible,
                v.price_per_minute,
                v.image_url
            FROM vehicles v
            WHERE 1=1
        ";
        
        $params = [];
        $types = '';
        
        // Búsqueda global por matrícula, marca o modelo
        // Si se usan filtros avanzados de marca/modelo, ignorar completamente la búsqueda global
        $hasAdvancedFilters = !empty($filters['brand']) || !empty($filters['model']);
        
        if (!empty($search) && !$hasAdvancedFilters) {
            // Dividir la búsqueda en palabras
            $searchWords = array_filter(explode(' ', trim($search)));
            
            if (!empty($searchWords)) {
                $searchConditions = [];
                
                foreach ($searchWords as $word) {
                    // Cada palabra debe aparecer en al menos uno de los campos (matrícula, marca o modelo)
                    $searchConditions[] = "(v.plate LIKE ? OR v.brand LIKE ? OR v.model LIKE ?)";
                    $wordParam = "%$word%";
                    $params[] = $wordParam;
                    $params[] = $wordParam;
                    $params[] = $wordParam;
                    $types .= 'sss';
                }
                
                // Unir todas las condiciones con AND (todas las palabras deben coincidir)
                if (!empty($searchConditions)) {
                    $query .= " AND (" . implode(" AND ", $searchConditions) . ")";
                }
            }
        }
        
        // Filtros avanzados
        if (!empty($filters['brand'])) {
            $query .= " AND v.brand LIKE ?";
            $params[] = '%' . $filters['brand'] . '%';
            $types .= 's';
        }
        
        if (!empty($filters['model'])) {
            $query .= " AND v.model LIKE ?";
            $params[] = '%' . $filters['model'] . '%';
            $types .= 's';
        }
        
        // Filtros opcionales
        if (!empty($filters['status'])) {
            $query .= " AND v.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        if (isset($filters['is_accessible']) && $filters['is_accessible'] !== '') {
            $query .= " AND v.is_accessible = ?";
            $params[] = (int)$filters['is_accessible'];
            $types .= 'i';
        }
        
        if (!empty($filters['min_battery'])) {
            $query .= " AND v.battery_level >= ?";
            $params[] = (int)$filters['min_battery'];
            $types .= 'i';
        }
        
        $query .= " ORDER BY v.id DESC";
        
        // Paginación
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        
        $stmt = $this->db->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Contar vehículos totales (con filtros opcionales)
     */
    public function countVehicles($search = '', $filters = []) {
        $query = "SELECT COUNT(*) as total FROM vehicles v WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Búsqueda global
        // Si se usan filtros avanzados de marca/modelo, ignorar completamente la búsqueda global
        $hasAdvancedFilters = !empty($filters['brand']) || !empty($filters['model']);
        
        if (!empty($search) && !$hasAdvancedFilters) {
            // Dividir la búsqueda en palabras
            $searchWords = array_filter(explode(' ', trim($search)));
            
            if (!empty($searchWords)) {
                $searchConditions = [];
                
                foreach ($searchWords as $word) {
                    // Cada palabra debe aparecer en al menos uno de los campos (matrícula, marca o modelo)
                    $searchConditions[] = "(v.plate LIKE ? OR v.brand LIKE ? OR v.model LIKE ?)";
                    $wordParam = "%$word%";
                    $params[] = $wordParam;
                    $params[] = $wordParam;
                    $params[] = $wordParam;
                    $types .= 'sss';
                }
                
                // Unir todas las condiciones con AND (todas las palabras deben coincidir)
                if (!empty($searchConditions)) {
                    $query .= " AND (" . implode(" AND ", $searchConditions) . ")";
                }
            }
        }
        
        // Filtros avanzados
        if (!empty($filters['brand'])) {
            $query .= " AND v.brand LIKE ?";
            $params[] = '%' . $filters['brand'] . '%';
            $types .= 's';
        }
        
        if (!empty($filters['model'])) {
            $query .= " AND v.model LIKE ?";
            $params[] = '%' . $filters['model'] . '%';
            $types .= 's';
        }
        
        // Filtros opcionales
        if (!empty($filters['status'])) {
            $query .= " AND v.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        if (isset($filters['is_accessible']) && $filters['is_accessible'] !== '') {
            $query .= " AND v.is_accessible = ?";
            $params[] = (int)$filters['is_accessible'];
            $types .= 'i';
        }
        
        if (!empty($filters['min_battery'])) {
            $query .= " AND v.battery_level >= ?";
            $params[] = (int)$filters['min_battery'];
            $types .= 'i';
        }
        
        $stmt = $this->db->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
    
    // ============================================
    // 🔧 MÉTODOS CRUD PARA ADMINISTRACIÓN
    // ============================================
    
    /**
     * Crear un nuevo vehículo
     * 
     * @param array $data Datos del vehículo
     * @return int|false ID del vehículo creado o false si falla
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO vehicles (
                plate, 
                brand, 
                model, 
                year, 
                battery_level, 
                latitude, 
                longitude, 
                status, 
                vehicle_type, 
                is_accessible, 
                price_per_minute, 
                image_url
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Valores por defecto si no se proporcionan
        $battery = $data['battery_level'] ?? 100;
        $latitude = $data['latitude'] ?? 40.7117;
        $longitude = $data['longitude'] ?? 0.5783;
        $status = $data['status'] ?? 'available';
        $isAccessible = $data['is_accessible'] ?? 0;
        $pricePerMinute = $data['price_per_minute'] ?? 0.35;
        $imageUrl = $data['image_url'] ?? null;
        
        $stmt->bind_param(
            'sssiddsssdds',
            $data['plate'],
            $data['brand'],
            $data['model'],
            $data['year'],
            $battery,
            $latitude,
            $longitude,
            $status,
            $data['vehicle_type'],
            $isAccessible,
            $pricePerMinute,
            $imageUrl
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    /**
     * Actualizar un vehículo existente
     * 
     * @param int $id ID del vehículo
     * @param array $data Datos a actualizar
     * @return bool Éxito de la operación
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE vehicles 
            SET 
                plate = ?,
                brand = ?,
                model = ?,
                year = ?,
                battery_level = ?,
                latitude = ?,
                longitude = ?,
                status = ?,
                vehicle_type = ?,
                is_accessible = ?,
                price_per_minute = ?,
                image_url = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            'sssidddssidsi',
            $data['plate'],
            $data['brand'],
            $data['model'],
            $data['year'],
            $data['battery_level'],
            $data['latitude'],
            $data['longitude'],
            $data['status'],
            $data['vehicle_type'],
            $data['is_accessible'],
            $data['price_per_minute'],
            $data['image_url'],
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar un vehículo
     * 
     * @param int $id ID del vehículo
     * @return bool Éxito de la operación
     */
    public function delete($id) {
        // Primero verificar que no esté en uso
        $stmt = $this->db->prepare("
            SELECT status FROM vehicles WHERE id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehicle = $result->fetch_assoc();
        
        if ($vehicle && $vehicle['status'] === 'in_use') {
            // No permitir eliminar vehículos en uso
            return false;
        }
        
        // Eliminar el vehículo
        $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    
    /**
     * Buscar vehículos con filtros
     * 
     * @param array $filters Filtros de búsqueda
     * @return array Vehículos encontrados
     */
    public function search($filters = []) {
        $query = "SELECT * FROM vehicles WHERE 1=1";
        $params = [];
        $types = '';
        
        if (!empty($filters['brand'])) {
            $query .= " AND brand LIKE ?";
            $params[] = '%' . $filters['brand'] . '%';
            $types .= 's';
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        if (!empty($filters['vehicle_type'])) {
            $query .= " AND vehicle_type = ?";
            $params[] = $filters['vehicle_type'];
            $types .= 's';
        }
        
        $stmt = $this->db->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Validar datos de vehículo
     * 
     * @param array $data Datos a validar
     * @return array Errores encontrados (vacío si es válido)
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['plate'])) {
            $errors[] = 'La matrícula es obligatoria';
        }
        
        if (empty($data['brand'])) {
            $errors[] = 'La marca es obligatoria';
        }
        
        if (empty($data['model'])) {
            $errors[] = 'El modelo es obligatorio';
        }
        
        if (empty($data['year']) || $data['year'] < 1900 || $data['year'] > date('Y') + 1) {
            $errors[] = 'El año no es válido';
        }
        
        if (!in_array($data['vehicle_type'], ['car', 'bike', 'scooter', 'motorcycle'])) {
            $errors[] = 'Tipo de vehículo no válido';
        }
        
        if (!in_array($data['status'], ['available', 'in_use', 'charging', 'maintenance', 'reserved'])) {
            $errors[] = 'Estado no válido';
        }
        
        return $errors;
    }
}
