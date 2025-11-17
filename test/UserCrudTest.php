<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/User.php';

// Some environments running tests may not have mysqli constants defined — define fallbacks
if (!defined('MYSQLI_ASSOC')) {
    define('MYSQLI_ASSOC', 1);
}

# --- Fake DB helpers (copied/adjusted from IncidentTest) ---
if (!class_exists('FakeResult')) {
class FakeResult {
    private $rows;
    public $num_rows;

    public function __construct($rows) {
        $this->rows = $rows;
        $this->num_rows = is_array($rows) ? count($rows) : ($rows ? 1 : 0);
    }

    public function fetch_all($flag = MYSQLI_ASSOC) {
        return $this->rows ?: [];
    }

    public function fetch_assoc() {
        return ($this->rows && count($this->rows) > 0) ? $this->rows[0] : null;
    }
}

}

if (!class_exists('FakeStmt')) {
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

        if ($this->expectedBindCount !== null && count($this->boundValues) !== $this->expectedBindCount) {
            $this->execReturn = false;
        }

        if ($this->expectedBindCount === null && is_string($this->boundTypes)) {
            $typeCount = strlen($this->boundTypes);
            if ($typeCount !== count($this->boundValues)) {
                $this->execReturn = false;
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

}

if (!class_exists('FakeDB')) {
class FakeDB {
    private $mapping;
    public $lastPreparedSql = null;
    public $lastStmt = null;

    public function __construct($mapping = []) {
        $this->mapping = $mapping;
    }

    public function prepare($sql) {
        $this->lastPreparedSql = $sql;

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

        if (strpos($sql, 'SELECT id FROM users') !== false) {
            $rows = $this->mapping['existing'] ?? null;
            $stmt = new FakeStmt($rows);
            $this->lastStmt = $stmt;
            return $stmt;
        }

        if (strpos($sql, "COUNT(*)") !== false) {
            $rows = [['total' => $this->mapping['count'] ?? 0]];
            $stmt = new FakeStmt($rows);
            $this->lastStmt = $stmt;
            return $stmt;
        }

        if (strpos($sql, 'FROM users u') !== false && strpos($sql, 'LIKE') === false) {
            $rows = $this->mapping['all'] ?? [];
            $stmt = new FakeStmt($rows);
            $this->lastStmt = $stmt;
            return $stmt;
        }

        // Default: return a statement which executes with the mapping's execute_result
        $exec = $this->mapping['execute_result'] ?? true;
        $stmt = new FakeStmt(null, $exec);
        $this->lastStmt = $stmt;
        return $stmt;
    }
}

}

class UserCrudTest extends TestCase {

    public function testFindByUsernameFound() {
        $row = ['id'=>5, 'username'=>'alice', 'password'=>'h', 'role_id'=>3, 'role_name'=>'Client'];
        // pass an array-of-rows to fake DB
        $db = new FakeDB(['expect_sql' => [ 'WHERE u.username =' => [[$row]] ]]);
        $user = new User($db);

        $res = $user->findByUsername('alice');
        $this->assertIsArray($res);
        $this->assertEquals('alice', $res['username']);
    }

    public function testFindByUsernameOrEmailExists() {
        $db = new FakeDB(['expect_sql' => [ 'SELECT id FROM users' => [[['id'=>2]]] ]]);
        $user = new User($db);

        $res = $user->findByUsernameOrEmail('bob','bob@example.com');
        $this->assertIsArray($res);
        $this->assertEquals(2, $res['id']);
    }

    public function testCreateUserSuccess() {
        // Expect INSERT with 16 bind params (as per model)
        $db = new FakeDB(['expect_sql' => [ 'INSERT INTO users' => [null, true, 16] ]]);
        $user = new User($db);

        $data = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Secret123!',
            'fullname' => 'Test User',
            'phone' => '123456789',
            'nationality_id' => null,
            'fecha_nacimiento' => null,
            'sex' => 'F',
            'dni' => null,
            'address' => null,
            'iban' => null,
            'driver_license_photo' => null,
            'lang' => 'ca'
        ];

        $this->assertTrue($user->create($data));
        $this->assertNotNull($db->lastStmt);
        $this->assertCount(16, $db->lastStmt->boundValues);
    }

    public function testCreateUserDuplicate() {
        $db = new FakeDB(['expect_sql' => ['SELECT id FROM users' => [[['id'=>3]]]]]);
        $user = new User($db);

        $this->assertNotEmpty($user->findByUsernameOrEmail('exists','exists@example.com'));
    }

    public function testUpdateUserSuccess() {
        $db = new FakeDB(['expect_sql' => ['UPDATE users SET' => [null, true, 6]]]);
        $user = new User($db);

        $id = 10;
        $data = ['username'=>'u','email'=>'e@e.com','fullname'=>'U','phone'=>'123','role_id'=>3];
        $this->assertTrue($user->update($id, $data));
        $this->assertNotNull($db->lastStmt);
        $this->assertCount(6, $db->lastStmt->boundValues);
    }

    public function testDeleteUserCannotDeleteAdmin() {
        $db = new FakeDB(['execute_result' => true]);
        $user = new User($db);
        $this->assertFalse($user->delete(1));
    }

    public function testDeleteUserSuccess() {
        $db = new FakeDB(['expect_sql' => ['DELETE FROM users WHERE id = ?' => [null, true, 1]]]);
        $user = new User($db);
        $this->assertTrue($user->delete(42));
        $this->assertNotNull($db->lastStmt);
        $this->assertCount(1, $db->lastStmt->boundValues);
        $this->assertEquals(42, $db->lastStmt->boundValues[0]);
    }

    public function testGetAllNoSearch() {
        $rows = [
            ['id'=>1,'username'=>'a'],
            ['id'=>2,'username'=>'b']
        ];
        // Make sure we return rows when the SQL contains the users table
        $db = new FakeDB(['expect_sql' => ['FROM users u' => [$rows]]]);
        $user = new User($db);

        $res = $user->getAll(10,0);
        $this->assertCount(2, $res);
    }

    public function testCountSearch() {
        // Use expect_sql to return COUNT(*) result
        $db = new FakeDB(['expect_sql' => ["COUNT(*)" => [[['total' => 7]]]]]);
        $user = new User($db);

        $this->assertEquals(7, $user->count('foo'));
    }
    
    public function testGetProfileFound() {
        $row = ['fullname'=>'Test','dni'=>'123','phone'=>'555','birthdate'=>'1990-01-01','address'=>'X','sex'=>'M'];
        $db = new FakeDB(['expect_sql' => ['SELECT fullname, dni' => [[$row]]]]);
        $user = new User($db);
        $res = $user->getProfile(10);
        $this->assertIsArray($res);
        $this->assertEquals('Test', $res['fullname']);
    }
    
    public function testGetProfileNotFound() {
        $db = new FakeDB(['expect_sql' => ['SELECT fullname, dni' => [null]]]);
        $user = new User($db);
        $this->assertNull($user->getProfile(999));
    }
    
    public function testUpdateProfileSuccess() {
        $db = new FakeDB(['expect_sql' => ['UPDATE users SET' => [null, true, 7]]]);
        $user = new User($db);
        $this->assertTrue($user->updateProfile(10, ['fullname'=>'A','dni'=>'D','phone'=>'P','birthdate'=>'1990-01-01','address'=>'addr','sex'=>'M']));
        $this->assertCount(7, $db->lastStmt->boundValues);
    }
    
    public function testGetUserInfo() {
        $row = ['username'=>'u','email'=>'e@e','minute_balance'=>5,'role_id'=>3];
        $db = new FakeDB(['expect_sql' => ['SELECT username, email' => [[$row]]]]);
        $user = new User($db);
        $res = $user->getUserInfo(10);
        $this->assertEquals('u', $res['username']);
        $this->assertEquals(5, $res['minute_balance']);
    }
    
    public function testAddMinutesAndBalance() {
        $db = new FakeDB(['expect_sql' => ['UPDATE users SET minute_balance' => [null, true, 2]]]);
        $user = new User($db);
        $this->assertTrue($user->addMinutes(10, 15));
        $this->assertCount(2, $db->lastStmt->boundValues);
        
        // get minute balance
        $db = new FakeDB(['expect_sql' => ['SELECT minute_balance' => [[['minute_balance'=>25]]]]]);
        $user = new User($db);
        $this->assertEquals(25, $user->getMinuteBalance(10));
    }
    
    public function testGetAllRolesAndRoleById() {
        $rows = [['id'=>1,'name'=>'Admin','description'=>'x']];
        $db = new FakeDB(['expect_sql' => ['FROM roles' => [$rows]]]);
        $user = new User($db);
        $res = $user->getAllRoles();
        $this->assertCount(1, $res);
        
        $db = new FakeDB(['expect_sql' => ['WHERE id = ?' => [$rows]]]);
        $user = new User($db);
        $this->assertIsArray($user->getRoleById(1));
    }
    
    public function testGetUsersByRole() {
        $rows = [['id'=>2,'username'=>'cli','email'=>'c@example.com','fullname'=>'Cli','created_at'=>'2025-01-01']];
        $db = new FakeDB(['expect_sql' => ['WHERE u.role_id = ?' => [$rows]]]);
        $user = new User($db);
        $res = $user->getUsersByRole(3);
        $this->assertCount(1, $res);
    }
    
    public function testGetUserHistory() {
        $row = ['id'=>1,'start_time'=>'2025-01-01 10:00:00','end_time'=>'2025-01-01 10:30:00','total_distance_km'=>5,'duration_minutes'=>30,'vehicle_id'=>99,'vehicle_plate'=>'ABC','vehicle_brand'=>'X','vehicle_model'=>'Y','vehicle_image'=>null,'start_location_name'=>'S','end_location_name'=>'E'];
        $db = new FakeDB(['expect_sql' => ['FROM vehicle_usage vu' => [[$row]]]]);
        $user = new User($db);
        $res = $user->getUserHistory(5);
        $this->assertCount(1, $res);
        $this->assertEquals(99, $res[0]['vehicle_id']);
    }
}
