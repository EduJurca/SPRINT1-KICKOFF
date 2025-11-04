<?php
/**
 * 📍 Definició CENTRALITZADA de rutes
 * Totes les rutes de l'aplicació es defineixen aquí
 */

// ==========================================
// 🏠 RUTES PÚBLIQUES
// ==========================================

// Pàgina principal
Router::get('/', function() {
    Router::view('auth.home');
});

// ==========================================
// 🔐 AUTENTICACIÓ
// ==========================================

// Login
Router::get('/login', function() {
    Router::view('auth.login');
});

Router::post('/login', ['AuthController', 'login']);

// Registre
Router::get('/register', function() {
    Router::view('auth.register');
});

Router::post('/register', ['AuthController', 'register']);

// Logout
Router::post('/logout', ['AuthController', 'logout']);
Router::get('/logout', ['AuthController', 'logout']);

// Recuperar contrasenya
Router::get('/recover-password', function() {
    Router::view('auth.recover-password');
});

Router::post('/recover-password', ['AuthController', 'recoverPassword']);

// ==========================================
// 📊 DASHBOARD
// ==========================================

// Dashboard principal (gestió)
Router::get('/dashboard', ['DashboardController', 'showGestio']);

Router::get('/gestio', ['DashboardController', 'showGestio']);

// Resum del projecte
Router::get('/resum-projecte', function() {
    Router::view('public.dashboard.resum-projecte');
});

// ==========================================
// 👤 PERFIL D'USUARI
// ==========================================

// Perfil
Router::get('/perfil', ['ProfileController', 'showProfile']);

Router::get('/profile', ['ProfileController', 'showProfile']);

// Completar perfil
Router::get('/completar-perfil', ['ProfileController', 'showCompleteProfile']);

Router::post('/completar-perfil', ['ProfileController', 'completeProfile']);

// Verificar carnet de conduir
Router::get('/verificar-conduir', function() {
    Router::view('public.profile.verificar-conduir');
});

Router::post('/verificar-conduir', ['ProfileController', 'verifyLicense']);

// Historial
Router::get('/historial', ['ProfileController', 'showHistory']);

// API: Obtenir historial en JSON
Router::get('/api/historial', ['ProfileController', 'getHistory']);

// Pagaments
Router::get('/pagaments', function() {
    Router::view('public.profile.pagaments');
});

// Premium
Router::get('/premium', function() {
    Router::view('public.profile.premium');
});

// ==========================================
// 🚗 VEHICLES
// ==========================================

// Localitzar vehicle
Router::get('/localitzar-vehicle', ['VehicleController', 'showLocalitzar']);

Router::get('/vehicles/search', ['VehicleController', 'search']);

// Administrar vehicle
Router::get('/administrar-vehicle', function() {
    Router::view('public.vehicle.administrar-vehicle');
});

// Detalls del vehicle
Router::get('/detalls-vehicle', function() {
    Router::view('public.vehicle.detalls-vehicle');
});

Router::get('/vehicles/{id}', ['VehicleController', 'show']);

// Booking
Router::get('/booking', function() {
    Router::view('public.vehicle.booking');
});

Router::post('/book-vehicle', ['VehicleController', 'bookVehicle']);

// Comprar temps
Router::get('/purchase-time', function() {
    Router::view('public.vehicle.purchase-time');
});

Router::post('/purchase-time', ['VehicleController', 'purchaseTime']);

// ==========================================
// 📡 API ENDPOINTS
// ==========================================

// Vehicles API
Router::get('/api/vehicles', ['VehicleController', 'getAvailableVehicles']);
Router::get('/api/vehicles/{id}', ['VehicleController', 'getVehicleById']);
Router::post('/api/vehicles/claim', ['VehicleController', 'claimVehicle']);
Router::post('/api/vehicles/release', ['VehicleController', 'releaseVehicle']);
Router::get('/api/vehicles/current', ['VehicleController', 'getCurrentVehicle']);

// Vehicle Control API
Router::post('/api/vehicles/horn', ['VehicleController', 'activateHorn']);
Router::post('/api/vehicles/lights', ['VehicleController', 'activateLights']);
Router::post('/api/vehicles/start', ['VehicleController', 'startEngine']);
Router::post('/api/vehicles/stop', ['VehicleController', 'stopEngine']);
Router::post('/api/vehicles/lock', ['VehicleController', 'lockDoors']);
Router::post('/api/vehicles/unlock', ['VehicleController', 'unlockDoors']);

// Booking API
Router::get('/api/bookings', ['BookingController', 'index']);
Router::get('/api/bookings/{id}', ['BookingController', 'show']);
Router::post('/api/bookings', ['BookingController', 'create']);
Router::put('/api/bookings/{id}', ['BookingController', 'update']);
Router::delete('/api/bookings/{id}', ['BookingController', 'delete']);

// Users API
Router::get('/api/users/profile', ['ProfileController', 'getProfile']);
Router::post('/api/users/profile', ['ProfileController', 'updateProfile']);
Router::post('/api/users/language', ['ProfileController', 'updateLanguage']);

// Sessió
Router::get('/api/session-check', ['AuthController', 'checkSession']);
Router::get('/api/session-status', ['AuthController', 'getSessionStatus']);

// Gestió (dashboard data)
Router::get('/api/gestio', ['DashboardController', 'getGestioData']);

// ==========================================
// ♿ ACCESSIBILITAT
// ==========================================

Router::get('/accessibilitat', function() {
    Router::view('commons.accessibility.accessibilitat');
});

// ==========================================
// 🔧 ADMIN (Panel d'Administració)
// ==========================================
require_once CONTROLLERS_PATH . '/admin/AdminController.php';

// Dashboard principal d'admin
Router::get('/admin', ['AdminController', 'dashboard']);
Router::get('/admin/dashboard', ['AdminController', 'dashboard']);

// Gestió de vehicles
Router::get('/admin/vehicles', ['AdminController', 'vehicles']);

// Gestió de reserves
Router::get('/admin/bookings', ['AdminController', 'bookings']);

// Incidències
Router::get('/admin/incidencies', ['AdminController', 'incidencies']);

// Configuració
Router::get('/admin/settings', ['AdminController', 'settings']);

// ==========================================
// 👥 CRUD USUARIS
// ==========================================
require_once CONTROLLERS_PATH . '/admin/UserController.php';

Router::get('/admin/users', function() {
    $controller = new UserController();
    $controller->index();
});

Router::get('/admin/users/create', function() {
    $controller = new UserController();
    $controller->create();
});

Router::post('/admin/users/store', function() {
    $controller = new UserController();
    $controller->store();
});

Router::get('/admin/users/edit', function() {
    $controller = new UserController();
    $controller->edit();
});

Router::post('/admin/users/update', function() {
    $controller = new UserController();
    $controller->update();
});

Router::post('/admin/users/delete', function() {
    $controller = new UserController();
    $controller->delete();
});



// ==========================================
// 🧪 DEBUG / TESTING (només en desenvolupament)
// ==========================================

if (getenv('APP_ENV') === 'development' || !getenv('APP_ENV')) {
    Router::get('/debug/db', function() {
        require_once PUBLIC_PATH . '/php/api/debug-db.php';
    });
    
    Router::get('/debug/vehicle', function() {
        require_once PUBLIC_PATH . '/php/api/debug-vehicle.php';
    });
    
    Router::get('/test/claim', function() {
        require_once PUBLIC_PATH . '/php/api/test-claim.php';
    });
}

// ==========================================
// 🧪 TEST D'AUTORITZACIÓ
// ==========================================

Router::get('/test/auth', function() {
    // Requereix autenticació per veure el test
    AuthController::requireAuth();
    Router::view('test.auth-test');
});
