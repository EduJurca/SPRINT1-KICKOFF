<?php
require_once __DIR__ . '/../core/DatabaseMariaDB.php';

class User {
    // Login
    public static function findByUsername($username) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Register
    public static function findByUsernameOrEmail($username, $email) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function create($data) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("INSERT INTO users 
            (username, nationality_id, phone, birth_date, email, password, iban, driver_license_photo, minute_balance, is_admin, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $nationality_id = $data['nationality_id'] ?? null;
        $phone = $data['phone'] ?? null;
        $birth_date = $data['fecha_nacimiento'] ?? null;
        $iban = $data['iban'] ?? null;
        $driver_license_photo = $data['driver_license_photo'] ?? null;
        $minute_balance = 0;
        $is_admin = 0;
        $created_at = date('Y-m-d H:i:s');
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->bind_param(
            "sisssssssis",
            $data['username'],
            $nationality_id,
            $phone,
            $birth_date,
            $data['email'],
            $password_hash,
            $iban,
            $driver_license_photo,
            $minute_balance,
            $is_admin,
            $created_at
        );

        return $stmt->execute();
    }

    // Perfil
    public static function getProfile($user_id) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("SELECT fullname, dni,phone, birth_date AS birthdate, address, sex FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function updateProfile($user_id, $data) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("UPDATE users SET fullname = ?, dni = ?, phone = ?, birth_date = ?, address = ?, sex = ? WHERE id = ?");
        $stmt->bind_param(
            'sssssi',
            $data['fullname'],
            $data['dni'], 
            $data['phone'],
            $data['birthdate'],
            $data['address'],
            $data['sex'],
            $user_id
        );
        return $stmt->execute();
    }

    // Gestio
    public static function getUserInfo($user_id) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("SELECT username, email, minute_balance, is_admin FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}