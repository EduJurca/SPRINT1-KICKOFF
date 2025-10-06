<?php
/**
 * VoltiaCar - Main Entry Point
 * Carsharing Service Application
 */

// Start session
session_start();

// Include required files
require_once __DIR__ . '/php/language.php';

// Initialize language
$lang = new Language();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Set page title
$pageTitle = 'VoltiaCar - ' . $lang->get('inici', 'Inici');

// If logged in, redirect to dashboard
if ($isLoggedIn) {
    header('Location: /pages/dashboard/gestio.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="VoltiaCar - Servei de carsharing sostenible">
    <title><?php echo $pageTitle; ?></title>
    
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
</head>
<body class="bg-gradient-to-br from-primary-green to-primary-blue min-h-screen flex items-center justify-center p-4">
    <!-- Main Container -->
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <img src="/images/logo.png" alt="VoltiaCar Logo" class="h-24 w-24 mx-auto rounded-full mb-4 shadow-lg">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">VoltiaCar</h1>
            <p class="text-gray-600">
                <?php echo $lang->get('benvingut_voltiacar', 'Benvingut al servei de carsharing'); ?>
            </p>
        </div>
        
        <!-- Language Switcher -->
        <div class="mb-6">
            <label for="languageSwitcher" class="block text-sm font-medium text-gray-700 mb-2">
                <?php echo $lang->get('idioma', 'Idioma'); ?>
            </label>
            <select id="languageSwitcher" class="w-full bg-gray-100 border border-gray-300 text-gray-700 py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green">
                <option value="ca" <?php echo $lang->getCurrentLang() === 'ca' ? 'selected' : ''; ?>>Català</option>
                <option value="es" <?php echo $lang->getCurrentLang() === 'es' ? 'selected' : ''; ?>>Español</option>
                <option value="en" <?php echo $lang->getCurrentLang() === 'en' ? 'selected' : ''; ?>>English</option>
            </select>
        </div>
        
        <!-- Action Buttons -->
        <div class="space-y-4">
            <a href="/pages/auth/login.html" class="block w-full bg-primary-blue hover:bg-primary-blue-dark text-white font-semibold py-3 px-6 rounded-lg text-center transition-colors duration-300 shadow-md">
                <?php echo $lang->get('iniciar_sessi', 'Iniciar Sessió'); ?>
            </a>
            
            <a href="/pages/auth/register.html" class="block w-full bg-primary-green hover:bg-primary-green-dark text-white font-semibold py-3 px-6 rounded-lg text-center transition-colors duration-300 shadow-md">
                <?php echo $lang->get('registrar_se', 'Registrar-se'); ?>
            </a>
        </div>
        
        <!-- Additional Links -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <a href="/pages/accessibility/accessibilitat.html" class="hover:text-primary-green transition-colors">
                <?php echo $lang->get('accessibilitat', 'Accessibilitat'); ?>
            </a>
            <span class="mx-2">•</span>
            <a href="/pages/dashboard/resum-projecte.html" class="hover:text-primary-green transition-colors">
                <?php echo $lang->get('sobre_el_projecte', 'Sobre el Projecte'); ?>
            </a>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="/js/language-switcher.js"></script>
    
    <!-- UserWay Accessibility Widget -->
    <script>
        (function(d){
            var s = d.createElement("script");
            s.setAttribute("data-account","RrwQjeYdrh");
            s.src = "https://cdn.userway.org/widget.js";
            (d.body || d.head).appendChild(s);
        })(document);
    </script>
</body>
</html>
