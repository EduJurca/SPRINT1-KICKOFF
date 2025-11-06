# 🔐 Panel de Administración

Vistas del panel de administración del sistema SIMS.

## 📁 Estructura

```
admin/
├── dashboard.php       # Dashboard principal con estadísticas
├── users.php          # Gestión de usuarios
├── vehicles.php       # Gestión de vehículos
├── bookings.php       # Gestión de reservas
├── reports.php        # Informes y análisis
└── settings.php       # Configuración del sistema
```

## 🎨 Layout de Admin

Las vistas de admin usan layouts específicos:
- `admin-header.php` - Header con sidebar de navegación
- `admin-footer.php` - Footer básico con scripts

### Variables disponibles:

```php
$title = 'Título de la página';           // Título del documento
$pageTitle = 'Título de la sección';      // Título en el top bar
$currentPage = 'dashboard';                // Página activa en el menú
$additionalCSS = ['/css/custom.css'];     // CSS adicional
$additionalJS = ['/js/admin.js'];         // JS adicional
```

## 🔒 Seguridad

Todas las vistas deben verificar que el usuario sea administrador:

```php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}
```

## 📊 Secciones del Panel

### Dashboard
- Estadísticas principales (usuarios, vehículos, reservas, ingresos)
- Reservas recientes
- Vehículos más populares
- Acciones rápidas

### Usuarios
- Lista de usuarios
- Crear/editar/eliminar usuarios
- Cambiar roles y permisos

### Vehículos
- Lista de vehículos
- Agregar/editar/eliminar vehículos
- Gestión de disponibilidad

### Reservas
- Lista de todas las reservas
- Aprobar/rechazar reservas
- Historial de reservas

### Informes
- Estadísticas detalladas
- Gráficos y análisis
- Exportación de datos

### Configuración
- Parámetros del sistema
- Configuración de pagos
- Ajustes generales
