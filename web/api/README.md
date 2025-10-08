# Carsharing Platform API

API RESTful para la plataforma de carsharing. Implementa autenticación JWT, control de acceso basado en roles (RBAC) y operaciones CRUD completas.

## Características

- **Autenticación JWT**: Tokens seguros con expiración de 24 horas
- **Control de Acceso Basado en Roles (RBAC)**: Tres roles (user, technician, admin)
- **Seguridad 2025**: Argon2id para hashing de contraseñas, prepared statements, validación de entrada
- **Base de Datos Dual**: MariaDB para datos relacionales, MongoDB para datos de sensores y logs
- **API RESTful**: Endpoints consistentes con respuestas JSON
- **CORS Configurado**: Soporte para aplicaciones frontend
- **Validación Completa**: Validación de entrada en todos los endpoints

## Requisitos

- PHP 8.x
- MariaDB 10.x
- MongoDB 5.x+
- Apache con mod_rewrite
- Extensiones PHP: PDO, pdo_mysql, mongodb

## Instalación

1. Configurar base de datos MariaDB:
```bash
mysql -u root -p < database/mariadb/schema.sql
mysql -u root -p < database/mariadb/seed.sql
```

2. Configurar MongoDB:
```bash
mongosh < database/mongodb/init.js
```

3. Configurar variables de entorno (opcional):
```bash
export DB_HOST=mariadb
export DB_PORT=3306
export DB_NAME=carsharing
export DB_USER=carsharing_user
export DB_PASS=carsharing_pass
export MONGO_HOST=mongodb
export MONGO_PORT=27017
export JWT_SECRET=your-secret-key-here
```

## Estructura de Directorios

```
api/
├── config/
│   ├── database.php      # Conexiones a bases de datos
│   ├── jwt.php           # Configuración JWT
│   └── cors.php          # Configuración CORS
├── middleware/
│   ├── auth.php          # Middleware de autenticación
│   └── rbac.php          # Control de acceso basado en roles
├── models/
│   ├── User.php          # Modelo de usuario
│   ├── Vehicle.php       # Modelo de vehículo
│   ├── Booking.php       # Modelo de reserva
│   └── Sensor.php        # Modelo de datos de sensores
├── controllers/
│   ├── AuthController.php      # Autenticación
│   ├── UserController.php      # Gestión de usuarios
│   ├── VehicleController.php   # Gestión de vehículos
│   ├── BookingController.php   # Gestión de reservas
│   └── SensorController.php    # Datos de sensores y logs
├── utils/
│   ├── Validator.php     # Validación de entrada
│   └── Response.php      # Formato de respuestas
├── index.php             # Router principal
└── .htaccess            # Configuración Apache
```

## Endpoints de la API

### Autenticación

#### POST /api/auth/login
Iniciar sesión de usuario.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "Password123!"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "full_name": "John Doe",
      "role": "user"
    },
    "expires_in": 86400
  }
}
```

#### POST /api/auth/register
Registrar nuevo usuario.

**Request:**
```json
{
  "email": "newuser@example.com",
  "password": "Password123!",
  "full_name": "New User",
  "phone": "+1234567890",
  "license_number": "DL1234567"
}
```

#### POST /api/auth/logout
Cerrar sesión (requiere autenticación).

#### GET /api/auth/me
Obtener perfil del usuario actual (requiere autenticación).

#### POST /api/auth/refresh
Renovar token JWT (requiere autenticación).

#### GET /api/auth/verify
Verificar validez del token (requiere autenticación).

### Usuarios

#### GET /api/users
Listar todos los usuarios (solo admin).

**Query Parameters:**
- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (default: 20)
- `search`: Búsqueda por email o nombre

#### GET /api/users/{id}
Obtener detalles de usuario (requiere autenticación).

#### POST /api/users
Crear nuevo usuario (solo admin).

#### PUT /api/users/{id}
Actualizar usuario (requiere autenticación).

#### DELETE /api/users/{id}
Eliminar usuario (solo admin).

### Vehículos

#### GET /api/vehicles
Listar vehículos.

**Query Parameters:**
- `page`: Número de página
- `per_page`: Elementos por página
- `status`: Filtrar por estado (available, in_use, maintenance, unavailable)
- `search`: Búsqueda por modelo, marca o matrícula
- `lat`, `lng`, `radius`: Buscar vehículos cercanos

#### GET /api/vehicles/{id}
Obtener detalles de vehículo.

#### POST /api/vehicles
Crear vehículo (technician o admin).

**Request:**
```json
{
  "model": "Model 3",
  "brand": "Tesla",
  "license_plate": "ABC1234",
  "status": "available",
  "location_lat": 40.7128,
  "location_lng": -74.0060,
  "price_per_hour": 25.00
}
```

#### PUT /api/vehicles/{id}
Actualizar vehículo (technician o admin).

#### DELETE /api/vehicles/{id}
Eliminar vehículo (solo admin).

#### PATCH /api/vehicles/{id}/location
Actualizar ubicación del vehículo (technician o admin).

#### PATCH /api/vehicles/{id}/status
Actualizar estado del vehículo (technician o admin).

### Reservas

#### GET /api/bookings
Listar reservas (usuarios ven solo las suyas).

**Query Parameters:**
- `page`: Número de página
- `per_page`: Elementos por página
- `user_id`: Filtrar por usuario (solo admin)
- `vehicle_id`: Filtrar por vehículo (solo admin)
- `status`: Filtrar por estado

#### GET /api/bookings/{id}
Obtener detalles de reserva (requiere autenticación).

#### POST /api/bookings
Crear reserva (requiere autenticación).

**Request:**
```json
{
  "vehicle_id": 1,
  "start_time": "2025-10-07 10:00:00",
  "end_time": "2025-10-07 14:00:00"
}
```

#### PUT /api/bookings/{id}
Actualizar reserva (requiere autenticación).

#### POST /api/bookings/{id}/complete
Completar reserva (requiere autenticación).

#### POST /api/bookings/{id}/cancel
Cancelar reserva (requiere autenticación).

#### DELETE /api/bookings/{id}
Eliminar reserva (solo admin).

### Sensores y Logs

#### GET /api/sensors/{vehicle_id}
Obtener datos de sensores del vehículo (requiere autenticación).

**Query Parameters:**
- `limit`: Número de registros (default: 100)
- `skip`: Registros a omitir
- `latest`: true para obtener solo el último registro
- `start_date`, `end_date`: Filtrar por rango de fechas

#### GET /api/sensors/{vehicle_id}/average
Obtener promedios de datos de sensores (requiere autenticación).

**Query Parameters:**
- `hours`: Horas a promediar (default: 24)

#### POST /api/sensors
Insertar datos de sensores (technician o admin).

#### GET /api/logs
Obtener logs del sistema (solo admin).

**Query Parameters:**
- `limit`: Número de registros
- `skip`: Registros a omitir
- `level`: Filtrar por nivel (info, warning, error)

#### POST /api/logs
Insertar log del sistema (solo admin).

#### DELETE /api/sensors/cleanup
Limpiar datos antiguos de sensores (solo admin).

### Utilidades

#### GET /api/health
Verificar estado de la API.

#### GET /api
Información de la API.

## Roles y Permisos

### User (usuario)
- Ver vehículos
- Crear y ver propias reservas
- Ver propio perfil

### Technician (técnico)
- Todos los permisos de user
- Crear y actualizar vehículos
- Insertar datos de sensores

### Admin (administrador)
- Todos los permisos
- Gestionar usuarios
- Eliminar vehículos y reservas
- Ver logs del sistema

## Códigos de Estado HTTP

- `200 OK`: Solicitud exitosa
- `201 Created`: Recurso creado exitosamente
- `204 No Content`: Solicitud exitosa sin contenido
- `400 Bad Request`: Solicitud inválida
- `401 Unauthorized`: No autenticado
- `403 Forbidden`: Sin permisos
- `404 Not Found`: Recurso no encontrado
- `409 Conflict`: Conflicto (ej. email duplicado)
- `422 Unprocessable Entity`: Error de validación
- `429 Too Many Requests`: Límite de tasa excedido
- `500 Internal Server Error`: Error del servidor

## Formato de Respuesta

### Respuesta Exitosa
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "timestamp": "2025-10-07 11:27:00"
}
```

### Respuesta de Error
```json
{
  "success": false,
  "message": "Error message",
  "error_code": "ERROR_CODE",
  "timestamp": "2025-10-07 11:27:00"
}
```

### Respuesta Paginada
```json
{
  "success": true,
  "message": "Data retrieved",
  "data": [ ... ],
  "pagination": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "total_pages": 5,
    "has_more": true
  },
  "timestamp": "2025-10-07 11:27:00"
}
```

## Seguridad

- **Autenticación JWT**: Tokens con expiración de 24 horas
- **Hashing de Contraseñas**: Argon2id (estándar 2025)
- **Prepared Statements**: Prevención de inyección SQL
- **Validación de Entrada**: Sanitización y validación en todos los endpoints
- **CORS**: Configuración de whitelist de orígenes
- **Rate Limiting**: Protección contra fuerza bruta
- **HTTPS**: Recomendado para producción

## Usuarios de Prueba

### Admin
- Email: `admin@carsharing.com`
- Password: `Admin123!`

### Technician
- Email: `tech@carsharing.com`
- Password: `Tech123!`

### User
- Email: `john.doe@example.com`
- Password: `Admin123!`

**Nota**: Cambiar estas contraseñas en producción.

## Desarrollo

### Habilitar Modo Debug
En `index.php`, cambiar:
```php
Response::handleException($e, true); // true = modo debug
```

### Logs
Los errores se registran en el log de errores de PHP configurado en el sistema.

## Licencia

Copyright © 2025 Carsharing Platform
