<?php

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
     * @param int $duration Durada estimada en minuts (default 30)
     * @return int|false ID de la reserva o false si falla
     */
    public function createBooking($userId, $vehicleId, $unlockFee = 0.50, $duration = 30) {
        $startTime = date('Y-m-d H:i:s');
        $endTime = date('Y-m-d H:i:s', strtotime("+{$duration} minutes"));
        
        // Obtenir preu per minut del vehicle
        $vehicleStmt = $this->db->prepare("
            SELECT price_per_minute FROM vehicles WHERE id = ?
        ");
        $vehicleStmt->bind_param('i', $vehicleId);
        $vehicleStmt->execute();
        $vehicleResult = $vehicleStmt->get_result();
        
        if ($vehicleResult->num_rows === 0) {
            error_log("Booking Model Error - Vehicle not found: $vehicleId");
            return false;
        }
        
        $vehicle = $vehicleResult->fetch_assoc();
        $pricePerMinute = (float)$vehicle['price_per_minute'];
        
        // Calcular cost total: (durada × preu_per_minut) + tarifa_desbloqueig
        $timeCost = $duration * $pricePerMinute;
        $totalCost = $timeCost + $unlockFee;
        
        error_log("Booking calculation: duration={$duration}min, pricePerMinute={$pricePerMinute}€, timeCost={$timeCost}€, unlockFee={$unlockFee}€, totalCost={$totalCost}€");
        
        $stmt = $this->db->prepare("
            INSERT INTO bookings (user_id, vehicle_id, start_datetime, end_datetime, total_minutes, total_cost, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        
        if (!$stmt) {
            error_log("Booking Model Error - Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param('iissid', $userId, $vehicleId, $startTime, $endTime, $duration, $totalCost);
        
        if ($stmt->execute()) {
            $bookingId = $this->db->insert_id;
            error_log("Booking created successfully: ID=$bookingId, User=$userId, Vehicle=$vehicleId, Duration={$duration}min, TotalCost={$totalCost}€");
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
        // Obtenir la reserva activa
        $bookingStmt = $this->db->prepare("
            SELECT b.id, b.start_datetime, b.total_cost
            FROM bookings b
            WHERE b.vehicle_id = ? AND b.user_id = ? AND b.status = 'active'
            LIMIT 1
        ");
        $bookingStmt->bind_param('ii', $vehicleId, $userId);
        $bookingStmt->execute();
        $result = $bookingStmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("CompleteBooking Error: No active booking found for vehicle=$vehicleId, user=$userId");
            return false;
        }
        
        $booking = $result->fetch_assoc();
        $bookingId = $booking['id'];
        $startDatetime = $booking['start_datetime'];
        $paidAmount = $booking['total_cost']; // Cost ja pagat
        
        // Calcular minuts reals d'ús (només per estadístiques)
        $now = date('Y-m-d H:i:s');
        $start = new DateTime($startDatetime);
        $end = new DateTime($now);
        $interval = $start->diff($end);
        
        // Calcular segons totals i convertir a minuts (arrodonit cap amunt)
        $totalSeconds = ($interval->days * 24 * 60 * 60) + ($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s;
        $totalMinutes = ceil($totalSeconds / 60);
        
        if ($totalMinutes < 1) {
            $totalMinutes = 1;
        }
        
        error_log("CompleteBooking: bookingId=$bookingId, realMinutes=$totalMinutes, paidAmount={$paidAmount}EUR (NO REFUND)");
        
        // Actualitzar booking amb temps real PERÒ mantenir el cost pagat
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET end_datetime = NOW(),
                total_minutes = ?,
                status = 'completed'
            WHERE id = ?
        ");
        $stmt->bind_param('ii', $totalMinutes, $bookingId);
        
        $success = $stmt->execute();
        
        if ($success) {
            error_log("Booking $bookingId completed. Real minutes: $totalMinutes. Paid: €{$paidAmount} (prepaid, no refund)");
        } else {
            error_log("CompleteBooking Error: Failed to update booking $bookingId");
        }
        
        return $success;
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
