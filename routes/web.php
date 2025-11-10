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


// Endpoint POST de compra (compatibilitat)
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

// Public incident reporting
Router::get('/report-incident', ['IncidentController', 'createIncident']);
Router::post('/report-incident', ['IncidentController', 'createIncident']);

// Admin incident management
Router::get('/admin/incidents', ['AdminIncidentController', 'getAllIncidents']);
Router::get('/admin/incidents/create', ['AdminIncidentController', 'createIncident']);
Router::post('/admin/incidents/create', ['AdminIncidentController', 'createIncident']);
Router::get('/admin/incidents/{id}/edit', ['AdminIncidentController', 'getIncident']);
Router::post('/admin/incidents/{id}/update', ['AdminIncidentController', 'updateIncident']);
Router::post('/admin/incidents/{id}/resolve', ['AdminIncidentController', 'resolveIncident']);
Router::delete('/admin/incidents/{id}', ['AdminIncidentController', 'deleteIncident']);


Router::get('/admin/settings', function() {
    require_once PUBLIC_PATH . '/php/admin/settings.php';
});


