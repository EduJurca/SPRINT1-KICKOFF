<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Vehicle.php';

class FakeVehicleResult {
    private $rows;
    public $num_rows;

    // Initialize fake result with provided rows
    public function __construct($rows) {
        $this->rows = $rows;
        $this->num_rows = is_array($rows) ? count($rows) : ($rows ? 1 : 0);
    }

    // Return all rows as an array (simulates mysqli_result::fetch_all)
    public function fetch_all($flag) {
        return $this->rows ?: [];
    }

    // Return first row as associative array (simulates mysqli_result::fetch_assoc)
    public function fetch_assoc() {
        return ($this->rows && count($this->rows) > 0) ? $this->rows[0] : null;
    }
}

class FakeVehicleStmt {
    private $rows;
    private $execReturn;
    private $expectedBindCount;
    public $boundTypes;
    public $boundValues;

    // Initialize fake statement with rows, execution result and optional expected bind count
    public function __construct($rows = null, $execReturn = true, $expectedBindCount = null) {
        $this->rows = $rows;
        $this->execReturn = $execReturn;
        $this->expectedBindCount = $expectedBindCount;
        $this->boundTypes = null;
        $this->boundValues = [];
    }

    // Capture and validate bound parameters and types
    public function bind_param(...$args) {
        $this->boundTypes = array_shift($args);
        $this->boundValues = $args;

        // Basic validation: if expected count is provided, ensure it matches
        if ($this->expectedBindCount !== null && count($this->boundValues) !== $this->expectedBindCount) {
            // mark execution as failed if bind count mismatch
            $this->execReturn = false;
        }

        // If no explicit expected count provided, validate against the types string if present
        if ($this->expectedBindCount === null && is_string($this->boundTypes)) {
            $typeCount = strlen($this->boundTypes);
            if ($typeCount !== count($this->boundValues)) {
                $this->execReturn = false;
            } else {
                // validate allowed type characters (common ones)
                if (preg_match('/^[sidxb]+$/', $this->boundTypes) !== 1) {
                    $this->execReturn = false;
                }
            }
        }

        return true;
    }

    // Simulate executing the prepared statement, returns configured exec result
    public function execute() {
        return $this->execReturn;
    }

    // Return a FakeResult wrapping configured rows
    public function get_result() {
        return new FakeVehicleResult($this->rows);
    }
}

class FakeVehicleDB {
    private $mapping;
    public $lastPreparedSql = null;
    public $lastStmt = null;
    public $insert_id = 0;

    public function __construct($mapping = []) {
        // mapping can contain keys: by_id, all, available, execute_result, insert_id
        // or a special key 'expect_sql' => array of patterns => [rows, execReturn, expectedBindCount]
        $this->mapping = $mapping;
        $this->insert_id = $mapping['insert_id'] ?? 0;
    }

    // Prepare a fake statement based on SQL pattern matches and mapping
    public function prepare($sql) {
        $this->lastPreparedSql = $sql;

        // If explicit expectations provided, try to match
        if (!empty($this->mapping['expect_sql']) && is_array($this->mapping['expect_sql'])) {
            foreach ($this->mapping['expect_sql'] as $pattern => $cfg) {
                if (strpos($sql, $pattern) !== false) {
                    $rows = $cfg[0] ?? null;
                    $exec = array_key_exists(1, $cfg) ? $cfg[1] : true;
                    $expectedBindCount = $cfg[2] ?? null;
                    $stmt = new FakeVehicleStmt($rows, $exec, $expectedBindCount);
                    $this->lastStmt = $stmt;
                    return $stmt;
                }
            }
        }

        // Fallback behavior for common queries used in tests
        if (strpos($sql, 'WHERE id = ?') !== false || strpos($sql, 'WHERE v.id = ?') !== false) {
            $rows = $this->mapping['by_id'] ?? null;
            $stmt = new FakeVehicleStmt($rows, true, null);
            $this->lastStmt = $stmt;
            return $stmt;
        }

        if (strpos($sql, "status != 'maintenance'") !== false) {
            $rows = $this->mapping['available'] ?? [];
            $stmt = new FakeVehicleStmt($rows);
            $this->lastStmt = $stmt;
            return $stmt;
        }

        if (strpos($sql, 'FROM vehicles v') !== false && strpos($sql, 'WHERE') === false) {
            $rows = $this->mapping['all'] ?? [];
            $stmt = new FakeVehicleStmt($rows);
            $this->lastStmt = $stmt;
            return $stmt;
        }

        // Generic insert/update/delete: return stmt reflecting execute_result if provided
        $exec = $this->mapping['execute_result'] ?? true;
        $stmt = new FakeVehicleStmt(null, $exec);
        $this->lastStmt = $stmt;
        return $stmt;
    }
}

class VehicleTest extends TestCase {

    // Create vehicle successfully with valid data
    public function testCreateVehicleSuccess() {
        $db = new FakeVehicleDB(['execute_result' => true, 'insert_id' => 42]);
        $vehicle = new Vehicle($db);

        $data = [
            'plate' => 'ABC1234',
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'year' => 2023,
            'vehicle_type' => 'car',
            'battery_level' => 100,
            'status' => 'available',
            'price_per_minute' => 0.45
        ];

        $result = $vehicle->create($data);
        $this->assertEquals(42, $result);
    }

    // Fail to create vehicle when DB execution fails
    public function testCreateVehicleDbFailure() {
        $db = new FakeVehicleDB([
            'expect_sql' => [
                'INSERT INTO vehicles' => [null, false, 12]
            ]
        ]);
        $vehicle = new Vehicle($db);

        $data = [
            'plate' => 'ABC1234',
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'year' => 2023,
            'vehicle_type' => 'car'
        ];

        $this->assertFalse($vehicle->create($data));
    }

    // Get vehicle by ID returns vehicle data when found
    public function testGetVehicleByIdFound() {
        $row = [
            'id' => 1,
            'license_plate' => 'ABC1234',
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'year' => 2023,
            'battery' => 85,
            'latitude' => 40.7117,
            'longitude' => 0.5783,
            'status' => 'available',
            'vehicle_type' => 'car',
            'is_accessible' => 0,
            'price_per_minute' => 0.45
        ];

        $db = new FakeVehicleDB(['by_id' => [$row]]);
        $vehicle = new Vehicle($db);

        $result = $vehicle->getVehicleById(1);
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('ABC1234', $result['license_plate']);
        $this->assertEquals('Tesla', $result['brand']);
        $this->assertArrayHasKey('location', $result);
        $this->assertEquals(40.7117, $result['location']['lat']);
    }

    // Get vehicle by ID returns null when not found
    public function testGetVehicleByIdNotFound() {
        $db = new FakeVehicleDB(['by_id' => null]);
        $vehicle = new Vehicle($db);

        $this->assertNull($vehicle->getVehicleById(99999));
    }

    // Update vehicle successfully
    public function testUpdateVehicleSuccess() {
        $db = new FakeVehicleDB(['execute_result' => true]);
        $vehicle = new Vehicle($db);

        $data = [
            'plate' => 'ABC1234',
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'year' => 2023,
            'battery_level' => 90,
            'latitude' => 40.7117,
            'longitude' => 0.5783,
            'status' => 'available',
            'vehicle_type' => 'car',
            'is_accessible' => 0,
            'price_per_minute' => 0.45,
            'image_url' => null
        ];

        $this->assertTrue($vehicle->update(1, $data));
    }

    // Delete vehicle successfully when not in use
    public function testDeleteVehicleSuccess() {
        $db = new FakeVehicleDB([
            'expect_sql' => [
                'SELECT status FROM vehicles WHERE id = ?' => [[['status' => 'available']], true, 1],
                'DELETE FROM vehicles WHERE id = ?' => [null, true, 1]
            ]
        ]);

        $vehicle = new Vehicle($db);
        $this->assertTrue($vehicle->delete(1));
    }

    // Delete vehicle fails when vehicle is in use
    public function testDeleteVehicleInUse() {
        $db = new FakeVehicleDB([
            'expect_sql' => [
                'SELECT status FROM vehicles WHERE id = ?' => [[['status' => 'in_use']], true, 1]
            ]
        ]);

        $vehicle = new Vehicle($db);
        $this->assertFalse($vehicle->delete(1));
    }

    // Get all available vehicles
    public function testGetAvailableVehicles() {
        $rows = [
            [
                'id' => 1,
                'license_plate' => 'ABC1234',
                'brand' => 'Tesla',
                'model' => 'Model 3',
                'status' => 'available'
            ],
            [
                'id' => 2,
                'license_plate' => 'XYZ5678',
                'brand' => 'BMW',
                'model' => 'i3',
                'status' => 'available'
            ]
        ];

        $db = new FakeVehicleDB(['available' => $rows]);
        $vehicle = new Vehicle($db);

        $result = $vehicle->getAvailableVehicles();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    // Validate vehicle data - valid case
    public function testValidateValidData() {
        $db = new FakeVehicleDB();
        $vehicle = new Vehicle($db);

        $data = [
            'plate' => 'ABC1234',
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'year' => 2023,
            'vehicle_type' => 'car',
            'status' => 'available'
        ];

        $errors = $vehicle->validate($data);
        $this->assertEmpty($errors);
    }

    // Validate vehicle data - missing plate
    public function testValidateMissingPlate() {
        $db = new FakeVehicleDB();
        $vehicle = new Vehicle($db);

        $data = [
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'year' => 2023,
            'vehicle_type' => 'car',
            'status' => 'available'
        ];

        $errors = $vehicle->validate($data);
        $this->assertNotEmpty($errors);
        $this->assertContains('La matrícula es obligatoria', $errors);
    }
}
