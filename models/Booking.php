<?php
/**
 * 📊 Booking Model
 * Gestiona les operacions de reserves/bookings
 */

class Booking {
    private $db;
    
    public function __construct($dbConnection = null) {
        $this->db = $dbConnection ?? Database::getMariaDBConnection();
    }
    
    /**
     * Crear una nova reserva
     * 
     * @param int $userId ID de l'usuari
     * @param int $vehicleId ID del vehicle
     * @param float $unlockFee Tarifa de desbloqueig
     * @return int|false ID de la reserva o false si falla
     */
    public function createBooking($userId, $vehicleId, $unlockFee = 0.50) {
        $startTime = date('Y-m-d H:i:s');
        $endTime = date('Y-m-d H:i:s', strtotime('+2 hours')); // Estimat 2 hores
        
        $stmt = $this->db->prepare("
            INSERT INTO bookings (user_id, vehicle_id, start_datetime, end_datetime, total_cost, status)
            VALUES (?, ?, ?, ?, ?, 'active')
        ");
        
        if (!$stmt) {
            error_log("Booking Model Error - Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param('iissd', $userId, $vehicleId, $startTime, $endTime, $unlockFee);
        
        if ($stmt->execute()) {
            $bookingId = $this->db->insert_id;
            error_log("Booking created successfully: ID=$bookingId, User=$userId, Vehicle=$vehicleId");
            return $bookingId;
        }
        
        error_log("Booking Model Error - Execute failed: " . $stmt->error);
        return false;
    }
    
    /**
     * Obtenir reserva activa per usuari
     * 
     * @param int $userId ID de l'usuari
     * @return array|null Dades de la reserva
     */
    public function getActiveBooking($userId) {
        $stmt = $this->db->prepare("
            SELECT b.*, v.plate as license_plate, v.model, v.brand
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.user_id = ? AND b.status = 'active'
            ORDER BY b.start_datetime DESC
            LIMIT 1
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Obtenir reserva activa per vehicle
     * 
     * @param int $vehicleId ID del vehicle
     * @return array|null Dades de la reserva
     */
    public function getActiveBookingByVehicle($vehicleId) {
        $stmt = $this->db->prepare("
            SELECT * FROM bookings 
            WHERE vehicle_id = ? AND status = 'active'
            LIMIT 1
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
     * Completar una reserva
     * 
     * @param int $vehicleId ID del vehicle
     * @param int $userId ID de l'usuari
     * @return bool Èxit de l'operació
     */
    public function completeBooking($vehicleId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET end_datetime = NOW(), 
                status = 'completed'
            WHERE vehicle_id = ? AND user_id = ? AND status = 'active'
        ");
        $stmt->bind_param('ii', $vehicleId, $userId);
        return $stmt->execute();
    }
    
    /**
     * Cancel·lar una reserva
     * 
     * @param int $bookingId ID de la reserva
     * @return bool Èxit de l'operació
     */
    public function cancelBooking($bookingId) {
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET status = 'cancelled'
            WHERE id = ?
        ");
        $stmt->bind_param('i', $bookingId);
        return $stmt->execute();
    }
    
    /**
     * Obtenir historial de reserves per usuari
     * 
     * @param int $userId ID de l'usuari
     * @param int $limit Límit de resultats
     * @return array Llista de reserves
     */
    public function getBookingHistory($userId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT b.*, v.plate as license_plate, v.model, v.brand
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.user_id = ?
            ORDER BY b.start_datetime DESC
            LIMIT ?
        ");
        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtenir reserva per ID
     * 
     * @param int $bookingId ID de la reserva
     * @return array|null Dades de la reserva
     */
    public function getBookingById($bookingId) {
        $stmt = $this->db->prepare("
            SELECT b.*, v.plate as license_plate, v.model, v.brand
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.id = ?
        ");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Obtenir totes les reserves
     * 
     * @return array Llista de totes les reserves
     */
    public function getAllBookings() {
        $stmt = $this->db->prepare("
            SELECT b.*, 
                   u.username, 
                   v.plate as license_plate, 
                   v.model, 
                   v.brand
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN vehicles v ON b.vehicle_id = v.id
            ORDER BY b.start_datetime DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
