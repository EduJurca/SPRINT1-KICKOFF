<?php
/**
 * Booking Model
 * Handles booking data operations with MariaDB
 * Implements 2025 security best practices with PDO prepared statements
 */

require_once __DIR__ . '/../config/database.php';

class Booking {
    private $db;
    
    public function __construct() {
        $this->db = getMariaDBConnection();
    }
    
    /**
     * Find booking by ID
     * @param int $id Booking ID
     * @return array|null Booking data or null if not found
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT b.id, b.user_id, b.vehicle_id, b.start_time, b.end_time, 
                       b.status, b.total_cost, b.created_at,
                       u.email as user_email, u.full_name as user_name,
                       v.model as vehicle_model, v.brand as vehicle_brand, 
                       v.license_plate as vehicle_license_plate
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                LEFT JOIN vehicles v ON b.vehicle_id = v.id
                WHERE b.id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $booking = $stmt->fetch();
            
            return $booking ?: null;
            
        } catch (PDOException $e) {
            error_log("Error finding booking by ID: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Create new booking
     * @param array $data Booking data
     * @return int|false Booking ID or false on failure
     */
    public function create($data) {
        try {
            // Calculate total cost if end_time is provided
            $totalCost = null;
            if (isset($data['end_time']) && isset($data['price_per_hour'])) {
                $totalCost = $this->calculateCost(
                    $data['start_time'], 
                    $data['end_time'], 
                    $data['price_per_hour']
                );
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO bookings (user_id, vehicle_id, start_time, end_time, status, total_cost, created_at)
                VALUES (:user_id, :vehicle_id, :start_time, :end_time, :status, :total_cost, NOW())
            ");
            
            $result = $stmt->execute([
                'user_id' => $data['user_id'],
                'vehicle_id' => $data['vehicle_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'] ?? null,
                'status' => $data['status'] ?? 'active',
                'total_cost' => $totalCost
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error creating booking: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Update booking
     * @param int $id Booking ID
     * @param array $data Booking data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = ['id' => $id];
            
            // Build dynamic update query
            if (isset($data['start_time'])) {
                $fields[] = "start_time = :start_time";
                $params['start_time'] = $data['start_time'];
            }
            
            if (isset($data['end_time'])) {
                $fields[] = "end_time = :end_time";
                $params['end_time'] = $data['end_time'];
            }
            
            if (isset($data['status'])) {
                $fields[] = "status = :status";
                $params['status'] = $data['status'];
            }
            
            if (isset($data['total_cost'])) {
                $fields[] = "total_cost = :total_cost";
                $params['total_cost'] = $data['total_cost'];
            }
            
            if (empty($fields)) {
                return true; // Nothing to update
            }
            
            $sql = "UPDATE bookings SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error updating booking: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Delete booking
     * @param int $id Booking ID
     * @return bool Success status
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM bookings WHERE id = :id");
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log("Error deleting booking: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get all bookings with pagination
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Bookings array
     */
    public function getAll($page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $stmt = $this->db->prepare("
                SELECT b.id, b.user_id, b.vehicle_id, b.start_time, b.end_time, 
                       b.status, b.total_cost, b.created_at,
                       u.email as user_email, u.full_name as user_name,
                       v.model as vehicle_model, v.brand as vehicle_brand, 
                       v.license_plate as vehicle_license_plate
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                LEFT JOIN vehicles v ON b.vehicle_id = v.id
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error getting all bookings: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get bookings by user ID
     * @param int $userId User ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Bookings array
     */
    public function findByUser($userId, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $stmt = $this->db->prepare("
                SELECT b.id, b.user_id, b.vehicle_id, b.start_time, b.end_time, 
                       b.status, b.total_cost, b.created_at,
                       v.model as vehicle_model, v.brand as vehicle_brand, 
                       v.license_plate as vehicle_license_plate,
                       v.location_lat, v.location_lng
                FROM bookings b
                LEFT JOIN vehicles v ON b.vehicle_id = v.id
                WHERE b.user_id = :user_id
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error getting bookings by user: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get bookings by vehicle ID
     * @param int $vehicleId Vehicle ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Bookings array
     */
    public function findByVehicle($vehicleId, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $stmt = $this->db->prepare("
                SELECT b.id, b.user_id, b.vehicle_id, b.start_time, b.end_time, 
                       b.status, b.total_cost, b.created_at,
                       u.email as user_email, u.full_name as user_name
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                WHERE b.vehicle_id = :vehicle_id
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error getting bookings by vehicle: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get active bookings
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Bookings array
     */
    public function getActive($page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $stmt = $this->db->prepare("
                SELECT b.id, b.user_id, b.vehicle_id, b.start_time, b.end_time, 
                       b.status, b.total_cost, b.created_at,
                       u.email as user_email, u.full_name as user_name,
                       v.model as vehicle_model, v.brand as vehicle_brand, 
                       v.license_plate as vehicle_license_plate
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                LEFT JOIN vehicles v ON b.vehicle_id = v.id
                WHERE b.status = 'active'
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error getting active bookings: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get total booking count
     * @param int $userId Filter by user ID (optional)
     * @return int Total count
     */
    public function count($userId = null) {
        try {
            if ($userId) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $userId]);
            } else {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM bookings");
            }
            
            $result = $stmt->fetch();
            return (int)$result['count'];
            
        } catch (PDOException $e) {
            error_log("Error counting bookings: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Calculate booking cost
     * @param string $startTime Start time
     * @param string $endTime End time
     * @param float $pricePerHour Price per hour
     * @return float Total cost
     */
    public function calculateCost($startTime, $endTime, $pricePerHour) {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        $interval = $start->diff($end);
        
        // Calculate total hours (including fractional hours)
        $hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);
        
        return round($hours * $pricePerHour, 2);
    }
    
    /**
     * Complete booking and calculate final cost
     * @param int $id Booking ID
     * @param string $endTime End time
     * @param float $pricePerHour Price per hour
     * @return bool Success status
     */
    public function complete($id, $endTime, $pricePerHour) {
        try {
            // Get booking to get start time
            $booking = $this->findById($id);
            
            if (!$booking) {
                return false;
            }
            
            // Calculate total cost
            $totalCost = $this->calculateCost($booking['start_time'], $endTime, $pricePerHour);
            
            // Update booking
            $stmt = $this->db->prepare("
                UPDATE bookings 
                SET end_time = :end_time, status = 'completed', total_cost = :total_cost
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'end_time' => $endTime,
                'total_cost' => $totalCost
            ]);
            
        } catch (PDOException $e) {
            error_log("Error completing booking: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Cancel booking
     * @param int $id Booking ID
     * @return bool Success status
     */
    public function cancel($id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE bookings 
                SET status = 'cancelled'
                WHERE id = :id
            ");
            
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log("Error cancelling booking: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Check if vehicle is available for booking
     * @param int $vehicleId Vehicle ID
     * @param string $startTime Start time
     * @param string $endTime End time
     * @return bool True if available
     */
    public function isVehicleAvailable($vehicleId, $startTime, $endTime) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM bookings
                WHERE vehicle_id = :vehicle_id
                AND status = 'active'
                AND (
                    (start_time <= :start_time AND end_time >= :start_time)
                    OR (start_time <= :end_time AND end_time >= :end_time)
                    OR (start_time >= :start_time AND end_time <= :end_time)
                )
            ");
            
            $stmt->execute([
                'vehicle_id' => $vehicleId,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
            
            $result = $stmt->fetch();
            return $result['count'] == 0;
            
        } catch (PDOException $e) {
            error_log("Error checking vehicle availability: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
}
