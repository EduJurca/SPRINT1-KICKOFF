<?php
/**
 * 🔐 Permissions
 * Gestiona els permisos específics de cada rol
 */

class Permissions {
    
    /**
     * Permisos per rol
     * Format: 'permís' => [role_ids que tenen aquest permís]
     */
    private static $permissions = [
        // 👥 Gestió d'usuaris
        'users.view' => [1, 2],        // SuperAdmin, Treballador
        'users.create' => [1],          // Només SuperAdmin
        'users.edit' => [1],            // Només SuperAdmin
        'users.delete' => [1],          // Només SuperAdmin
        
        // 🚗 Gestió de vehicles
        'vehicles.view' => [1, 2, 3],   // Tots
        'vehicles.create' => [1, 2],    // SuperAdmin, Treballador
        'vehicles.edit' => [1, 2],      // SuperAdmin, Treballador
        'vehicles.delete' => [1],       // Només SuperAdmin
        
        // 📅 Gestió de reserves
        'bookings.view_all' => [1, 2],  // SuperAdmin, Treballador
        'bookings.view_own' => [1, 2, 3], // Tots (els seus propis)
        'bookings.create' => [1, 2, 3], // Tots
        'bookings.edit' => [1, 2],      // SuperAdmin, Treballador
        'bookings.delete' => [1],       // Només SuperAdmin
        'bookings.cancel_own' => [1, 2, 3], // Tots (cancel·lar els seus)
        
        // ⚙️ Administració
        'admin.dashboard' => [1, 2],    // SuperAdmin, Treballador
        'admin.settings' => [1],        // Només SuperAdmin
        'admin.reports' => [1, 2],      // SuperAdmin, Treballador
        'admin.activity' => [1, 2],     // SuperAdmin, Treballador
    ];
    
    /**
     * Verificar si l'usuari actual té un permís
     */
    public static function can($permission) {
        $roleId = $_SESSION['role_id'] ?? 3;
        
        if (!isset(self::$permissions[$permission])) {
            return false; // Permís no definit = denegat
        }
        
        return in_array($roleId, self::$permissions[$permission]);
    }
    
    /**
     * Verificar múltiples permisos (ha de tenir TOTS)
     */
    public static function canAll($permissions) {
        foreach ($permissions as $permission) {
            if (!self::can($permission)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Verificar múltiples permisos (ha de tenir almenys UN)
     */
    public static function canAny($permissions) {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Llançar excepció si no té permís
     */
    public static function authorize($permission) {
        if (!self::can($permission)) {
            $_SESSION['error'] = 'No tens permisos per realitzar aquesta acció.';
            Router::redirect('/dashboard');
            exit;
        }
    }
    
    /**
     * Obtenir tots els permisos de l'usuari actual
     */
    public static function getUserPermissions() {
        $roleId = $_SESSION['role_id'] ?? 3;
        $userPermissions = [];
        
        foreach (self::$permissions as $permission => $roles) {
            if (in_array($roleId, $roles)) {
                $userPermissions[] = $permission;
            }
        }
        
        return $userPermissions;
    }
    
    /**
     * Helpers per permisos comuns
     */
    public static function canManageUsers() {
        return self::can('users.edit');
    }
    
    public static function canManageVehicles() {
        return self::can('vehicles.edit');
    }
    
    public static function canViewAdminPanel() {
        return self::can('admin.dashboard');
    }
    
    public static function canDeleteAnything() {
        return ($_SESSION['role_id'] ?? 3) == 1; // Només SuperAdmin
    }
}
