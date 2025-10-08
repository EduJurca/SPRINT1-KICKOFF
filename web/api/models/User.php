<?php
/**
 * User Model
 * Handles user data operations with MariaDB
 * Implements 2025 security best practices with PDO prepared statements
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = getMariaDBConnection();
    }
    
    /**
     * Find user by ID
     * @param int $id User ID
     * @return array|null User data or null if not found
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, full_name, phone, license_number, role, created_at, updated_at
                FROM users
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
            
            return $user ?: null;
            
        } catch (PDOException $e) {
            error_log("Error finding user by ID: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Find user by email
     * @param string $email User email
     * @return array|null User data or null if not found
     */
    public function findByEmail($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, password_hash, full_name, phone, license_number, role, created_at, updated_at
                FROM users
                WHERE email = :email
            ");
            
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            return $user ?: null;
            
        } catch (PDOException $e) {
            error_log("Error finding user by email: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Create new user
     * @param array $data User data
     * @return int|false User ID or false on failure
     */
    public function create($data) {
        try {
            // Hash password using Argon2id (2025 best practice)
            $passwordHash = password_hash($data['password'], PASSWORD_ARGON2ID);
            
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password_hash, full_name, phone, license_number, role, created_at, updated_at)
                VALUES (:email, :password_hash, :full_name, :phone, :license_number, :role, NOW(), NOW())
            ");
            
            $result = $stmt->execute([
                'email' => $data['email'],
                'password_hash' => $passwordHash,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'] ?? null,
                'license_number' => $data['license_number'] ?? null,
                'role' => $data['role'] ?? 'user'
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            
            // Check for duplicate email
            if ($e->getCode() == 23000) {
                throw new Exception("Email already exists", 409);
            }
            
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Update user
     * @param int $id User ID
     * @param array $data User data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = ['id' => $id];
            
            // Build dynamic update query
            if (isset($data['email'])) {
                $fields[] = "email = :email";
                $params['email'] = $data['email'];
            }
            
            if (isset($data['full_name'])) {
                $fields[] = "full_name = :full_name";
                $params['full_name'] = $data['full_name'];
            }
            
            if (isset($data['phone'])) {
                $fields[] = "phone = :phone";
                $params['phone'] = $data['phone'];
            }
            
            if (isset($data['license_number'])) {
                $fields[] = "license_number = :license_number";
                $params['license_number'] = $data['license_number'];
            }
            
            if (isset($data['role'])) {
                $fields[] = "role = :role";
                $params['role'] = $data['role'];
            }
            
            if (isset($data['password'])) {
                $fields[] = "password_hash = :password_hash";
                $params['password_hash'] = password_hash($data['password'], PASSWORD_ARGON2ID);
            }
            
            if (empty($fields)) {
                return true; // Nothing to update
            }
            
            $fields[] = "updated_at = NOW()";
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            
            // Check for duplicate email
            if ($e->getCode() == 23000) {
                throw new Exception("Email already exists", 409);
            }
            
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Delete user
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get all users with pagination
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Users array
     */
    public function getAll($page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $stmt = $this->db->prepare("
                SELECT id, email, full_name, phone, license_number, role, created_at, updated_at
                FROM users
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error getting all users: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Get total user count
     * @return int Total count
     */
    public function count() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            return (int)$result['count'];
            
        } catch (PDOException $e) {
            error_log("Error counting users: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Authenticate user
     * @param string $email User email
     * @param string $password User password
     * @return array|false User data or false if authentication fails
     */
    public function authenticate($email, $password) {
        try {
            $user = $this->findByEmail($email);
            
            if (!$user) {
                return false;
            }
            
            // Verify password using password_verify (2025 best practice)
            if (!password_verify($password, $user['password_hash'])) {
                return false;
            }
            
            // Check if password needs rehashing (algorithm updated)
            if (password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID)) {
                $newHash = password_hash($password, PASSWORD_ARGON2ID);
                $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
                $stmt->execute(['hash' => $newHash, 'id' => $user['id']]);
            }
            
            // Remove password hash from returned data
            unset($user['password_hash']);
            
            return $user;
            
        } catch (PDOException $e) {
            error_log("Error authenticating user: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Check if email exists
     * @param string $email Email to check
     * @return bool True if exists
     */
    public function emailExists($email) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetch();
            
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error checking email existence: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
    
    /**
     * Search users by query
     * @param string $query Search query
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Users array
     */
    public function search($query, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            $searchTerm = "%{$query}%";
            
            $stmt = $this->db->prepare("
                SELECT id, email, full_name, phone, license_number, role, created_at, updated_at
                FROM users
                WHERE email LIKE :query OR full_name LIKE :query
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error searching users: " . $e->getMessage());
            throw new Exception("Database error", 500);
        }
    }
}
