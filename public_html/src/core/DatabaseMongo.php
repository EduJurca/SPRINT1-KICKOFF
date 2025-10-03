<?php
require 'vendor/autoload.php'; // si usas Composer para MongoDB

use MongoDB\Client;

class DatabaseMongo {
    private static $client;

    public static function getConnection() {
        if (!self::$client) {
            self::$client = new Client(
                "mongodb://simsadmin:Putamare123.@mongodb:27017"
            );
        }
        return self::$client->simsdb; // devuelve la BD "simsdb"
    }
}