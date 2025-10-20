<?php
class DatabaseMariaDB {
    private static $conn;

    public static function getConnection() {
        if (!self::$conn) {
            self::$conn = new mysqli(
                'mariadb',        // host (nombre del servicio en Docker)
                'simsuser',       // usuario
                'Putamare123',    // contraseña
                'simsdb'          // base de datos
            );

            if (self::$conn->connect_error) {
                throw new Exception("MariaDB connection failed: " . self::$conn->connect_error);
            }

            self::$conn->set_charset('utf8mb4');
        }
        return self::$conn;
    }
}