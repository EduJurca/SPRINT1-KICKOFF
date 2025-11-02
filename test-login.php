<?php
/**
 * Script de prueba para debug de login
 */

// Configuración de paths
define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', __DIR__ . '/config');
define('PUBLIC_PATH', __DIR__ . '/public_html');
define('CONTROLLERS_PATH', __DIR__ . '/controllers');
define('MODELS_PATH', __DIR__ . '/models');
define('VIEWS_PATH', __DIR__ . '/views');

// Autoload de clases
require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/models/User.php';

echo "🔍 TEST LOGIN DEBUG\n";
echo "==================\n\n";

// Test 1: Conexión a base de datos
echo "1. Probando conexión a base de datos...\n";
try {
    $db = Database::getMariaDBConnection();
    echo "   ✅ Conexión exitosa\n\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Buscar usuario admin
echo "2. Buscando usuario 'admin'...\n";
$userModel = new User();
$user = $userModel->findByUsername('admin');

if ($user) {
    echo "   ✅ Usuario encontrado\n";
    echo "   - ID: " . $user['id'] . "\n";
    echo "   - Username: " . $user['username'] . "\n";
    echo "   - Email: " . ($user['email'] ?? 'N/A') . "\n";
    echo "   - is_admin: " . ($user['is_admin'] ?? 0) . "\n";
    echo "   - role_id: " . ($user['role_id'] ?? 'N/A') . "\n";
    echo "   - role_name: " . ($user['role_name'] ?? 'N/A') . "\n";
    echo "   - Password hash: " . substr($user['password'], 0, 20) . "...\n\n";
} else {
    echo "   ❌ Usuario no encontrado\n\n";
    exit(1);
}

// Test 3: Verificar password
echo "3. Verificando password 'admin123'...\n";
$password = 'admin123';
if (password_verify($password, $user['password'])) {
    echo "   ✅ Password correcto\n\n";
} else {
    echo "   ❌ Password incorrecto\n";
    echo "   - Password probado: " . $password . "\n";
    echo "   - Hash en DB: " . $user['password'] . "\n\n";
    
    // Generar nuevo hash para comparar
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    echo "   - Nuevo hash generado: " . $newHash . "\n";
    echo "   - ¿El nuevo hash verifica? " . (password_verify($password, $newHash) ? 'SÍ' : 'NO') . "\n\n";
}

// Test 4: Simular session
echo "4. Simulando inicio de sesión...\n";
session_start();
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['is_admin'] = $user['is_admin'] ?? 0;
$_SESSION['role_id'] = $user['role_id'] ?? null;
$_SESSION['role_name'] = $user['role_name'] ?? 'user';

echo "   ✅ Sesión iniciada\n";
echo "   - Session ID: " . session_id() . "\n";
echo "   - user_id: " . $_SESSION['user_id'] . "\n";
echo "   - username: " . $_SESSION['username'] . "\n";
echo "   - is_admin: " . $_SESSION['is_admin'] . "\n\n";

// Test 5: Verificar redirección
echo "5. Lógica de redirección...\n";
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    echo "   ✅ Debería redirigir a: /admin\n";
} else {
    echo "   ✅ Debería redirigir a: /dashboard\n";
}

echo "\n✨ Test completado\n";
