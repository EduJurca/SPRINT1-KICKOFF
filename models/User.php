<?php
/**
 * 📊 User Model
 * Gestiona les operacions relacionades amb usuaris
 */

class User {
    private $db;
    
    public function __construct($dbConnection = null) {
        $this->db = $dbConnection ?? Database::getMariaDBConnection();
    }
    
    /**
     * Buscar usuari per nom d'usuari (Login)
     * 
     * @param string $username Nom d'usuari
     * @return array|null Dades de l'usuari
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Buscar usuari per nom d'usuari o email (Registre)
     * 
     * @param string $username Nom d'usuari
     * @param string $email Email
     * @return array|null Dades de l'usuari
     */
    public function findByUsernameOrEmail($username, $email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Crear nou usuari
     * 
     * @param array $data Dades de l'usuari
     * @return bool Èxit de l'operació
     */
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO users 
            (username, nationality_id, phone, birth_date, sex, dni, address, email, password, iban, driver_license_photo, minute_balance, is_admin, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $username = $data['username'] ?? null;
        $nationality_id = $data['nationality_id'] ?? null;
        $phone = $data['phone'] ?? null;
        $birth_date = $data['fecha_nacimiento'] ?? null;
        $sex = $data['sex'] ?? null;
        $dni = $data['dni'] ?? null;
        $address = $data['address'] ?? null;
        $iban = $data['iban'] ?? null;
        $email = $data['email'] ?? null;
        $driver_license_photo = $data['driver_license_photo'] ?? null;
        $minute_balance = 0;
        $is_admin = 0;
        $created_at = date('Y-m-d H:i:s');
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->bind_param(
            "sisssssssssiis",
            $username,
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
            $minute_balance,
            $is_admin,
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

    /**
     * Obtenir informació de l'usuari (gestió)
     * 
     * @param int $user_id ID de l'usuari
     * @return array|null Informació de l'usuari
     */
    public function getUserInfo($user_id) {
        $stmt = $this->db->prepare("SELECT username, email, minute_balance, is_admin FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Buscar usuari per ID
     * 
     * @param int $id ID de l'usuari
     * @return array|null Dades de l'usuari
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Afegir minuts al balanç de l'usuari
     * 
     * @param int $user_id ID de l'usuari
     * @param int $minutes Minuts a afegir
     * @return bool Èxit de l'operació
     */
    public function addMinutes($user_id, $minutes) {
        $stmt = $this->db->prepare("UPDATE users SET minute_balance = minute_balance + ? WHERE id = ?");
        $stmt->bind_param("ii", $minutes, $user_id);
        return $stmt->execute();
    }
    
    /**
     * Obtenir balanç de minuts de l'usuari
     * 
     * @param int $user_id ID de l'usuari
     * @return int|null Balanç de minuts
     */
    public function getMinuteBalance($user_id) {
        $stmt = $this->db->prepare("SELECT minute_balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? (int)$result['minute_balance'] : null;
    }
}
