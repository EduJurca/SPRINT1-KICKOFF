<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Incident.php';

class FakeResult {
    private $rows;
    public $num_rows;

    public function __construct($rows) {
        $this->rows = $rows;
        $this->num_rows = is_array($rows) ? count($rows) : ($rows ? 1 : 0);
    }

    public function fetch_all($flag) {
        return $this->rows ?: [];
    }

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

    public function __construct($rows = null, $execReturn = true, $expectedBindCount = null) {
        $this->rows = $rows;
        $this->execReturn = $execReturn;
        $this->expectedBindCount = $expectedBindCount;
        $this->boundTypes = null;
        $this->boundValues = [];
    }

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

    public function execute() {
        return $this->execReturn;
    }

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

    public function __construct($ids = []) {
        $this->existingIds = $ids;
    }

    public function findById($id) {
        return in_array((int)$id, $this->existingIds, true);
    }
}





// TODO:  
class IncidentTest extends TestCase {

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


    
    public function testCreateIncidentInvalidCreator() {
        $db = new FakeDB(['execute_result' => true]);
        $user = new FakeUser([]); // no users
        $incident = new Incident($db, $user);

        $data = [
            'type' => 'mechanical',
            'description' => 'X',
            'incident_creator' => 999
        ];

        $this->assertFalse($incident->createIncident($data));
    }



    public function testGetIncidentByIdNotFound() {
        $db = new FakeDB(['by_id' => null]);
        $user = new FakeUser([]);
        $incident = new Incident($db, $user);

        $this->assertNull($incident->getIncidentById(12345));
    }



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



    public function testGetActiveIncidents() {
        $db = new FakeDB(['active_total' => 5]);
        $user = new FakeUser([]);
        $incident = new Incident($db, $user);

        $this->assertEquals(5, $incident->getActiveIncidents());
    }



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



    public function testCreateIncidentBindsValues() {
        // Expect the INSERT and capture bound values
        $db = new FakeDB(['expect_sql' => [
            'INSERT INTO incidents' => [null, true, 5]
        ]]);
        $user = new FakeUser([10,2]);

        $incident = new Incident($db, $user);

        $data = [
            'type' => 'mechanical',
            'description' => 'Falla en frenos',
            'notes' => 'Nota breve',
            'incident_creator' => 10,
            'incident_assignee' => 2
        ];

        $this->assertTrue($incident->createIncident($data));

        // lastStmt should contain boundValues captured by our FakeStmt
        $this->assertNotNull($db->lastStmt, 'Expected lastStmt to be set');
        $bound = $db->lastStmt->boundValues;

        // bound order: type, description, notes, incident_creator, incident_assignee
        $this->assertCount(5, $bound);
        $this->assertEquals('mechanical', $bound[0]);
        $this->assertStringContainsString('Falla en frenos', $bound[1]);
        $this->assertStringContainsString('Nota breve', $bound[2]);
        $this->assertEquals(10, $bound[3]);
        $this->assertEquals(2, $bound[4]);
    }



    public function testUpdateIncidentNotFound() {
        $db = new FakeDB(['by_id' => null]);
        $user = new FakeUser([]);
        $incident = new Incident($db, $user);

        $this->assertFalse($incident->updateIncident(123, ['description' => 'X']));
    }



    public function testUpdateIncidentSuccessAndBinds() {
        $existing = [
            'id' => 5,
            'type' => 'mechanical',
            'status' => 'pending',
        ];

        $db = new FakeDB([
            'by_id' => [$existing],
            'expect_sql' => [
                'UPDATE incidents SET' => [null, true, 2]
            ]
        ]);

        $user = new FakeUser([]);
        $incident = new Incident($db, $user);

        $this->assertTrue($incident->updateIncident(5, ['description' => 'Cambio']));

        $this->assertNotNull($db->lastStmt);
        $bound = $db->lastStmt->boundValues;
        $this->assertCount(2, $bound);
        $this->assertEquals('Cambio', $bound[0]);
        $this->assertEquals(5, $bound[1]);

        // Validate the generated UPDATE SQL contains the expected SET and WHERE parts
        $this->assertNotNull($db->lastPreparedSql, 'Expected lastPreparedSql to be set');
        $this->assertStringContainsString('UPDATE incidents SET', $db->lastPreparedSql);
        $this->assertStringContainsString('description = ?', $db->lastPreparedSql);
        $this->assertStringContainsString('WHERE id = ?', $db->lastPreparedSql);
    }


    
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
