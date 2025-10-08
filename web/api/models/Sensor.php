<?php
/**
 * Sensor Model
 * Handles sensor data operations with MongoDB
 * Implements 2025 MongoDB PHP Library v2.1.0 best practices
 */

require_once __DIR__ . '/../config/database.php';

class Sensor {
    private $db;
    private $collection;
    
    public function __construct() {
        $this->db = getMongoDBConnection();
        $this->collection = $this->db->selectCollection('sensor_data');
    }
    
    /**
     * Insert sensor data
     * @param array $data Sensor data
     * @return string|false Inserted ID or false on failure
     */
    public function insert($data) {
        try {
            $document = [
                'vehicle_id' => (int)$data['vehicle_id'],
                'battery_level' => (float)($data['battery_level'] ?? 0),
                'fuel_level' => (float)($data['fuel_level'] ?? 0),
                'speed' => (float)($data['speed'] ?? 0),
                'location' => [
                    'lat' => (float)($data['location_lat'] ?? 0),
                    'lng' => (float)($data['location_lng'] ?? 0)
                ],
                'temperature' => (float)($data['temperature'] ?? 0),
                'tire_pressure' => (float)($data['tire_pressure'] ?? 0),
                'timestamp' => new MongoDB\BSON\UTCDateTime(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->collection->insertOne($document);
            
            if ($result->getInsertedCount() > 0) {
                return (string)$result->getInsertedId();
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error inserting sensor data: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get latest sensor data by vehicle ID
     * @param int $vehicleId Vehicle ID
     * @return array|null Sensor data or null if not found
     */
    public function getLatestByVehicle($vehicleId) {
        try {
            $document = $this->collection->findOne(
                ['vehicle_id' => (int)$vehicleId],
                ['sort' => ['timestamp' => -1]]
            );
            
            if ($document) {
                return $this->formatDocument($document);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error getting latest sensor data: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get sensor data history by vehicle ID
     * @param int $vehicleId Vehicle ID
     * @param int $limit Number of records to retrieve
     * @param int $skip Number of records to skip
     * @return array Sensor data array
     */
    public function getHistoryByVehicle($vehicleId, $limit = 100, $skip = 0) {
        try {
            $cursor = $this->collection->find(
                ['vehicle_id' => (int)$vehicleId],
                [
                    'sort' => ['timestamp' => -1],
                    'limit' => $limit,
                    'skip' => $skip
                ]
            );
            
            $results = [];
            foreach ($cursor as $document) {
                $results[] = $this->formatDocument($document);
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Error getting sensor data history: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get sensor data by date range
     * @param int $vehicleId Vehicle ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Sensor data array
     */
    public function getByDateRange($vehicleId, $startDate, $endDate) {
        try {
            $startTimestamp = new MongoDB\BSON\UTCDateTime(strtotime($startDate) * 1000);
            $endTimestamp = new MongoDB\BSON\UTCDateTime(strtotime($endDate) * 1000);
            
            $cursor = $this->collection->find(
                [
                    'vehicle_id' => (int)$vehicleId,
                    'timestamp' => [
                        '$gte' => $startTimestamp,
                        '$lte' => $endTimestamp
                    ]
                ],
                ['sort' => ['timestamp' => -1]]
            );
            
            $results = [];
            foreach ($cursor as $document) {
                $results[] = $this->formatDocument($document);
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Error getting sensor data by date range: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get all sensor data with pagination
     * @param int $limit Number of records to retrieve
     * @param int $skip Number of records to skip
     * @return array Sensor data array
     */
    public function getAll($limit = 100, $skip = 0) {
        try {
            $cursor = $this->collection->find(
                [],
                [
                    'sort' => ['timestamp' => -1],
                    'limit' => $limit,
                    'skip' => $skip
                ]
            );
            
            $results = [];
            foreach ($cursor as $document) {
                $results[] = $this->formatDocument($document);
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Error getting all sensor data: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get sensor data count by vehicle
     * @param int $vehicleId Vehicle ID
     * @return int Count
     */
    public function countByVehicle($vehicleId) {
        try {
            return $this->collection->countDocuments(['vehicle_id' => (int)$vehicleId]);
            
        } catch (Exception $e) {
            error_log("Error counting sensor data: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Delete old sensor data
     * @param int $daysOld Number of days old
     * @return int Number of deleted documents
     */
    public function deleteOldData($daysOld = 90) {
        try {
            $cutoffDate = new MongoDB\BSON\UTCDateTime(strtotime("-{$daysOld} days") * 1000);
            
            $result = $this->collection->deleteMany([
                'timestamp' => ['$lt' => $cutoffDate]
            ]);
            
            return $result->getDeletedCount();
            
        } catch (Exception $e) {
            error_log("Error deleting old sensor data: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get average sensor values by vehicle
     * @param int $vehicleId Vehicle ID
     * @param int $hours Number of hours to average
     * @return array|null Average values or null
     */
    public function getAverageByVehicle($vehicleId, $hours = 24) {
        try {
            $cutoffDate = new MongoDB\BSON\UTCDateTime(strtotime("-{$hours} hours") * 1000);
            
            $pipeline = [
                [
                    '$match' => [
                        'vehicle_id' => (int)$vehicleId,
                        'timestamp' => ['$gte' => $cutoffDate]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$vehicle_id',
                        'avg_battery_level' => ['$avg' => '$battery_level'],
                        'avg_fuel_level' => ['$avg' => '$fuel_level'],
                        'avg_speed' => ['$avg' => '$speed'],
                        'avg_temperature' => ['$avg' => '$temperature'],
                        'avg_tire_pressure' => ['$avg' => '$tire_pressure'],
                        'count' => ['$sum' => 1]
                    ]
                ]
            ];
            
            $cursor = $this->collection->aggregate($pipeline);
            $result = $cursor->toArray();
            
            if (!empty($result)) {
                return [
                    'vehicle_id' => $result[0]['_id'],
                    'avg_battery_level' => round($result[0]['avg_battery_level'], 2),
                    'avg_fuel_level' => round($result[0]['avg_fuel_level'], 2),
                    'avg_speed' => round($result[0]['avg_speed'], 2),
                    'avg_temperature' => round($result[0]['avg_temperature'], 2),
                    'avg_tire_pressure' => round($result[0]['avg_tire_pressure'], 2),
                    'sample_count' => $result[0]['count']
                ];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error getting average sensor data: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get system logs
     * @param int $limit Number of records to retrieve
     * @param int $skip Number of records to skip
     * @param string $level Filter by level (optional)
     * @return array Logs array
     */
    public function getSystemLogs($limit = 100, $skip = 0, $level = null) {
        try {
            $logsCollection = $this->db->selectCollection('system_logs');
            
            $filter = [];
            if ($level) {
                $filter['level'] = $level;
            }
            
            $cursor = $logsCollection->find(
                $filter,
                [
                    'sort' => ['timestamp' => -1],
                    'limit' => $limit,
                    'skip' => $skip
                ]
            );
            
            $results = [];
            foreach ($cursor as $document) {
                $results[] = $this->formatDocument($document);
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Error getting system logs: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Insert system log
     * @param array $data Log data
     * @return string|false Inserted ID or false on failure
     */
    public function insertLog($data) {
        try {
            $logsCollection = $this->db->selectCollection('system_logs');
            
            $document = [
                'level' => $data['level'] ?? 'info',
                'message' => $data['message'],
                'user_id' => $data['user_id'] ?? null,
                'action' => $data['action'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'timestamp' => new MongoDB\BSON\UTCDateTime(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $logsCollection->insertOne($document);
            
            if ($result->getInsertedCount() > 0) {
                return (string)$result->getInsertedId();
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error inserting system log: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Count system logs
     * @param string $level Filter by level (optional)
     * @return int Count
     */
    public function countLogs($level = null) {
        try {
            $logsCollection = $this->db->selectCollection('system_logs');
            
            $filter = [];
            if ($level) {
                $filter['level'] = $level;
            }
            
            return $logsCollection->countDocuments($filter);
            
        } catch (Exception $e) {
            error_log("Error counting system logs: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Format MongoDB document for output
     * @param object $document MongoDB document
     * @return array Formatted array
     */
    private function formatDocument($document) {
        $array = [];
        
        foreach ($document as $key => $value) {
            if ($key === '_id') {
                $array['id'] = (string)$value;
            } elseif ($value instanceof MongoDB\BSON\UTCDateTime) {
                $array[$key] = date('Y-m-d H:i:s', $value->toDateTime()->getTimestamp());
            } elseif ($value instanceof MongoDB\BSON\ObjectId) {
                $array[$key] = (string)$value;
            } elseif (is_object($value)) {
                $array[$key] = (array)$value;
            } else {
                $array[$key] = $value;
            }
        }
        
        return $array;
    }
}
