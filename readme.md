# Proyecto SPRINT1-KICKOFF

Este proyecto es un sistema web desarrollado con PHP, HTML, CSS, JavaScript y Python (para funcionalidades adicionales), que utiliza bases de datos MariaDB y MongoDB. Se ejecuta en contenedores Docker y está organizado siguiendo buenas prácticas de estructura de archivos.

## 📁 Estructura del proyecto

```
project-root/
├── public_html/               # DocumentRoot de Apache
│   ├── assets/                # CSS, JS e imágenes
│   ├── index.html             # Página principal
│   └── pages/                 # Vistas HTML organizadas por módulo
│       ├── auth/
│       ├── dashboard/
│       ├── profile/
│       ├── vehicle/
│       └── accessibility/
├── src/                       # Backend y lógica de la aplicación
│   ├── controllers/           # Scripts PHP que manejan la lógica
│   ├── models/                # Modelos de datos (usuarios, vehículos, etc.)
│   └── core/                  # Clases base (Router, Controller, Database)
├── config/                    # Configuración de la aplicación y Apache
├── docker/                    # Dockerfiles y docker-compose
├── assets/                    # Recursos generales (opcional, ya dentro de public_html)
└── README.md                  # Documentación del proyecto
```

## 🐳 Docker

El proyecto se ejecuta en contenedores Docker con `docker-compose`.

### Servicios

- **web**: Servidor Apache con PHP. DocumentRoot: `public_html/`.
- **mariadb**: Base de datos relacional para usuarios y otros datos.
- **mongodb**: Base de datos NoSQL para información adicional (logs, historial, etc.).

### Montajes importantes

- `../public_html:/var/www/html` → HTML y assets.
- `../src:/var/www/src` → PHP backend (controladores y modelos).
- Volumen para logs de Apache.

### Comandos útiles

```bash
# Iniciar el proyecto
docker-compose up --build

# Detener el proyecto
docker-compose down

# Limpiar todo (contenedores, volúmenes y redes)
docker-compose down -v
```

## ⚙️ Configuración de PHP y Base de Datos

- Los scripts PHP usan `mariadb` como host para conectarse a MariaDB dentro de Docker.
- Se usa `utf8mb4` para el charset de la base de datos.
- Los controladores PHP están en `src/controllers/` y los modelos en `src/models/`.
- `.gitignore` excluye archivos temporales y sensibles (`*.env`, `*.log`, `.vscode/`, `__pycache__/`).

## 🖥️ Flujo del proyecto

1. El navegador solicita una página a Apache (`public_html/index.html` o páginas dentro de `pages/`).
2. Los formularios o fetch requests envían datos a PHP (`src/controllers/`).
3. Los controladores llaman a los modelos (`src/models/`) para interactuar con la base de datos.
4. La respuesta se devuelve en JSON o se renderiza en HTML según el caso.

## 🔹 Buenas prácticas incluidas

- Estructura tipo MVC (controllers, models, views).
- DocumentRoot separado de la lógica backend.
- Controladores PHP montados dentro de Docker correctamente.
- Uso de Docker Compose para levantar contenedores y redes.
- `.gitignore` actualizado para limpiar archivos no deseados.
- CORS habilitado para desarrollo local.

## ⚡ Notas adicionales

- Para que los fetch de PHP funcionen, se recomienda usar rutas relativas dentro de `public_html` o montar controladores correctamente en Docker.
- Las credenciales de las bases de datos se encuentran en `docker-compose.yml` y deben coincidir con las usadas en los scripts PHP.
- Para colaboración, se recomienda fork + pull requests si no se tiene permiso de escritura en el repositorio principal.