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
        
        $vehicle = $result->fetch_assoc();
        
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
    public function getAllVehicles() {
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
                v.price_per_minute,
                v.image_url
            FROM vehicles v
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
