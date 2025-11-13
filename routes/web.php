<?php
/**
 * 📍 Definició CENTRALITZADA de rutes
 * Totes les rutes de l'aplicació es defineixen aquí
 */

// ==========================================
// 🏠 RUTES PÚBLIQUES
// ==========================================

Router::get('/', function() { Router::view('auth.home'); });

// ==========================================
// 🔐 AUTENTICACIÓ
// ==========================================

Router::get('/login', ['AuthController', 'showLogin']);
Router::post('/login', ['AuthController', 'login']);

Router::get('/register', ['AuthController', 'showRegister']);
Router::post('/register', ['AuthController', 'register']);

Router::post('/logout', ['AuthController', 'logout']);
Router::get('/logout', ['AuthController', 'logout']);

Router::get('/recover-password', ['AuthController', 'showRecover']);
Router::post('/recover-password', ['AuthController', 'recoverPassword']);

// ==========================================
// 📊 DASHBOARD
// ==========================================

Router::get('/dashboard', ['DashboardController', 'showGestio']);
Router::get('/gestio', ['DashboardController', 'showGestio']);

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
Router::get('/historial', function() {
    Router::view('public.profile.historial');
});

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

Router::get('/localitzar-vehicle', ['VehicleController', 'showLocalitzar']);
Router::get('/vehicles/search', ['VehicleController', 'search']);
Router::get('/administrar-vehicle', ['VehicleController', 'showAdministrar']);
Router::get('/detalls-vehicle', ['VehicleController', 'showDetalls']);
Router::get('/vehicles/{id}', ['VehicleController', 'show']);
Router::get('/booking', ['VehicleController', 'showBooking']);
Router::post('/book-vehicle', ['VehicleController', 'bookVehicle']);
Router::post('/purchase-time', ['VehicleController', 'purchaseTime']);

// ==========================================
// 📡 API - VEHICLES
// ==========================================

Router::get('/api/vehicles', ['VehicleController', 'getAvailableVehicles']);
Router::get('/api/vehicles/{id}', ['VehicleController', 'getVehicleById']);
Router::post('/api/vehicles/claim', ['VehicleController', 'claimVehicle']);
Router::post('/api/vehicles/release', ['VehicleController', 'releaseVehicle']);
Router::get('/api/vehicles/current', ['VehicleController', 'getCurrentVehicle']);
Router::post('/api/vehicles/horn', ['VehicleController', 'activateHorn']);
Router::post('/api/vehicles/lights', ['VehicleController', 'activateLights']);
Router::post('/api/vehicles/start', ['VehicleController', 'startEngine']);
Router::post('/api/vehicles/stop', ['VehicleController', 'stopEngine']);
Router::post('/api/vehicles/lock', ['VehicleController', 'lockDoors']);
Router::post('/api/vehicles/unlock', ['VehicleController', 'unlockDoors']);

// ==========================================
// 📡 API - BOOKINGS
// ==========================================

Router::get('/api/bookings', ['BookingController', 'index']);
Router::get('/api/bookings/{id}', ['BookingController', 'show']);
Router::post('/api/bookings', ['BookingController', 'create']);
Router::put('/api/bookings/{id}', ['BookingController', 'update']);
Router::delete('/api/bookings/{id}', ['BookingController', 'delete']);

// ==========================================
// 📡 API - SESSIÓ
// ==========================================

Router::get('/api/session-check', ['AuthController', 'checkSession']);
Router::get('/api/session-status', ['AuthController', 'getSessionStatus']);

// ==========================================
// 📡 API - ESTACIONS
// ==========================================

Router::get('/api/charging-stations', ['ChargingStationController', 'getStationsJSON']);

// ==========================================
// ♿ ACCESSIBILITAT
// ==========================================

Router::get('/accessibilitat', function() { Router::view('commons.accessibility.accessibilitat'); });

// ==========================================
// 🔧 ADMIN - DASHBOARD
// ==========================================

Router::get('/admin', ['AdminController', 'dashboard']);
Router::get('/admin/dashboard', ['AdminController', 'dashboard']);

// ==========================================
// 👥 ADMIN - USUARIOS
// ==========================================

Router::get('/admin/users', ['UserController', 'index']);
Router::get('/admin/users/create', ['UserController', 'create']);
Router::post('/admin/users/store', ['UserController', 'store']);
// Edit, update and delete routes for users
Router::get('/admin/users/edit', ['UserController', 'edit']);
Router::post('/admin/users/update', ['UserController', 'update']);
Router::post('/admin/users/delete', ['UserController', 'delete']);

// ==========================================
// � CHAT / ASISTENTE IA
// ==========================================

Router::get('/chat', ['ChatController', 'index']);
Router::post('/chat/send', ['ChatController', 'send']);

// ==========================================
// �🔧 ADMIN (si tens zona d'administració)
// ==========================================

Router::get('/admin/vehicles', ['AdminVehicleController', 'index']);
Router::get('/admin/vehicles/create', ['AdminVehicleController', 'create']);
Router::post('/admin/vehicles', ['AdminVehicleController', 'store']);
Router::get('/admin/vehicles/{id}', ['AdminVehicleController', 'show']);
Router::get('/admin/vehicles/{id}/edit', ['AdminVehicleController', 'edit']);
Router::put('/admin/vehicles/{id}', ['AdminVehicleController', 'update']);
Router::post('/admin/vehicles/{id}', ['AdminVehicleController', 'update']);
Router::delete('/admin/vehicles/{id}', ['AdminVehicleController', 'destroy']);
Router::get('/admin/api/vehicles', ['AdminVehicleController', 'api']);

// ==========================================
// 🔌 ADMIN - ESTACIONES
// ==========================================

Router::get('/admin/charging-stations', ['ChargingStationController', 'index']);
Router::get('/admin/charging-stations/create', ['ChargingStationController', 'create']);
Router::post('/admin/charging-stations/store', ['ChargingStationController', 'store']);
Router::get('/admin/charging-stations/{id}/edit', ['ChargingStationController', 'edit']);
Router::post('/admin/charging-stations/{id}/update', ['ChargingStationController', 'update']);
Router::post('/admin/charging-stations/{id}/delete', ['ChargingStationController', 'delete']);

// ==========================================
// 📍 ESTACIONES PUBLICAS
// ==========================================

Router::get('/charging-stations', ['ChargingStationController', 'showMap']);
Router::get('/charging-stations/{id}', ['ChargingStationController', 'getStationDetails']);

// ==========================================
// 📋 ADMIN - INCIDENCIAS
// ==========================================

Router::get('/admin/incidents', ['AdminIncidentController', 'getAllIncidents']);
Router::get('/admin/incidents/create', ['AdminIncidentController', 'createIncident']);
Router::post('/admin/incidents/create', ['AdminIncidentController', 'createIncident']);
Router::get('/admin/incidents/{id}/edit', ['AdminIncidentController', 'getIncident']);
Router::post('/admin/incidents/{id}/update', ['AdminIncidentController', 'updateIncident']);
Router::post('/admin/incidents/{id}/resolve', ['AdminIncidentController', 'resolveIncident']);
Router::delete('/admin/incidents/{id}', ['AdminIncidentController', 'deleteIncident']);

// ==========================================
// 📋 INCIDENCIAS PUBLICAS
// ==========================================

Router::get('/report-incident', ['IncidentController', 'createIncident']);
Router::post('/report-incident', ['IncidentController', 'createIncident']);

// ==========================================
// ⚙️ ADMIN - CONFIGURACIÓN
// ==========================================

Router::get('/admin/settings', ['AdminController', 'showSettings']);
