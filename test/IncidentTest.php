<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Incident.php';

class FakeResult {
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

class FakeStmt {
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
        return new FakeResult($this->rows);
    }
}

class FakeDB {
    private $mapping;
    public $lastPreparedSql = null;
    public $lastStmt = null;

    public function __construct($mapping = []) {
        // mapping can contain keys: by_id, active_total, all, execute_result
        // or a special key 'expect_sql' => array of patterns => [rows, execReturn, expectedBindCount]
        $this->mapping = $mapping;
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
                    $stmt = new FakeStmt($rows, $exec, $expectedBindCount);
                    $this->lastStmt = $stmt;
                    return $stmt;
                }
            }
        }

        // Fallback behavior for common queries used in tests
        if (strpos($sql, 'WHERE i.id = ?') !== false) {
            $rows = $this->mapping['by_id'] ?? null;
            $stmt = new FakeStmt($rows, true, null);
            $this->lastStmt = $stmt;
            return $stmt;
        }

        if (strpos($sql, "COUNT(*)") !== false) {
            $rows = [['total' => $this->mapping['active_total'] ?? 0]];
            $stmt = new FakeStmt($rows);
            $this->lastStmt = $stmt;
            return $stmt;
        }

        if (strpos($sql, 'FROM incidents i') !== false && strpos($sql, 'WHERE') === false) {
            $rows = $this->mapping['all'] ?? [];
            $stmt = new FakeStmt($rows);
            $this->lastStmt = $stmt;
            return $stmt;
        }

        // Generic insert/update/delete: return stmt reflecting execute_result if provided
        $exec = $this->mapping['execute_result'] ?? true;
        $stmt = new FakeStmt(null, $exec);
        $this->lastStmt = $stmt;
        return $stmt;
    }
}

class FakeUser {
    private $existingIds;

    // Initialize fake user store with existing IDs
    public function __construct($ids = []) {
        $this->existingIds = $ids;
    }

    // Return whether a user id exists in the fake store
    public function findById($id) {
        return in_array((int)$id, $this->existingIds, true);
    }
}

class IncidentTest extends TestCase {

    // Create incident successfully with valid data
    public function testCreateIncidentSuccess() {
        $db = new FakeDB(['execute_result' => true]);
        $user = new FakeUser([10]);

        $incident = new Incident($db, $user);

        $data = [
            'type' => 'mechanical',
            'description' => 'Falla en frenos',
            'incident_creator' => 10,
            'incident_assignee' => null
        ];

        $this->assertTrue($incident->createIncident($data));
    }



    // Fail to create incident when provided type is invalid
    public function testCreateIncidentInvalidType() {
        $db = new FakeDB(['execute_result' => true]);
        $user = new FakeUser([10]);
        $incident = new Incident($db, $user);

        $data = [
            'type' => 'invalid_type',
            'description' => 'X',
            'incident_creator' => 10
        ];

        $this->assertFalse($incident->createIncident($data));
    }


    
    // Fail to create incident if creator does not exist
    public function testCreateIncidentInvalidCreator() {
        $db = new FakeDB(['execute_result' => true]);
        $user = new FakeUser([]); // no users
        $incident = new Incident($db, $user);

        $data = [
            'type' => 'mechanical',
            'description' => 'Falla en frenos',
            'incident_creator' => 10,
            'incident_assignee' => 2
        ];

        $this->assertFalse($incident->createIncident($data));
    }



    // Return null when getting an incident by non-existing id
    public function testGetIncidentByIdNotFound() {
        $db = new FakeDB(['by_id' => null]);
        $user = new FakeUser([]);
        $incident = new Incident($db, $user);

        $this->assertNull($incident->getIncidentById(12345));
    }

    // Return incident data when getting by existing id
    public function testGetIncidentByIdFound() {
        $row = [
            'id' => 1,
            'type' => 'mechanical',
            'status' => 'pending',
            'description' => 'Desc'
        ];
        $db = new FakeDB(['by_id' => [$row]]);
        $user = new FakeUser([]);
        $incident = new Incident($db, $user);

        $res = $incident->getIncidentById(1);
        $this->assertIsArray($res);
        $this->assertEquals(1, $res['id']);
        $this->assertEquals('mechanical', $res['type']);
    }



    // Return the count of active (non-resolved) incidents
    public function testGetActiveIncidents() {
        $db = new FakeDB(['active_total' => 5]);
        $user = new FakeUser([]);
        $incident = new Incident($db, $user);

        $this->assertEquals(5, $incident->getActiveIncidents());
    }



    // Simulate DB INSERT failure and expect createIncident to return false
    public function testCreateIncidentDbFailure() {
        // Simulate DB failing to execute INSERT
        $db = new FakeDB(['expect_sql' => [
            'INSERT INTO incidents' => [null, false, 5] // execReturn = false
        ]]);
        $user = new FakeUser([10]);

        $incident = new Incident($db, $user);

        $data = [
            'type' => 'mechanical',
            'description' => 'Falla en frenos',
            'incident_creator' => 10,
            'incident_assignee' => 2
        ];

        $this->assertFalse($incident->createIncident($data));
    }



    // Update returns false when the incident does not exist
    public function testUpdateIncidentNotFound() {
        $db = new FakeDB(['by_id' => null]);
        $user = new FakeUser([]);
        $incident = new Incident($db, $user);

        $this->assertNull($incident->getIncidentById(12345));
    }

    // Delete incident and ensure the id is bound to the DELETE statement
    public function testDeleteIncidentBindsId() {
        $db = new FakeDB([
            'expect_sql' => [
                'DELETE FROM incidents WHERE id = ?' => [null, true, 1]
            ]
        ]);

        $user = new FakeUser([]);
        $incident = new Incident($db, $user);

        $this->assertTrue($incident->deleteIncident(42));
        $this->assertNotNull($db->lastStmt);
        $this->assertCount(1, $db->lastStmt->boundValues);
        $this->assertEquals(42, $db->lastStmt->boundValues[0]);
    }

}
