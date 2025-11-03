# 🚀 Flux de Login i Redirects

## Diagrama de Flux

```
┌─────────────────┐
│  Usuario hace   │
│     LOGIN       │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  AuthController::attemptLogin()     │
│  - Verifica credenciales            │
│  - Guarda en sesión:                │
│    * user_id                        │
│    * username                       │
│    * role_id (1, 2 o 3)            │
│    * role_name                      │
│    * is_admin (1 o 0)              │
└────────┬────────────────────────────┘
         │
         ▼
    ¿Login OK?
         │
    ┌────┴────┐
    NO        YES
    │         │
    ▼         ▼
Redirect  Verificar
/login    role_id
          │
    ┌─────┴─────────┐
    │               │
    ▼               ▼
role_id = 1    role_id = 3
    o 2           │
    │             │
    ▼             ▼
┌──────────┐  ┌──────────┐
│  ADMIN   │  │ CLIENTE  │
│Dashboard │  │Dashboard │
└──────────┘  └──────────┘
    │             │
    ▼             ▼
/admin/dashboard  /dashboard
```

## Detalles de Implementación

### 1️⃣ SuperAdmin (role_id = 1)
- **Redirect después de login**: `/admin/dashboard`
- **Vista**: `views/admin/dashboard.php`
- **Controller**: `AdminController::dashboard()`
- **Acceso**: Completo a todo el sistema

### 2️⃣ Treballador (role_id = 2)
- **Redirect después de login**: `/admin/dashboard`
- **Vista**: `views/admin/dashboard.php` (misma que SuperAdmin)
- **Controller**: `AdminController::dashboard()`
- **Acceso**: Gestión operativa (sin eliminar)

### 3️⃣ Client (role_id = 3)
- **Redirect después de login**: `/dashboard`
- **Vista**: `views/public/dashboard/gestio.php`
- **Controller**: `DashboardController::showGestio()`
- **Acceso**: Solo vistas públicas y reservas propias

---

## Código Implementado

### AuthController.php (líneas ~47-60)
```php
// 🎯 Redirigir segons el rol
$roleId = $_SESSION['role_id'] ?? 3;
if ($roleId == 1 || $roleId == 2) {
    // SuperAdmin i Treballadors → Dashboard Admin
    return Router::redirect('/admin/dashboard');
} else {
    // Clients → Dashboard Públic
    return Router::redirect('/dashboard');
}
```

### routes/web.php
```php
// Dashboard Admin (SuperAdmin y Treballador)
Router::get('/admin/dashboard', ['AdminController', 'dashboard']);

// Dashboard Público (Client)
Router::get('/dashboard', ['DashboardController', 'showGestio']);
```

---

## Testing del Flux

### ✅ Test 1: Login SuperAdmin
1. Usuario: `admin` / Password: `admin123`
2. Click "Login"
3. ✅ Debe redirigir a `/admin/dashboard`
4. ✅ Debe ver estadísticas del sistema
5. ✅ Debe tener menú lateral admin

### ✅ Test 2: Login Treballador
1. Usuario: `treballador1` / Password: `treballador123`
2. Click "Login"
3. ✅ Debe redirigir a `/admin/dashboard`
4. ✅ Debe ver estadísticas (misma vista que admin)
5. ✅ Debe tener menú lateral admin

### ✅ Test 3: Login Client
1. Usuario: `user1` / Password: `user123`
2. Click "Login"
3. ✅ Debe redirigir a `/dashboard`
4. ✅ Debe ver su dashboard personal (gestió)
5. ✅ No debe tener acceso a panel admin

### ❌ Test 4: Client intenta acceder a admin
1. Logueado como `user1`
2. Intenta acceder directamente a `/admin/dashboard`
3. ✅ Debe redirigir a `/dashboard`
4. ✅ Debe mostrar error: "Accés denegat. Només per personal autoritzat."

---

## Personalización por Rol

### Dashboard Admin (`/admin/dashboard`)
**Visible para**: SuperAdmin (1), Treballador (2)

**Contenido**:
- 📊 Estadísticas generales:
  - Total usuarios
  - Total vehículos
  - Vehículos disponibles
  - Total reservas
  - Reservas activas
- 📈 Gráficos y métricas
- 📋 Lista de reservas recientes
- 🚗 Lista de vehículos activos
- 👥 Últimos usuarios registrados

**Diferencias**:
- **SuperAdmin**: Ve TODO + opciones de eliminar
- **Treballador**: Ve TODO pero SIN opciones de eliminar

### Dashboard Público (`/dashboard`)
**Visible para**: Client (3)

**Contenido**:
- 🚗 Reserva activa (si tiene)
- 📅 Historial de reservas propias (últimas 5)
- 🔍 Buscar vehículos disponibles
- 👤 Información de perfil
- ⚙️ Acceso a configuración personal

---

## Seguridad

### Protección Backend
```php
// AdminController::dashboard()
$userId = AuthController::requireAuth();
$roleId = $_SESSION['role_id'] ?? 3;

if (!in_array($roleId, [1, 2])) {
    $_SESSION['error'] = 'Accés denegat.';
    Router::redirect('/dashboard');
    exit;
}
```

### Protección Frontend
```php
// En el header admin
<?php if (!isStaff()): ?>
    <?php Router::redirect('/dashboard'); ?>
<?php endif; ?>
```

---

## Variables de Sesión Disponibles

Después del login, están disponibles:
- `$_SESSION['user_id']` - ID del usuario
- `$_SESSION['username']` - Nombre de usuario
- `$_SESSION['role_id']` - ID del rol (1, 2 o 3)
- `$_SESSION['role_name']` - Nombre del rol (SuperAdmin, Treballador, Client)
- `$_SESSION['is_admin']` - 1 si es Staff (role_id 1 o 2), 0 si es Cliente

---

## Helpers Disponibles en Vistas

```php
// Verificar rol
<?php if (isSuperAdmin()): ?>
    <!-- Solo SuperAdmin -->
<?php elseif (isTreballador()): ?>
    <!-- Solo Treballador -->
<?php elseif (isClient()): ?>
    <!-- Solo Cliente -->
<?php endif; ?>

// Verificar si es Staff
<?php if (isStaff()): ?>
    <!-- SuperAdmin o Treballador -->
<?php endif; ?>

// Mostrar badge del rol
<?= roleBadge() ?>

// Obtener nombre del rol
<?= getRoleName() ?>
```
