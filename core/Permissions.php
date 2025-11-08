<?php

class Permissions {
    
    private static $permissions = [
        'users.view' => [1, 2],
        'users.create' => [1],
        'users.edit' => [1],
        'users.delete' => [1],
        
        'vehicles.view' => [1, 2, 3],
        'vehicles.create' => [1, 2],
        'vehicles.edit' => [1, 2],
        'vehicles.delete' => [1],
        
        'bookings.view_all' => [1, 2],
        'bookings.view_own' => [1, 2, 3],
        'bookings.create' => [1, 2, 3],
        'bookings.edit' => [1, 2],
        'bookings.delete' => [1],
        'bookings.cancel_own' => [1, 2, 3],
        
    'admin.dashboard' => [1, 2],
    'admin.reports' => [1, 2],
        'admin.activity' => [1, 2],
    ];
    
    public static function can($permission) {
        $roleId = $_SESSION['role_id'] ?? 3;
        
        if (!isset(self::$permissions[$permission])) {
            return false;
        }
        
        return in_array($roleId, self::$permissions[$permission]);
    }
    
    public static function canAll($permissions) {
        foreach ($permissions as $permission) {
            if (!self::can($permission)) {
                return false;
            }
        }
        return true;
    }
    
    public static function canAny($permissions) {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }
        return false;
    }
    
    public static function authorize($permission) {
        if (!self::can($permission)) {
            $_SESSION['error'] = 'No tens permisos per realitzar aquesta acció.';
            Router::redirect('/dashboard');
            exit;
        }
    }
    
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
        return ($_SESSION['role_id'] ?? 3) == 1;
    }
}
