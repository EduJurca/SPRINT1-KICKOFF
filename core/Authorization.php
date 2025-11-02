<?php
/**
 * 🔒 Authorization - Sistema de control d'accés basat en rols i permisos
 * Gestiona qui pot fer què a l'aplicació segons el seu rol
 */

class Authorization {
    
    /**
     * Definició de rols del sistema
     */
    const ROLE_GUEST = 'guest';           // Usuari no autenticat
    const ROLE_USER = 'user';             // Usuari estàndard
    const ROLE_PREMIUM = 'premium';       // Usuari premium
    const ROLE_MANAGER = 'manager';       // Gestor de flota
    const ROLE_ADMIN = 'admin';           // Administrador
    const ROLE_SUPERADMIN = 'superadmin'; // Superadministrador
    
    /**
     * Jerarquia de rols (cada rol hereta els permisos dels anteriors)
     */
    private static $roleHierarchy = [
        self::ROLE_GUEST => [],
        self::ROLE_USER => [self::ROLE_GUEST],
        self::ROLE_PREMIUM => [self::ROLE_USER, self::ROLE_GUEST],
        self::ROLE_MANAGER => [self::ROLE_PREMIUM, self::ROLE_USER, self::ROLE_GUEST],
        self::ROLE_ADMIN => [self::ROLE_MANAGER, self::ROLE_PREMIUM, self::ROLE_USER, self::ROLE_GUEST],
        self::ROLE_SUPERADMIN => [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_PREMIUM, self::ROLE_USER, self::ROLE_GUEST],
    ];
    
    /**
     * Definició de permisos per rol
     */
    private static $permissions = [
        // GUEST - No autenticat
        self::ROLE_GUEST => [
            'view_home',
            'view_login',
            'view_register',
        ],
        
        // USER - Usuari estàndard
        self::ROLE_USER => [
            'view_dashboard',
            'view_profile',
            'edit_own_profile',
            'view_vehicles',
            'search_vehicles',
            'claim_vehicle',
            'release_vehicle',
            'control_own_vehicle',
            'view_own_bookings',
            'create_booking',
            'purchase_time',
            'view_payment_history',
        ],
        
        // PREMIUM - Usuari premium (subscripció mensual)
        self::ROLE_PREMIUM => [
            'unlimited_minutes',
            'priority_booking',
            'discount_rates',
            'premium_vehicles',
            'advanced_stats',
        ],
        
        // MANAGER - Gestor de flota
        self::ROLE_MANAGER => [
            'view_all_vehicles',
            'add_vehicle',
            'edit_vehicle',
            'disable_vehicle',
            'view_all_bookings',
            'manage_vehicle_maintenance',
            'view_fleet_stats',
        ],
        
        // ADMIN - Administrador
        self::ROLE_ADMIN => [
            'view_admin_panel',
            'view_all_users',
            'edit_users',
            'disable_users',
            'delete_users',
            'manage_roles',
            'view_system_logs',
            'manage_settings',
            'delete_vehicle',
            'delete_booking',
        ],
        
        // SUPERADMIN - Superadministrador
        self::ROLE_SUPERADMIN => [
            'manage_admins',
            'system_configuration',
            'database_access',
            'full_control',
        ],
    ];
    
    /**
     * Obtenir el rol actual de l'usuari
     * 
     * @return string Nom del rol
     */
    public static function getCurrentRole() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Si no està autenticat
        if (!isset($_SESSION['user_id'])) {
            return self::ROLE_GUEST;
        }
        
        // Si té role_name en sessió (de la BD)
        if (isset($_SESSION['role_name'])) {
            return strtolower($_SESSION['role_name']);
        }
        
        // Fallback: si és admin (legacy)
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
            return self::ROLE_ADMIN;
        }
        
        // Per defecte, usuari estàndard
        return self::ROLE_USER;
    }
    
    /**
     * Comprovar si l'usuari té un permís específic
     * 
     * @param string $permission Nom del permís
     * @return bool True si té el permís
     */
    public static function can($permission) {
        $currentRole = self::getCurrentRole();
        
        // Superadmin té tots els permisos
        if ($currentRole === self::ROLE_SUPERADMIN) {
            return true;
        }
        
        // Obtenir permisos del rol actual i rols heretats
        $allPermissions = self::getAllPermissionsForRole($currentRole);
        
        return in_array($permission, $allPermissions);
    }
    
    /**
     * Comprovar si l'usuari té algun dels permisos especificats
     * 
     * @param array $permissions Array de permisos
     * @return bool True si té almenys un permís
     */
    public static function canAny(array $permissions) {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Comprovar si l'usuari té tots els permisos especificats
     * 
     * @param array $permissions Array de permisos
     * @return bool True si té tots els permisos
     */
    public static function canAll(array $permissions) {
        foreach ($permissions as $permission) {
            if (!self::can($permission)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Comprovar si l'usuari té un rol específic
     * 
     * @param string $role Nom del rol
     * @return bool True si té el rol
     */
    public static function hasRole($role) {
        return self::getCurrentRole() === strtolower($role);
    }
    
    /**
     * Comprovar si l'usuari té algun dels rols especificats
     * 
     * @param array $roles Array de rols
     * @return bool True si té almenys un rol
     */
    public static function hasAnyRole(array $roles) {
        $currentRole = self::getCurrentRole();
        foreach ($roles as $role) {
            if ($currentRole === strtolower($role)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Middleware: Requerir un permís específic
     * 
     * @param string $permission Nom del permís
     * @param string $redirectUrl URL de redirecció si no té permís
     */
    public static function requirePermission($permission, $redirectUrl = '/') {
        if (!self::can($permission)) {
            $_SESSION['error'] = 'No tens permís per accedir a aquesta pàgina';
            Router::redirect($redirectUrl);
            exit;
        }
    }
    
    /**
     * Middleware: Requerir un rol específic
     * 
     * @param string $role Nom del rol
     * @param string $redirectUrl URL de redirecció si no té el rol
     */
    public static function requireRole($role, $redirectUrl = '/') {
        if (!self::hasRole($role) && !self::isHigherRole($role)) {
            $_SESSION['error'] = 'No tens el rol necessari per accedir a aquesta pàgina';
            Router::redirect($redirectUrl);
            exit;
        }
    }
    
    /**
     * Middleware: Requerir qualsevol dels rols especificats
     * 
     * @param array $roles Array de rols
     * @param string $redirectUrl URL de redirecció
     */
    public static function requireAnyRole(array $roles, $redirectUrl = '/') {
        if (!self::hasAnyRole($roles)) {
            $_SESSION['error'] = 'No tens el rol necessari per accedir a aquesta pàgina';
            Router::redirect($redirectUrl);
            exit;
        }
    }
    
    /**
     * Comprovar si el rol actual és superior al rol especificat
     * 
     * @param string $role Rol a comparar
     * @return bool True si és superior
     */
    public static function isHigherRole($role) {
        $currentRole = self::getCurrentRole();
        $roleOrder = array_keys(self::$roleHierarchy);
        
        $currentIndex = array_search($currentRole, $roleOrder);
        $compareIndex = array_search(strtolower($role), $roleOrder);
        
        return $currentIndex !== false && $compareIndex !== false && $currentIndex > $compareIndex;
    }
    
    /**
     * Obtenir tots els permisos per un rol (incloent heretats)
     * 
     * @param string $role Nom del rol
     * @return array Array de permisos
     */
    private static function getAllPermissionsForRole($role) {
        $permissions = self::$permissions[$role] ?? [];
        
        // Afegir permisos heretats
        if (isset(self::$roleHierarchy[$role])) {
            foreach (self::$roleHierarchy[$role] as $inheritedRole) {
                $permissions = array_merge($permissions, self::$permissions[$inheritedRole] ?? []);
            }
        }
        
        return array_unique($permissions);
    }
    
    /**
     * Obtenir tots els permisos de l'usuari actual
     * 
     * @return array Array de permisos
     */
    public static function getUserPermissions() {
        $currentRole = self::getCurrentRole();
        return self::getAllPermissionsForRole($currentRole);
    }
    
    /**
     * Generar informació d'autorització per la vista
     * 
     * @return array Informació del rol i permisos
     */
    public static function getAuthInfo() {
        $role = self::getCurrentRole();
        $permissions = self::getUserPermissions();
        
        return [
            'role' => $role,
            'role_display' => ucfirst($role),
            'is_guest' => $role === self::ROLE_GUEST,
            'is_user' => $role === self::ROLE_USER,
            'is_premium' => $role === self::ROLE_PREMIUM,
            'is_manager' => $role === self::ROLE_MANAGER,
            'is_admin' => in_array($role, [self::ROLE_ADMIN, self::ROLE_SUPERADMIN]),
            'is_superadmin' => $role === self::ROLE_SUPERADMIN,
            'permissions' => $permissions,
            'can' => function($permission) {
                return Authorization::can($permission);
            }
        ];
    }
}
