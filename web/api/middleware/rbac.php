<?php
/**
 * Role-Based Access Control (RBAC) Middleware
 * Implements permission checking based on user roles
 * Following 2025 RBAC best practices with flexible permission matrices
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../utils/Response.php';

// Define roles
define('ROLE_USER', 'user');
define('ROLE_TECHNICIAN', 'technician');
define('ROLE_ADMIN', 'admin');

// Define permissions
define('PERM_VIEW_VEHICLES', 'view_vehicles');
define('PERM_CREATE_BOOKING', 'create_booking');
define('PERM_VIEW_OWN_BOOKINGS', 'view_own_bookings');
define('PERM_CREATE_VEHICLE', 'create_vehicle');
define('PERM_UPDATE_VEHICLE', 'update_vehicle');
define('PERM_DELETE_VEHICLE', 'delete_vehicle');
define('PERM_VIEW_USERS', 'view_users');
define('PERM_UPDATE_USER', 'update_user');
define('PERM_DELETE_USER', 'delete_user');
define('PERM_VIEW_LOGS', 'view_logs');
define('PERM_MANAGE_ROLES', 'manage_roles');

/**
 * Permission matrix mapping roles to permissions
 * Based on 2025 RBAC design patterns
 */
function getPermissionMatrix() {
    return [
        ROLE_USER => [
            PERM_VIEW_VEHICLES,
            PERM_CREATE_BOOKING,
            PERM_VIEW_OWN_BOOKINGS
        ],
        ROLE_TECHNICIAN => [
            PERM_VIEW_VEHICLES,
            PERM_CREATE_BOOKING,
            PERM_VIEW_OWN_BOOKINGS,
            PERM_CREATE_VEHICLE,
            PERM_UPDATE_VEHICLE
        ],
        ROLE_ADMIN => [
            PERM_VIEW_VEHICLES,
            PERM_CREATE_BOOKING,
            PERM_VIEW_OWN_BOOKINGS,
            PERM_CREATE_VEHICLE,
            PERM_UPDATE_VEHICLE,
            PERM_DELETE_VEHICLE,
            PERM_VIEW_USERS,
            PERM_UPDATE_USER,
            PERM_DELETE_USER,
            PERM_VIEW_LOGS,
            PERM_MANAGE_ROLES
        ]
    ];
}

/**
 * Check if user has specific permission
 * @param array $user User data with role
 * @param string $permission Permission to check
 * @return bool True if user has permission
 */
function hasPermission($user, $permission) {
    if (!isset($user['role'])) {
        return false;
    }
    
    $permissionMatrix = getPermissionMatrix();
    $userRole = $user['role'];
    
    if (!isset($permissionMatrix[$userRole])) {
        return false;
    }
    
    return in_array($permission, $permissionMatrix[$userRole]);
}

/**
 * Check if user has specific role
 * @param array $user User data
 * @param string $role Role to check
 * @return bool True if user has role
 */
function hasRole($user, $role) {
    return isset($user['role']) && $user['role'] === $role;
}

/**
 * Check if user has any of the specified roles
 * @param array $user User data
 * @param array $roles Array of roles to check
 * @return bool True if user has any of the roles
 */
function hasAnyRole($user, $roles) {
    if (!isset($user['role'])) {
        return false;
    }
    
    return in_array($user['role'], $roles);
}

/**
 * Require specific permission or fail with 403
 * @param string $permission Required permission
 * @return array User data if authorized
 */
function requirePermission($permission) {
    $user = authenticate();
    
    if (!hasPermission($user, $permission)) {
        Response::error('Insufficient permissions', 403, 'FORBIDDEN');
        exit();
    }
    
    return $user;
}

/**
 * Require specific role or fail with 403
 * @param string $role Required role
 * @return array User data if authorized
 */
function requireRole($role) {
    $user = authenticate();
    
    if (!hasRole($user, $role)) {
        Response::error('Insufficient permissions', 403, 'FORBIDDEN');
        exit();
    }
    
    return $user;
}

/**
 * Require any of specified roles or fail with 403
 * @param array $roles Required roles
 * @return array User data if authorized
 */
function requireAnyRole($roles) {
    $user = authenticate();
    
    if (!hasAnyRole($user, $roles)) {
        Response::error('Insufficient permissions', 403, 'FORBIDDEN');
        exit();
    }
    
    return $user;
}

/**
 * Check if user is admin
 * @param array $user User data
 * @return bool True if user is admin
 */
function isAdmin($user) {
    return hasRole($user, ROLE_ADMIN);
}

/**
 * Check if user is technician or admin
 * @param array $user User data
 * @return bool True if user is technician or admin
 */
function isTechnicianOrAdmin($user) {
    return hasAnyRole($user, [ROLE_TECHNICIAN, ROLE_ADMIN]);
}

/**
 * Check if user can access resource
 * @param array $user Current user
 * @param int $resourceUserId User ID of resource owner
 * @return bool True if user can access
 */
function canAccessUserResource($user, $resourceUserId) {
    // Admin can access all resources
    if (isAdmin($user)) {
        return true;
    }
    
    // User can access their own resources
    return isset($user['id']) && $user['id'] == $resourceUserId;
}

/**
 * Require admin role
 * @return array User data if admin
 */
function requireAdmin() {
    return requireRole(ROLE_ADMIN);
}

/**
 * Require technician or admin role
 * @return array User data if authorized
 */
function requireTechnicianOrAdmin() {
    return requireAnyRole([ROLE_TECHNICIAN, ROLE_ADMIN]);
}

/**
 * Log access control event
 * @param array $user User data
 * @param string $action Action attempted
 * @param bool $granted Whether access was granted
 */
function logAccessControl($user, $action, $granted) {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        $mongodb = getMongoDBConnection();
        $logsCollection = $mongodb->selectCollection('system_logs');
        
        $logEntry = [
            'level' => $granted ? 'info' : 'warning',
            'message' => $granted ? 'Access granted' : 'Access denied',
            'user_id' => $user['id'] ?? null,
            'action' => $action,
            'role' => $user['role'] ?? 'unknown',
            'ip_address' => getClientIP(),
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $logsCollection->insertOne($logEntry);
        
    } catch (Exception $e) {
        error_log("Failed to log access control: " . $e->getMessage());
    }
}

/**
 * Validate role value
 * @param string $role Role to validate
 * @return bool True if valid role
 */
function isValidRole($role) {
    return in_array($role, [ROLE_USER, ROLE_TECHNICIAN, ROLE_ADMIN]);
}
