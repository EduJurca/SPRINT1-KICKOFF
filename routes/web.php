<?php

// Pàgina principal
Router::get('/', function() {
    Router::view('auth.home');
});

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

// Perfil
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
Router::get('/historial', function() {
    Router::view('public.profile.historial');
});

// Pagaments
Router::get('/pagaments', function() {
    Router::view('public.profile.pagaments');
});

// ==========================================
// 📊 DASHBOARD
// ==========================================

// Dashboard principal (gestió)
Router::get('/dashboard', ['DashboardController', 'showGestio']);

// ==========================================
// 👤 PERFIL D'USUARI
// ==========================================

// Perfil
Router::get('/profile', ['ProfileController', 'showProfile']);

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
Router::get('/historial', function() {
    Router::view('public.profile.historial');
});

// Pagaments
Router::get('/profile/pagaments', ['ProfileController', 'showPayments']);

Router::post('/profile/pagaments/add', ['ProfileController', 'addPaymentMethod']);

Router::post('/profile/pagaments/delete/{id}', ['ProfileController', 'deletePaymentMethod']);

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

Router::post('/api/users/language', ['ProfileController', 'updateLanguage']);

// ==========================================
// ♿ ACCESSIBILITAT
// ==========================================

Router::get('/accessibilitat', function() {
    Router::view('commons.accessibility.accessibilitat');
});

// ==========================================
// � CHAT / ASISTENTE IA
// ==========================================

Router::get('/chat', ['ChatController', 'index']);
Router::post('/chat/send', ['ChatController', 'send']);

// ==========================================
// �🔧 ADMIN (si tens zona d'administració)
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



Router::get('/admin/vehicles', ['AdminVehicleController', 'index']);
Router::get('/admin/vehicles/create', ['AdminVehicleController', 'create']);
Router::post('/admin/vehicles', ['AdminVehicleController', 'store']);
Router::get('/admin/vehicles/{id}', ['AdminVehicleController', 'show']);
Router::get('/admin/vehicles/{id}/edit', ['AdminVehicleController', 'edit']);
Router::put('/admin/vehicles/{id}', ['AdminVehicleController', 'update']);
Router::post('/admin/vehicles/{id}', ['AdminVehicleController', 'update']);

// DESTROY - Eliminar vehículo (simulando DELETE con POST + _method)
Router::delete('/admin/vehicles/{id}', ['AdminVehicleController', 'destroy']);
Router::get('/admin/api/vehicles', ['AdminVehicleController', 'api']);

// ADMIN ROUTES (gestió CRUD)
Router::get('/admin/charging-stations', ['ChargingStationController', 'index']);
Router::get('/admin/charging-points', ['ChargingStationController', 'index']); // Alias
Router::get('/admin/charging-stations/create', ['ChargingStationController', 'create']);
Router::post('/admin/charging-stations/store', ['ChargingStationController', 'store']);
Router::get('/admin/charging-stations/{id}/edit', ['ChargingStationController', 'edit']);
Router::post('/admin/charging-stations/{id}/update', ['ChargingStationController', 'update']);
Router::post('/admin/charging-stations/{id}/delete', ['ChargingStationController', 'delete']);

// PUBLIC ROUTES (mapa i detalls)
Router::get('/charging-stations', ['ChargingStationController', 'showMap']);
Router::get('/charging-stations/{id}', ['ChargingStationController', 'getStationDetails']);

// API ROUTES (JSON endpoints)
Router::get('/api/charging-stations', ['ChargingStationController', 'getStationsJSON']);

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


