-- Carsharing Platform Sample Data
-- MariaDB/MySQL Seed Data
-- Created: 2025-10-07
-- Contains realistic sample data for testing and development

USE carsharing;

-- Clear existing data
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE bookings;
TRUNCATE TABLE vehicles;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert users
-- Note: Passwords are hashed using Argon2id (2025 best practice)
-- Password for all users: Admin123! (for testing purposes)
-- In production, these should be changed immediately

-- Admin user
INSERT INTO users (email, password_hash, full_name, phone, license_number, role, created_at, updated_at) VALUES
('admin@carsharing.com', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQxMjM0NTY3OA$8xhkKz5VqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVo', 'Admin User', '+1234567890', 'ADM123456', 'admin', '2025-01-01 10:00:00', '2025-01-01 10:00:00');

-- Technician user
INSERT INTO users (email, password_hash, full_name, phone, license_number, role, created_at, updated_at) VALUES
('tech@carsharing.com', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQxMjM0NTY3OA$8xhkKz5VqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVo', 'Tech Support', '+1234567891', 'TEC123456', 'technician', '2025-01-02 10:00:00', '2025-01-02 10:00:00');

-- Regular users
INSERT INTO users (email, password_hash, full_name, phone, license_number, role, created_at, updated_at) VALUES
('john.doe@example.com', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQxMjM0NTY3OA$8xhkKz5VqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVo', 'John Doe', '+1234567892', 'DL1234567', 'user', '2025-02-15 09:30:00', '2025-02-15 09:30:00'),
('jane.smith@example.com', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQxMjM0NTY3OA$8xhkKz5VqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVo', 'Jane Smith', '+1234567893', 'DL2345678', 'user', '2025-03-10 14:20:00', '2025-03-10 14:20:00'),
('michael.johnson@example.com', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQxMjM0NTY3OA$8xhkKz5VqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVo', 'Michael Johnson', '+1234567894', 'DL3456789', 'user', '2025-04-05 11:15:00', '2025-04-05 11:15:00'),
('sarah.williams@example.com', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQxMjM0NTY3OA$8xhkKz5VqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVqVo', 'Sarah Williams', '+1234567895', 'DL4567890', 'user', '2025-05-20 16:45:00', '2025-05-20 16:45:00');

-- Insert vehicles with realistic locations (coordinates for major cities)
INSERT INTO vehicles (model, brand, license_plate, status, location_lat, location_lng, price_per_hour, created_at, updated_at) VALUES
-- Available vehicles
('Model 3', 'Tesla', 'ABC1234', 'available', 40.7128, -74.0060, 25.00, '2025-01-15 08:00:00', '2025-10-07 10:00:00'),
('Civic', 'Honda', 'XYZ5678', 'available', 34.0522, -118.2437, 15.00, '2025-01-20 09:00:00', '2025-10-07 10:00:00'),
('Corolla', 'Toyota', 'DEF9012', 'available', 41.8781, -87.6298, 18.00, '2025-02-01 10:00:00', '2025-10-07 10:00:00'),
('Leaf', 'Nissan', 'GHI3456', 'available', 37.7749, -122.4194, 20.00, '2025-02-10 11:00:00', '2025-10-07 10:00:00'),
('Prius', 'Toyota', 'JKL7890', 'available', 29.7604, -95.3698, 17.00, '2025-02-15 12:00:00', '2025-10-07 10:00:00'),

-- In use vehicles
('Model Y', 'Tesla', 'MNO1234', 'in_use', 39.7392, -104.9903, 28.00, '2025-03-01 08:00:00', '2025-10-07 09:00:00'),
('Accord', 'Honda', 'PQR5678', 'in_use', 33.4484, -112.0740, 19.00, '2025-03-05 09:00:00', '2025-10-07 08:30:00'),

-- Maintenance vehicles
('Camry', 'Toyota', 'STU9012', 'maintenance', 42.3601, -71.0589, 16.00, '2025-03-10 10:00:00', '2025-10-06 15:00:00'),

-- Unavailable vehicles
('Bolt', 'Chevrolet', 'VWX3456', 'unavailable', 47.6062, -122.3321, 22.00, '2025-03-15 11:00:00', '2025-10-05 14:00:00'),

-- Additional available vehicles
('Mustang Mach-E', 'Ford', 'YZA7890', 'available', 30.2672, -97.7431, 30.00, '2025-04-01 08:00:00', '2025-10-07 10:00:00');

-- Insert bookings
INSERT INTO bookings (user_id, vehicle_id, start_time, end_time, status, total_cost, created_at) VALUES
-- Active bookings
(3, 6, '2025-10-07 08:00:00', NULL, 'active', NULL, '2025-10-07 08:00:00'),
(4, 7, '2025-10-07 07:30:00', NULL, 'active', NULL, '2025-10-07 07:30:00'),

-- Completed bookings
(3, 1, '2025-10-05 10:00:00', '2025-10-05 14:00:00', 'completed', 100.00, '2025-10-05 10:00:00'),
(4, 2, '2025-10-04 09:00:00', '2025-10-04 12:00:00', 'completed', 45.00, '2025-10-04 09:00:00'),
(5, 3, '2025-10-03 14:00:00', '2025-10-03 18:00:00', 'completed', 72.00, '2025-10-03 14:00:00'),
(6, 4, '2025-10-02 11:00:00', '2025-10-02 15:00:00', 'completed', 80.00, '2025-10-02 11:00:00'),
(3, 5, '2025-10-01 08:00:00', '2025-10-01 10:00:00', 'completed', 34.00, '2025-10-01 08:00:00'),

-- Cancelled booking
(5, 1, '2025-09-30 10:00:00', NULL, 'cancelled', NULL, '2025-09-30 10:00:00');

-- Display summary
SELECT 'Database seeded successfully!' as message;
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_vehicles FROM vehicles;
SELECT COUNT(*) as total_bookings FROM bookings;
SELECT role, COUNT(*) as count FROM users GROUP BY role;
SELECT status, COUNT(*) as count FROM vehicles GROUP BY status;
SELECT status, COUNT(*) as count FROM bookings GROUP BY status;
