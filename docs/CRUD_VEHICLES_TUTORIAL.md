# 📚 Tutorial: CRUD de Vehículos en MVC

## 🎯 Objetivo
Aprender a crear un CRUD completo siguiendo el patrón **MVC (Model-View-Controller)** en PHP.

---

## 📖 ¿Qué es MVC?

**MVC** es un patrón de diseño que separa la aplicación en 3 capas:

### 1. **MODEL (Modelo)** - `models/Vehicle.php`
- **Responsabilidad**: Comunicarse con la base de datos
- **Contiene**: Métodos para crear, leer, actualizar y eliminar datos (CRUD)
- **NO debe**: Mostrar HTML ni manejar peticiones HTTP

### 2. **VIEW (Vista)** - `views/admin/Vehicles/*.php`
- **Responsabilidad**: Mostrar información al usuario (HTML)
- **Contiene**: Formularios, tablas, botones
- **NO debe**: Contener lógica de negocio ni consultas a BD

### 3. **CONTROLLER (Controlador)** - `controllers/admin/VehicleController.php`
- **Responsabilidad**: Coordinar Model y View
- **Contiene**: Lógica de negocio, validaciones, decisiones
- **NO debe**: Tener SQL directo ni HTML

---

## 📂 Estructura del Proyecto

```
SIMS---GRUP-2/
├── models/
│   └── Vehicle.php              ← Modelo (BD)
├── controllers/
│   └── admin/
│       └── VehicleController.php  ← Controlador
├── views/
│   └── admin/
│       └── Vehicles/
│           ├── index.php         ← Lista de vehículos
│           ├── create.php        ← Formulario crear
│           ├── edit.php          ← Formulario editar
│           └── show.php          ← Ver detalles
├── routes/
│   └── web.php                  ← Rutas (URLs)
└── core/
    └── Router.php               ← Sistema de rutas
```

---

## 🔄 Flujo de una Petición

### Ejemplo: Usuario visita `/admin/vehicles`

```
1. Usuario → Navegador: http://localhost/admin/vehicles
                 ↓
2. index.php → Captura la petición
                 ↓
3. Router → Busca en routes/web.php la ruta '/admin/vehicles'
                 ↓
4. Router → Llama a AdminVehicleController::index()
                 ↓
5. Controller → Llama a Vehicle::getAllVehicles() (Modelo)
                 ↓
6. Modelo → Consulta la BD: SELECT * FROM vehicles
                 ↓
7. Modelo → Devuelve array de vehículos al Controller
                 ↓
8. Controller → Pasa datos a la Vista: Router::view('admin.Vehicles.index', $vehicles)
                 ↓
9. Vista → Renderiza HTML con los datos
                 ↓
10. Navegador ← Muestra la página al usuario
```

---

## 🛠️ Implementación del CRUD

### ✅ 1. MODELO - `models/Vehicle.php`

**Métodos implementados:**

| Método | Descripción | SQL |
|--------|-------------|-----|
| `getAllVehicles()` | Obtener todos los vehículos | `SELECT * FROM vehicles` |
| `getVehicleById($id)` | Obtener un vehículo por ID | `SELECT * FROM vehicles WHERE id = ?` |
| `create($data)` | Crear nuevo vehículo | `INSERT INTO vehicles (...)` |
| `update($id, $data)` | Actualizar vehículo | `UPDATE vehicles SET ... WHERE id = ?` |
| `delete($id)` | Eliminar vehículo | `DELETE FROM vehicles WHERE id = ?` |
| `search($filters)` | Buscar con filtros | `SELECT * WHERE brand LIKE ...` |
| `validate($data)` | Validar datos | Lógica de validación |

**Ejemplo de método:**

```php
public function create($data) {
    $stmt = $this->db->prepare("
        INSERT INTO vehicles (plate, brand, model, year, ...)
        VALUES (?, ?, ?, ?, ...)
    ");
    
    $stmt->bind_param('sssi...', 
        $data['plate'],
        $data['brand'],
        $data['model'],
        $data['year']
    );
    
    if ($stmt->execute()) {
        return $this->db->insert_id; // Devuelve el ID del nuevo vehículo
    }
    
    return false;
}
```

---

### ✅ 2. CONTROLADOR - `controllers/admin/VehicleController.php`

**Métodos implementados:**

| Método | Ruta | Descripción |
|--------|------|-------------|
| `index()` | GET `/admin/vehicles` | Lista todos los vehículos |
| `create()` | GET `/admin/vehicles/create` | Muestra formulario de crear |
| `store()` | POST `/admin/vehicles` | Guarda nuevo vehículo |
| `show($id)` | GET `/admin/vehicles/{id}` | Muestra detalle de un vehículo |
| `edit($id)` | GET `/admin/vehicles/{id}/edit` | Muestra formulario de editar |
| `update($id)` | POST `/admin/vehicles/{id}` (PUT) | Actualiza vehículo |
| `destroy($id)` | POST `/admin/vehicles/{id}` (DELETE) | Elimina vehículo |

**Ejemplo de método:**

```php
public function store() {
    // 1. Obtener datos del formulario
    $data = [
        'plate' => $_POST['plate'],
        'brand' => $_POST['brand'],
        'model' => $_POST['model'],
        // ...
    ];
    
    // 2. Validar (usando el modelo)
    $errors = $this->vehicleModel->validate($data);
    
    if (!empty($errors)) {
        // Guardar errores en sesión y volver al formulario
        $_SESSION['errors'] = $errors;
        return Router::redirect('/admin/vehicles/create');
    }
    
    // 3. Crear vehículo (llamar al modelo)
    $vehicleId = $this->vehicleModel->create($data);
    
    // 4. Redirigir con mensaje de éxito
    if ($vehicleId) {
        $_SESSION['success'] = 'Vehículo creado correctamente';
        return Router::redirect('/admin/vehicles/' . $vehicleId);
    }
}
```

---

### ✅ 3. VISTAS - `views/admin/Vehicles/*.php`

**Archivos creados:**

| Archivo | Propósito |
|---------|-----------|
| `index.php` | Tabla con listado de vehículos + filtros |
| `create.php` | Formulario para crear vehículo |
| `edit.php` | Formulario para editar vehículo |
| `show.php` | Detalle completo de un vehículo |

**Ejemplo (index.php):**

```php
<?php require_once __DIR__ . '/../admin-header.php'; ?>

<h1>Gestión de Vehículos</h1>

<!-- Tabla de vehículos -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Matrícula</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vehicles as $vehicle): ?>
            <tr>
                <td><?= $vehicle['id'] ?></td>
                <td><?= htmlspecialchars($vehicle['plate']) ?></td>
                <td><?= htmlspecialchars($vehicle['brand']) ?></td>
                <td><?= htmlspecialchars($vehicle['model']) ?></td>
                <td>
                    <a href="/admin/vehicles/<?= $vehicle['id'] ?>">Ver</a>
                    <a href="/admin/vehicles/<?= $vehicle['id'] ?>/edit">Editar</a>
                    <button onclick="deleteVehicle(<?= $vehicle['id'] ?>)">Eliminar</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../admin-footer.php'; ?>
```

---

### ✅ 4. RUTAS - `routes/web.php`

**Rutas definidas:**

```php
// INDEX - Listar todos
Router::get('/admin/vehicles', ['AdminVehicleController', 'index']);

// CREATE - Mostrar formulario
Router::get('/admin/vehicles/create', ['AdminVehicleController', 'create']);

// STORE - Guardar nuevo
Router::post('/admin/vehicles', ['AdminVehicleController', 'store']);

// SHOW - Ver detalle
Router::get('/admin/vehicles/{id}', ['AdminVehicleController', 'show']);

// EDIT - Mostrar formulario editar
Router::get('/admin/vehicles/{id}/edit', ['AdminVehicleController', 'edit']);

// UPDATE - Actualizar (simulado con POST + _method=PUT)
Router::post('/admin/vehicles/{id}', ['AdminVehicleController', 'update']);

// DESTROY - Eliminar (simulado con POST + _method=DELETE)
Router::delete('/admin/vehicles/{id}', ['AdminVehicleController', 'destroy']);
```

**¿Por qué POST + _method?**

HTML solo soporta GET y POST. Para PUT y DELETE usamos un campo oculto:

```html
<form method="POST" action="/admin/vehicles/123">
    <input type="hidden" name="_method" value="PUT">
    <!-- campos del formulario -->
</form>
```

El Router lo detecta y trata la petición como PUT.

---

## 🚀 Cómo Usar el CRUD

### 1️⃣ **Listar Vehículos**
- URL: `http://localhost:8080/admin/vehicles`
- Muestra tabla con todos los vehículos
- Permite filtrar por marca, estado, tipo

### 2️⃣ **Crear Vehículo**
- Click en "Nuevo Vehículo"
- URL: `/admin/vehicles/create`
- Rellena el formulario
- Click en "Crear Vehículo"
- Se guarda en BD y redirige a `/admin/vehicles/{id}`

### 3️⃣ **Ver Detalle**
- Click en el ícono 👁️ de un vehículo
- URL: `/admin/vehicles/123`
- Muestra toda la información

### 4️⃣ **Editar Vehículo**
- Click en ✏️ o botón "Editar"
- URL: `/admin/vehicles/123/edit`
- Modifica campos
- Click en "Guardar Cambios"
- Se actualiza en BD

### 5️⃣ **Eliminar Vehículo**
- Click en 🗑️
- Aparece confirmación
- Si confirmas, se elimina de BD
- Redirige a `/admin/vehicles`

---

## 🔐 Seguridad Implementada

### 1. **Autenticación Admin**
```php
public function __construct() {
    // Solo admins pueden acceder
    AuthController::requireAdmin();
    $this->vehicleModel = new Vehicle();
}
```

### 2. **Validación de Datos**
```php
// En el modelo
public function validate($data) {
    $errors = [];
    
    if (empty($data['plate'])) {
        $errors[] = 'La matrícula es obligatoria';
    }
    
    // ... más validaciones
    
    return $errors;
}
```

### 3. **Protección XSS**
```php
// En las vistas, siempre usar htmlspecialchars
<?= htmlspecialchars($vehicle['brand']) ?>
```

### 4. **Prepared Statements**
```php
// En el modelo, prevenir SQL Injection
$stmt = $this->db->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->bind_param('i', $id);
```

---

## 📊 Diagrama del Flujo CRUD

```
┌─────────────────────────────────────────────────────────┐
│                    CRUD DE VEHÍCULOS                     │
└─────────────────────────────────────────────────────────┘

CREATE (Crear)
--------------
Usuario → /admin/vehicles/create (GET)
       → AdminVehicleController::create()
       → views/admin/Vehicles/create.php
       → [Usuario rellena formulario]
       → /admin/vehicles (POST)
       → AdminVehicleController::store()
       → Vehicle::create($data)
       → INSERT INTO vehicles...
       → Redirect a /admin/vehicles/{id}

READ (Leer)
-----------
Usuario → /admin/vehicles (GET)
       → AdminVehicleController::index()
       → Vehicle::getAllVehicles()
       → SELECT * FROM vehicles
       → views/admin/Vehicles/index.php

UPDATE (Actualizar)
-------------------
Usuario → /admin/vehicles/123/edit (GET)
       → AdminVehicleController::edit(123)
       → Vehicle::getVehicleById(123)
       → views/admin/Vehicles/edit.php
       → [Usuario modifica formulario]
       → /admin/vehicles/123 (POST + _method=PUT)
       → AdminVehicleController::update(123)
       → Vehicle::update(123, $data)
       → UPDATE vehicles SET ... WHERE id = 123
       → Redirect a /admin/vehicles/123

DELETE (Eliminar)
-----------------
Usuario → Click en eliminar
       → [Confirmación JavaScript]
       → /admin/vehicles/123 (POST + _method=DELETE)
       → AdminVehicleController::destroy(123)
       → Vehicle::delete(123)
       → DELETE FROM vehicles WHERE id = 123
       → Redirect a /admin/vehicles
```

---

## ✅ Checklist de Implementación

- [x] **Modelo** - Métodos CRUD en `Vehicle.php`
- [x] **Controlador** - `AdminVehicleController.php` creado
- [x] **Vistas** - 4 archivos (index, create, edit, show)
- [x] **Rutas** - Definidas en `routes/web.php`
- [x] **Router** - Actualizado para soportar PUT/DELETE
- [x] **Seguridad** - Validación, auth admin, XSS protection
- [x] **UX** - Mensajes de éxito/error, confirmaciones

---

## 🎓 Conceptos Aprendidos

1. **Separación de responsabilidades** (MVC)
2. **CRUD completo** (Create, Read, Update, Delete)
3. **Routing dinámico** con parámetros `{id}`
4. **Method spoofing** (PUT/DELETE con POST)
5. **Validación de datos** en el servidor
6. **Sesiones PHP** para mensajes flash
7. **Prepared Statements** para seguridad
8. **Reutilización de código** (DRY principle)

---

## 🚦 Próximos Pasos

### Para mejorar el CRUD:

1. **Paginación** - Si hay muchos vehículos
2. **Búsqueda avanzada** - Más filtros
3. **Subida de imágenes** - Upload de fotos
4. **Auditoría** - Log de cambios (quién, cuándo)
5. **Soft Delete** - No eliminar, marcar como eliminado
6. **API REST** - Endpoints JSON para uso con JavaScript
7. **Tests** - Pruebas automatizadas

### Para practicar:

- Crea un CRUD de **Users** siguiendo este mismo patrón
- Crea un CRUD de **Bookings** (reservas)
- Añade **relaciones** entre tablas (vehículo → reservas)

---

## 📞 Ayuda

Si tienes dudas:

1. Revisa el código comentado
2. Consulta este README
3. Usa `console.log()` en JavaScript
4. Usa `var_dump()` en PHP para debug
5. Comprueba la consola del navegador (F12)
6. Revisa logs de PHP en el contenedor Docker

---

**¡Felicidades! Has creado tu primer CRUD completo en MVC** 🎉
