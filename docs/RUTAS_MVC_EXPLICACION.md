# 🚦 Explicación: Rutas en MVC

## ❌ **Error Común: Confundir rutas de archivos con rutas del Router**

### **INCORRECTO** ❌

```php
<!-- En una vista -->
<a href="/views/admin/vehicles/create.php">Crear</a>
```

**¿Por qué está mal?**
- Intenta acceder directamente al archivo físico
- Salta el Router y el Controller
- No ejecuta la lógica del controlador
- No valida permisos de admin
- Puede generar errores de variables no definidas

---

### **CORRECTO** ✅

```php
<!-- En una vista -->
<a href="/admin/vehicles/create">Crear</a>
```

**¿Por qué está bien?**
- Usa la ruta del Router definida en `routes/web.php`
- Pasa por el Controller que valida permisos
- Ejecuta toda la lógica necesaria
- Renderiza la vista correctamente

---

## 🔄 **Flujo de una petición MVC**

```
1. Usuario hace click: <a href="/admin/vehicles/create">
                              ↓
2. Navegador hace petición: GET /admin/vehicles/create
                              ↓
3. index.php captura la petición
                              ↓
4. Router busca en routes/web.php:
   Router::get('/admin/vehicles/create', ['AdminVehicleController', 'create'])
                              ↓
5. Router carga y ejecuta:
   AdminVehicleController->create()
                              ↓
6. Controller valida permisos:
   AuthController::requireAdmin()
                              ↓
7. Controller renderiza vista:
   Router::view('admin.Vehicles.create')
                              ↓
8. Router convierte la notación de puntos a ruta:
   'admin.Vehicles.create' → views/admin/Vehicles/create.php
                              ↓
9. Se incluye y ejecuta el archivo PHP de la vista
                              ↓
10. HTML se envía al navegador
```

---

## 📋 **Tabla de Rutas del CRUD de Vehículos**

| Acción | Ruta URL (Router) | Archivo Vista | Controlador |
|--------|-------------------|---------------|-------------|
| Listar | `/admin/vehicles` | `views/admin/Vehicles/index.php` | `index()` |
| Crear (form) | `/admin/vehicles/create` | `views/admin/Vehicles/create.php` | `create()` |
| Guardar | `/admin/vehicles` (POST) | - | `store()` |
| Ver | `/admin/vehicles/123` | `views/admin/Vehicles/show.php` | `show(123)` |
| Editar (form) | `/admin/vehicles/123/edit` | `views/admin/Vehicles/edit.php` | `edit(123)` |
| Actualizar | `/admin/vehicles/123` (PUT) | - | `update(123)` |
| Eliminar | `/admin/vehicles/123` (DELETE) | - | `destroy(123)` |

---

## 🎯 **Reglas de Oro para las Vistas**

### 1. **Enlaces a otras páginas** → Usa rutas del Router

```php
<!-- ✅ CORRECTO -->
<a href="/admin/vehicles">Volver al listado</a>
<a href="/admin/vehicles/create">Crear nuevo</a>
<a href="/admin/vehicles/<?= $vehicle['id'] ?>">Ver detalles</a>
<a href="/admin/vehicles/<?= $vehicle['id'] ?>/edit">Editar</a>

<!-- ❌ INCORRECTO -->
<a href="/views/admin/Vehicles/index.php">Volver al listado</a>
<a href="../create.php">Crear nuevo</a>
```

### 2. **Formularios** → `action` usa rutas del Router

```php
<!-- ✅ CORRECTO -->
<form method="POST" action="/admin/vehicles">
    <!-- Crear vehículo -->
</form>

<form method="POST" action="/admin/vehicles/<?= $vehicle['id'] ?>">
    <input type="hidden" name="_method" value="PUT">
    <!-- Editar vehículo -->
</form>

<!-- ❌ INCORRECTO -->
<form method="POST" action="store.php">
<form method="POST" action="../update.php?id=123">
```

### 3. **Assets (CSS, JS, imágenes)** → Rutas públicas

```php
<!-- ✅ CORRECTO -->
<link rel="stylesheet" href="/css/admin.css">
<script src="/js/admin.js"></script>
<img src="/images/logo.png">

<!-- También correcto (ruta absoluta) -->
<link rel="stylesheet" href="<?= PUBLIC_PATH ?>/css/admin.css">
```

### 4. **Includes de PHP** → Rutas relativas o constantes

```php
<!-- ✅ CORRECTO -->
<?php require_once __DIR__ . '/../admin-header.php'; ?>
<?php require_once VIEWS_PATH . '/admin/admin-header.php'; ?>

<!-- ❌ INCORRECTO -->
<?php include '/admin-header.php'; ?>
```

---

## 🔧 **Ejemplos Prácticos**

### **Ejemplo 1: Botón "Crear Vehículo"**

```php
<!-- En index.php -->
<a href="/admin/vehicles/create" class="btn btn-primary">
    Nuevo Vehículo
</a>
```

**¿Qué pasa?**
1. Usuario hace click
2. Router ejecuta `AdminVehicleController::create()`
3. Controller verifica que es admin
4. Controller renderiza `views/admin/Vehicles/create.php`
5. Usuario ve el formulario

---

### **Ejemplo 2: Formulario de Edición**

```php
<!-- En edit.php -->
<form method="POST" action="/admin/vehicles/<?= $vehicle['id'] ?>">
    <input type="hidden" name="_method" value="PUT">
    
    <input type="text" name="plate" value="<?= $vehicle['plate'] ?>">
    <!-- más campos... -->
    
    <button type="submit">Guardar Cambios</button>
</form>
```

**¿Qué pasa?**
1. Usuario rellena y envía el formulario
2. POST a `/admin/vehicles/123` con `_method=PUT`
3. Router lo detecta y ejecuta `AdminVehicleController::update(123)`
4. Controller valida y actualiza en BD
5. Controller redirige a `/admin/vehicles/123` (show)

---

### **Ejemplo 3: Botones de Acción**

```php
<!-- En index.php (tabla) -->
<td class="acciones">
    <!-- Ver -->
    <a href="/admin/vehicles/<?= $vehicle['id'] ?>" title="Ver detalles">
        👁️
    </a>
    
    <!-- Editar -->
    <a href="/admin/vehicles/<?= $vehicle['id'] ?>/edit" title="Editar">
        ✏️
    </a>
    
    <!-- Eliminar -->
    <button onclick="deleteVehicle(<?= $vehicle['id'] ?>)" title="Eliminar">
        🗑️
    </button>
</td>

<script>
function deleteVehicle(id) {
    if (confirm('¿Seguro que quieres eliminar este vehículo?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/vehicles/${id}`;
        form.submit();
    }
}
</script>

<!-- Formulario oculto para DELETE -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>
```

---

## 📝 **Conversión de Notación de Puntos a Ruta**

El Router convierte automáticamente:

```php
Router::view('admin.Vehicles.index')
// Se convierte a:
// views/admin/Vehicles/index.php

Router::view('public.vehicle.booking')
// Se convierte a:
// views/public/vehicle/booking.php

Router::view('auth.login')
// Se convierte a:
// views/auth/login.php
```

**Regla:**
- Los **puntos** se convierten en **/**
- Se añade automáticamente `views/` al inicio
- Se añade automáticamente `.php` al final

---

## 🚨 **Errores Comunes y Soluciones**

### Error 1: "Controller class not found"

**Causa:** Ruta incorrecta en `routes/web.php` o nombre de clase mal escrito

**Solución:**
```php
// Verificar que coincidan:
Router::get('/admin/vehicles', ['AdminVehicleController', 'index']);
                                 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
// Con el archivo:
controllers/admin/AdminVehicleController.php
                  ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

// Y la clase:
class AdminVehicleController { ... }
      ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
```

---

### Error 2: "Undefined variable: vehicles"

**Causa:** Accediste directamente a la vista sin pasar por el controller

**Solución:**
```php
// ❌ NO hagas esto:
http://localhost/views/admin/Vehicles/index.php

// ✅ HAZ esto:
http://localhost/admin/vehicles
```

---

### Error 3: "404 Not Found"

**Causa:** La ruta no está definida en `routes/web.php`

**Solución:**
Verifica que exista la ruta:
```php
// En routes/web.php debe estar:
Router::get('/admin/vehicles/create', ['AdminVehicleController', 'create']);
```

---

## ✅ **Checklist para las Vistas**

Antes de crear un enlace o formulario, pregúntate:

- [ ] ¿Estoy usando una ruta del Router? (ej: `/admin/vehicles`)
- [ ] ¿La ruta está definida en `routes/web.php`?
- [ ] ¿El controlador existe y tiene el método?
- [ ] ¿El método del formulario es correcto? (GET/POST/PUT/DELETE)
- [ ] ¿Estoy usando `htmlspecialchars()` para prevenir XSS?

---

## 🎓 **Conclusión**

**Regla de Oro:**
> En las vistas, **SIEMPRE** usa rutas del Router (las que empiezan con `/`), **NUNCA** rutas de archivos físicos.

**Recuerda:**
- ✅ `/admin/vehicles/create` → Pasa por Router → Controller → Vista
- ❌ `/views/admin/Vehicles/create.php` → Acceso directo → Errores

**El flujo MVC es:**
```
Usuario → Router → Controller → Model (opcional) → Vista → Usuario
```

---

¿Preguntas? Revisa siempre:
1. `routes/web.php` - ¿Está definida la ruta?
2. Controller - ¿Existe el método?
3. Vista - ¿Usa rutas del Router?
