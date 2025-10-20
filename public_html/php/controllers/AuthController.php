<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {

    public static function login($username, $password) {
        // --- 1. Start session BEFORE any output ---
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session cookie settings (adjusted for localhost)
            session_set_cookie_params([
                'lifetime' => 3600, // 1 hora
                'path' => '/',
                'domain' => '', // Dejar vacío para localhost
                'secure' => false, // false para HTTP, true para HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            return ['success' => true, 'msg' => 'Login exitoso'];

        }

        // --- 2. Find user in the database ---
        $user = User::findByUsername($username);

        if (!$user) {
            return ['success' => false, 'msg' => 'User not found'];
        }

        // --- 3. Verify password ---
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'msg' => 'Incorrect password'];
        }

        // --- 4. Store user data in the session ---
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;

        // --- 5. Return result (no echo here) ---
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
        // --- Validate required fields ---
        if (!isset($data['username'], $data['password'], $data['email'])) {
            return ['success' => false, 'msg' => 'Missing required fields'];
        }

        // --- Check if username or email already exists ---
        if (User::findByUsernameOrEmail($data['username'], $data['email'])) {
            return ['success' => false, 'msg' => 'Username or email already exists'];
        }

        // --- Create user in database ---
        if (User::create($data)) {
            return ['success' => true, 'msg' => 'User registered successfully'];
        }

        return ['success' => false, 'msg' => 'Error registering user'];
    }

    public static function logout() {
        // --- Safely destroy session ---
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        return ['success' => true, 'msg' => 'Session closed'];
    }
}
