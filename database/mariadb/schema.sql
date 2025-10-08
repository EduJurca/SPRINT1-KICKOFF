-- Carsharing Platform Database Schema
-- MariaDB/MySQL Database
-- Created: 2025-10-07
-- Following 2025 database design best practices

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS carsharing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE carsharing;

-- Drop tables if they exist (for clean installation)
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    license_number VARCHAR(50),
    role ENUM('user', 'technician', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vehicles table
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(100) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    license_plate VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('available', 'in_use', 'maintenance', 'unavailable') NOT NULL DEFAULT 'available',
    location_lat DECIMAL(10, 8),
    location_lng DECIMAL(11, 8),
    price_per_hour DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_license_plate (license_plate),
    INDEX idx_status (status),
    INDEX idx_location (location_lat, location_lng),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    total_cost DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_status (status),
    INDEX idx_start_time (start_time),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create views for common queries

-- Active bookings view
CREATE OR REPLACE VIEW active_bookings AS
SELECT 
    b.id,
    b.user_id,
    b.vehicle_id,
    b.start_time,
    b.end_time,
    b.total_cost,
    u.email as user_email,
    u.full_name as user_name,
    v.model as vehicle_model,
    v.brand as vehicle_brand,
    v.license_plate as vehicle_license_plate
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN vehicles v ON b.vehicle_id = v.id
WHERE b.status = 'active';

-- Available vehicles view
CREATE OR REPLACE VIEW available_vehicles AS
SELECT 
    id,
    model,
    brand,
    license_plate,
    location_lat,
    location_lng,
    price_per_hour,
    created_at,
    updated_at
FROM vehicles
WHERE status = 'available';

-- User statistics view
CREATE OR REPLACE VIEW user_statistics AS
SELECT 
    u.id,
    u.email,
    u.full_name,
    u.role,
    COUNT(b.id) as total_bookings,
    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
    SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
    SUM(CASE WHEN b.status = 'completed' THEN b.total_cost ELSE 0 END) as total_spent
FROM users u
LEFT JOIN bookings b ON u.id = b.user_id
GROUP BY u.id, u.email, u.full_name, u.role;

-- Vehicle statistics view
CREATE OR REPLACE VIEW vehicle_statistics AS
SELECT 
    v.id,
    v.model,
    v.brand,
    v.license_plate,
    v.status,
    v.price_per_hour,
    COUNT(b.id) as total_bookings,
    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
    SUM(CASE WHEN b.status = 'completed' THEN b.total_cost ELSE 0 END) as total_revenue
FROM vehicles v
LEFT JOIN bookings b ON v.id = b.vehicle_id
GROUP BY v.id, v.model, v.brand, v.license_plate, v.status, v.price_per_hour;

-- Grant privileges (adjust username and password as needed)
-- GRANT ALL PRIVILEGES ON carsharing.* TO 'carsharing_user'@'%' IDENTIFIED BY 'carsharing_pass';
-- FLUSH PRIVILEGES;
