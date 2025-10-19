<?php
class DatabaseMariaDB {
    private static $conn;

    public static function getConnection() {
        if (!self::$conn) {
            $host = getenv('DB_HOST') ?: 'mariadb';
            $user = getenv('DB_USER');
            $pass = getenv('DB_PASS');
            $dbname = getenv('DB_NAME') ?: 'simsdb';

            // Validar que las credenciales críticas existen
            if (!$user || !$pass) {
                throw new Exception("Database credentials not configured. Please check your .env file.");
            }

            self::$conn = new mysqli($host, $user, $pass, $dbname);

            if (self::$conn->connect_error) {
                throw new Exception("MariaDB connection failed: " . self::$conn->connect_error);
            }

            self::$conn->set_charset('utf8mb4');
        }
        return self::$conn;
    }
}