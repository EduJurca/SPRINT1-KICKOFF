<?php
/**
 * Database Configuration
 * Handles connections to MariaDB (PDO) and MongoDB
 * Based on 2025 best practices for secure database connections
 */

// MariaDB Connection using PDO
function getMariaDBConnection() {
    $host = getenv('DB_HOST') ?: 'mariadb';
    $port = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_NAME') ?: 'carsharing';
    $username = getenv('DB_USER') ?: 'carsharing_user';
    $password = getenv('DB_PASS') ?: 'carsharing_pass';
    
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        
        // PDO options following 2025 security best practices
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error securely without exposing sensitive information
        error_log("MariaDB Connection Error: " . $e->getMessage());
        throw new Exception("Database connection failed", 500);
    }
}

// MongoDB Connection using MongoDB PHP Library v2.1.0
function getMongoDBConnection() {
    $host = getenv('MONGO_HOST') ?: 'mongodb';
    $port = getenv('MONGO_PORT') ?: '27017';
    $dbname = getenv('MONGO_DB') ?: 'carsharing';
    
    try {
        // MongoDB connection string
        $uri = "mongodb://{$host}:{$port}";
        
        // Create MongoDB client (v2.1.0 syntax)
        $client = new MongoDB\Client($uri);
        
        // Select database
        $database = $client->selectDatabase($dbname);
        
        return $database;
        
    } catch (Exception $e) {
        // Log error securely
        error_log("MongoDB Connection Error: " . $e->getMessage());
        throw new Exception("MongoDB connection failed", 500);
    }
}

// Test database connections
function testConnections() {
    try {
        $mariadb = getMariaDBConnection();
        $mongodb = getMongoDBConnection();
        
        return [
            'mariadb' => $mariadb !== null,
            'mongodb' => $mongodb !== null
        ];
    } catch (Exception $e) {
        return [
            'mariadb' => false,
            'mongodb' => false,
            'error' => $e->getMessage()
        ];
    }
}
