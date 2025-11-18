<?php

class Database {
    private static $mariadb_connection = null;
    private static $mongodb_connection = null;

    public static function getMariaDBConnection() {
        if (self::$mariadb_connection === null) {
            self::loadEnv();
            
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';
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
    
    public static function getMongoDBConnection() {
        if (self::$mongodb_connection === null) {
            try {
                $autoloadPath = ROOT_PATH . '/vendor/autoload.php';
                if (file_exists($autoloadPath)) {
                    require_once $autoloadPath;
                }
                
                self::loadEnv();
                
                $mongo_host = $_ENV['MONGO_HOST'] ?? 'localhost';
                $mongo_user = $_ENV['MONGO_INITDB_ROOT_USERNAME'] ?? 'root';
                $mongo_pass = $_ENV['MONGO_INITDB_ROOT_PASSWORD'] ?? 'root';
                $mongo_db = $_ENV['MONGO_INITDB_DATABASE'] ?? 'simsdb';

                $uri = "mongodb://{$mongo_user}:{$mongo_pass}@{$mongo_host}:27017/{$mongo_db}";
                
                self::$mongodb_connection = new MongoDB\Client($uri);
                self::$mongodb_connection->listDatabases();
            } catch (Exception $e) {
                error_log("MongoDB Error: " . $e->getMessage());
                die("MongoDB connection error. Please try again later.");
            }
        }
        
        return self::$mongodb_connection;
    }

    public static function getMongoDatabase() {
        $client = self::getMongoDBConnection();
        $mongo_db = $_ENV['MONGO_INITDB_DATABASE'] ?? 'simsdb';
        return $client->$mongo_db;
    }
    
    private static function loadEnv() {
        $rootEnv = ROOT_PATH . '/.env';
        $configEnv = CONFIG_PATH . '/.env';
        $env_file = null;
        
        if (file_exists($rootEnv)) {
            $env_file = $rootEnv;
        } elseif (file_exists($configEnv)) {
            $env_file = $configEnv;
        }

        if ($env_file !== null && file_exists($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    $value = trim($value, '"\'');
                    
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
    
    public static function closeConnections() {
        if (self::$mariadb_connection !== null) {
            self::$mariadb_connection->close();
            self::$mariadb_connection = null;
        }
    }
}

function getDB() {
    return Database::getMariaDBConnection();
}

function getMongoDB() {
    return Database::getMongoDatabase();
}

