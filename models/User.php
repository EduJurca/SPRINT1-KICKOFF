<?php

class User {
    private $db;
    
    public function __construct($dbConnection = null) {
        $this->db = $dbConnection ?? Database::getMariaDBConnection();
    }
    
    public function findByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.password, u.role_id, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.username = ?
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findByUsernameOrEmail($username, $email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO users 
            (username, fullname, nationality_id, phone, birth_date, sex, dni, address, email, password, iban, driver_license_photo, role_id, lang, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $username = $data['username'] ?? null;
        $fullname = $data['fullname'] ?? null;
        $nationality_id = $data['nationality_id'] ?? null;
        $phone = $data['phone'] ?? null;
        $birth_date = $data['fecha_nacimiento'] ?? null;
        $sex = $data['sex'] ?? null;
        $dni = $data['dni'] ?? null;
        $address = $data['address'] ?? null;
        $iban = $data['iban'] ?? null;
        $email = $data['email'] ?? null;
        $driver_license_photo = $data['driver_license_photo'] ?? null;
        $role_id = isset($data['role_id']) ? (int)$data['role_id'] : 3;
        $lang = $data['lang'] ?? 'ca';
        $created_at = date('Y-m-d H:i:s');
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->bind_param(
            "ssisssssssssiss",
            $username,
            $fullname,
            $nationality_id,
            $phone,
            $birth_date,
            $sex,
            $dni,
            $address,
            $email,
            $password_hash,
            $iban,
            $driver_license_photo,
            $role_id,
            $lang,
            $created_at
        );

        return $stmt->execute();
    }

    /**
     * Obtenir perfil d'usuari
     * 
     * @param int $user_id ID de l'usuari
     * @return array|null Dades del perfil
     */
    public function getProfile($user_id) {
        $stmt = $this->db->prepare("SELECT fullname, dni, phone, birth_date AS birthdate, address, sex FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Actualitzar perfil d'usuari
     * 
     * @param int $user_id ID de l'usuari
     * @param array $data Dades a actualitzar
     * @return bool Èxit de l'operació
     */
    public function updateProfile($user_id, $data) {
        $stmt = $this->db->prepare("UPDATE users SET fullname = ?, dni = ?, phone = ?, birth_date = ?, address = ?, sex = ? WHERE id = ?");

        // Normalitzar camps
        $birthdate = !empty($data['birthdate']) ? $data['birthdate'] : null;
        
        // Normalitzar valor de sex a 'M', 'F', 'O', o NULL
        $sex = isset($data['sex']) ? strtoupper($data['sex']) : null;
        if (!in_array($sex, ['M', 'F', 'O'])) {
            $sex = null;
        }

        $stmt->bind_param(
            'ssssssi',
            $data['fullname'],
            $data['dni'],
            $data['phone'],
            $birthdate,
            $data['address'],
            $sex,
            $user_id
        );

        return $stmt->execute();
    }

    public function getUserInfo($user_id) {
        $stmt = $this->db->prepare("SELECT username, email, role_id FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getAll($limit = 20, $offset = 0, $search = '') {
        if (!empty($search)) {
            $stmt = $this->db->prepare("
                SELECT u.id, u.username, u.email, u.fullname, u.role_id, r.name as role_name, u.created_at 
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.username LIKE ? OR u.email LIKE ? OR u.fullname LIKE ?
                ORDER BY u.id DESC 
                LIMIT ? OFFSET ?
            ");
            $searchParam = "%$search%";
            $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $limit, $offset);
        } else {
            $stmt = $this->db->prepare("
                SELECT u.id, u.username, u.email, u.fullname, u.role_id, r.name as role_name, u.created_at 
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                ORDER BY u.id DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ii", $limit, $offset);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Comptar usuaris totals
     */
    public function count($search = '') {
        if (!empty($search)) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM users 
                WHERE username LIKE ? OR email LIKE ? OR fullname LIKE ?
            ");
            $searchParam = "%$search%";
            $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users");
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
    
    /**
     * Actualitzar usuari (Admin)
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE users SET 
                username = ?, 
                email = ?, 
                fullname = ?, 
                phone = ?, 
                role_id = ?
            WHERE id = ?
        ");
        
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $fullname = $data['fullname'] ?? '';
        $phone = $data['phone'] ?? '';
        $role_id = isset($data['role_id']) ? (int)$data['role_id'] : 3;
        
        $stmt->bind_param("sssiii", $username, $email, $fullname, $phone, $role_id, $id);
        return $stmt->execute();
    }
    
    public function delete($id) {
        // No permetre eliminar l'usuari amb ID 1
        if ($id == 1) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function getAllRoles() {
        $stmt = $this->db->prepare("SELECT id, name, description FROM roles ORDER BY id");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getRoleById($roleId) {
        $stmt = $this->db->prepare("SELECT id, name, description FROM roles WHERE id = ?");
        $stmt->bind_param("i", $roleId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getUsersByRole($roleId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email, u.fullname, u.created_at 
            FROM users u 
            WHERE u.role_id = ?
            ORDER BY u.id DESC
        ");
        $stmt->bind_param("i", $roleId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtenir historial de viatges de l'usuari
     * 
     * @param int $userId ID de l'usuari
     * @return array Historial de viatges amb informació del vehicle
     */
    public function getUserHistory($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                vu.id,
                vu.start_time,
                vu.end_time,
                vu.total_distance_km,
                TIMESTAMPDIFF(MINUTE, vu.start_time, vu.end_time) as duration_minutes,
                v.id as vehicle_id,
                v.plate as vehicle_plate,
                v.brand as vehicle_brand,
                v.model as vehicle_model,
                v.image_url as vehicle_image,
                l1.name as start_location_name,
                l1.address as start_location_address,
                l1.latitude as start_latitude,
                l1.longitude as start_longitude,
                l2.name as end_location_name,
                l2.address as end_location_address,
                l2.latitude as end_latitude,
                l2.longitude as end_longitude
            FROM vehicle_usage vu
            INNER JOIN vehicles v ON vu.vehicle_id = v.id
            LEFT JOIN locations l1 ON vu.start_location_id = l1.id
            LEFT JOIN locations l2 ON vu.end_location_id = l2.id
            WHERE vu.user_id = ?
            ORDER BY vu.start_time DESC
            LIMIT 50
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return $result;
    }
}
