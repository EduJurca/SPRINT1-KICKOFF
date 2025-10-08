# Infraestructura Docker - Plataforma de Carsharing

**Fecha:** Octubre 2025  
**Versión:** 1.0.0

## Descripción

Esta carpeta contiene la infraestructura completa de Docker para la plataforma de carsharing, incluyendo:

- **Servidor Web**: PHP 8.2 con Apache
- **Base de Datos Relacional**: MariaDB 11.4
- **Base de Datos NoSQL**: MongoDB 7.0

## Estructura del Proyecto

```
docker/
├── docker-compose.yml          # Orquestación de contenedores
├── Dockerfile                  # Definición del contenedor web
├── .env.example                # Plantilla de variables de entorno
├── .dockerignore              # Archivos excluidos del build
├── config/                     # Configuraciones de Apache y PHP
│   ├── apache2.conf           # Configuración principal de Apache
│   ├── security.conf          # Directivas de seguridad
│   ├── carsharing.conf        # Virtual host
│   └── php.ini                # Configuración de PHP
├── scripts/                    # Scripts de inicialización
│   ├── entrypoint.sh          # Script de entrada del contenedor
│   ├── init-mariadb.sh        # Inicialización de MariaDB
│   └── init-mongodb.sh        # Inicialización de MongoDB
└── README.md                   # Este archivo
```

## Requisitos Previos

- **Docker**: versión 20.10 o superior
- **Docker Compose**: versión 2.0 o superior
- **Sistema Operativo**: Linux, macOS, o Windows con WSL2
- **Recursos Mínimos**:
  - CPU: 2 cores
  - RAM: 4 GB
  - Disco: 10 GB de espacio libre

### Verificar Instalación de Docker

```bash
docker --version
docker-compose --version
```

## Configuración Inicial

### 1. Configurar Variables de Entorno

Copie el archivo de ejemplo y edite las variables según sus necesidades:

```bash
cp .env.example .env
nano .env  # o use su editor preferido
```

**Variables Importantes a Cambiar en Producción:**

- `MYSQL_ROOT_PASSWORD`: Contraseña del usuario root de MariaDB
- `DB_PASSWORD`: Contraseña del usuario de la aplicación
- `MONGO_ROOT_PASSWORD`: Contraseña del usuario root de MongoDB
- `MONGO_PASSWORD`: Contraseña del usuario de la aplicación
- `JWT_SECRET`: Clave secreta para tokens JWT (generar con: `openssl rand -base64 32`)

### 2. Verificar Archivos de Base de Datos

Asegúrese de que existen los siguientes archivos:

- `../database/mariadb/schema.sql` - Esquema de la base de datos
- `../database/mariadb/seed.sql` - Datos iniciales
- `../database/mongodb/init.js` - Inicialización de MongoDB

## Construcción y Despliegue

### Construcción de Imágenes

```bash
# Construir todas las imágenes
docker-compose build

# Construir sin caché (útil para actualizaciones)
docker-compose build --no-cache
```

### Iniciar los Servicios

```bash
# Iniciar todos los servicios en segundo plano
docker-compose up -d

# Iniciar con logs visibles
docker-compose up

# Iniciar servicios específicos
docker-compose up -d mariadb mongodb
docker-compose up -d web
```

### Verificar Estado de los Servicios

```bash
# Ver estado de los contenedores
docker-compose ps

# Ver logs de todos los servicios
docker-compose logs

# Ver logs de un servicio específico
docker-compose logs web
docker-compose logs mariadb
docker-compose logs mongodb

# Seguir logs en tiempo real
docker-compose logs -f web
```

## Gestión de Contenedores

### Detener Servicios

```bash
# Detener todos los servicios
docker-compose stop

# Detener un servicio específico
docker-compose stop web
```

### Reiniciar Servicios

```bash
# Reiniciar todos los servicios
docker-compose restart

# Reiniciar un servicio específico
docker-compose restart web
```

### Eliminar Contenedores

```bash
# Detener y eliminar contenedores
docker-compose down

# Eliminar contenedores y volúmenes (¡CUIDADO: elimina datos!)
docker-compose down -v

# Eliminar contenedores, volúmenes e imágenes
docker-compose down -v --rmi all
```

## Acceso a los Servicios

### Aplicación Web

- **URL**: http://localhost
- **Puerto**: 80

### MariaDB

- **Host**: localhost
- **Puerto**: 3306
- **Base de Datos**: carsharing
- **Usuario**: carsharing_user
- **Contraseña**: (definida en .env)

```bash
# Conectar desde línea de comandos
docker-compose exec mariadb mysql -u carsharing_user -p carsharing
```

### MongoDB

- **Host**: localhost
- **Puerto**: 27017
- **Base de Datos**: carsharing
- **Usuario**: carsharing_user
- **Contraseña**: (definida en .env)

```bash
# Conectar desde línea de comandos
docker-compose exec mongodb mongosh -u carsharing_user -p --authenticationDatabase carsharing
```

## Comandos Útiles

### Ejecutar Comandos en Contenedores

```bash
# Acceder al shell del contenedor web
docker-compose exec web bash

# Acceder al shell de MariaDB
docker-compose exec mariadb bash

# Acceder al shell de MongoDB
docker-compose exec mongodb bash
```

### Ver Información de Recursos

```bash
# Ver uso de recursos
docker stats

# Ver información de volúmenes
docker volume ls

# Ver información de redes
docker network ls
```

### Limpiar Sistema Docker

```bash
# Eliminar contenedores detenidos
docker container prune

# Eliminar imágenes no utilizadas
docker image prune

# Eliminar volúmenes no utilizados
docker volume prune

# Limpieza completa del sistema
docker system prune -a
```

## Backup y Restauración

### Backup de MariaDB

```bash
# Crear backup
docker-compose exec mariadb mysqldump -u root -p carsharing > backup_mariadb_$(date +%Y%m%d).sql

# Restaurar backup
docker-compose exec -T mariadb mysql -u root -p carsharing < backup_mariadb_20251007.sql
```

### Backup de MongoDB

```bash
# Crear backup
docker-compose exec mongodb mongodump --username admin --password admin_password --authenticationDatabase admin --db carsharing --out /tmp/backup
docker-compose cp mongodb:/tmp/backup ./backup_mongodb_$(date +%Y%m%d)

# Restaurar backup
docker-compose cp ./backup_mongodb_20251007 mongodb:/tmp/restore
docker-compose exec mongodb mongorestore --username admin --password admin_password --authenticationDatabase admin --db carsharing /tmp/restore/carsharing
```

## Solución de Problemas

### Los contenedores no inician

```bash
# Ver logs detallados
docker-compose logs

# Verificar configuración
docker-compose config

# Reconstruir sin caché
docker-compose build --no-cache
docker-compose up -d
```

### Error de conexión a base de datos

```bash
# Verificar que los contenedores estén corriendo
docker-compose ps

# Verificar health checks
docker inspect carsharing-mariadb | grep Health
docker inspect carsharing-mongodb | grep Health

# Reiniciar servicios de base de datos
docker-compose restart mariadb mongodb
```

### Problemas de permisos

```bash
# Ejecutar desde el contenedor web
docker-compose exec web chown -R www-data:www-data /var/www/html
docker-compose exec web chmod -R 755 /var/www/html
```

### Puerto 80 ya en uso

```bash
# Verificar qué proceso usa el puerto
sudo lsof -i :80

# Cambiar el puerto en docker-compose.yml
# Modificar: ports: - "8080:80"
```

### Limpiar y reiniciar desde cero

```bash
# Detener y eliminar todo
docker-compose down -v

# Eliminar imágenes
docker-compose down --rmi all

# Reconstruir y reiniciar
docker-compose build --no-cache
docker-compose up -d
```

## Monitoreo y Logs

### Ver Logs en Tiempo Real

```bash
# Todos los servicios
docker-compose logs -f

# Servicio específico
docker-compose logs -f web

# Últimas 100 líneas
docker-compose logs --tail=100 web
```

### Logs de Apache

```bash
# Error log
docker-compose exec web tail -f /var/log/apache2/error.log

# Access log
docker-compose exec web tail -f /var/log/apache2/access.log

# PHP errors
docker-compose exec web tail -f /var/log/apache2/php_errors.log
```

## Seguridad

### Recomendaciones de Seguridad

1. **Cambiar todas las contraseñas por defecto** antes de desplegar en producción
2. **Generar JWT_SECRET único**: `openssl rand -base64 32`
3. **Configurar SSL/TLS** para conexiones HTTPS
4. **Restringir acceso a puertos** usando firewall
5. **Mantener Docker actualizado** regularmente
6. **No exponer puertos de bases de datos** en producción
7. **Usar secrets de Docker** para información sensible
8. **Revisar logs regularmente** para detectar actividad sospechosa

### Configurar HTTPS (Producción)

1. Obtener certificados SSL (Let's Encrypt recomendado)
2. Modificar `docker-compose.yml` para exponer puerto 443
3. Descomentar configuración SSL en `config/carsharing.conf`
4. Reiniciar servicios

## Actualización

### Actualizar Aplicación

```bash
# Detener servicios
docker-compose stop web

# Actualizar código fuente
git pull  # o copiar nuevos archivos

# Reconstruir imagen
docker-compose build web

# Reiniciar servicio
docker-compose up -d web
```

### Actualizar Imágenes Base

```bash
# Descargar últimas imágenes
docker-compose pull

# Reconstruir con nuevas imágenes
docker-compose build --pull

# Reiniciar servicios
docker-compose up -d
```

## Desarrollo vs Producción

### Modo Desarrollo

- Volúmenes montados para hot-reload
- Logs detallados habilitados
- Debug mode activado
- Puertos expuestos para acceso directo

### Modo Producción

- Código copiado en imagen (no montado)
- Logs mínimos
- Debug mode desactivado
- Puertos restringidos
- SSL/TLS habilitado
- Secrets de Docker para credenciales

## Soporte y Contacto

Para problemas o preguntas:

- **Documentación**: Consulte la documentación completa en `/final/README.md`
- **Logs**: Revise los logs de los contenedores
- **Issues**: Reporte problemas en el sistema de gestión de proyectos

## Licencia

Plataforma de Carsharing - Todos los derechos reservados © 2025

---

**Última actualización:** Octubre 2025  
**Mantenido por:** Equipo de Desarrollo de Carsharing
