<?php

/**
 * Booking Model
 * Handles booking/reservation operations
 */

class Booking {
    private $db;
    
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }
    
    /**
     * Create a new booking
     */
    public function createBooking($userId, $vehicleId, $unlockFee = 0.50) {
        $startTime = date('Y-m-d H:i:s');
        $endTime = date('Y-m-d H:i:s', strtotime('+2 hours')); // Estimado 2 horas
        
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
     * Get active booking for user
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
     * Get active booking by vehicle
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
     * Complete a booking
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
     * Cancel a booking
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
     * Get booking history for user
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
}
