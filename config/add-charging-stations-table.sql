-- Add charging stations table to the database
-- This script adds support for electric vehicle charging stations

USE simsdb;

-- Create charging_stations table
CREATE TABLE IF NOT EXISTS charging_stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(500) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10) DEFAULT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    total_slots INT NOT NULL DEFAULT 4,
    available_slots INT NOT NULL DEFAULT 4,
    power_kw INT NOT NULL DEFAULT 50,
    status ENUM('active', 'maintenance', 'out_of_service') NOT NULL DEFAULT 'active',
    operator VARCHAR(100) DEFAULT 'VoltiaCar',
    description TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_city (city),
    INDEX idx_status (status),
    INDEX idx_location (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create charging_sessions table
CREATE TABLE IF NOT EXISTS charging_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    user_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME DEFAULT NULL,
    duration_minutes INT DEFAULT NULL,
    start_battery INT NOT NULL,
    end_battery INT DEFAULT NULL,
    energy_consumed_kwh DECIMAL(10,2) DEFAULT NULL,
    status ENUM('in_progress', 'completed', 'interrupted') NOT NULL DEFAULT 'in_progress',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES charging_stations(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_station (station_id),
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample charging stations
INSERT INTO charging_stations (name, address, city, postal_code, latitude, longitude, total_slots, available_slots, power_kw, status) VALUES
('Amposta Centre Station', 'Placa de Espanya, 1', 'Amposta', '43870', 40.708889, 0.578333, 4, 4, 50, 'active'),
('Delta Ebre Station', 'Avinguda Sant Jaume, 50', 'Amposta', '43870', 40.712500, 0.582778, 6, 5, 50, 'active'),
('Eucaliptus Park Station', 'Carrer dels Eucaliptus, 25', 'Amposta', '43870', 40.706111, 0.576667, 8, 8, 50, 'active'),
('Hospital Comarcal Station', 'Avinguda de la Ràpita, 15', 'Amposta', '43870', 40.710000, 0.585000, 4, 2, 50, 'active'),
('Sant Carles Station', 'Carrer Major, 10', 'Sant Carles de la Rapita', '43540', 40.616667, 0.583333, 4, 4, 50, 'active');

SELECT 'Charging stations table created successfully!' AS message;
