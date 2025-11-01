# 🧪 Testing del Sistema de Roles i Permisos

## Usuaris de Test

La base de dades té els següents usuaris per fer testing:

### SuperAdmin
- **Username**: `admin`
- **Password**: `admin123`
- **Role**: SuperAdmin (role_id = 1)
- **Accés**: Complet

### Treballadors
- **Username**: `treballador1`
- **Password**: `treballador123`
- **Role**: Treballador (role_id = 2)
- **Accés**: Pot veure usuaris, gestionar vehicles i reserves

### Clients
- **Username**: `user1`
- **Password**: `user123`
- **Role**: Client (role_id = 3)
- **Accés**: Només dashboard i reserves pròpies

---

## Test Cases

### ✅ Test 1: Login i Redirect
1. Fes login com a **admin** → Ha de redirigir a `/admin/users`
2. Fes login com a **treballador1** → Ha de redirigir a `/admin/users`
3. Fes login com a **user1** → Ha de redirigir a `/dashboard`

### ✅ Test 2: SuperAdmin - Accés Complet
Fes login com a **admin**:

1. Accedeix a `/admin/users`
   - ✅ Has de veure el botó "Nou Usuari"
   - ✅ Has de veure botons "Editar" a cada fila
   - ✅ Has de veure botons "Eliminar" a cada fila (excepte user id=1)

2. Crea un nou usuari:
   - Clica "Nou Usuari"
   - Omple el formulari
   - ✅ Ha de guardar-se correctament

3. Edita un usuari:
   - Clica "Editar" en qualsevol usuari
   - Canvia el nom
   - ✅ Ha de guardar-se correctament

4. Intenta eliminar un usuari:
   - Clica "Eliminar" en un usuari (no el primer)
   - Confirma
   - ✅ Ha d'eliminar-se correctament

### ✅ Test 3: Treballador - Accés Limitat
Fes login com a **treballador1**:

1. Accedeix a `/admin/users`
   - ✅ Has de veure la llista d'usuaris
   - ⛔ **NO** has de veure el botó "Nou Usuari"
   - ⛔ **NO** has de veure botons "Editar"
   - ⛔ **NO** has de veure botons "Eliminar"

2. Intenta accedir directament a `/admin/users/create`:
   - ⛔ Ha de redirigir a `/dashboard`
   - ⛔ Ha de mostrar missatge: "No tens permisos per accedir a aquesta pàgina."

3. Intenta accedir directament a `/admin/users/edit?id=2`:
   - ⛔ Ha de redirigir a `/dashboard`
   - ⛔ Ha de mostrar missatge: "No tens permisos per accedir a aquesta pàgina."

### ✅ Test 4: Client - Sense Accés Admin
Fes login com a **user1**:

1. Intenta accedir a `/admin/users`:
   - ⛔ Ha de redirigir a `/dashboard`
   - ⛔ Ha de mostrar missatge: "Accés denegat. Només per personal autoritzat."

2. Intenta accedir a `/admin/users/create`:
   - ⛔ Ha de redirigir a `/dashboard`
   - ⛔ Ha de mostrar missatge d'error

3. Verifica que només veus el dashboard públic:
   - ✅ Has de veure `/dashboard`
   - ✅ Pots veure vehicles
   - ✅ Pots crear reserves

---

## Checklist de Seguretat

### Backend Protection
- ✅ `UserController::__construct()` té `AuthMiddleware::requireStaff()`
- ✅ `UserController::create()` té `Permissions::authorize('users.create')`
- ✅ `UserController::store()` té `Permissions::authorize('users.create')`
- ✅ `UserController::edit()` té `Permissions::authorize('users.edit')`
- ✅ `UserController::update()` té `Permissions::authorize('users.edit')`
- ✅ `UserController::delete()` té `Permissions::authorize('users.delete')`

### Frontend Protection
- ✅ Botó "Nou Usuari" només visible si `can('users.create')`
- ✅ Botó "Editar" només visible si `can('users.edit')`
- ✅ Botó "Eliminar" només visible si `can('users.delete')`

### Session Data
- ✅ `$_SESSION['user_id']` - ID de l'usuari
- ✅ `$_SESSION['username']` - Nom d'usuari
- ✅ `$_SESSION['role_id']` - ID del rol (1, 2 o 3)
- ✅ `$_SESSION['role_name']` - Nom del rol (SuperAdmin, Treballador, Client)
- ✅ `$_SESSION['is_admin']` - 1 si és Staff (role_id 1 o 2), 0 si Client

---

## Errors Comuns

### ❌ "Call to undefined function can()"
**Solució**: Assegura't que `helpers.php` està carregat a `index.php`

### ❌ "Call to undefined method Permissions::can()"
**Solució**: Assegura't que `Permissions.php` està a `core/`

### ❌ Els Treballadors veuen botons d'editar
**Solució**: Verifica que la vista usa `can('users.edit')` i no `isStaff()`

### ❌ Els Clients poden accedir a /admin/users
**Solució**: Verifica que el `UserController::__construct()` té `AuthMiddleware::requireStaff()`

---

## Debugging

### Veure informació de sessió
Afegeix temporalment a qualsevol vista:
```php
<pre><?php print_r($_SESSION); ?></pre>
```

### Veure permisos de l'usuari
```php
<pre><?php print_r(Permissions::getUserPermissions()); ?></pre>
```

### Veure rol actual
```php
<div class="alert alert-info">
    Rol: <?= getRoleName() ?> (ID: <?= $_SESSION['role_id'] ?>)
    <?= roleBadge() ?>
</div>
```

---

## Próxims Passos

1. ✅ Sistema de roles implementat
2. ✅ AuthMiddleware creat
3. ✅ Permissions creat
4. ✅ UserController protegit
5. ✅ Vistes actualitzades
6. 🔄 **Aplicar mateix sistema a VehicleController**
7. 🔄 **Aplicar mateix sistema a BookingController**
8. 🔄 **Crear vista diferents de dashboard segons rol**

---

## Contacte

Si trobes problemes o bugs, documenta:
1. Usuari amb el que fas login
2. URL que intentes accedir
3. Missatge d'error rebut
4. Comportament esperat vs comportament actual
