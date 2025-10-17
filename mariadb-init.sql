-- VoltiaCar MariaDB Initialization Script
-- Creates database, tables, and sample data

-- Create database
CREATE DATABASE IF NOT EXISTS simsdb;
USE simsdb;

-- Drop existing tables (in order of dependencies)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS vehicle_usage;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS nationalities;
SET FOREIGN_KEY_CHECKS = 1;

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
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    iban VARCHAR(34),
    driver_license_photo VARCHAR(255),
    nationality_id INT,
    minute_balance INT DEFAULT 0,
    balance INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nationality_id) REFERENCES nationalities(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_is_admin (is_admin)
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
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_plate (plate),
    INDEX idx_brand_model (brand, model)
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

-- Table: payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_usage_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    type ENUM('unlock', 'time', 'subscription') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_usage_id) REFERENCES vehicle_usage(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_type (type)
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

-- Insert sample locations (Barcelona area)
INSERT INTO locations (name, latitude, longitude, address) VALUES
('Plaça Catalunya', 41.38706100, 2.17009700, 'Plaça de Catalunya, Barcelona'),
('Sagrada Família', 41.40362400, 2.17432500, 'Carrer de Mallorca, 401, Barcelona'),
('Park Güell', 41.41449500, 2.15268900, 'Carrer d''Olot, s/n, Barcelona'),
('Camp Nou', 41.38087900, 2.12282700, 'C. d''Aristides Maillol, Barcelona'),
('Barceloneta Beach', 41.37545400, 2.18966700, 'Passeig Marítim de la Barceloneta, Barcelona');

-- Insert sample vehicles
INSERT INTO vehicles (plate, brand, model, year) VALUES
('1234ABC', 'Tesla', 'Model 3', 2023),
('5678DEF', 'Nissan', 'Leaf', 2022),
('9012GHI', 'Renault', 'Zoe', 2023),
('3456JKL', 'BMW', 'i3', 2022),
('7890MNO', 'Volkswagen', 'ID.3', 2023);

-- Success message
SELECT 'MariaDB database initialized successfully!' AS message;
