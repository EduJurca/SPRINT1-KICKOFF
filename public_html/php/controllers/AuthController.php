<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {

    public static function login($username, $password) {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 3600, // 1 hora
                'path' => '/',
                'domain' => '', // Dejar vacío para localhost
                'secure' => false, // false para HTTP, true para HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }

        $user = User::findByUsername($username);

        if (!$user) {
            return ['success' => false, 'msg' => 'User not found'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'msg' => 'Incorrect password'];
        }

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;

        return [
            'success' => true, 
            'msg' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'is_admin' => $user['is_admin'] ?? 0
            ],
            'session_id' => session_id(),
            'debug' => [
                'session_data' => $_SESSION,
                'cookie_params' => session_get_cookie_params()
            ]
        ];
    }

    public static function register($data) {
        if (!isset($data['username'], $data['password'], $data['email'])) {
            return ['success' => false, 'msg' => 'Missing required fields'];
        }

        if (User::findByUsernameOrEmail($data['username'], $data['email'])) {
            return ['success' => false, 'msg' => 'Username or email already exists'];
        }

        if (User::create($data)) {
            return ['success' => true, 'msg' => 'User registered successfully'];
        }

        return ['success' => false, 'msg' => 'Error registering user'];
    }

    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        return ['success' => true, 'msg' => 'Session closed'];
    }
