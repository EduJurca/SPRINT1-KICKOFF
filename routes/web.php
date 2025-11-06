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
    Router::view('auth.recuperar-contrasenya');
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
//  VEHICLES
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

// Configuració (settings page removed)

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

// ==========================================
// 🚗 ADMIN - CRUD DE VEHICLES (MVC)
// ==========================================

// INDEX - Listar todos los vehículos
Router::get('/admin/vehicles', ['AdminVehicleController', 'index']);

// CREATE - Mostrar formulario de crear
Router::get('/admin/vehicles/create', ['AdminVehicleController', 'create']);

// STORE - Guardar nuevo vehículo
Router::post('/admin/vehicles', ['AdminVehicleController', 'store']);

// SHOW - Ver detalle de un vehículo
Router::get('/admin/vehicles/{id}', ['AdminVehicleController', 'show']);

// EDIT - Mostrar formulario de editar
Router::get('/admin/vehicles/{id}/edit', ['AdminVehicleController', 'edit']);

// UPDATE - Actualizar vehículo (soporta PUT y POST)
Router::put('/admin/vehicles/{id}', ['AdminVehicleController', 'update']);
Router::post('/admin/vehicles/{id}', ['AdminVehicleController', 'update']);

// DESTROY - Eliminar vehículo (simulando DELETE con POST + _method)
Router::delete('/admin/vehicles/{id}', ['AdminVehicleController', 'destroy']);

// API - Obtener vehículos en JSON
Router::get('/admin/api/vehicles', ['AdminVehicleController', 'api']);

// ==========================================
// 🔧 ADMIN - OTRAS SECCIONES
// ==========================================

Router::post('/admin/users/update', function() {
    $controller = new UserController();
    $controller->update();
});

Router::post('/admin/users/delete', function() {
    $controller = new UserController();
    $controller->delete();
});



//==============
// 🧪 TEST D'AUTORITZACIÓ
// ==========================================

// Test d'autorització removed from routes - dev-only view deleted
