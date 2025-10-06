<?php
/**
 * Database Configuration and Connection Handler
 * Supports both MariaDB and MongoDB connections
 */

class Database {
    private static $mariadb_connection = null;
    private static $mongodb_connection = null;
    
    /**
     * Get MariaDB connection
     * @return mysqli
     */
    public static function getMariaDBConnection() {
        if (self::$mariadb_connection === null) {
            // Load environment variables
            $env_file = __DIR__ . '/.env';
            if (file_exists($env_file)) {
                $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                        list($key, $value) = explode('=', $line, 2);
                        $_ENV[trim($key)] = trim($value);
                    }
                }
            }
            
            $host = $_ENV['DB_HOST'] ?? 'mariadb';
            $user = $_ENV['DB_USER'] ?? 'simsuser';
            $pass = $_ENV['DB_PASS'] ?? 'Putamare123';
            $dbname = $_ENV['DB_NAME'] ?? 'simsdb';
            
            try {
                self::$mariadb_connection = new mysqli($host, $user, $pass, $dbname);
                
                if (self::$mariadb_connection->connect_error) {
                    throw new Exception("MariaDB Connection failed: " . self::$mariadb_connection->connect_error);
                }
                
                self::$mariadb_connection->set_charset("utf8mb4");
            } catch (Exception $e) {
                error_log("Database Error: " . $e->getMessage());
                die("Database connection error. Please try again later.");
            }
        }
        
        return self::$mariadb_connection;
    }
    
    /**
     * Get MongoDB connection
     * @return MongoDB\Client
     */
    public static function getMongoDBConnection() {
        if (self::$mongodb_connection === null) {
            try {
                require_once __DIR__ . '/../vendor/autoload.php';
                
                $mongo_host = 'mongodb';
                $mongo_user = 'simsadmin';
                $mongo_pass = 'Putamare123.';
                $mongo_db = 'simsdb';
                
                $uri = "mongodb://{$mongo_user}:{$mongo_pass}@{$mongo_host}:27017/{$mongo_db}";
                
                self::$mongodb_connection = new MongoDB\Client($uri);
                
                // Test connection
                self::$mongodb_connection->listDatabases();
            } catch (Exception $e) {
                error_log("MongoDB Error: " . $e->getMessage());
                die("MongoDB connection error. Please try again later.");
            }
        }
        
        return self::$mongodb_connection;
    }
    
    /**
     * Get MongoDB database
     * @return MongoDB\Database
     */
    public static function getMongoDatabase() {
        $client = self::getMongoDBConnection();
        return $client->simsdb;
    }
    
    /**
     * Close all connections
     */
    public static function closeConnections() {
        if (self::$mariadb_connection !== null) {
            self::$mariadb_connection->close();
            self::$mariadb_connection = null;
        }
        // MongoDB connections are closed automatically
    }
}

// Helper function for quick MariaDB access
function getDB() {
    return Database::getMariaDBConnection();
}

// Helper function for quick MongoDB access
function getMongoDB() {
    return Database::getMongoDatabase();
}
?>
