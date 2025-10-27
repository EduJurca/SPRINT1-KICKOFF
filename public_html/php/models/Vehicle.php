<?php

/**
 * Vehicle Model
 * Handles vehicle data operations with MariaDB and MongoDB
 */

class Vehicle {
    private $db;
    
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }
    
    /**
     * Get all available vehicles
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
     * Get vehicle by ID
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
        
        $vehicle = $result->fetch_assoc();
        
        // Formatear ubicación
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
        
        // Limpiar campos duplicados
        unset($vehicle['latitude']);
        unset($vehicle['longitude']);
        
        return $vehicle;
    }
    
    /**
     * Check if vehicle is available
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
     * Update vehicle status
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
     * Get vehicle with full details for claiming
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
}
