-- VoltiaCar MariaDB Initialization Script
-- Creates database, tables, and sample data

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS vehicle_usage;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS payment_methods;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS nationalities;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS charging_sessions;
DROP TABLE IF EXISTS charging_stations;
DROP TABLE IF EXISTS incidents;


SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- SISTEMA DE ROLS (Hardcoded)
-- ==========================================
CREATE TABLE roles (
    id INT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir els 3 rols del sistema
INSERT INTO roles (id, name, description) VALUES
(1, 'SuperAdmin', 'Administrador amb accés total al sistema'),
(2, 'Treballador', 'Personal que gestiona vehicles i reserves'),
(3, 'Client', 'Usuari estàndard del sistema');

-- Table: nationalities
CREATE TABLE nationalities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(50),
    phone VARCHAR(20),
    birth_date DATE,
    sex ENUM('M', 'F', 'O') DEFAULT NULL, 
    address VARCHAR(255),                       
    dni VARCHAR(20),   
    role_id INT DEFAULT 3,
    lang VARCHAR(5) DEFAULT 'ca',
    iban VARCHAR(34),
    driver_license_photo VARCHAR(255),
    nationality_id INT,
    minute_balance INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nationality_id) REFERENCES nationalities(id) ON DELETE SET NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role_id (role_id),
    INDEX idx_lang (lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payment_methods
-- Stores tokenized payment methods for users (DO NOT store raw PAN/CVC)
-- Use tokens returned by a PCI-compliant gateway (Stripe, Adyen, etc.)
CREATE TABLE payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(100) NOT NULL,
    provider_token VARBINARY(512) NOT NULL,
    last4 CHAR(4),
    brand VARCHAR(50),
    exp_month TINYINT UNSIGNED,
    exp_year SMALLINT UNSIGNED,
    is_default BOOLEAN DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_provider_token (provider, provider_token),
    INDEX idx_user_id (user_id),
    INDEX idx_provider (provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: subscriptions
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('basic', 'premium') NOT NULL DEFAULT 'basic',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    free_minutes INT DEFAULT 25,
    unlock_fee_waived BOOLEAN DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vehicles
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate VARCHAR(20) NOT NULL UNIQUE,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT,
    status ENUM('available', 'in_use', 'charging', 'maintenance', 'reserved') DEFAULT 'available',
    battery_level INT DEFAULT 100,
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    vehicle_type ENUM('car', 'bike', 'scooter', 'motorcycle') DEFAULT 'car',
    is_accessible BOOLEAN DEFAULT FALSE,
    accessibility_features JSON DEFAULT NULL,
    price_per_minute DECIMAL(5,2) DEFAULT 0.35,
    image_url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_plate (plate),
    INDEX idx_brand_model (brand, model),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: locations
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    address VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_coordinates (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vehicle_usage
CREATE TABLE vehicle_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    start_location_id INT,
    end_location_id INT,
    total_distance_km DECIMAL(8,2),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (start_location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (end_location_id) REFERENCES locations(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_start_time (start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('mechanical', 'electrical', 'other') NOT NULL,
    status ENUM('in_progress', 'pending', 'resolved') DEFAULT 'pending',
    description TEXT NOT NULL,
    notes TEXT,
    incident_creator INT NOT NULL,
    incident_assignee INT,
    resolved_by INT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at DATETIME,
    FOREIGN KEY (incident_creator) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (incident_assignee) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_incident_creator (incident_creator),
    INDEX idx_incident_assignee (incident_assignee),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_usage_id INT,
    payment_method_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    type ENUM('unlock', 'time', 'subscription') NOT NULL,
    status ENUM('pending', 'succeeded', 'failed', 'refunded') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_usage_id) REFERENCES vehicle_usage(id) ON DELETE SET NULL,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_type (type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample nationalities
INSERT INTO nationalities (name) VALUES
('Spain'),
('France'),
('Germany'),
('Italy'),
('United Kingdom'),
('Portugal'),
('Netherlands'),
('Belgium'),
('Sweden'),
('Norway');

-- Insert sample locations (Amposta area)
INSERT INTO locations (name, latitude, longitude, address) VALUES
('Amposta Centre', 40.71170000, 0.57830000, 'Plaça de l''Ajuntament, Amposta'),
('Parc Natural Delta de l''Ebre', 40.72000000, 0.73500000, 'Deltebre, Tarragona'),
('Port dels Alfacs', 40.62500000, 0.87000000, 'Sant Carles de la Ràpita'),
('Platja de la Marquesa', 40.68000000, 0.60000000, 'Amposta, Tarragona'),
('Centre Esportiu', 40.71500000, 0.58500000, 'Avinguda de la Ràpita, Amposta');

-- Table: bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    total_minutes INT,
    total_cost DECIMAL(10,2),
    pickup_location VARCHAR(255),
    dropoff_location VARCHAR(255),
    notes TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_status (status),
    INDEX idx_datetime (start_datetime, end_datetime),
    INDEX idx_user_vehicle (user_id, vehicle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample vehicles with Amposta locations
INSERT INTO vehicles (plate, brand, model, year, status, battery_level, latitude, longitude, vehicle_type, is_accessible, price_per_minute, image_url) VALUES
('1234ABC', 'Tesla', 'Model 3', 2023, 'available', 85, 40.71170000, 0.57830000, 'car', 0, 0.40, '/images/tesla-model3.jpg'),
('5678DEF', 'Nissan', 'Leaf', 2022, 'available', 92, 40.71350000, 0.57650000, 'car', 1, 0.35, '/images/nissan-leaf.jpg'),
('9012GHI', 'Renault', 'Zoe', 2023, 'available', 78, 40.71000000, 0.58100000, 'car', 0, 0.35, '/images/renault-zoe.jpg'),
('3456JKL', 'BMW', 'i3', 2022, 'available', 65, 40.71450000, 0.57500000, 'car', 1, 0.45, '/images/bmw-i3.jpg'),
('7890MNO', 'Volkswagen', 'ID.3', 2023, 'available', 88, 40.70950000, 0.58300000, 'car', 0, 0.38, '/images/vw-id3.jpg');

-- ==========================================
-- USUARIS DE PROVA AMB ROLS
-- ==========================================
INSERT INTO users (username, email, password, fullname, role_id, minute_balance, created_at) VALUES
-- SuperAdmin (password: admin123)
('admin', 'admin@sims.cat', '$2y$10$FDHmfPCgzisG0KHG2Q9K8eoAodytkui9A0nMmtDY4W6sIbbN2FfA.', 'Administrator', 1, 1000, NOW()),

-- Treballadors (password: treballador123)
('treballador1', 'treballador1@sims.cat', '$2y$10$uvFEE/dr3fKA.Do/CC7f3uv9IWw71o2zlSX40vCNu05rcx8wgqFU6', 'Joan Pérez', 2, 500, NOW()),
('treballador2', 'treballador2@sims.cat', '$2y$10$uvFEE/dr3fKA.Do/CC7f3uv9IWw71o2zlSX40vCNu05rcx8wgqFU6', 'Maria García', 2, 500, NOW()),

-- Clients (password: client123)
('client1', 'client1@example.com', '$2y$10$LMsChqzpt0EcZu.VQWdPLu6ZEu8DaEJfHK3/h8zzAWrNbJIfKgPtW', 'Pau Martínez', 3, 100, NOW()),
('client2', 'client2@example.com', '$2y$10$LMsChqzpt0EcZu.VQWdPLu6ZEu8DaEJfHK3/h8zzAWrNbJIfKgPtW', 'Anna López', 3, 150, NOW()),
('client3', 'client3@example.com', '$2y$10$LMsChqzpt0EcZu.VQWdPLu6ZEu8DaEJfHK3/h8zzAWrNbJIfKgPtW', 'Marc Vila', 3, 75, NOW());

-- ==========================================
-- DADES DE PROVA: HISTORIAL DE VIATGES
-- ==========================================

-- Viatges del client1 (user_id = 4)
INSERT INTO vehicle_usage (user_id, vehicle_id, start_time, end_time, start_location_id, end_location_id, total_distance_km) VALUES
(4, 1, '2025-10-25 10:30:00', '2025-10-25 11:00:00', 1, 2, 5.2),
(4, 2, '2025-10-28 15:15:00', '2025-10-28 15:45:00', 2, 3, 8.7),
(4, 3, '2025-10-30 09:00:00', '2025-10-30 09:35:00', 1, 4, 6.3),
(4, 1, '2025-11-01 14:20:00', '2025-11-01 15:10:00', 3, 5, 12.5);

-- Viatges del client2 (user_id = 5)
INSERT INTO vehicle_usage (user_id, vehicle_id, start_time, end_time, start_location_id, end_location_id, total_distance_km) VALUES
(5, 4, '2025-10-26 08:45:00', '2025-10-26 09:20:00', 1, 3, 7.1),
(5, 5, '2025-10-29 12:30:00', '2025-10-29 13:05:00', 4, 1, 9.8),
(5, 2, '2025-10-31 16:00:00', NULL, 2, NULL, NULL);  -- Viatge en curs

-- Viatges del client3 (user_id = 6)
INSERT INTO vehicle_usage (user_id, vehicle_id, start_time, end_time, start_location_id, end_location_id, total_distance_km) VALUES
(6, 3, '2025-10-27 11:15:00', '2025-10-27 11:55:00', 5, 2, 11.2),
(6, 1, '2025-11-01 10:00:00', '2025-11-01 10:40:00', 1, 5, 8.9);

-- Success message
SELECT 'MariaDB database initialized successfully!' AS message;


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
