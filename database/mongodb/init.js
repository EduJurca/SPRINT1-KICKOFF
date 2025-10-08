// Carsharing Platform MongoDB Initialization
// MongoDB Database Setup Script
// Created: 2025-10-07
// Following 2025 MongoDB best practices

// Switch to carsharing database
db = db.getSiblingDB('carsharing');

// Drop existing collections if they exist (for clean installation)
db.sensor_data.drop();
db.system_logs.drop();

print('Creating collections...');

// Create sensor_data collection
db.createCollection('sensor_data', {
    validator: {
        $jsonSchema: {
            bsonType: 'object',
            required: ['vehicle_id', 'timestamp'],
            properties: {
                vehicle_id: {
                    bsonType: 'int',
                    description: 'Vehicle ID - required'
                },
                battery_level: {
                    bsonType: 'double',
                    minimum: 0,
                    maximum: 100,
                    description: 'Battery level percentage'
                },
                fuel_level: {
                    bsonType: 'double',
                    minimum: 0,
                    maximum: 100,
                    description: 'Fuel level percentage'
                },
                speed: {
                    bsonType: 'double',
                    minimum: 0,
                    description: 'Current speed'
                },
                location: {
                    bsonType: 'object',
                    properties: {
                        lat: {
                            bsonType: 'double',
                            minimum: -90,
                            maximum: 90
                        },
                        lng: {
                            bsonType: 'double',
                            minimum: -180,
                            maximum: 180
                        }
                    }
                },
                temperature: {
                    bsonType: 'double',
                    description: 'Temperature in Celsius'
                },
                tire_pressure: {
                    bsonType: 'double',
                    minimum: 0,
                    description: 'Tire pressure in PSI'
                },
                timestamp: {
                    bsonType: 'date',
                    description: 'Timestamp - required'
                },
                created_at: {
                    bsonType: 'string',
                    description: 'Creation date string'
                }
            }
        }
    }
});

print('sensor_data collection created');

// Create system_logs collection
db.createCollection('system_logs', {
    validator: {
        $jsonSchema: {
            bsonType: 'object',
            required: ['level', 'message', 'timestamp'],
            properties: {
                level: {
                    enum: ['info', 'warning', 'error'],
                    description: 'Log level - required'
                },
                message: {
                    bsonType: 'string',
                    description: 'Log message - required'
                },
                user_id: {
                    bsonType: ['int', 'null'],
                    description: 'User ID if applicable'
                },
                action: {
                    bsonType: ['string', 'null'],
                    description: 'Action performed'
                },
                ip_address: {
                    bsonType: ['string', 'null'],
                    description: 'IP address of request'
                },
                timestamp: {
                    bsonType: 'date',
                    description: 'Timestamp - required'
                },
                created_at: {
                    bsonType: 'string',
                    description: 'Creation date string'
                }
            }
        }
    }
});

print('system_logs collection created');

// Create indexes for sensor_data collection
print('Creating indexes for sensor_data...');

db.sensor_data.createIndex(
    { vehicle_id: 1 },
    { name: 'idx_vehicle_id' }
);

db.sensor_data.createIndex(
    { timestamp: -1 },
    { name: 'idx_timestamp' }
);

db.sensor_data.createIndex(
    { vehicle_id: 1, timestamp: -1 },
    { name: 'idx_vehicle_timestamp' }
);

db.sensor_data.createIndex(
    { 'location.lat': 1, 'location.lng': 1 },
    { name: 'idx_location' }
);

print('Indexes created for sensor_data');

// Create indexes for system_logs collection
print('Creating indexes for system_logs...');

db.system_logs.createIndex(
    { timestamp: -1 },
    { name: 'idx_timestamp' }
);

db.system_logs.createIndex(
    { level: 1 },
    { name: 'idx_level' }
);

db.system_logs.createIndex(
    { user_id: 1 },
    { name: 'idx_user_id' }
);

db.system_logs.createIndex(
    { level: 1, timestamp: -1 },
    { name: 'idx_level_timestamp' }
);

db.system_logs.createIndex(
    { action: 1 },
    { name: 'idx_action' }
);

print('Indexes created for system_logs');

// Insert sample sensor data
print('Inserting sample sensor data...');

const now = new Date();
const oneHourAgo = new Date(now.getTime() - 3600000);
const twoHoursAgo = new Date(now.getTime() - 7200000);

db.sensor_data.insertMany([
    {
        vehicle_id: 1,
        battery_level: 85.5,
        fuel_level: 0,
        speed: 0,
        location: { lat: 40.7128, lng: -74.0060 },
        temperature: 22.5,
        tire_pressure: 32.0,
        timestamp: now,
        created_at: now.toISOString()
    },
    {
        vehicle_id: 1,
        battery_level: 87.2,
        fuel_level: 0,
        speed: 45.5,
        location: { lat: 40.7138, lng: -74.0070 },
        temperature: 23.0,
        tire_pressure: 32.0,
        timestamp: oneHourAgo,
        created_at: oneHourAgo.toISOString()
    },
    {
        vehicle_id: 2,
        battery_level: 0,
        fuel_level: 75.0,
        speed: 0,
        location: { lat: 34.0522, lng: -118.2437 },
        temperature: 28.5,
        tire_pressure: 33.0,
        timestamp: now,
        created_at: now.toISOString()
    },
    {
        vehicle_id: 3,
        battery_level: 0,
        fuel_level: 82.5,
        speed: 0,
        location: { lat: 41.8781, lng: -87.6298 },
        temperature: 18.0,
        tire_pressure: 31.5,
        timestamp: now,
        created_at: now.toISOString()
    },
    {
        vehicle_id: 4,
        battery_level: 92.0,
        fuel_level: 0,
        speed: 0,
        location: { lat: 37.7749, lng: -122.4194 },
        temperature: 20.5,
        tire_pressure: 32.5,
        timestamp: now,
        created_at: now.toISOString()
    },
    {
        vehicle_id: 5,
        battery_level: 0,
        fuel_level: 68.0,
        speed: 0,
        location: { lat: 29.7604, lng: -95.3698 },
        temperature: 30.0,
        tire_pressure: 33.5,
        timestamp: now,
        created_at: now.toISOString()
    },
    {
        vehicle_id: 6,
        battery_level: 78.5,
        fuel_level: 0,
        speed: 55.0,
        location: { lat: 39.7392, lng: -104.9903 },
        temperature: 25.0,
        tire_pressure: 32.0,
        timestamp: now,
        created_at: now.toISOString()
    },
    {
        vehicle_id: 7,
        battery_level: 0,
        fuel_level: 45.5,
        speed: 60.0,
        location: { lat: 33.4484, lng: -112.0740 },
        temperature: 32.0,
        tire_pressure: 31.0,
        timestamp: now,
        created_at: now.toISOString()
    }
]);

print('Sample sensor data inserted');

// Insert sample system logs
print('Inserting sample system logs...');

db.system_logs.insertMany([
    {
        level: 'info',
        message: 'System initialized',
        user_id: 1,
        action: 'system_init',
        ip_address: '127.0.0.1',
        timestamp: twoHoursAgo,
        created_at: twoHoursAgo.toISOString()
    },
    {
        level: 'info',
        message: 'User logged in',
        user_id: 3,
        action: 'login',
        ip_address: '192.168.1.100',
        timestamp: oneHourAgo,
        created_at: oneHourAgo.toISOString()
    },
    {
        level: 'info',
        message: 'Booking created for vehicle ABC1234',
        user_id: 3,
        action: 'create_booking',
        ip_address: '192.168.1.100',
        timestamp: oneHourAgo,
        created_at: oneHourAgo.toISOString()
    },
    {
        level: 'warning',
        message: 'Failed login attempt',
        user_id: null,
        action: 'login',
        ip_address: '203.0.113.45',
        timestamp: oneHourAgo,
        created_at: oneHourAgo.toISOString()
    },
    {
        level: 'info',
        message: 'Vehicle status updated',
        user_id: 2,
        action: 'update_vehicle',
        ip_address: '192.168.1.50',
        timestamp: now,
        created_at: now.toISOString()
    }
]);

print('Sample system logs inserted');

// Display summary
print('\n=== MongoDB Initialization Complete ===');
print('Collections created: sensor_data, system_logs');
print('Indexes created for optimal query performance');
print('Sample data inserted for testing');
print('\nCollection statistics:');
print('sensor_data documents: ' + db.sensor_data.countDocuments());
print('system_logs documents: ' + db.system_logs.countDocuments());
print('\nIndexes:');
print('sensor_data indexes:');
db.sensor_data.getIndexes().forEach(function(index) {
    print('  - ' + index.name);
});
print('system_logs indexes:');
db.system_logs.getIndexes().forEach(function(index) {
    print('  - ' + index.name);
});
print('=======================================\n');
