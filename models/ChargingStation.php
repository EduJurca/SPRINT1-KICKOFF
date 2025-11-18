<?php
/**
 * ChargingStation Model
 * Manages charging stations database operations
 */

class ChargingStation {
    private $db;
    
    public function __construct($dbConnection = null) {
        $this->db = $dbConnection ?? Database::getMariaDBConnection();
    }
    
    /**
     * Get all charging stations
     * 
     * @return array List of all stations
     */
    public function getAllStations($limit = null, $offset = null, $search = '', $filters = []) {
        $query = "
            SELECT 
                id,
                name,
                address,
                city,
                postal_code,
                latitude,
                longitude,
                total_slots,
                available_slots,
                power_kw,
                status,
                operator,
                description,
                created_at,
                updated_at
            FROM charging_stations
            WHERE 1=1
        ";
        
        $params = [];
        $types = '';
        
        // Búsqueda global por nombre, ciudad o calle
        if (!empty($search)) {
            $searchWords = array_filter(explode(' ', trim($search)));
            
            if (!empty($searchWords)) {
                $searchConditions = [];
                
                foreach ($searchWords as $word) {
                    $searchConditions[] = "(name LIKE ? OR city LIKE ? OR address LIKE ?)";
                    $wordParam = "%$word%";
                    $params[] = $wordParam;
                    $params[] = $wordParam;
                    $params[] = $wordParam;
                    $types .= 'sss';
                }
                
                if (!empty($searchConditions)) {
                    $query .= " AND (" . implode(" AND ", $searchConditions) . ")";
                }
            }
        }
        
        // Filtros avanzados
        if (!empty($filters['city'])) {
            $query .= " AND city LIKE ?";
            $params[] = '%' . $filters['city'] . '%';
            $types .= 's';
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        // Filtro de disponibilidad
        if (isset($filters['availability']) && $filters['availability'] !== '') {
            if ($filters['availability'] === 'available') {
                $query .= " AND available_slots > 0";
            } elseif ($filters['availability'] === 'full') {
                $query .= " AND available_slots = 0";
            }
        }
        
        if (isset($filters['min_power']) && $filters['min_power'] !== '') {
            $query .= " AND power_kw >= ?";
            $params[] = (int)$filters['min_power'];
            $types .= 'i';
        }
        
        $query .= " ORDER BY city, name";
        
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
     * Count charging stations with filters
     * 
     * @param string $search Search term
     * @param array $filters Filter parameters
     * @return int Total count
     */
    public function countStations($search = '', $filters = []) {
        $query = "SELECT COUNT(*) as total FROM charging_stations WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Búsqueda global
        if (!empty($search)) {
            $searchWords = array_filter(explode(' ', trim($search)));
            
            if (!empty($searchWords)) {
                $searchConditions = [];
                
                foreach ($searchWords as $word) {
                    $searchConditions[] = "(name LIKE ? OR city LIKE ? OR address LIKE ?)";
                    $wordParam = "%$word%";
                    $params[] = $wordParam;
                    $params[] = $wordParam;
                    $params[] = $wordParam;
                    $types .= 'sss';
                }
                
                if (!empty($searchConditions)) {
                    $query .= " AND (" . implode(" AND ", $searchConditions) . ")";
                }
            }
        }
        
        // Filtros avanzados
        if (!empty($filters['city'])) {
            $query .= " AND city LIKE ?";
            $params[] = '%' . $filters['city'] . '%';
            $types .= 's';
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        // Filtro de disponibilidad
        if (isset($filters['availability']) && $filters['availability'] !== '') {
            if ($filters['availability'] === 'available') {
                $query .= " AND available_slots > 0";
            } elseif ($filters['availability'] === 'full') {
                $query .= " AND available_slots = 0";
            }
        }
        
        if (isset($filters['min_power']) && $filters['min_power'] !== '') {
            $query .= " AND power_kw >= ?";
            $params[] = (int)$filters['min_power'];
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
    
    /**
     * Get station by ID
     * 
     * @param int $id Station ID
     * @return array|null Station data
     */
    public function getStationById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                name,
                address,
                city,
                postal_code,
                latitude,
                longitude,
                total_slots,
                available_slots,
                power_kw,
                status,
                operator,
                description,
                created_at,
                updated_at
            FROM charging_stations
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Create new charging station
     * 
     * @param array $data Station data
     * @return int|bool Insert ID or false on failure
     */
    public function createStation($data) {
        $stmt = $this->db->prepare("
            INSERT INTO charging_stations (
                name,
                address,
                city,
                postal_code,
                latitude,
                longitude,
                total_slots,
                available_slots,
                power_kw,
                status,
                operator,
                description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "ssssddiiisss",
            $data['name'],
            $data['address'],
            $data['city'],
            $data['postal_code'],
            $data['latitude'],
            $data['longitude'],
            $data['total_slots'],
            $data['available_slots'],
            $data['power_kw'],
            $data['status'],
            $data['operator'],
            $data['description']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    /**
     * Update charging station
     * 
     * @param int $id Station ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function updateStation($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE charging_stations SET
                name = ?,
                address = ?,
                city = ?,
                postal_code = ?,
                latitude = ?,
                longitude = ?,
                total_slots = ?,
                available_slots = ?,
                power_kw = ?,
                status = ?,
                operator = ?,
                description = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "ssssddiiisssi",
            $data['name'],
            $data['address'],
            $data['city'],
            $data['postal_code'],
            $data['latitude'],
            $data['longitude'],
            $data['total_slots'],
            $data['available_slots'],
            $data['power_kw'],
            $data['status'],
            $data['operator'],
            $data['description'],
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Delete charging station
     * 
     * @param int $id Station ID
     * @return bool Success status
     */
    public function deleteStation($id) {
        $stmt = $this->db->prepare("DELETE FROM charging_stations WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Get stations by city
     * 
     * @param string $city City name
     * @return array List of stations in the city
     */
    public function getStationsByCity($city) {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                name,
                address,
                city,
                postal_code,
                latitude,
                longitude,
                total_slots,
                available_slots,
                power_kw,
                status,
                operator
            FROM charging_stations
            WHERE city = ?
            ORDER BY name
        ");
        $stmt->bind_param("s", $city);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get available stations (active and with available slots)
     * 
     * @return array List of available stations
     */
    public function getAvailableStations() {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                name,
                address,
                city,
                postal_code,
                latitude,
                longitude,
                total_slots,
                available_slots,
                power_kw,
                status,
                operator
            FROM charging_stations
            WHERE status = 'active' AND available_slots > 0
            ORDER BY city, name
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Update station availability
     * 
     * @param int $id Station ID
     * @param int $availableSlots New available slots count
     * @return bool Success status
     */
    public function updateAvailability($id, $availableSlots) {
        $stmt = $this->db->prepare("
            UPDATE charging_stations 
            SET available_slots = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $availableSlots, $id);
        return $stmt->execute();
    }
    
    /**
     * Get total count of stations
     * 
     * @return int Total number of stations
     */
    public function getTotalCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM charging_stations");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
