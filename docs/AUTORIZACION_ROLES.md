# 🔒 Sistema d'Autorització i Rols

## Resum

Aquest projecte implementa un sistema complet de **control d'accés basat en rols (RBAC)** que permet gestionar què pot veure i fer cada usuari segons el seu rol.

---

## 📋 Rols Disponibles

### 1. **Guest** (Convidat)
- Usuari **no autenticat**
- Pot veure la pàgina inicial i formularis de login/registre

### 2. **User** (Usuari Estàndard)
- Usuari registrat bàsic
- Pot:
  - Veure vehicles i cercar-los
  - Reclamar i alliberar vehicles
  - Controlar el seu vehicle (botzina, llums, portes, motor)
  - Veure les seves reserves
  - Comprar temps
  - Gestionar el seu perfil

### 3. **Premium**
- Usuari amb subscripció mensual
- Hereta tots els permisos de **User** + afegeix:
  - Minuts il·limitats
  - Reserves prioritàries
  - Descomptes en tarifes
  - Accés a vehicles premium
  - Estadístiques avançades

### 4. **Manager** (Gestor de Flota)
- Gestor de vehicles
- Hereta permisos de **Premium** + afegeix:
  - Veure tots els vehicles
  - Afegir, editar i desactivar vehicles
  - Gestionar manteniment de vehicles
  - Veure totes les reserves
  - Veure estadístiques de la flota

### 5. **Admin** (Administrador)
- Administrador del sistema
- Hereta permisos de **Manager** + afegeix:
  - Accés al panell d'administració
  - Veure, editar, desactivar i eliminar usuaris
  - Gestionar rols
  - Veure logs del sistema
  - Gestionar configuració del sistema
  - Eliminar vehicles i reserves

### 6. **Superadmin** (Superadministrador)
- Màxim nivell d'accés
- **Tots els permisos possibles** + afegeix:
  - Gestionar altres administradors
  - Configuració avançada del sistema
  - Accés directe a la base de dades

---

## 🔧 Com Utilitzar el Sistema

### 1️⃣ **En Controllers** (Protegir rutes)

#### Protegir amb autenticació simple:
```php
public function myMethod() {
    $userId = AuthController::requireAuth(); // Requereix estar autenticat
    
    // El teu codi aquí...
}
```

#### Protegir amb rol específic:
```php
public function adminOnlyMethod() {
    $userId = AuthController::requireRole('admin'); // Només admins
    
    // Codi d'admin...
}
```

#### Protegir amb permís específic:
```php
public function editVehicle() {
    $userId = AuthController::requirePermission('edit_vehicle'); // Requereix permís
    
    // Codi per editar vehicle...
}
```

#### Comprovar sense aturar l'execució:
```php
public function mixedMethod() {
    $userId = AuthController::requireAuth();
    
    if (AuthController::hasRole('premium')) {
        // Funcionalitat premium
    } else {
        // Funcionalitat estàndard
    }
}
```

### 2️⃣ **En Views** (Mostrar/amagar elements)

#### Mostrar només per admins:
```php
<?php if ($auth['is_admin']): ?>
    <a href="/admin">Panell Admin</a>
<?php endif; ?>
```

#### Mostrar per múltiples rols:
```php
<?php if ($auth['is_manager'] || $auth['is_admin']): ?>
    <a href="/fleet">Gestió de Flota</a>
<?php endif; ?>
```

#### Comprovar permís específic:
```php
<?php if ($auth['can']('edit_vehicle')): ?>
    <button>Editar Vehicle</button>
<?php endif; ?>
```

#### Mostrar rol actual:
```php
<p>Benvingut, <?= $auth['role_display'] ?>!</p>
```

#### Mostrar contingut diferent segons rol:
```php
<?php if ($auth['is_premium']): ?>
    <p>Tens minuts il·limitats! 🎉</p>
<?php else: ?>
    <p>Temps disponible: <?= $minute_balance ?> min</p>
    <a href="/premium">Fes-te Premium</a>
<?php endif; ?>
```

### 3️⃣ **En Routes** (Protegir endpoints)

```php
// routes/web.php

// Només autenticats
$router->get('/dashboard', [DashboardController::class, 'index'], AuthController::requireAuth());

// Només admins
$router->get('/admin', [AdminController::class, 'index'], AuthController::requireAdmin());

// Amb rol específic
$router->get('/fleet', [FleetController::class, 'index'], function() {
    AuthController::requireRole('manager');
});

// Amb permís específic
$router->post('/vehicles/add', [VehicleController::class, 'add'], function() {
    AuthController::requirePermission('add_vehicle');
});
```

---

## 📊 Jerarquia de Permisos

Cada rol **hereta** els permisos dels rols inferiors:

```
Superadmin
    ↓ (hereta tots els permisos)
Admin
    ↓ (hereta Manager, Premium, User, Guest)
Manager
    ↓ (hereta Premium, User, Guest)
Premium
    ↓ (hereta User, Guest)
User
    ↓ (hereta Guest)
Guest
```

**Exemple:** Un **Manager** té accés a tot el que pot fer un **Premium**, un **User** i un **Guest**.

---

## 🎯 Permisos per Rol

### 🔹 Permisos de **Guest**:
- `view_home`
- `view_login`
- `view_register`

### 🔹 Permisos de **User**:
- `view_dashboard`
- `view_profile`
- `edit_own_profile`
- `view_vehicles`
- `search_vehicles`
- `claim_vehicle`
- `release_vehicle`
- `control_own_vehicle`
- `view_own_bookings`
- `create_booking`
- `purchase_time`
- `view_payment_history`

### 🔹 Permisos de **Premium**:
- Tots els de User +
- `unlimited_minutes`
- `priority_booking`
- `discount_rates`
- `premium_vehicles`
- `advanced_stats`

### 🔹 Permisos de **Manager**:
- Tots els de Premium +
- `view_all_vehicles`
- `add_vehicle`
- `edit_vehicle`
- `disable_vehicle`
- `view_all_bookings`
- `manage_vehicle_maintenance`
- `view_fleet_stats`

### 🔹 Permisos de **Admin**:
- Tots els de Manager +
- `view_admin_panel`
- `view_all_users`
- `edit_users`
- `disable_users`
- `delete_users`
- `manage_roles`
- `view_system_logs`
- `manage_settings`
- `delete_vehicle`
- `delete_booking`

### 🔹 Permisos de **Superadmin**:
- **TOTS** els permisos possibles +
- `manage_admins`
- `system_configuration`
- `database_access`
- `full_control`

---

## 🔐 Variables Disponibles en Views

Cada vista té automàticament la variable `$auth` amb:

```php
$auth = [
    'role' => 'user',              // Nom del rol (lowercase)
    'role_display' => 'User',      // Nom del rol (capitalitzat)
    'is_guest' => false,           // Boolean: és convidat?
    'is_user' => true,             // Boolean: és usuari estàndard?
    'is_premium' => false,         // Boolean: és premium?
    'is_manager' => false,         // Boolean: és gestor?
    'is_admin' => false,           // Boolean: és admin?
    'is_superadmin' => false,      // Boolean: és superadmin?
    'permissions' => [...],        // Array amb tots els permisos
    'can' => function($perm) {...} // Funció per comprovar permisos
];
```

---

## 🛠️ Exemples Pràctics

### Exemple 1: Protegir un Controller
```php
<?php
// controllers/FleetController.php

class FleetController {
    
    public function index() {
        // Només managers i admins
        AuthController::requireRole('manager');
        
        // Obtenir tots els vehicles
        $vehicles = $this->vehicleModel->getAllVehicles();
        
        Router::view('fleet.index', [
            'vehicles' => $vehicles
        ]);
    }
    
    public function addVehicle() {
        // Requereix permís específic
        AuthController::requirePermission('add_vehicle');
        
        // Lògica per afegir vehicle...
    }
}
```

### Exemple 2: Vista Adaptativa
```php
<!-- views/dashboard/gestio.php -->

<div class="dashboard-cards">
    <!-- Sempre visible per usuaris autenticats -->
    <a href="/perfil">Perfil</a>
    <a href="/vehicles">Vehicles</a>
    
    <!-- Només per Premium -->
    <?php if ($auth['is_premium']): ?>
        <a href="/premium-features">Funcions Premium ⭐</a>
    <?php endif; ?>
    
    <!-- Només per Managers i Admins -->
    <?php if ($auth['can']('view_all_vehicles')): ?>
        <a href="/fleet">Gestió de Flota 🚗</a>
    <?php endif; ?>
    
    <!-- Només per Admins -->
    <?php if ($auth['is_admin']): ?>
        <a href="/admin">Panell Admin 🛡️</a>
    <?php endif; ?>
</div>

<!-- Contingut diferent segons rol -->
<?php if ($auth['is_premium']): ?>
    <div class="premium-banner">
        <h2>Tens accés Premium! 🎉</h2>
        <p>Minuts il·limitats i prioritat en reserves</p>
    </div>
<?php else: ?>
    <div class="upgrade-banner">
        <h2>Fes-te Premium!</h2>
        <p>Per només 9.99€/mes tingues minuts il·limitats</p>
        <a href="/premium">Millorar Compte</a>
    </div>
<?php endif; ?>
```

### Exemple 3: Route amb Protecció
```php
<?php
// routes/web.php

// Pàgina pública
$router->get('/', [HomeController::class, 'index']);

// Requereix autenticació
$router->get('/dashboard', [DashboardController::class, 'index'], function() {
    AuthController::requireAuth();
});

// Requereix ser Manager
$router->get('/fleet', [FleetController::class, 'index'], function() {
    AuthController::requireRole('manager');
});

// Requereix ser Admin
$router->get('/admin', [AdminController::class, 'index'], function() {
    AuthController::requireAdmin();
});

// Requereix permís específic
$router->post('/vehicles/add', [VehicleController::class, 'add'], function() {
    AuthController::requirePermission('add_vehicle');
});
```

---

## 🗄️ Configuració de Base de Dades

Assegura't que tens la taula `roles`:

```sql
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (name, description) VALUES
('guest', 'Usuari no autenticat'),
('user', 'Usuari estàndard registrat'),
('premium', 'Usuari amb subscripció premium'),
('manager', 'Gestor de flota de vehicles'),
('admin', 'Administrador del sistema'),
('superadmin', 'Superadministrador amb accés total');

-- Afegir role_id a users si no existeix
ALTER TABLE users ADD COLUMN role_id INT DEFAULT 2;
ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id);
```

---

## ✅ Checklist d'Implementació

- [x] Classe `Authorization` creada
- [x] Rols i permisos definits
- [x] Jerarquia de rols implementada
- [x] Mètodes de middleware en `AuthController`
- [x] Session guarda `role_id` i `role_name`
- [x] Router passa automàticament `$auth` a views
- [x] Exemples de protecció en controllers
- [x] Exemples de visualització condicional en views
- [ ] Protegir totes les rutes sensibles
- [ ] Actualitzar totes les vistes amb control de rols
- [ ] Crear panell admin amb gestió de rols
- [ ] Tests de permisos

---

## 🚀 Pròxims Passos

1. **Protegir routes**: Revisa `routes/web.php` i afegeix protecció segons necessitis
2. **Actualitzar views**: Revisa totes les vistes i amaga/mostra elements segons rol
3. **Crear panell admin**: Implementa `/admin` amb gestió d'usuaris i rols
4. **Testing**: Crea usuaris amb diferents rols i prova l'accés

---

## 📞 Suport

Si tens dubtes sobre com utilitzar el sistema d'autorització:
- Revisa els exemples d'aquest document
- Mira `core/Authorization.php` per veure tots els mètodes disponibles
- Consulta `controllers/AuthController.php` per middleware examples

---

**Ara tens control total sobre qui pot veure i fer què! 🎉**
