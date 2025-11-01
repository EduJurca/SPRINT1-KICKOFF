<?php
/**
 * 🛠️ Helpers Globals
 * Funcions d'ajuda per utilitzar a qualsevol part de l'aplicació
 */

/**
 * Verificar si l'usuari té un permís
 */
function can($permission) {
    require_once CORE_PATH . '/Permissions.php';
    return Permissions::can($permission);
}

/**
 * Verificar si l'usuari és SuperAdmin
 */
function isSuperAdmin() {
    return ($_SESSION['role_id'] ?? 3) == 1;
}

/**
 * Verificar si l'usuari és Treballador
 */
function isTreballador() {
    return ($_SESSION['role_id'] ?? 3) == 2;
}

/**
 * Verificar si l'usuari és Client
 */
function isClient() {
    return ($_SESSION['role_id'] ?? 3) == 3;
}

/**
 * Verificar si l'usuari és Staff (SuperAdmin o Treballador)
 */
function isStaff() {
    return in_array($_SESSION['role_id'] ?? 3, [1, 2]);
}

/**
 * Obtenir el nom del rol actual
 */
function getRoleName() {
    return $_SESSION['role_name'] ?? 'Client';
}

/**
 * Obtenir el color CSS del rol actual
 */
function getRoleColor() {
    $colors = [
        'SuperAdmin' => 'bg-purple-100 text-purple-800',
        'Treballador' => 'bg-blue-100 text-blue-800',
        'Client' => 'bg-green-100 text-green-800'
    ];
    return $colors[getRoleName()] ?? 'bg-gray-100 text-gray-800';
}

/**
 * Mostrar badge del rol
 */
function roleBadge($roleName = null) {
    if (!$roleName) {
        $roleName = getRoleName();
    }
    
    $colors = [
        'SuperAdmin' => 'bg-purple-100 text-purple-800',
        'Treballador' => 'bg-blue-100 text-blue-800',
        'Client' => 'bg-green-100 text-green-800'
    ];
    
    $color = $colors[$roleName] ?? 'bg-gray-100 text-gray-800';
    
    return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '">' 
           . htmlspecialchars($roleName) . '</span>';
}

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Mostrar missatge d'èxit
 */
function showSuccess() {
    if (isset($_SESSION['success'])) {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">';
        echo e($_SESSION['success']);
        echo '</div>';
        unset($_SESSION['success']);
    }
}

/**
 * Mostrar missatge d'error
 */
function showError() {
    if (isset($_SESSION['error'])) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">';
        echo e($_SESSION['error']);
        echo '</div>';
        unset($_SESSION['error']);
    }
}

/**
 * Mostrar tots els missatges
 */
function showMessages() {
    showSuccess();
    showError();
}
