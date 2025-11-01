# 🔐 Sistema de Roles i Permisos

## Rols del Sistema

### 1️⃣ SuperAdmin (role_id = 1)
**Accés complet al sistema**

#### Permisos:
- ✅ **Usuaris**
  - Visualitzar tots els usuaris
  - Crear nous usuaris
  - Editar qualsevol usuari
  - Eliminar usuaris (excepte ell mateix)
  - Canviar rols d'usuaris

- ✅ **Vehicles**
  - Visualitzar tots els vehicles
  - Crear vehicles
  - Editar qualsevol vehicle
  - Eliminar vehicles

- ✅ **Reserves (Bookings)**
  - Visualitzar totes les reserves
  - Crear reserves
  - Editar qualsevol reserva
  - Cancel·lar/Eliminar reserves
  - Gestionar les seves pròpies reserves

- ✅ **Administració**
  - Accés al panell d'administració
  - Configuració del sistema
  - Reports i estadístiques
  - Historial d'activitat

---

### 2️⃣ Treballador (role_id = 2)
**Gestió operativa de vehicles i reserves**

#### Permisos:
- ✅ **Usuaris**
  - Només visualitzar (no pot crear, editar ni eliminar)

- ✅ **Vehicles**
  - Visualitzar tots els vehicles
  - Crear vehicles
  - Editar vehicles
  - ⛔ **NO** pot eliminar vehicles

- ✅ **Reserves (Bookings)**
  - Visualitzar totes les reserves
  - Crear reserves
  - Editar reserves
  - Cancel·lar les seves pròpies reserves
  - ⛔ **NO** pot eliminar reserves

- ✅ **Administració**
  - Accés al panell d'administració (vista limitada)
  - Reports i estadístiques
  - Historial d'activitat
  - ⛔ **NO** pot accedir a configuració del sistema

---

### 3️⃣ Client (role_id = 3)
**Usuari final de l'aplicació**

#### Permisos:
- ⛔ **Usuaris**
  - No pot gestionar usuaris

- ✅ **Vehicles**
  - Només visualitzar vehicles disponibles
  - ⛔ **NO** pot crear, editar ni eliminar

- ✅ **Reserves (Bookings)**
  - Visualitzar només les seves pròpies reserves
  - Crear reserves per a ell mateix
  - Cancel·lar les seves pròpies reserves
  - ⛔ **NO** pot veure reserves d'altres
  - ⛔ **NO** pot editar ni eliminar reserves

- ⛔ **Administració**
  - No té accés al panell d'administració
  - Només veu el dashboard públic

---

## Implementació Tècnica

### AuthMiddleware
```php
// Verificar autenticació
AuthMiddleware::requireAuth();

// Verificar si és SuperAdmin
AuthMiddleware::requireSuperAdmin();

// Verificar si és Staff (SuperAdmin o Treballador)
AuthMiddleware::requireStaff();

// Verificar rol específic
AuthMiddleware::requireRole([1, 2]);

// Helpers
AuthMiddleware::isSuperAdmin();    // true si role_id = 1
AuthMiddleware::isTreballador();   // true si role_id = 2
AuthMiddleware::isClient();        // true si role_id = 3
AuthMiddleware::isStaff();         // true si role_id = 1 o 2
```

### Permissions
```php
// Verificar permís
if (Permissions::can('users.edit')) {
    // Mostrar botó d'editar
}

// Llançar error si no té permís
Permissions::authorize('users.delete');

// Verificar múltiples permisos
Permissions::canAll(['users.view', 'users.edit']);
Permissions::canAny(['admin.dashboard', 'admin.reports']);

// Helpers
Permissions::canManageUsers();        // Pot gestionar usuaris?
Permissions::canManageVehicles();     // Pot gestionar vehicles?
Permissions::canViewAdminPanel();     // Pot veure admin panel?
Permissions::canDeleteAnything();     // És SuperAdmin?
```

### Permisos Definits

| Permís | SuperAdmin | Treballador | Client |
|--------|-----------|-------------|--------|
| `users.view` | ✅ | ✅ | ⛔ |
| `users.create` | ✅ | ⛔ | ⛔ |
| `users.edit` | ✅ | ⛔ | ⛔ |
| `users.delete` | ✅ | ⛔ | ⛔ |
| `vehicles.view` | ✅ | ✅ | ✅ |
| `vehicles.create` | ✅ | ✅ | ⛔ |
| `vehicles.edit` | ✅ | ✅ | ⛔ |
| `vehicles.delete` | ✅ | ⛔ | ⛔ |
| `bookings.view_all` | ✅ | ✅ | ⛔ |
| `bookings.view_own` | ✅ | ✅ | ✅ |
| `bookings.create` | ✅ | ✅ | ✅ |
| `bookings.edit` | ✅ | ✅ | ⛔ |
| `bookings.delete` | ✅ | ⛔ | ⛔ |
| `bookings.cancel_own` | ✅ | ✅ | ✅ |
| `admin.dashboard` | ✅ | ✅ | ⛔ |
| `admin.settings` | ✅ | ⛔ | ⛔ |
| `admin.reports` | ✅ | ✅ | ⛔ |
| `admin.activity` | ✅ | ✅ | ⛔ |

---

## Ús en Controllers

### Protegir tot el controller
```php
class UserController {
    public function __construct() {
        AuthMiddleware::requireStaff();
    }
}
```

### Protegir mètodes individuals
```php
public function create() {
    Permissions::authorize('users.create');
    // Codi...
}

public function delete() {
    Permissions::authorize('users.delete');
    // Codi...
}
```

---

## Ús en Views

### Mostrar/Ocultar elements
```php
<?php if (Permissions::can('users.create')): ?>
    <a href="/admin/users/create" class="btn">Nou Usuari</a>
<?php endif; ?>

<?php if (Permissions::can('users.edit')): ?>
    <a href="/admin/users/edit?id=<?= $user['id'] ?>">Editar</a>
<?php endif; ?>

<?php if (Permissions::can('users.delete') && $user['id'] != 1): ?>
    <button onclick="deleteUser(<?= $user['id'] ?>)">Eliminar</button>
<?php endif; ?>
```

### Missatges segons rol
```php
<?php if (AuthMiddleware::isSuperAdmin()): ?>
    <div class="alert alert-info">
        Tens accés complet al sistema
    </div>
<?php elseif (AuthMiddleware::isTreballador()): ?>
    <div class="alert alert-warning">
        Accés limitat a gestió de vehicles i reserves
    </div>
<?php endif; ?>
```

---

## Redirects segons Rol

Després del login:
- **SuperAdmin (1)** → `/admin/users`
- **Treballador (2)** → `/admin/users` (només visualitza)
- **Client (3)** → `/dashboard`

---

## Seguretat

1. **Tots els controllers admin** han de tenir `AuthMiddleware::requireStaff()` al constructor
2. **Accions destructives** (delete) han de tenir `Permissions::authorize('xxx.delete')`
3. **Vistes** han de comprovar permisos abans de mostrar botons/formularis
4. **Mai confiar només en la UI** - sempre validar al backend

---

## Testing de Permisos

### SuperAdmin (username: admin, role_id=1)
```
✅ Pot veure /admin/users
✅ Pot crear usuari
✅ Pot editar usuari
✅ Pot eliminar usuari
✅ Veu tots els botons
```

### Treballador (username: treballador1, role_id=2)
```
✅ Pot veure /admin/users
⛔ NO veu botó "Nou Usuari"
⛔ NO veu botons "Editar" ni "Eliminar"
⛔ Si intenta accedir a /admin/users/create → Redirigeix amb error
```

### Client (username: user1, role_id=3)
```
⛔ NO pot accedir a /admin/users
⛔ Redirigeix a /dashboard amb missatge d'error
⛔ Només veu vehicles i reserves pròpies
```
