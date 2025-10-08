<?php
/**
 * Vehicle Model
 * Handles vehicle data operations with MariaDB
 * Implements 2025 security best practices with PDO prepared statements
 */

require_once __DIR__ . '/../config/database.php';

class Vehicle {
    private $db;
    
    public function __construct() {
        $this->db = getMariaDBConnection();
    }
    
    /**
     * Find vehicle by ID
     * @param int $id Vehicle ID
     * @return array|null Vehicle data or null if not found
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, model, brand, license_plate, status, location_lat, location_lng, 
                       price_per_hour, created_at, updated_at
                FROM vehicles
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $vehicle = $stmt->fetch();
            
            return $vehicle ?: null;
            
        } catch (PDOException $e) {
            error_log("Error finding vehicle by ID: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Find vehicle by license plate
     * @param string $licensePlate License plate
     * @return array|null Vehicle data or null if not found
     */
    public function findByLicensePlate($licensePlate) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, model, brand, license_plate, status, location_lat, location_lng, 
                       price_per_hour, created_at, updated_at
                FROM vehicles
                WHERE license_plate = :license_plate
            ");
            
            $stmt->execute(['license_plate' => $licensePlate]);
            $vehicle = $stmt->fetch();
            
            return $vehicle ?: null;
            
        } catch (PDOException $e) {
            error_log("Error finding vehicle by license plate: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Create new vehicle
     * @param array $data Vehicle data
     * @return int|false Vehicle ID or false on failure
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO vehicles (model, brand, license_plate, status, location_lat, location_lng, 
                                     price_per_hour, created_at, updated_at)
                VALUES (:model, :brand, :license_plate, :status, :location_lat, :location_lng, 
                        :price_per_hour, NOW(), NOW())
            ");
            
            $result = $stmt->execute([
                'model' => $data['model'],
                'brand' => $data['brand'],
                'license_plate' => $data['license_plate'],
                'status' => $data['status'] ?? 'available',
                'location_lat' => $data['location_lat'] ?? null,
                'location_lng' => $data['location_lng'] ?? null,
                'price_per_hour' => $data['price_per_hour']
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error creating vehicle: " . $e->getMessage());
            
            // Check for duplicate license plate
            if ($e->getCode() == 23000) {
                throw new Exception("License plate already exists", 409);
            }
            
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Update vehicle
     * @param int $id Vehicle ID
     * @param array $data Vehicle data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = ['id' => $id];
            
            // Build dynamic update query
            if (isset($data['model'])) {
                $fields[] = "model = :model";
                $params['model'] = $data['model'];
            }
            
            if (isset($data['brand'])) {
                $fields[] = "brand = :brand";
                $params['brand'] = $data['brand'];
            }
            
            if (isset($data['license_plate'])) {
                $fields[] = "license_plate = :license_plate";
                $params['license_plate'] = $data['license_plate'];
            }
            
            if (isset($data['status'])) {
                $fields[] = "status = :status";
                $params['status'] = $data['status'];
            }
            
            if (isset($data['location_lat'])) {
                $fields[] = "location_lat = :location_lat";
                $params['location_lat'] = $data['location_lat'];
            }
            
            if (isset($data['location_lng'])) {
                $fields[] = "location_lng = :location_lng";
                $params['location_lng'] = $data['location_lng'];
            }
            
            if (isset($data['price_per_hour'])) {
                $fields[] = "price_per_hour = :price_per_hour";
                $params['price_per_hour'] = $data['price_per_hour'];
            }
            
            if (empty($fields)) {
                return true; // Nothing to update
            }
            
            $fields[] = "updated_at = NOW()";
            
            $sql = "UPDATE vehicles SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error updating vehicle: " . $e->getMessage());
            
            // Check for duplicate license plate
            if ($e->getCode() == 23000) {
                throw new Exception("License plate already exists", 409);
            }
            
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Delete vehicle
     * @param int $id Vehicle ID
     * @return bool Success status
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = :id");
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log("Error deleting vehicle: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get all vehicles with pagination
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string $status Filter by status (optional)
     * @return array Vehicles array
     */
    public function getAll($page = 1, $perPage = 20, $status = null) {
        try {
            $offset = ($page - 1) * $perPage;
            
            if ($status) {
                $stmt = $this->db->prepare("
                    SELECT id, model, brand, license_plate, status, location_lat, location_lng, 
                           price_per_hour, created_at, updated_at
                    FROM vehicles
                    WHERE status = :status
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset
                ");
                
                $stmt->bindValue(':status', $status, PDO::PARAM_STR);
                $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare("
                    SELECT id, model, brand, license_plate, status, location_lat, location_lng, 
                           price_per_hour, created_at, updated_at
                    FROM vehicles
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset
                ");
                
                $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error getting all vehicles: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get available vehicles
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Vehicles array
     */
    public function findAvailable($page = 1, $perPage = 20) {
        return $this->getAll($page, $perPage, 'available');
    }
    
    /**
     * Get total vehicle count
     * @param string $status Filter by status (optional)
     * @return int Total count
     */
    public function count($status = null) {
        try {
            if ($status) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM vehicles WHERE status = :status");
                $stmt->execute(['status' => $status]);
            } else {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM vehicles");
            }
            
            $result = $stmt->fetch();
            return (int)$result['count'];
            
        } catch (PDOException $e) {
            error_log("Error counting vehicles: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Update vehicle location
     * @param int $id Vehicle ID
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return bool Success status
     */
    public function updateLocation($id, $lat, $lng) {
        try {
            $stmt = $this->db->prepare("
                UPDATE vehicles 
                SET location_lat = :lat, location_lng = :lng, updated_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'lat' => $lat,
                'lng' => $lng
            ]);
            
        } catch (PDOException $e) {
            error_log("Error updating vehicle location: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Update vehicle status
     * @param int $id Vehicle ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($id, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE vehicles 
                SET status = :status, updated_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'status' => $status
            ]);
            
        } catch (PDOException $e) {
            error_log("Error updating vehicle status: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Search vehicles by query
     * @param string $query Search query
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Vehicles array
     */
    public function search($query, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            $searchTerm = "%{$query}%";
            
            $stmt = $this->db->prepare("
                SELECT id, model, brand, license_plate, status, location_lat, location_lng, 
                       price_per_hour, created_at, updated_at
                FROM vehicles
                WHERE model LIKE :query OR brand LIKE :query OR license_plate LIKE :query
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error searching vehicles: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Find vehicles near location
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param float $radius Radius in kilometers
     * @return array Vehicles array
     */
    public function findNearby($lat, $lng, $radius = 5) {
        try {
            // Using Haversine formula for distance calculation
            $stmt = $this->db->prepare("
                SELECT id, model, brand, license_plate, status, location_lat, location_lng, 
                       price_per_hour, created_at, updated_at,
                       (6371 * acos(cos(radians(:lat)) * cos(radians(location_lat)) * 
                       cos(radians(location_lng) - radians(:lng)) + 
                       sin(radians(:lat)) * sin(radians(location_lat)))) AS distance
                FROM vehicles
                WHERE location_lat IS NOT NULL AND location_lng IS NOT NULL
                HAVING distance < :radius
                ORDER BY distance
            ");
            
            $stmt->execute([
                'lat' => $lat,
                'lng' => $lng,
                'radius' => $radius
            ]);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error finding nearby vehicles: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
}
