<?php
require 'vendor/autoload.php';

use MongoDB\Client;

class DatabaseMongo {
    private static $client;

    public static function getConnection() {
        if (!self::$client) {
            $mongoUser = getenv('MONGO_INITDB_ROOT_USERNAME');
            $mongoPass = getenv('MONGO_INITDB_ROOT_PASSWORD');
            $mongoHost = getenv('MONGO_HOST') ?: 'mongodb';
            $mongoPort = getenv('MONGO_PORT') ?: '27017';
            $mongoDb = getenv('MONGO_INITDB_DATABASE') ?: 'simsdb';

            if (!$mongoUser || !$mongoPass) {
                throw new Exception("MongoDB credentials not configured. Please check your .env file.");
            }

            $connectionString = "mongodb://{$mongoUser}:{$mongoPass}@{$mongoHost}:{$mongoPort}";
            
            self::$client = new Client($connectionString);
        }
        
        $dbName = getenv('MONGO_INITDB_DATABASE') ?: 'simsdb';
        return self::$client->$dbName;
    }
}