<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {

    public static function login($username, $password) {
        $user = User::findByUsername($username);
        if (!$user) return ['success' => false, 'msg' => 'Usuario no encontrado'];
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return ['success' => true, 'msg' => 'Login exitoso'];
        }
        return ['success' => false, 'msg' => 'Contraseña incorrecta'];
    }

    public static function register($data) {
        if (!isset($data['username'], $data['password'], $data['email']))
            return ['success' => false, 'msg' => 'Faltan datos obligatorios'];

        if (User::findByUsernameOrEmail($data['username'], $data['email']))
            return ['success' => false, 'msg' => 'El nombre de usuario o correo ya existe'];

        if (User::create($data))
            return ['success' => true, 'msg' => 'Usuario registrado correctamente'];

        return ['success' => false, 'msg' => 'Error al registrar el usuario'];
    }

    public static function logout() {
        session_start();
        session_unset();
        session_destroy();
        return ['success' => true, 'msg' => 'Sesión cerrada'];
    }
}