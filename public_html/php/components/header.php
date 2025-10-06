<?php
/**
 * Header Component
 * Reusable header with navigation bar and language switcher
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include language handler
require_once __DIR__ . '/../language.php';

// Initialize language
$lang = new Language();
$currentLang = $lang->getCurrentLang();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="VoltiaCar - Carsharing Service">
    <title><?php echo $pageTitle ?? 'VoltiaCar'; ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-green': '#10b981',
                        'primary-green-dark': '#059669',
                        'primary-blue': '#3b82f6',
                        'primary-blue-dark': '#2563eb',
                        'gray-custom': '#6b7280',
                        'gray-light': '#9ca3af'
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/custom.css">
    <link rel="stylesheet" href="/css/accessibility.css">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Brand -->
                <div class="flex items-center">
                    <a href="/index.php" class="flex items-center space-x-2">
                        <img src="/images/logo.png" alt="VoltiaCar Logo" class="h-10 w-10 rounded-full">
                        <span class="text-xl font-bold text-primary-green">VoltiaCar</span>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if ($isLoggedIn): ?>
                        <!-- Logged in menu -->
                        <a href="/pages/vehicle/localitzar-vehicle.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            <?php echo $lang->get('localitzar_vehicle', 'Localitzar Vehicle'); ?>
                        </a>
                        <a href="/pages/dashboard/gestio.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            <?php echo $lang->get('gesti', 'Gestió'); ?>
                        </a>
                        <a href="/pages/profile/perfil.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            <?php echo $lang->get('perfil', 'Perfil'); ?>
                        </a>
                        <a href="/pages/dashboard/historial.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            <?php echo $lang->get('historial', 'Historial'); ?>
                        </a>
                    <?php else: ?>
                        <!-- Guest menu -->
                        <a href="/pages/auth/login.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            <?php echo $lang->get('iniciar_sessi', 'Iniciar Sessió'); ?>
                        </a>
                        <a href="/pages/auth/register.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            <?php echo $lang->get('registrar_se', 'Registrar-se'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Language Switcher -->
                    <div class="relative inline-block text-left">
                        <select id="languageSwitcher" class="bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green">
                            <option value="ca" <?php echo $currentLang === 'ca' ? 'selected' : ''; ?>>Català</option>
                            <option value="es" <?php echo $currentLang === 'es' ? 'selected' : ''; ?>>Español</option>
                            <option value="en" <?php echo $currentLang === 'en' ? 'selected' : ''; ?>>English</option>
                        </select>
                    </div>
                    
                    <?php if ($isLoggedIn): ?>
                        <!-- User Profile / Logout -->
                        <div class="flex items-center space-x-3">
                            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($username); ?></span>
                            <button id="logoutBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                                <?php echo $lang->get('tancar_sessi', 'Tancar Sessió'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
