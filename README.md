# Plataforma de Carsharing - Documentación Completa

**Fecha:** Octubre 2025  
**Versión:** 1.0.0  
**Estado:** Producción

## Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Requisitos Previos](#requisitos-previos)
4. [Instalación](#instalación)
5. [Despliegue](#despliegue)
6. [Uso de la Aplicación](#uso-de-la-aplicación)
7. [Herramienta de Administración](#herramienta-de-administración)
8. [Gestión de Bases de Datos](#gestión-de-bases-de-datos)
9. [Monitoreo y Salud](#monitoreo-y-salud)
10. [Estructura del Proyecto](#estructura-del-proyecto)
11. [Stack Tecnológico](#stack-tecnológico)
12. [Solución de Problemas](#solución-de-problemas)
13. [Seguridad](#seguridad)
14. [Contribución](#contribución)
15. [Licencia](#licencia)

---

## Descripción General

La **Plataforma de Carsharing** es una aplicación web completa y lista para producción que permite la gestión de vehículos compartidos. La plataforma incluye:

- **Interfaz Web Moderna**: Diseño responsivo optimizado para escritorio y móviles
- **Sistema de Autenticación**: Registro, login y gestión de usuarios con JWT
- **Gestión de Vehículos**: CRUD completo de vehículos con ubicación en tiempo real
- **Sistema de Reservas**: Reserva y gestión de vehículos compartidos
- **Panel de Administración**: Gestión completa de usuarios, vehículos y reservas
- **Mapa Interactivo**: Visualización de vehículos disponibles con OpenStreetMap
- **API RESTful**: Backend completo con endpoints documentados
- **Multi-idioma**: Soporte para español e inglés (i18n)
- **Accesibilidad**: Cumplimiento con estándares WCAG

### Características Principales

✅ **Frontend Moderno**
- HTML5 semántico con Tailwind CSS
- JavaScript modular y reutilizable
- Diseño responsivo (mobile-first)
- Integración con mapas interactivos

✅ **Backend Robusto**
- PHP 8.2 con arquitectura RESTful
- Autenticación JWT segura
- Validación de datos completa
- Manejo de errores centralizado

✅ **Bases de Datos Duales**
- MariaDB 11.4 para datos relacionales
- MongoDB 7.0 para datos de sensores y logs

✅ **Infraestructura Docker**
- Contenedores optimizados
- Orquestación con Docker Compose
- Configuración de seguridad avanzada
- Volúmenes persistentes

✅ **Herramienta de Administración**
- CLI Python completa
- Despliegue automatizado
- Backup y restauración de bases de datos
- Monitoreo de salud del sistema

---

## Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                         USUARIO FINAL                            │
│                    (Navegador Web / Móvil)                       │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             │ HTTP/HTTPS
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    CONTENEDOR WEB (Apache + PHP)                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │   Frontend   │  │   API REST   │  │  Autenticación│          │
│  │  HTML/CSS/JS │  │     PHP      │  │     JWT       │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                    Puerto: 80                                    │
└────────────┬───────────────────────────────┬────────────────────┘
             │                               │
             │                               │
    ┌────────▼────────┐            ┌────────▼────────┐
    │   CONTENEDOR    │            │   CONTENEDOR    │
    │    MARIADB      │            │    MONGODB      │
    │                 │            │                 │
    │  • Usuarios     │            │  • Sensores     │
    │  • Vehículos    │            │  • Logs         │
    │  • Reservas     │            │  • Telemetría   │
    │  • Roles        │            │                 │
    │                 │            │                 │
    │  Puerto: 3306   │            │  Puerto: 27017  │
    └─────────────────┘            └─────────────────┘
             │                               │
             │                               │
    ┌────────▼───────────────────────────────▼────────┐
    │           VOLÚMENES PERSISTENTES                │
    │  • carsharing-mariadb-data                      │
    │  • carsharing-mongodb-data                      │
    │  • carsharing-logs                              │
    └─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                  HERRAMIENTA DE ADMINISTRACIÓN                   │
│                      (admin_tool.py)                             │
│  • Despliegue automatizado                                       │
│  • Gestión de contenedores                                       │
│  • Backup/Restore de bases de datos                              │
│  • Monitoreo de salud                                            │
└─────────────────────────────────────────────────────────────────┘
```

### Flujo de Datos

1. **Usuario** → Accede a la aplicación web a través del navegador
2. **Frontend** → Envía peticiones a la API REST
3. **API REST** → Valida JWT y procesa la petición
4. **Bases de Datos** → Almacenan y recuperan datos
5. **Respuesta** → Retorna datos en formato JSON al frontend

---

## Requisitos Previos

### Software Requerido

#### Docker y Docker Compose
- **Docker**: versión 20.10 o superior
- **Docker Compose**: versión 2.0 o superior

**Instalación en Ubuntu/Debian:**
```bash
# Actualizar repositorios
sudo apt-get update

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Instalar Docker Compose
sudo apt-get install docker-compose-plugin

# Verificar instalación
docker --version
docker compose version
```

**Instalación en macOS:**
```bash
# Instalar Docker Desktop desde:
# https://www.docker.com/products/docker-desktop

# Verificar instalación
docker --version
docker compose version
```

**Instalación en Windows:**
- Descargar e instalar Docker Desktop desde: https://www.docker.com/products/docker-desktop
- Habilitar WSL2 (Windows Subsystem for Linux 2)

#### Python 3.8+
```bash
# Verificar versión de Python
python3 --version

# Instalar Python (Ubuntu/Debian)
sudo apt-get install python3 python3-pip

# Instalar Python (macOS con Homebrew)
brew install python3
```

### Recursos del Sistema

**Mínimos:**
- CPU: 2 cores
- RAM: 4 GB
- Disco: 10 GB de espacio libre
- Sistema Operativo: Linux, macOS, o Windows con WSL2

**Recomendados:**
- CPU: 4 cores
- RAM: 8 GB
- Disco: 20 GB de espacio libre
- Conexión a Internet (para descargar imágenes Docker)

---

## Instalación

### 1. Clonar el Repositorio

```bash
# Clonar el repositorio
git clone https://github.com/tu-usuario/carsharing-platform.git

# Navegar al directorio del proyecto
cd carsharing-platform/final
```

### 2. Instalar Dependencias de Python

```bash
# Instalar dependencias de la herramienta de administración
pip3 install -r requirements.txt
```

Las dependencias incluyen:
- `docker` - SDK de Docker para Python
- `colorama` - Salida coloreada en terminal
- `python-dotenv` - Gestión de variables de entorno
- `tqdm` - Barras de progreso para CLI

### 3. Hacer Ejecutable la Herramienta de Administración

```bash
# Dar permisos de ejecución
chmod +x admin_tool.py
```

### 4. Ejecutar el Asistente de Configuración

```bash
# Iniciar el asistente de configuración interactivo
python3 admin_tool.py setup
```

El asistente te guiará a través de la configuración inicial:

1. **Configuración de MariaDB**
   - Contraseña root de MariaDB
   - Nombre de la base de datos
   - Usuario de la base de datos
   - Contraseña del usuario

2. **Configuración de MongoDB**
   - Usuario root de MongoDB
   - Contraseña root de MongoDB
   - Nombre de la base de datos
   - Usuario de la aplicación
   - Contraseña del usuario

3. **Configuración de la Aplicación**
   - Entorno (development/production)
   - Clave secreta JWT (se genera automáticamente si no se proporciona)
   - Orígenes CORS permitidos

4. **Confirmación**
   - Revisa la configuración
   - Confirma para crear el archivo `.env`
   - Opción de desplegar inmediatamente

---

## Despliegue

### Despliegue Completo (Primera Vez)

```bash
# Desplegar la plataforma completa
python3 admin_tool.py deploy
```

Este comando ejecuta los siguientes pasos:

1. ✅ **Construcción de imágenes Docker**
   - Construye la imagen del servidor web con PHP y Apache
   - Configura todas las dependencias necesarias

2. ✅ **Inicio de contenedores**
   - Inicia MariaDB con configuración de seguridad
   - Inicia MongoDB con autenticación
   - Inicia el servidor web con Apache y PHP

3. ✅ **Espera de inicialización**
   - Espera a que los contenedores estén listos
   - Verifica que los health checks pasen

4. ✅ **Inicialización de bases de datos**
   - Ejecuta el esquema de MariaDB (schema.sql)
   - Carga datos de prueba (seed.sql)
   - Inicializa colecciones de MongoDB (init.js)

### Despliegue Rápido (Después de la Primera Vez)

```bash
# Iniciar contenedores existentes
python3 admin_tool.py start

# Detener contenedores
python3 admin_tool.py stop

# Reiniciar contenedores
python3 admin_tool.py restart
```

### Modo Dry-Run (Vista Previa)

```bash
# Ver qué acciones se ejecutarían sin ejecutarlas
python3 admin_tool.py deploy --dry-run
```

### Modo Verbose (Depuración)

```bash
# Ver información detallada de depuración
python3 admin_tool.py deploy --verbose
```

---

## Uso de la Aplicación

### Acceso a la Aplicación Web

Una vez desplegada, la aplicación está disponible en:

**URL Principal:** http://localhost

### Páginas Disponibles

| Página | URL | Descripción |
|--------|-----|-------------|
| Inicio | http://localhost/ | Página de bienvenida |
| Login | http://localhost/login.html | Inicio de sesión |
| Registro | http://localhost/register.html | Registro de nuevos usuarios |
| Dashboard | http://localhost/dashboard.html | Panel de usuario |
| Mapa | http://localhost/map.html | Mapa de vehículos disponibles |
| Reservas | http://localhost/bookings.html | Gestión de reservas |
| Perfil | http://localhost/profile.html | Perfil de usuario |
| Admin | http://localhost/admin/ | Panel de administración |

### Credenciales por Defecto

**Administrador:**
- Email: `admin@carsharing.com`
- Contraseña: `Admin123!`

**Usuario de Prueba:**
- Email: `user@carsharing.com`
- Contraseña: `User123!`

⚠️ **IMPORTANTE:** Cambia estas credenciales en producción.

### API REST

**Endpoint Base:** http://localhost/api

**Documentación de Endpoints:**

#### Autenticación
- `POST /api/auth/register.php` - Registro de usuario
- `POST /api/auth/login.php` - Inicio de sesión
- `POST /api/auth/logout.php` - Cierre de sesión
- `GET /api/auth/me.php` - Información del usuario actual

#### Usuarios
- `GET /api/users/list.php` - Listar usuarios (admin)
- `GET /api/users/get.php?id={id}` - Obtener usuario
- `PUT /api/users/update.php` - Actualizar usuario
- `DELETE /api/users/delete.php?id={id}` - Eliminar usuario

#### Vehículos
- `GET /api/vehicles/list.php` - Listar vehículos
- `GET /api/vehicles/get.php?id={id}` - Obtener vehículo
- `POST /api/vehicles/create.php` - Crear vehículo (admin)
- `PUT /api/vehicles/update.php` - Actualizar vehículo (admin)
- `DELETE /api/vehicles/delete.php?id={id}` - Eliminar vehículo (admin)
- `GET /api/vehicles/available.php` - Vehículos disponibles

#### Reservas
- `GET /api/bookings/list.php` - Listar reservas
- `GET /api/bookings/get.php?id={id}` - Obtener reserva
- `POST /api/bookings/create.php` - Crear reserva
- `PUT /api/bookings/update.php` - Actualizar reserva
- `DELETE /api/bookings/cancel.php?id={id}` - Cancelar reserva

#### Sensores (MongoDB)
- `GET /api/sensors/data.php?vehicle_id={id}` - Datos de sensores
- `POST /api/sensors/log.php` - Registrar datos de sensor

---

## Herramienta de Administración

La herramienta `admin_tool.py` proporciona una interfaz de línea de comandos completa para gestionar la plataforma.

### Comandos Disponibles

#### setup - Asistente de Configuración
```bash
python3 admin_tool.py setup
```
Ejecuta el asistente interactivo para configurar la plataforma por primera vez.

#### deploy - Despliegue Completo
```bash
python3 admin_tool.py deploy
```
Despliega la plataforma completa (construcción, inicio, inicialización).

#### start - Iniciar Contenedores
```bash
python3 admin_tool.py start
```
Inicia todos los contenedores Docker.

#### stop - Detener Contenedores
```bash
python3 admin_tool.py stop
```
Detiene todos los contenedores Docker.

#### restart - Reiniciar Contenedores
```bash
python3 admin_tool.py restart
```
Reinicia todos los contenedores Docker.

#### status - Estado de Contenedores
```bash
python3 admin_tool.py status
```
Muestra el estado actual de todos los contenedores.

**Salida de ejemplo:**
```
Container                      Status          Health         
------------------------------------------------------------
carsharing-web                 running         healthy        
carsharing-mariadb             running         healthy        
carsharing-mongodb             running         healthy        
```

#### logs - Ver Logs
```bash
# Ver logs del servidor web
python3 admin_tool.py logs --service web

# Ver logs de MariaDB
python3 admin_tool.py logs --service mariadb

# Ver logs de MongoDB
python3 admin_tool.py logs --service mongodb

# Seguir logs en tiempo real
python3 admin_tool.py logs --service web --follow

# Ver últimas 50 líneas
python3 admin_tool.py logs --service web --tail 50
```

#### health - Verificación de Salud
```bash
python3 admin_tool.py health
```
Realiza una verificación completa de salud del sistema:
- Estado del daemon de Docker
- Estado de contenedores
- Conexiones a bases de datos
- Respuesta del servidor web
- Uso de espacio en disco

#### db-backup - Respaldo de Bases de Datos
```bash
python3 admin_tool.py db-backup
```
Crea un respaldo completo de MariaDB y MongoDB:
- Genera un directorio con timestamp
- Exporta MariaDB usando mysqldump
- Exporta MongoDB usando mongodump
- Comprime el respaldo en formato tar.gz
- Muestra la ubicación y tamaño del respaldo

**Ubicación de respaldos:** `./backups/backup_YYYY-MM-DD_HH-MM-SS.tar.gz`

#### db-restore - Restaurar Bases de Datos
```bash
# Restaurar desde el respaldo más reciente (interactivo)
python3 admin_tool.py db-restore

# Restaurar desde un archivo específico
python3 admin_tool.py db-restore --backup-file ./backups/backup_2025-10-07_12-00-00.tar.gz
```
Restaura las bases de datos desde un respaldo:
- Lista respaldos disponibles
- Permite seleccionar el respaldo a restaurar
- Solicita confirmación (operación destructiva)
- Restaura MariaDB y MongoDB
- Verifica la restauración

⚠️ **ADVERTENCIA:** Esta operación sobrescribe los datos actuales.

#### db-init - Inicializar Bases de Datos
```bash
python3 admin_tool.py db-init
```
Fuerza la reinicialización de las bases de datos:
- Ejecuta schema.sql en MariaDB
- Ejecuta seed.sql en MariaDB
- Ejecuta init.js en MongoDB

#### clean - Limpiar Contenedores
```bash
python3 admin_tool.py clean
```
Elimina todos los contenedores y volúmenes:
- Solicita confirmación
- Detiene todos los contenedores
- Elimina contenedores
- Elimina volúmenes (datos persistentes)

⚠️ **ADVERTENCIA:** Esta operación elimina todos los datos. Haz un respaldo primero.

#### update - Actualizar Plataforma
```bash
python3 admin_tool.py update
```
Actualiza la plataforma a la última versión:
- Descarga las últimas imágenes Docker
- Reinicia los servicios con las nuevas imágenes

### Opciones Globales

```bash
# Ver versión
python3 admin_tool.py --version

# Modo verbose (depuración)
python3 admin_tool.py deploy --verbose

# Modo dry-run (vista previa)
python3 admin_tool.py deploy --dry-run
```

---

## Gestión de Bases de Datos

### MariaDB

#### Acceso Directo
```bash
# Acceder a MariaDB desde el contenedor
docker exec -it carsharing-mariadb mysql -u carsharing_user -p carsharing
```

#### Consultas Útiles
```sql
-- Ver todas las tablas
SHOW TABLES;

-- Ver usuarios
SELECT * FROM users;

-- Ver vehículos
SELECT * FROM vehicles;

-- Ver reservas
SELECT * FROM bookings;

-- Estadísticas de usuarios
SELECT role, COUNT(*) as total FROM users GROUP BY role;
```

#### Respaldo Manual
```bash
# Respaldo de la base de datos
docker exec carsharing-mariadb mysqldump -u carsharing_user -pcarsharing_pass carsharing > backup.sql

# Restaurar desde respaldo
docker exec -i carsharing-mariadb mysql -u carsharing_user -pcarsharing_pass carsharing < backup.sql
```

### MongoDB

#### Acceso Directo
```bash
# Acceder a MongoDB desde el contenedor
docker exec -it carsharing-mongodb mongosh carsharing -u carsharing_user -p carsharing_pass --authenticationDatabase admin
```

#### Consultas Útiles
```javascript
// Ver colecciones
show collections

// Ver datos de sensores
db.sensor_data.find().limit(10)

// Ver logs
db.logs.find().sort({timestamp: -1}).limit(10)

// Contar documentos
db.sensor_data.countDocuments()

// Estadísticas de colección
db.sensor_data.stats()
```

#### Respaldo Manual
```bash
# Respaldo de MongoDB
docker exec carsharing-mongodb mongodump --db=carsharing --username=carsharing_user --password=carsharing_pass --authenticationDatabase=admin --out=/tmp/backup

# Copiar respaldo del contenedor
docker cp carsharing-mongodb:/tmp/backup ./mongodb_backup

# Restaurar desde respaldo
docker cp ./mongodb_backup carsharing-mongodb:/tmp/restore
docker exec carsharing-mongodb mongorestore --db=carsharing --username=carsharing_user --password=carsharing_pass --authenticationDatabase=admin /tmp/restore/carsharing
```

---

## Monitoreo y Salud

### Verificación de Estado

```bash
# Estado rápido de contenedores
python3 admin_tool.py status

# Verificación completa de salud
python3 admin_tool.py health
```

### Logs en Tiempo Real

```bash
# Seguir logs del servidor web
python3 admin_tool.py logs --service web --follow

# Ver logs de errores
docker logs carsharing-web 2>&1 | grep -i error
```

### Monitoreo de Recursos

```bash
# Ver uso de recursos de contenedores
docker stats

# Ver espacio en disco de Docker
docker system df

# Ver información de volúmenes
docker volume ls
docker volume inspect carsharing-mariadb-data
```

### Health Checks Automáticos

Los contenedores incluyen health checks automáticos:

**Web Server:**
- Intervalo: 30 segundos
- Timeout: 10 segundos
- Comando: `curl -f http://localhost/`

**MariaDB:**
- Intervalo: 10 segundos
- Timeout: 5 segundos
- Comando: `healthcheck.sh --connect --innodb_initialized`

**MongoDB:**
- Intervalo: 10 segundos
- Timeout: 5 segundos
- Comando: `mongosh --eval "db.adminCommand('ping')"`

---

## Estructura del Proyecto

```
final/
├── admin_tool.py                    # Herramienta de administración CLI
├── requirements.txt                 # Dependencias de Python
├── README.md                        # Esta documentación
├── backups/                         # Respaldos de bases de datos (generado)
├── admin_tool.log                   # Logs de la herramienta (generado)
│
├── docker/                          # Infraestructura Docker
│   ├── docker-compose.yml           # Orquestación de contenedores
│   ├── Dockerfile                   # Definición del contenedor web
│   ├── .env.example                 # Plantilla de variables de entorno
│   ├── .env                         # Variables de entorno (generado)
│   ├── .dockerignore               # Archivos excluidos del build
│   ├── config/                      # Configuraciones
│   │   ├── apache2.conf            # Configuración de Apache
│   │   ├── security.conf           # Directivas de seguridad
│   │   ├── carsharing.conf         # Virtual host
│   │   └── php.ini                 # Configuración de PHP
│   ├── scripts/                     # Scripts de inicialización
│   │   ├── entrypoint.sh           # Script de entrada
│   │   ├── init-mariadb.sh         # Inicialización de MariaDB
│   │   └── init-mongodb.sh         # Inicialización de MongoDB
│   └── README.md                    # Documentación de Docker
│
├── web/                             # Aplicación web
│   ├── public/                      # Directorio público HTML
│   │   ├── index.html              # Página de inicio
│   │   ├── login.html              # Página de login
│   │   ├── register.html           # Página de registro
│   │   ├── dashboard.html          # Dashboard de usuario
│   │   ├── map.html                # Mapa de vehículos
│   │   ├── bookings.html           # Gestión de reservas
│   │   ├── profile.html            # Perfil de usuario
│   │   ├── vehicles.html           # Listado de vehículos
│   │   └── admin/                  # Panel de administración
│   │       ├── index.html          # Dashboard admin
│   │       ├── users.html          # Gestión de usuarios
│   │       ├── vehicles.html       # Gestión de vehículos
│   │       └── bookings.html       # Gestión de reservas
│   │
│   ├── css/                         # Hojas de estilo
│   │   └── styles.css              # Estilos personalizados
│   │
│   ├── js/                          # JavaScript
│   │   ├── api.js                  # Cliente API
│   │   ├── auth.js                 # Autenticación
│   │   ├── i18n.js                 # Internacionalización
│   │   ├── map.js                  # Funcionalidad de mapas
│   │   └── utils.js                # Utilidades
│   │
│   ├── lang/                        # Archivos de idioma
│   │   ├── es.json                 # Español
│   │   └── en.json                 # Inglés
│   │
│   └── api/                         # Backend PHP
│       ├── config/                  # Configuración
│       │   ├── database.php        # Conexión a bases de datos
│       │   ├── jwt.php             # Configuración JWT
│       │   └── cors.php            # Configuración CORS
│       │
│       ├── middleware/              # Middleware
│       │   ├── auth.php            # Autenticación
│       │   ├── admin.php           # Autorización admin
│       │   └── validation.php      # Validación
│       │
│       ├── models/                  # Modelos
│       │   ├── User.php            # Modelo de usuario
│       │   ├── Vehicle.php         # Modelo de vehículo
│       │   └── Booking.php         # Modelo de reserva
│       │
│       ├── auth/                    # Endpoints de autenticación
│       │   ├── register.php        # Registro
│       │   ├── login.php           # Login
│       │   ├── logout.php          # Logout
│       │   └── me.php              # Usuario actual
│       │
│       ├── users/                   # Endpoints de usuarios
│       │   ├── list.php            # Listar usuarios
│       │   ├── get.php             # Obtener usuario
│       │   ├── update.php          # Actualizar usuario
│       │   └── delete.php          # Eliminar usuario
│       │
│       ├── vehicles/                # Endpoints de vehículos
│       │   ├── list.php            # Listar vehículos
│       │   ├── get.php             # Obtener vehículo
│       │   ├── create.php          # Crear vehículo
│       │   ├── update.php          # Actualizar vehículo
│       │   ├── delete.php          # Eliminar vehículo
│       │   └── available.php       # Vehículos disponibles
│       │
│       ├── bookings/                # Endpoints de reservas
│       │   ├── list.php            # Listar reservas
│       │   ├── get.php             # Obtener reserva
│       │   ├── create.php          # Crear reserva
│       │   ├── update.php          # Actualizar reserva
│       │   └── cancel.php          # Cancelar reserva
│       │
│       └── sensors/                 # Endpoints de sensores
│           ├── data.php            # Datos de sensores
│           └── log.php             # Registrar datos
│
└── database/                        # Inicialización de bases de datos
    ├── mariadb/                     # Scripts de MariaDB
    │   ├── schema.sql              # Esquema de base de datos
    │   └── seed.sql                # Datos de prueba
    │
    └── mongodb/                     # Scripts de MongoDB
        └── init.js                 # Inicialización de MongoDB
```

---

## Stack Tecnológico

### Frontend
- **HTML5**: Estructura semántica
- **Tailwind CSS 3.3**: Framework CSS utility-first
- **JavaScript ES6+**: Programación modular
- **Leaflet.js**: Mapas interactivos con OpenStreetMap
- **Font Awesome 6**: Iconos

### Backend
- **PHP 8.2**: Lenguaje del servidor
- **Apache 2.4**: Servidor web
- **JWT (JSON Web Tokens)**: Autenticación
- **RESTful API**: Arquitectura de API

### Bases de Datos
- **MariaDB 11.4**: Base de datos relacional
  - Usuarios, vehículos, reservas, roles
- **MongoDB 7.0**: Base de datos NoSQL
  - Datos de sensores, logs, telemetría

### DevOps
- **Docker 20.10+**: Contenedorización
- **Docker Compose 2.0+**: Orquestación
- **Python 3.8+**: Herramienta de administración

### Librerías Python
- **docker-py**: SDK de Docker
- **colorama**: Salida coloreada en terminal
- **python-dotenv**: Gestión de variables de entorno
- **tqdm**: Barras de progreso

### Seguridad
- **HTTPS Ready**: Configuración SSL/TLS preparada
- **CORS**: Control de acceso entre orígenes
- **JWT**: Tokens seguros con expiración
- **Password Hashing**: Bcrypt para contraseñas
- **SQL Injection Protection**: Prepared statements
- **XSS Protection**: Sanitización de entradas
- **CSRF Protection**: Tokens CSRF

---

## Solución de Problemas

### Docker no está ejecutándose

**Síntoma:**
```
Error: Failed to connect to Docker daemon
```

**Solución:**
```bash
# Linux
sudo systemctl start docker
sudo systemctl enable docker

# macOS/Windows
# Iniciar Docker Desktop desde la aplicación
```

### Puerto 80 ya está en uso

**Síntoma:**
```
Error: Bind for 0.0.0.0:80 failed: port is already allocated
```

**Solución:**
```bash
# Ver qué proceso está usando el puerto 80
sudo lsof -i :80

# Detener el proceso o cambiar el puerto en docker-compose.yml
# Editar docker/docker-compose.yml:
# ports:
#   - "8080:80"  # Cambiar a puerto 8080
```

### Contenedores no inician

**Síntoma:**
```
Container status: exited
```

**Solución:**
```bash
# Ver logs del contenedor
python3 admin_tool.py logs --service web

# Ver logs detallados
docker logs carsharing-web

# Reiniciar contenedores
python3 admin_tool.py restart
```

### Error de conexión a base de datos

**Síntoma:**
```
Error: Connection refused to database
```

**Solución:**
```bash
# Verificar que los contenedores estén ejecutándose
python3 admin_tool.py status

# Verificar salud de las bases de datos
python3 admin_tool.py health

# Reinicializar bases de datos
python3 admin_tool.py db-init

# Verificar credenciales en .env
cat docker/.env
```

### Permisos denegados

**Síntoma:**
```
Permission denied
```

**Solución:**
```bash
# Agregar usuario al grupo docker (Linux)
sudo usermod -aG docker $USER
newgrp docker

# O ejecutar con sudo
sudo python3 admin_tool.py deploy
```

### Espacio en disco insuficiente

**Síntoma:**
```
Error: no space left on device
```

**Solución:**
```bash
# Limpiar imágenes y contenedores no utilizados
docker system prune -a

# Ver uso de espacio
docker system df

# Eliminar volúmenes no utilizados
docker volume prune
```

### Error al construir imágenes

**Síntoma:**
```
Error: failed to build image
```

**Solución:**
```bash
# Limpiar caché de construcción
docker builder prune

# Reconstruir sin caché
cd docker
docker-compose build --no-cache

# Verificar Dockerfile
cat Dockerfile
```

### La aplicación web no responde

**Síntoma:**
- Página no carga en http://localhost

**Solución:**
```bash
# Verificar estado del contenedor web
docker ps | grep carsharing-web

# Ver logs del servidor web
python3 admin_tool.py logs --service web --follow

# Verificar health check
python3 admin_tool.py health

# Reiniciar el contenedor web
docker restart carsharing-web
```

### Problemas con JWT

**Síntoma:**
```
Error: Invalid token
```

**Solución:**
```bash
# Verificar que JWT_SECRET esté configurado
grep JWT_SECRET docker/.env

# Regenerar JWT_SECRET
python3 -c "import secrets; print(secrets.token_urlsafe(32))"

# Actualizar en .env y reiniciar
python3 admin_tool.py restart
```

### Restauración de respaldo falla

**Síntoma:**
```
Error: Failed to restore database
```

**Solución:**
```bash
# Verificar que el archivo de respaldo existe
ls -lh backups/

# Verificar integridad del archivo tar.gz
tar -tzf backups/backup_YYYY-MM-DD_HH-MM-SS.tar.gz

# Intentar restauración manual
# Ver sección "Gestión de Bases de Datos"
```

---

## Seguridad

### Recomendaciones de Seguridad

#### 1. Cambiar Credenciales por Defecto

⚠️ **CRÍTICO**: Cambia todas las contraseñas por defecto antes de desplegar en producción.

```bash
# Editar archivo .env
nano docker/.env

# Cambiar:
# - MYSQL_ROOT_PASSWORD
# - DB_PASSWORD
# - MONGO_ROOT_PASSWORD
# - MONGO_PASSWORD
# - JWT_SECRET
```

#### 2. Generar JWT Secret Seguro

```bash
# Generar clave aleatoria segura
openssl rand -base64 32

# O con Python
python3 -c "import secrets; print(secrets.token_urlsafe(32))"
```

#### 3. Configurar HTTPS

Para producción, configura HTTPS con certificados SSL/TLS:

```bash
# Opción 1: Let's Encrypt (recomendado)
# Usar Certbot para obtener certificados gratuitos

# Opción 2: Certificados propios
# Colocar certificados en docker/config/ssl/
# Actualizar docker/config/carsharing.conf
```

#### 4. Configurar CORS Correctamente

```bash
# En producción, especifica dominios permitidos
# Editar docker/.env:
CORS_ALLOWED_ORIGINS=https://tudominio.com,https://www.tudominio.com
```

#### 5. Firewall y Puertos

```bash
# Permitir solo puertos necesarios
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# No exponer puertos de bases de datos públicamente
# (ya están configurados para ser internos en docker-compose.yml)
```

#### 6. Respaldos Regulares

```bash
# Configurar cron job para respaldos automáticos
crontab -e

# Agregar línea para respaldo diario a las 2 AM:
0 2 * * * cd /ruta/al/proyecto/final && python3 admin_tool.py db-backup
```

#### 7. Actualizar Regularmente

```bash
# Actualizar imágenes Docker
python3 admin_tool.py update

# Actualizar dependencias de Python
pip3 install --upgrade -r requirements.txt
```

#### 8. Monitoreo de Logs

```bash
# Revisar logs regularmente
python3 admin_tool.py logs --service web --tail 100 | grep -i error

# Configurar alertas para errores críticos
```

#### 9. Limitar Intentos de Login

La aplicación ya incluye protección contra fuerza bruta:
- Máximo 5 intentos de login
- Bloqueo de cuenta por 15 minutos después de 5 intentos fallidos

#### 10. Variables de Entorno Seguras

```bash
# Nunca commitear .env a Git
echo "docker/.env" >> .gitignore

# Restringir permisos del archivo .env
chmod 600 docker/.env
```

---

## Contribución

### Cómo Contribuir

1. **Fork del Repositorio**
   ```bash
   # Fork en GitHub y clonar
   git clone https://github.com/tu-usuario/carsharing-platform.git
   ```

2. **Crear Rama de Desarrollo**
   ```bash
   git checkout -b feature/nueva-funcionalidad
   ```

3. **Hacer Cambios**
   - Seguir las convenciones de código
   - Agregar tests si es necesario
   - Actualizar documentación

4. **Commit y Push**
   ```bash
   git add .
   git commit -m "Descripción clara de los cambios"
   git push origin feature/nueva-funcionalidad
   ```

5. **Crear Pull Request**
   - Describir los cambios realizados
   - Referenciar issues relacionados
   - Esperar revisión

### Guías de Estilo

#### PHP
- Seguir PSR-12
- Usar type hints
- Documentar funciones con PHPDoc

#### JavaScript
- Usar ES6+
- Seguir Airbnb Style Guide
- Documentar con JSDoc

#### Python
- Seguir PEP 8
- Usar type hints
- Documentar con docstrings

### Reportar Bugs

Crear un issue en GitHub con:
- Descripción del problema
- Pasos para reproducir
- Comportamiento esperado vs actual
- Logs relevantes
- Entorno (OS, versiones, etc.)

---

## Licencia

Este proyecto está licenciado bajo la **MIT License**.

```
MIT License

Copyright (c) 2025 Carsharing Platform

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## Contacto y Soporte

### Documentación Adicional

- **Docker**: [./docker/README.md](./docker/README.md)
- **API Backend**: [./BACKEND_API_SUMMARY.md](./BACKEND_API_SUMMARY.md)
- **Infraestructura Docker**: [./docker/DOCKER_INFRASTRUCTURE_SUMMARY.md](./docker/DOCKER_INFRASTRUCTURE_SUMMARY.md)

### Recursos Útiles

- **Docker Documentation**: https://docs.docker.com/
- **PHP Documentation**: https://www.php.net/docs.php
- **MariaDB Documentation**: https://mariadb.org/documentation/
- **MongoDB Documentation**: https://docs.mongodb.com/
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Leaflet.js**: https://leafletjs.com/reference.html

### Comunidad

- **GitHub Issues**: Para reportar bugs y solicitar funcionalidades
- **GitHub Discussions**: Para preguntas y discusiones generales
- **Wiki**: Para guías adicionales y tutoriales

---

## Agradecimientos

Gracias a todos los contribuidores y a las siguientes tecnologías de código abierto:

- Docker y Docker Compose
- PHP y Apache
- MariaDB y MongoDB
- Tailwind CSS
- Leaflet.js y OpenStreetMap
- Python y sus librerías

---

## Changelog

### Versión 1.0.0 (Octubre 2025)
- ✅ Lanzamiento inicial
- ✅ Frontend completo con 8 páginas HTML
- ✅ Backend API REST con 21 endpoints
- ✅ Infraestructura Docker completa
- ✅ Herramienta de administración CLI
- ✅ Soporte multi-idioma (ES/EN)
- ✅ Sistema de autenticación JWT
- ✅ Gestión de usuarios, vehículos y reservas
- ✅ Integración con mapas interactivos
- ✅ Panel de administración
- ✅ Documentación completa en español

---

**¡Gracias por usar la Plataforma de Carsharing!**

Para comenzar, ejecuta:
```bash
python3 admin_tool.py setup
```
