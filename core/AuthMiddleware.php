<?php
/**
 * 🔐 AuthMiddleware
 * Gestiona l'autorització basada en rols
 */

class AuthMiddleware {
    
    /**
     * Verificar si l'usuari està autenticat
     */
    public static function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            Router::redirect('/login');
            exit;
        }
    }
    
    /**
     * Verificar si l'usuari és SuperAdmin (role_id = 1)
     */
    public static function requireSuperAdmin() {
        self::requireAuth();
        
        $roleId = $_SESSION['role_id'] ?? 3;
        if ($roleId != 1) {
            $_SESSION['error'] = 'Accés denegat. Només per SuperAdmins.';
            Router::redirect('/dashboard');
            exit;
        }
    }
    
    /**
     * Verificar si l'usuari és Treballador o SuperAdmin (role_id = 1 o 2)
     */
    public static function requireStaff() {
        self::requireAuth();
        
        $roleId = $_SESSION['role_id'] ?? 3;
        if (!in_array($roleId, [1, 2])) {
            $_SESSION['error'] = 'Accés denegat. Només per personal autoritzat.';
            Router::redirect('/dashboard');
            exit;
        }
    }
    
    /**
     * Verificar si l'usuari té un rol específic
     */
    public static function requireRole($allowedRoles) {
        self::requireAuth();
        
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        
        $roleId = $_SESSION['role_id'] ?? 3;
        if (!in_array($roleId, $allowedRoles)) {
            $_SESSION['error'] = 'No tens permisos per accedir a aquesta pàgina.';
            Router::redirect('/dashboard');
            exit;
        }
    }
    
    /**
     * Obtenir el rol actual de l'usuari
     */
    public static function getCurrentRole() {
        return [
            'role_id' => $_SESSION['role_id'] ?? 3,
            'role_name' => $_SESSION['role_name'] ?? 'Client',
            'is_admin' => $_SESSION['is_admin'] ?? 0
        ];
    }
    
    /**
     * Verificar si l'usuari és SuperAdmin
     */
    public static function isSuperAdmin() {
        return ($_SESSION['role_id'] ?? 3) == 1;
    }
    
    /**
     * Verificar si l'usuari és Treballador
     */
    public static function isTreballador() {
        return ($_SESSION['role_id'] ?? 3) == 2;
    }
    
    /**
     * Verificar si l'usuari és Client
     */
    public static function isClient() {
        return ($_SESSION['role_id'] ?? 3) == 3;
    }
    
    /**
     * Verificar si l'usuari és Staff (SuperAdmin o Treballador)
     */
    public static function isStaff() {
        return in_array($_SESSION['role_id'] ?? 3, [1, 2]);
    }
}
