#!/usr/bin/env python3
"""
Carsharing Platform - Admin Deployment Tool
============================================
Herramienta de administración Python integral para el despliegue y gestión automatizada
de la infraestructura Docker de la plataforma de coche compartido.

Autor: Carsharing Platform Team
Fecha: Octubre 2025
Version: 1.0.0
"""

import argparse
import os
import sys
import time
import secrets
import re
import shutil
import tarfile
from datetime import datetime
from pathlib import Path
from typing import Optional, Dict, List, Tuple
import subprocess

# --- Lógica de Instalación Automática (Mantenida del paso anterior) ---
def install_package(package_name: str, import_name: str = None):
    """
    Intenta importar un paquete. Si falla, intenta instalarlo usando pip.
    """
    if import_name is None:
        import_name = package_name

    try:
        __import__(import_name)
    except ImportError:
        print(f"La librería '{package_name}' no está instalada.")
        print(f"Intentando instalar {package_name} automáticamente...")
        
        try:
            pip_executable = [sys.executable, "-m", "pip"]
            subprocess.check_call(pip_executable + ["--version"], stdout=subprocess.DEVNULL)
            subprocess.check_call(pip_executable + ["install", package_name])
            print(f"Instalación de '{package_name}' completada.")
            __import__(import_name)
        except subprocess.CalledProcessError as e:
            print(f"Error: Fallo al instalar {package_name}.")
            print("Asegúrese de que 'pip' esté instalado y en la variable PATH.")
            sys.exit(1)
        except Exception as e:
            print(f"Error: Fallo inesperado durante la instalación o re-importación de {package_name}.")
            sys.exit(1)

# --- Verificación e Instalación de Librerías ---
install_package("docker")
try:
    import docker
    from docker.errors import DockerException, APIError, NotFound
except ImportError:
    sys.exit(1)

install_package("colorama")
try:
    from colorama import Fore, Back, Style, init as colorama_init
except ImportError:
    sys.exit(1)

install_package("python-dotenv", "dotenv")
try:
    from dotenv import load_dotenv, set_key, dotenv_values
except ImportError:
    sys.exit(1)

install_package("tqdm")
try:
    from tqdm import tqdm
except ImportError:
    sys.exit(1)


# Inicializar colorama para salida con color en diferentes plataformas
colorama_init(autoreset=True)

# Constantes
VERSION = "1.0.0"
SCRIPT_DIR = Path(__file__).parent.resolve()
DOCKER_DIR = SCRIPT_DIR / "docker"
DATABASE_DIR = SCRIPT_DIR / "database"
WEB_DIR = SCRIPT_DIR / "web"
BACKUP_DIR = SCRIPT_DIR / "backups"
LOG_FILE = SCRIPT_DIR / "admin_tool.log"
ENV_FILE = DOCKER_DIR / ".env"
ENV_EXAMPLE_FILE = DOCKER_DIR / ".env.example"
DOCKER_COMPOSE_FILE = DOCKER_DIR / "docker-compose.yml"

# Nombres de contenedores de docker-compose.yml
CONTAINER_WEB = "carsharing-web"
CONTAINER_MARIADB = "carsharing-mariadb"
CONTAINER_MONGODB = "carsharing-mongodb"

# Nombres de servicios de docker-compose.yml
SERVICE_WEB = "web"
SERVICE_MARIADB = "mariadb"
SERVICE_MONGODB = "mongodb"


class Logger:
    """Clase Logger para registro en consola y archivo con colores."""
    
    def __init__(self, log_file: Path, verbose: bool = False):
        self.log_file = log_file
        self.verbose = verbose
        
    def _write_to_file(self, message: str):
        """Escribir mensaje de registro en archivo."""
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        with open(self.log_file, 'a', encoding='utf-8') as f:
            f.write(f"[{timestamp}] {message}\n")
    
    def success(self, message: str):
        """Registrar mensaje de éxito."""
        print(f"{Fore.GREEN}✓ {message}{Style.RESET_ALL}")
        self._write_to_file(f"SUCCESS: {message}")
    
    def error(self, message: str):
        """Registrar mensaje de error."""
        print(f"{Fore.RED}✗ {message}{Style.RESET_ALL}")
        self._write_to_file(f"ERROR: {message}")
    
    def warning(self, message: str):
        """Registrar mensaje de advertencia."""
        print(f"{Fore.YELLOW}⚠ {message}{Style.RESET_ALL}")
        self._write_to_file(f"WARNING: {message}")
    
    def info(self, message: str):
        """Registrar mensaje de información."""
        print(f"{Fore.CYAN}ℹ {message}{Style.RESET_ALL}")
        self._write_to_file(f"INFO: {message}")
    
    def debug(self, message: str):
        """Registrar mensaje de depuración (solo si es detallado)."""
        if self.verbose:
            print(f"{Fore.MAGENTA}[DEBUG] {message}{Style.RESET_ALL}")
        self._write_to_file(f"DEBUG: {message}")
    
    def header(self, message: str):
        """Registrar mensaje de encabezado."""
        print(f"\n{Fore.BLUE}{Style.BRIGHT}{'=' * 60}")
        print(f"{message}")
        print(f"{'=' * 60}{Style.RESET_ALL}\n")
        self._write_to_file(f"HEADER: {message}")
    
    def prompt(self, message: str) -> str:
        """Solicitar entrada al usuario."""
        return input(f"{Fore.YELLOW}? {message}{Style.RESET_ALL}")


class DockerManager:
    """Clase de gestión de Docker para operaciones con contenedores."""
    
    def __init__(self, logger: Logger, dry_run: bool = False):
        self.logger = logger
        self.dry_run = dry_run
        self.client = None
        
    def _detect_compose_cmd(self) -> List[str]:
        """Determina si usar `docker compose` (v2) o `docker-compose` (v1)."""
        try:
            result = subprocess.run(["docker", "compose", "version"], capture_output=True, text=True)
            if result.returncode == 0:
                return ["docker", "compose"]
        except Exception:
            pass
        # Fallback a docker-compose v1
        if shutil.which("docker-compose"):
            return ["docker-compose"]
        # Último recurso: usar docker compose aunque falle (para mensajes de error consistentes)
        return ["docker", "compose"]
        
    def connect(self) -> bool:
        """
        Conectar al demonio Docker usando múltiples estrategias con persistencia:
        1. Socket directo (Mac/Linux) o Named Pipe (Windows).
        2. docker.from_env() (Usa DOCKER_HOST o configuración por defecto).
        3. En macOS, detectar el socket de Docker Desktop (~/.docker/run/docker.sock) o el host del contexto actual.
        """
        max_attempts = 5
        wait_time = 5 # 5 segundos por intento
        platform = sys.platform

        # --- Intento de Conexión 1: Socket/Pipe Directo (Más fiable en entornos Desktop) ---
        try:
            if platform == 'darwin' or platform.startswith('linux'):
                self.logger.debug("Intentando conexión directa al/los socket(s) Unix...")
                candidate_sockets: List[Path] = []
                if platform == 'darwin':
                    # Docker Desktop (nuevas versiones) expone este socket en macOS
                    candidate_sockets.append(Path.home() / ".docker/run/docker.sock")
                # Socket clásico
                candidate_sockets.append(Path("/var/run/docker.sock"))

                last_err: Optional[Exception] = None
                for sock in candidate_sockets:
                    try:
                        if sock.exists():
                            self.logger.debug(f"Probando socket: {sock}")
                            self.client = docker.DockerClient(base_url=f"unix://{sock}", timeout=30)
                            self.client.ping()
                            self.logger.success(f"Conectado exitosamente al demonio Docker (Socket: {sock})")
                            return True
                        else:
                            self.logger.debug(f"Socket no encontrado: {sock}")
                    except DockerException as e:
                        last_err = e
                        self.logger.debug(f"Fallo conexión con socket {sock}: {e}")
                # Si no funcionó ningún socket directo en mac/linux, intentaremos otras vías
                if last_err:
                    raise last_err
            elif platform.startswith('win'):
                self.logger.debug("Intentando conexión directa al named pipe de Windows...")
                self.client = docker.DockerClient(base_url='npipe:////./pipe/docker_engine', timeout=30)
                self.client.ping()
                self.logger.success("Conectado exitosamente al demonio Docker (Named Pipe)")
                return True
            else:
                # Si la plataforma no es reconocida, saltar a from_env()
                raise DockerException("Plataforma no reconocida para conexión directa.")
            
        except DockerException:
            # Falló la conexión directa, pasamos a otras estrategias
            self.logger.debug("Fallo en la conexión directa al socket/pipe. Buscando configuración de entorno/contexto...")

        # --- Intento de Conexión 1b (macOS): Detectar host vía contexto de Docker CLI ---
        if platform == 'darwin':
            try:
                # Descubrir el contexto actual
                ctx_show = subprocess.run(["docker", "context", "show"], capture_output=True, text=True)
                if ctx_show.returncode == 0:
                    current_ctx = ctx_show.stdout.strip()
                    if current_ctx:
                        self.logger.debug(f"Contexto Docker actual: {current_ctx}")
                        ctx_inspect = subprocess.run([
                            "docker", "context", "inspect", current_ctx, "--format", "{{ (index . 0).Endpoints.docker.Host }}"
                        ], capture_output=True, text=True)
                        if ctx_inspect.returncode == 0:
                            host = ctx_inspect.stdout.strip()
                            if host:
                                self.logger.debug(f"Host detectado desde contexto: {host}")
                                try:
                                    self.client = docker.DockerClient(base_url=host, timeout=30)
                                    self.client.ping()
                                    self.logger.success("Conectado exitosamente al demonio Docker (Desde Contexto)")
                                    return True
                                except DockerException as e:
                                    self.logger.debug(f"Fallo conectando al host del contexto: {e}")
            except Exception as e:
                self.logger.debug(f"No se pudo inspeccionar el contexto de Docker: {e}")

        # --- Intento de Conexión 2: Ciclo de Reintentos con `docker.from_env()` (Usa DOCKER_HOST) ---
        for attempt in range(1, max_attempts + 1):
            try:
                self.logger.debug(f"Conectando al demonio Docker vía entorno (Intento {attempt}/{max_attempts})...")
                
                # from_env() respeta las variables DOCKER_HOST establecidas por Docker Desktop
                self.client = docker.from_env(timeout=30) 
                self.client.ping()
                
                self.logger.success("Conectado exitosamente al demonio Docker (Vía Entorno)")
                return True
            
            except DockerException as e:
                self.logger.debug(f"Fallo de conexión en el intento {attempt}: {e}")
                
                # Si es el último intento, emitir el error final y salir
                if attempt == max_attempts:
                    self.logger.error("Fallo al conectar al demonio Docker después de múltiples intentos.")
                    if platform.startswith('linux'):
                        self.logger.error("Causa: El demonio Docker no está ejecutándose o requiere permisos.")
                        self.logger.info(f"Intente iniciarlo: sudo systemctl start docker")
                    elif platform == 'darwin' or platform.startswith('win'):
                        self.logger.error("Causa: Docker Desktop no está completamente inicializado o las variables de entorno no se han cargado.")
                        self.logger.info(f"**Asegúrese de que Docker Desktop esté en estado 'Running'. En macOS, suele usarse ~/.docker/run/docker.sock o el host del contexto actual.**")
                    else:
                        self.logger.error("Asegúrese de que Docker esté instalado y en ejecución.")
                    return False

                # Lógica de reintento con mensaje informativo
                if platform.startswith('linux') and attempt == 1:
                    # Intenta iniciar solo en el primer reintento de Linux
                    self.logger.warning("Demonio Docker no encontrado. Intentando iniciarlo automáticamente (puede requerir sudo)...")
                    try:
                        subprocess.run(["sudo", "systemctl", "start", "docker"], check=False, capture_output=True)
                        self.logger.info(f"Comando de inicio enviado. Reintentando la conexión en {wait_time} segundos...")
                    except Exception as start_err:
                        self.logger.warning(f"Fallo al enviar comando de inicio: {start_err}")
                
                elif platform == 'darwin' or platform.startswith('win'):
                    self.logger.warning(f"No se pudo establecer la conexión (Docker Desktop). Reintentando en {wait_time} segundos...")

                time.sleep(wait_time) 
                
        return False
    
    def build_images(self) -> bool:
        """Construir imágenes Docker."""
        if self.dry_run:
            self.logger.info("[EJECUCIÓN EN SECO] Se construirían imágenes Docker")
            return True
        
        try:
            self.logger.info("Construyendo imágenes Docker...")
            
            # Construir imagen del servicio web
            self.logger.info(f"Construyendo imagen de {SERVICE_WEB}...")
            image, build_logs = self.client.images.build(
                path=str(SCRIPT_DIR),
                dockerfile="docker/Dockerfile",
                tag="carsharing-web:latest",
                rm=True
            )
            
            for log in build_logs:
                if 'stream' in log:
                    self.logger.debug(log['stream'].strip())
            
            self.logger.success("Imágenes Docker construidas exitosamente")
            return True
        except APIError as e:
            self.logger.error(f"Fallo al construir imágenes Docker: {e}")
            return False
    
    def start_containers(self) -> bool:
        """Iniciar todos los contenedores usando docker compose/compose v1."""
        if self.dry_run:
            self.logger.info("[EJECUCIÓN EN SECO] Se iniciarían contenedores")
            return True
        
        try:
            self.logger.info("Iniciando contenedores...")
            
            base_cmd = self._detect_compose_cmd()
            cmd = base_cmd + ["up", "-d"]
            result = subprocess.run(
                cmd,
                cwd=str(DOCKER_DIR),
                capture_output=True,
                text=True
            )
            
            if result.returncode == 0:
                self.logger.success("Contenedores iniciados exitosamente")
                return True
            else:
                self.logger.error(f"Fallo al iniciar contenedores: {result.stderr}")
                return False
        except Exception as e:
            self.logger.error(f"Fallo al iniciar contenedores: {e}")
            return False
    
    def stop_containers(self) -> bool:
        """Detener todos los contenedores."""
        if self.dry_run:
            self.logger.info("[EJECUCIÓN EN SECO] Se detendrían contenedores")
            return True
        
        try:
            self.logger.info("Deteniendo contenedores...")
            
            base_cmd = self._detect_compose_cmd()
            cmd = base_cmd + ["down"]
            result = subprocess.run(
                cmd,
                cwd=str(DOCKER_DIR),
                capture_output=True,
                text=True
            )
            
            if result.returncode == 0:
                self.logger.success("Contenedores detenidos exitosamente")
                return True
            else:
                self.logger.error(f"Fallo al detener contenedores: {result.stderr}")
                return False
        except Exception as e:
            self.logger.error(f"Fallo al detener contenedores: {e}")
            return False
    
    def restart_containers(self) -> bool:
        """Reiniciar todos los contenedores."""
        if self.dry_run:
            self.logger.info("[EJECUCIÓN EN SECO] Se reiniciarían contenedores")
            return True
        
        self.logger.info("Reiniciando contenedores...")
        if self.stop_containers():
            time.sleep(2)
            return self.start_containers()
        return False
    
    def get_container_status(self) -> List[Dict]:
        """Obtener el estado de todos los contenedores."""
        try:
            containers = []
            for container_name in [CONTAINER_WEB, CONTAINER_MARIADB, CONTAINER_MONGODB]:
                try:
                    container = self.client.containers.get(container_name)
                    container.reload()
                    containers.append({
                        'name': container.name,
                        'status': container.status,
                        'health': container.attrs.get('State', {}).get('Health', {}).get('Status', 'N/A')
                    })
                except NotFound:
                    containers.append({
                        'name': container_name,
                        'status': 'no encontrado',
                        'health': 'N/A'
                    })
            return containers
        except Exception as e:
            self.logger.error(f"Fallo al obtener el estado del contenedor: {e}")
            return []
    
    def get_logs(self, service: str, follow: bool = False, tail: int = 100) -> bool:
        """Obtener registros de un contenedor."""
        try:
            container_map = {
                SERVICE_WEB: CONTAINER_WEB,
                SERVICE_MARIADB: CONTAINER_MARIADB,
                SERVICE_MONGODB: CONTAINER_MONGODB
            }
            
            container_name = container_map.get(service)
            if not container_name:
                self.logger.error(f"Servicio desconocido: {service}")
                return False
            
            container = self.client.containers.get(container_name)
            
            if follow:
                self.logger.info(f"Siguiendo registros para {service} (Ctrl+C para detener)...")
                for log in container.logs(stream=True, follow=True):
                    print(log.decode('utf-8'), end='')
            else:
                logs = container.logs(tail=tail).decode('utf-8')
                print(logs)
            
            return True
        except NotFound:
            self.logger.error(f"Contenedor {service} no encontrado")
            return False
        except Exception as e:
            self.logger.error(f"Fallo al obtener registros: {e}")
            return False
    
    def exec_command(self, container_name: str, command: str) -> Tuple[int, str]:
        """Ejecutar comando en contenedor."""
        try:
            container = self.client.containers.get(container_name)
            exec_result = container.exec_run(command, demux=True)
            
            output = ""
            if exec_result.output:
                if isinstance(exec_result.output, tuple):
                    stdout, stderr = exec_result.output
                    if stdout:
                        output += stdout.decode('utf-8')
                    if stderr:
                        output += stderr.decode('utf-8')
                else:
                    output = exec_result.output.decode('utf-8')
            
            return exec_result.exit_code, output
        except Exception as e:
            self.logger.error(f"Fallo al ejecutar comando: {e}")
            return 1, str(e)
    
    def clean(self, remove_volumes: bool = False) -> bool:
        """Eliminar contenedores y opcionalmente volúmenes."""
        if self.dry_run:
            self.logger.info("[EJECUCIÓN EN SECO] Se limpiarían contenedores y volúmenes")
            return True
        
        try:
            self.logger.warning("¡Esto eliminará todos los contenedores y datos!")
            confirm = self.logger.prompt("¿Está seguro? (si/no): ")
            
            if confirm.lower() != 'si':
                self.logger.info("Operación de limpieza cancelada")
                return False
            
            self.logger.info("Limpiando contenedores...")
            
            base_cmd = self.docker._detect_compose_cmd()
            cmd = base_cmd + ["down"]
            if remove_volumes:
                cmd.append("-v")
            
            result = subprocess.run(
                cmd,
                cwd=str(DOCKER_DIR),
                capture_output=True,
                text=True
            )
            
            if result.returncode == 0:
                self.logger.success("Limpieza completada exitosamente")
                return True
            else:
                self.logger.error(f"Fallo al limpiar: {result.stderr}")
                return False
        except Exception as e:
            self.logger.error(f"Fallo al limpiar: {e}")
            return False


class DatabaseManager:
    """Clase de gestión de bases de datos para copia de seguridad, restauración e inicialización."""
    
    def __init__(self, logger: Logger, docker_manager: DockerManager, dry_run: bool = False):
        self.logger = logger
        self.docker = docker_manager
        self.dry_run = dry_run
    
    def backup(self) -> bool:
        """Copia de seguridad de las bases de datos MariaDB y MongoDB."""
        if self.dry_run:
            self.logger.info("[EJECUCIÓN EN SECO] Se haría copia de seguridad de las bases de datos")
            return True
        
        try:
            # Crear directorio de copia de seguridad con marca de tiempo
            timestamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
            backup_path = BACKUP_DIR / timestamp
            backup_path.mkdir(parents=True, exist_ok=True)
            
            self.logger.info(f"Creando copia de seguridad en: {backup_path}")
            
            # Cargar variables de entorno
            env_vars = dotenv_values(ENV_FILE)
            
            # Copia de seguridad de MariaDB
            self.logger.info("Haciendo copia de seguridad de MariaDB...")
            mariadb_backup = backup_path / "mariadb_backup.sql"
            
            db_name = env_vars.get('DB_NAME', 'carsharing')
            db_user = env_vars.get('DB_USER', 'carsharing_user')
            db_password = env_vars.get('DB_PASSWORD', 'carsharing_pass')
            
            cmd = f"mysqldump -u{db_user} -p{db_password} {db_name}"
            exit_code, output = self.docker.exec_command(CONTAINER_MARIADB, cmd)
            
            if exit_code == 0:
                with open(mariadb_backup, 'w', encoding='utf-8') as f:
                    f.write(output)
                self.logger.success(f"Copia de seguridad de MariaDB guardada: {mariadb_backup}")
            else:
                self.logger.error(f"Fallo en la copia de seguridad de MariaDB: {output}")
                return False
            
            # Copia de seguridad de MongoDB
            self.logger.info("Haciendo copia de seguridad de MongoDB...")
            mongodb_backup_dir = backup_path / "mongodb_backup"
            
            mongo_db = env_vars.get('MONGO_DB', 'carsharing')
            mongo_user = env_vars.get('MONGO_USER', 'carsharing_user')
            mongo_password = env_vars.get('MONGO_PASSWORD', 'carsharing_pass')
            
            cmd = f"mongodump --db={mongo_db} --username={mongo_user} --password={mongo_password} --authenticationDatabase=admin --out=/tmp/mongodb_backup"
            exit_code, output = self.docker.exec_command(CONTAINER_MONGODB, cmd)
            
            if exit_code == 0:
                # Copiar copia de seguridad del contenedor
                import subprocess
                result = subprocess.run(
                    ["docker", "cp", f"{CONTAINER_MONGODB}:/tmp/mongodb_backup", str(mongodb_backup_dir)],
                    capture_output=True,
                    text=True
                )
                
                if result.returncode == 0:
                    self.logger.success(f"Copia de seguridad de MongoDB guardada: {mongodb_backup_dir}")
                else:
                    self.logger.error(f"Fallo al copiar copia de seguridad de MongoDB: {result.stderr}")
                    return False
            else:
                self.logger.error(f"Fallo en la copia de seguridad de MongoDB: {output}")
                return False
            
            # Comprimir copia de seguridad
            self.logger.info("Comprimiendo copia de seguridad...")
            tar_file = BACKUP_DIR / f"backup_{timestamp}.tar.gz"
            
            with tarfile.open(tar_file, "w:gz") as tar:
                tar.add(backup_path, arcname=timestamp)
            
            # Eliminar copia de seguridad sin comprimir
            shutil.rmtree(backup_path)
            
            # Obtener tamaño de la copia de seguridad
            size_mb = tar_file.stat().st_size / (1024 * 1024)
            
            self.logger.success(f"Copia de seguridad completada: {tar_file} ({size_mb:.2f} MB)")
            return True
            
        except Exception as e:
            self.logger.error(f"Fallo en la copia de seguridad: {e}")
            return False
    
    def restore(self, backup_file: Optional[str] = None) -> bool:
        """Restaurar bases de datos desde copia de seguridad."""
        if self.dry_run:
            self.logger.info("[EJECUCIÓN EN SECO] Se restaurarían bases de datos")
            return True
        
        try:
            # Listar copias de seguridad disponibles
            if not BACKUP_DIR.exists():
                self.logger.error("No se encontraron copias de seguridad")
                return False
            
            backups = sorted([f for f in BACKUP_DIR.glob("backup_*.tar.gz")], reverse=True)
            
            if not backups:
                self.logger.error("No se encontraron copias de seguridad")
                return False
            
            if not backup_file:
                self.logger.info("Copias de seguridad disponibles:")
                for i, backup in enumerate(backups, 1):
                    size_mb = backup.stat().st_size / (1024 * 1024)
                    self.logger.info(f"  {i}. {backup.name} ({size_mb:.2f} MB)")
                
                choice = self.logger.prompt(f"Seleccione la copia de seguridad (1-{len(backups)}): ")
                try:
                    backup_file = str(backups[int(choice) - 1])
                except (ValueError, IndexError):
                    self.logger.error("Selección no válida")
                    return False
            
            backup_path = Path(backup_file)
            if not backup_path.exists():
                self.logger.error(f"Archivo de copia de seguridad no encontrado: {backup_file}")
                return False
            
            self.logger.warning("¡Esto sobrescribirá los datos actuales de la base de datos!")
            confirm = self.logger.prompt("¿Está seguro? (si/no): ")
            
            if confirm.lower() != 'si':
                self.logger.info("Operación de restauración cancelada")
                return False
            
            # Extraer copia de seguridad
            self.logger.info("Extrayendo copia de seguridad...")
            extract_dir = BACKUP_DIR / "temp_restore"
            extract_dir.mkdir(exist_ok=True)
            
            with tarfile.open(backup_path, "r:gz") as tar:
                tar.extractall(extract_dir)
            
            # Encontrar el directorio de la copia de seguridad
            backup_dirs = list(extract_dir.iterdir())
            if not backup_dirs:
                self.logger.error("Archivo de copia de seguridad no válido")
                shutil.rmtree(extract_dir)
                return False
            
            restore_path = backup_dirs[0]
            
            # Cargar variables de entorno
            env_vars = dotenv_values(ENV_FILE)
            
            # Restaurar MariaDB
            self.logger.info("Restaurando MariaDB...")
            mariadb_backup = restore_path / "mariadb_backup.sql"
            
            if mariadb_backup.exists():
                with open(mariadb_backup, 'r', encoding='utf-8') as f:
                    sql_content = f.read()
                
                db_name = env_vars.get('DB_NAME', 'carsharing')
                db_user = env_vars.get('DB_USER', 'carsharing_user')
                db_password = env_vars.get('DB_PASSWORD', 'carsharing_pass')
                
                # Copiar archivo SQL al contenedor
                import subprocess
                subprocess.run(
                    ["docker", "cp", str(mariadb_backup), f"{CONTAINER_MARIADB}:/tmp/restore.sql"],
                    check=True
                )
                
                cmd = f"mysql -u{db_user} -p{db_password} {db_name} < /tmp/restore.sql"
                exit_code, output = self.docker.exec_command(CONTAINER_MARIADB, f"sh -c '{cmd}'")
                
                if exit_code == 0:
                    self.logger.success("MariaDB restaurada exitosamente")
                else:
                    self.logger.error(f"Fallo en la restauración de MariaDB: {output}")
            
            # Restaurar MongoDB
            self.logger.info("Restaurando MongoDB...")
            mongodb_backup_dir = restore_path / "mongodb_backup"
            
            if mongodb_backup_dir.exists():
                import subprocess
                
                # Copiar copia de seguridad al contenedor
                subprocess.run(
                    ["docker", "cp", str(mongodb_backup_dir), f"{CONTAINER_MONGODB}:/tmp/"],
                    check=True
                )
                
                mongo_db = env_vars.get('MONGO_DB', 'carsharing')
                mongo_user = env_vars.get('MONGO_USER', 'carsharing_user')
                mongo_password = env_vars.get('MONGO_PASSWORD', 'carsharing_pass')
                
                cmd = f"mongorestore --db={mongo_db} --username={mongo_user} --password={mongo_password} --authenticationDatabase=admin --drop /tmp/mongodb_backup/{mongo_db}"
                exit_code, output = self.docker.exec_command(CONTAINER_MONGODB, cmd)
                
                if exit_code == 0:
                    self.logger.success("MongoDB restaurada exitosamente")
                else:
                    self.logger.error(f"Fallo en la restauración de MongoDB: {output}")
            
            # Limpieza
            shutil.rmtree(extract_dir)
            
            self.logger.success("Restauración de bases de datos completada")
            return True
            
        except Exception as e:
            self.logger.error(f"Fallo en la restauración: {e}")
            return False
    
    def initialize(self) -> bool:
        """Inicializar bases de datos con esquema y datos de ejemplo."""
        if self.dry_run:
            self.logger.info("[EJECUCIÓN EN SECO] Se inicializarían bases de datos")
            return True
        
        try:
            self.logger.info("Inicializando bases de datos...")
            
            # Esperar a que los contenedores estén listos
            self.logger.info("Esperando a que los contenedores de la base de datos estén listos...")
            time.sleep(5)
            
            # Cargar variables de entorno
            env_vars = dotenv_values(ENV_FILE)
            
            # Inicializar MariaDB
            self.logger.info("Inicializando MariaDB...")
            
            db_name = env_vars.get('DB_NAME', 'carsharing')
            db_user = env_vars.get('DB_USER', 'carsharing_user')
            db_password = env_vars.get('DB_PASSWORD', 'carsharing_pass')
            
            # Copiar archivos SQL al contenedor
            import subprocess
            schema_file = DATABASE_DIR / "mariadb" / "schema.sql"
            seed_file = DATABASE_DIR / "mariadb" / "seed.sql"
            
            subprocess.run(
                ["docker", "cp", str(schema_file), f"{CONTAINER_MARIADB}:/tmp/schema.sql"],
                check=True
            )
            subprocess.run(
                ["docker", "cp", str(seed_file), f"{CONTAINER_MARIADB}:/tmp/seed.sql"],
                check=True
            )
            
            # Ejecutar esquema
            cmd = f"mysql -u{db_user} -p{db_password} {db_name} < /tmp/schema.sql"
            exit_code, output = self.docker.exec_command(CONTAINER_MARIADB, f"sh -c '{cmd}'")
            
            if exit_code == 0:
                self.logger.success("Esquema de MariaDB inicializado")
            else:
                self.logger.error(f"Fallo en la inicialización del esquema de MariaDB: {output}")
                return False
            
            # Ejecutar datos de ejemplo
            cmd = f"mysql -u{db_user} -p{db_password} {db_name} < /tmp/seed.sql"
            exit_code, output = self.docker.exec_command(CONTAINER_MARIADB, f"sh -c '{cmd}'")
            
            if exit_code == 0:
                self.logger.success("Datos de ejemplo de MariaDB cargados")
            else:
                self.logger.error(f"Fallo en la carga de datos de ejemplo de MariaDB: {output}")
                return False
            
            # Inicializar MongoDB
            self.logger.info("Inicializando MongoDB...")
            
            init_file = DATABASE_DIR / "mongodb" / "init.js"
            
            subprocess.run(
                ["docker", "cp", str(init_file), f"{CONTAINER_MONGODB}:/tmp/init.js"],
                check=True
            )
            
            mongo_db = env_vars.get('MONGO_DB', 'carsharing')
            mongo_user = env_vars.get('MONGO_USER', 'carsharing_user')
            mongo_password = env_vars.get('MONGO_PASSWORD', 'carsharing_pass')
            
            cmd = f"mongosh {mongo_db} --username {mongo_user} --password {mongo_password} --authenticationDatabase admin /tmp/init.js"
            exit_code, output = self.docker.exec_command(CONTAINER_MONGODB, cmd)
            
            if exit_code == 0:
                self.logger.success("MongoDB inicializada")
            else:
                self.logger.warning(f"Salida de la inicialización de MongoDB: {output}")
            
            self.logger.success("Inicialización de bases de datos completada")
            return True
            
        except Exception as e:
            self.logger.error(f"Fallo en la inicialización de bases de datos: {e}")
            return False


class HealthChecker:
    """Clase de verificación de salud para la monitorización de servicios."""
    
    def __init__(self, logger: Logger, docker_manager: DockerManager):
        self.logger = logger
        self.docker = docker_manager
    
    def check_all(self) -> bool:
        """Realizar una verificación de salud completa."""
        self.logger.header("Informe de Verificación de Salud")
        
        all_healthy = True
        
        # Verificar demonio Docker
        self.logger.info("Verificando demonio Docker...")
        if self.docker.client:
            self.logger.success("Demonio Docker: En ejecución")
        else:
            self.logger.error("Demonio Docker: No está en ejecución")
            all_healthy = False
            return all_healthy
        
        # Verificar contenedores
        self.logger.info("\nVerificando contenedores...")
        containers = self.docker.get_container_status()
        
        for container in containers:
            name = container['name']
            status = container['status']
            health = container['health']
            
            if status == 'running':
                if health == 'healthy' or health == 'N/A':
                    self.logger.success(f"{name}: {status} ({health})")
                else:
                    self.logger.warning(f"{name}: {status} ({health})")
                    all_healthy = False
            else:
                self.logger.error(f"{name}: {status}")
                all_healthy = False
        
        # Verificar conexiones a bases de datos
        if all([c['status'] == 'running' for c in containers]):
            self.logger.info("\nVerificando conexiones a bases de datos...")
            
            # Verificar MariaDB
            exit_code, output = self.docker.exec_command(CONTAINER_MARIADB, "mysql -e 'SELECT 1'")
            if exit_code == 0:
                self.logger.success("MariaDB: Conexión exitosa")
            else:
                self.logger.error("MariaDB: Conexión fallida")
                all_healthy = False
            
            # Verificar MongoDB
            exit_code, output = self.docker.exec_command(CONTAINER_MONGODB, "mongosh --eval 'db.adminCommand(\"ping\")'")
            if exit_code == 0:
                self.logger.success("MongoDB: Conexión exitosa")
            else:
                self.logger.error("MongoDB: Conexión fallida")
                all_healthy = False
            
            # Verificar servidor web
            self.logger.info("\nVerificando servidor web...")
            try:
                import urllib.request
                response = urllib.request.urlopen('http://localhost', timeout=5)
                if response.status == 200:
                    self.logger.success("Servidor web: Respondiendo")
                else:
                    self.logger.warning(f"Servidor web: Estado {response.status}")
                    all_healthy = False
            except Exception as e:
                self.logger.error(f"Servidor web: No responde ({e})")
                all_healthy = False
        
        # Verificar espacio en disco
        self.logger.info("\nVerificando espacio en disco...")
        try:
            import subprocess
            result = subprocess.run(
                ["docker", "system", "df"],
                capture_output=True,
                text=True
            )
            if result.returncode == 0:
                print(result.stdout)
        except Exception as e:
            self.logger.warning(f"No se pudo verificar el espacio en disco: {e}")
        
        # Resumen
        self.logger.info("\n" + "=" * 60)
        if all_healthy:
            self.logger.success("Estado General: Todos los sistemas saludables")
        else:
            self.logger.error("Estado General: Se detectaron algunos problemas")
        self.logger.info("=" * 60)
        
        return all_healthy


class SetupWizard:
    """Asistente de configuración interactivo para la configuración por primera vez."""
    
    def __init__(self, logger: Logger):
        self.logger = logger
        self.config = {}
    
    def run(self) -> bool:
        """Ejecutar el asistente de configuración interactivo."""
        self.logger.header("Plataforma de Coche Compartido - Asistente de Configuración")
        
        self.logger.info("¡Bienvenido al asistente de configuración de la Plataforma de Coche Compartido!")
        self.logger.info("Este asistente le ayudará a configurar la plataforma para el primer despliegue.\n")
        
        # Comprobar si .env ya existe
        if ENV_FILE.exists():
            self.logger.warning(f"El archivo .env ya existe en {ENV_FILE}")
            overwrite = self.logger.prompt("¿Desea sobrescribirlo? (si/no): ")
            if overwrite.lower() != 'si':
                self.logger.info("Configuración cancelada")
                return False
            
            # Copia de seguridad del .env existente
            backup_file = ENV_FILE.parent / f".env.backup.{datetime.now().strftime('%Y%m%d_%H%M%S')}"
            shutil.copy(ENV_FILE, backup_file)
            self.logger.info(f"El archivo .env existente ha sido respaldado en {backup_file}")
        
        # Comprobar si .env.example existe
        if not ENV_EXAMPLE_FILE.exists():
            self.logger.error(f".env.example no encontrado en {ENV_EXAMPLE_FILE}")
            return False
        
        # Configuración de MariaDB
        self.logger.header("Configuración de MariaDB")
        self.config['MYSQL_ROOT_PASSWORD'] = self._prompt_password(
            "Contraseña de root de MariaDB",
            default="root_password_change_me"
        )
        self.config['DB_NAME'] = self.logger.prompt("Nombre de la base de datos [carsharing]: ") or "carsharing"
        self.config['DB_USER'] = self.logger.prompt("Usuario de la base de datos [carsharing_user]: ") or "carsharing_user"
        self.config['DB_PASSWORD'] = self._prompt_password(
            "Contraseña de la base de datos",
            default="carsharing_pass"
        )
        
        # Configuración de MongoDB
        self.logger.header("Configuración de MongoDB")
        self.config['MONGO_ROOT_USER'] = self.logger.prompt("Nombre de usuario de root de MongoDB [admin]: ") or "admin"
        self.config['MONGO_ROOT_PASSWORD'] = self._prompt_password(
            "Contraseña de root de MongoDB",
            default="admin_password_change_me"
        )
        self.config['MONGO_DB'] = self.logger.prompt("Nombre de la base de datos de MongoDB [carsharing]: ") or "carsharing"
        self.config['MONGO_USER'] = self.logger.prompt("Usuario de MongoDB [carsharing_user]: ") or "carsharing_user"
        self.config['MONGO_PASSWORD'] = self._prompt_password(
            "Contraseña de MongoDB",
            default="carsharing_pass"
        )
        
        # Configuración de la aplicación
        self.logger.header("Configuración de la Aplicación")
        
        env_choice = self.logger.prompt("Entorno (development/production) [production]: ") or "production"
        self.config['APP_ENV'] = env_choice
        
        # Secreto JWT
        self.logger.info("Clave secreta JWT (deje vacío para generar una aleatoria):")
        jwt_secret = self.logger.prompt("Secreto JWT: ")
        if not jwt_secret:
            jwt_secret = secrets.token_urlsafe(32)
            self.logger.success(f"Secreto JWT generado: {jwt_secret}")
        self.config['JWT_SECRET'] = jwt_secret
        
        # CORS
        cors = self.logger.prompt("Orígenes permitidos para CORS [*]: ") or "*"
        self.config['CORS_ALLOWED_ORIGINS'] = cors
        
        # Resumen
        self.logger.header("Resumen de Configuración")
        self.logger.info("MariaDB:")
        self.logger.info(f"  Base de Datos: {self.config['DB_NAME']}")
        self.logger.info(f"  Usuario: {self.config['DB_USER']}")
        self.logger.info("\nMongoDB:")
        self.logger.info(f"  Base de Datos: {self.config['MONGO_DB']}")
        self.logger.info(f"  Usuario: {self.config['MONGO_USER']}")
        self.logger.info("\nAplicación:")
        self.logger.info(f"  Entorno: {self.config['APP_ENV']}")
        self.logger.info(f"  CORS: {self.config['CORS_ALLOWED_ORIGINS']}")
        
        confirm = self.logger.prompt("\n¿Guardar configuración? (si/no): ")
        if confirm.lower() != 'si':
            self.logger.info("Configuración cancelada")
            return False
        
        # Crear archivo .env
        self.logger.info("Creando archivo .env...")
        
        # Copiar .env.example a .env
        shutil.copy(ENV_EXAMPLE_FILE, ENV_FILE)
        
        # Actualizar valores
        for key, value in self.config.items():
            set_key(ENV_FILE, key, value)
        
        self.logger.success(f"Archivo .env creado en {ENV_FILE}")
        
        # Preguntar si el usuario quiere desplegar ahora
        deploy_now = self.logger.prompt("\n¿Desplegar ahora? (si/no): ")
        if deploy_now.lower() == 'si':
            return True
        
        self.logger.info("Configuración completada. Ejecute 'python admin_tool.py deploy' para desplegar la plataforma.")
        return False
    
    def _prompt_password(self, name: str, default: str = "") -> str:
        """Solicitar contraseña con validación."""
        while True:
            password = self.logger.prompt(f"{name} [{default}]: ") or default
            
            if len(password) < 8:
                self.logger.warning("La contraseña debe tener al menos 8 caracteres")
                continue
            
            return password
    
    def _validate_email(self, email: str) -> bool:
        """Validar formato de correo electrónico."""
        pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        return re.match(pattern, email) is not None


def cmd_setup(args, logger: Logger):
    """Comando Setup - Asistente interactivo."""
    wizard = SetupWizard(logger)
    should_deploy = wizard.run()
    
    if should_deploy:
        # Ejecutar comando deploy
        return cmd_deploy(args, logger)
    
    return True


def cmd_deploy(args, logger: Logger):
    """Comando Deploy - Despliegue completo."""
    logger.header("Desplegando Plataforma de Coche Compartido")
    
    # Comprobar si .env existe
    if not ENV_FILE.exists():
        logger.error(f"Archivo .env no encontrado en {ENV_FILE}")
        logger.info("Ejecute 'python admin_tool.py setup' primero")
        return False
    
    docker_mgr = DockerManager(logger, args.dry_run)
    
    if not docker_mgr.connect():
        return False
    
    # Construir imágenes
    logger.info("Paso 1/4: Construyendo imágenes Docker...")
    if not docker_mgr.build_images():
        return False
    
    # Iniciar contenedores
    logger.info("Paso 2/4: Iniciando contenedores...")
    if not docker_mgr.start_containers():
        return False
    
    # Esperar a que los contenedores estén listos
    logger.info("Paso 3/4: Esperando a que los contenedores estén listos...")
    for i in tqdm(range(30), desc="Esperando"):
        time.sleep(1)
    
    # Inicializar bases de datos
    logger.info("Paso 4/4: Inicializando bases de datos...")
    db_mgr = DatabaseManager(logger, docker_mgr, args.dry_run)
    if not db_mgr.initialize():
        logger.warning("La inicialización de la base de datos tuvo problemas, pero el despliegue podría seguir funcionando")
    
    # Mostrar resumen
    logger.header("Resumen del Despliegue")
    logger.success("¡Plataforma de Coche Compartido desplegada exitosamente!")
    logger.info("\nAcceda a la aplicación:")
    logger.info("  Interfaz Web: http://localhost")
    logger.info("  Endpoint API: http://localhost/api")
    
    env_vars = dotenv_values(ENV_FILE)
    logger.info("\nCredenciales de la Base de Datos:")
    logger.info(f"  MariaDB: {env_vars.get('DB_USER')}@localhost:3306/{env_vars.get('DB_NAME')}")
    logger.info(f"  MongoDB: {env_vars.get('MONGO_USER')}@localhost:27017/{env_vars.get('MONGO_DB')}")
    
    logger.info("\nCredenciales de Administrador Predeterminadas:")
    logger.info("  Correo: admin@carsharing.com")
    logger.info("  Contraseña: Admin123!")
    
    logger.info("\nComandos de Gestión:")
    logger.info("  Ver registros: python admin_tool.py logs --service web")
    logger.info("  Verificar salud: python admin_tool.py health")
    logger.info("  Copia de seguridad de bases de datos: python admin_tool.py db-backup")
    
    return True


def cmd_start(args, logger: Logger):
    """Comando Start - Iniciar todos los contenedores."""
    logger.header("Iniciando Contenedores")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    return docker_mgr.start_containers()


def cmd_stop(args, logger: Logger):
    """Comando Stop - Detener todos los contenedores."""
    logger.header("Deteniendo Contenedores")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    return docker_mgr.stop_containers()


def cmd_restart(args, logger: Logger):
    """Comando Restart - Reiniciar todos los contenedores."""
    logger.header("Reiniciando Contenedores")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    return docker_mgr.restart_containers()


def cmd_status(args, logger: Logger):
    """Comando Status - Mostrar estado del contenedor."""
    logger.header("Estado del Contenedor")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    containers = docker_mgr.get_container_status()
    
    if not containers:
        logger.error("No se encontraron contenedores")
        return False
    
    # Mostrar tabla de estado
    print(f"\n{Fore.CYAN}{'Contenedor':<30} {'Estado':<15} {'Salud':<15}{Style.RESET_ALL}")
    print("-" * 60)
    
    for container in containers:
        name = container['name']
        status = container['status']
        health = container['health']
        
        # Código de color de estado
        if status == 'running':
            status_color = Fore.GREEN
            status_es = 'en ejecución'
        elif status == 'exited':
            status_color = Fore.RED
            status_es = 'detenido'
        elif status == 'not found':
            status_color = Fore.YELLOW
            status_es = 'no encontrado'
        else:
            status_color = Fore.YELLOW
            status_es = status
        
        # Código de color de salud
        if health == 'healthy':
            health_color = Fore.GREEN
            health_es = 'saludable'
        elif health == 'unhealthy':
            health_color = Fore.RED
            health_es = 'no saludable'
        else:
            health_color = Fore.YELLOW
            health_es = health
        
        print(f"{name:<30} {status_color}{status_es:<15}{Style.RESET_ALL} {health_color}{health_es:<15}{Style.RESET_ALL}")
    
    print()
    return True


def cmd_logs(args, logger: Logger):
    """Comando Logs - Ver registros del contenedor."""
    if not args.service:
        logger.error("Se requiere el nombre del servicio. Use la bandera --service")
        logger.info(f"Servicios disponibles: {SERVICE_WEB}, {SERVICE_MARIADB}, {SERVICE_MONGODB}")
        return False
    
    logger.header(f"Registros para {args.service}")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    return docker_mgr.get_logs(args.service, args.follow, args.tail)


def cmd_health(args, logger: Logger):
    """Comando Health - Verificación de salud completa."""
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    health_checker = HealthChecker(logger, docker_mgr)
    return health_checker.check_all()


def cmd_db_backup(args, logger: Logger):
    """Comando de copia de seguridad de bases de datos."""
    logger.header("Copia de Seguridad de Bases de Datos")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    db_mgr = DatabaseManager(logger, docker_mgr, args.dry_run)
    return db_mgr.backup()


def cmd_db_restore(args, logger: Logger):
    """Comando de restauración de bases de datos."""
    logger.header("Restauración de Bases de Datos")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    db_mgr = DatabaseManager(logger, docker_mgr, args.dry_run)
    return db_mgr.restore(args.backup_file)


def cmd_db_init(args, logger: Logger):
    """Comando de inicialización de bases de datos."""
    logger.header("Inicialización de Bases de Datos")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    db_mgr = DatabaseManager(logger, docker_mgr, args.dry_run)
    return db_mgr.initialize()


def cmd_clean(args, logger: Logger):
    """Comando Clean - Eliminar contenedores y volúmenes."""
    logger.header("Limpiar Contenedores y Volúmenes")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    return docker_mgr.clean(remove_volumes=True)


def cmd_update(args, logger: Logger):
    """Comando Update - Obtener últimas imágenes y reiniciar."""
    logger.header("Actualizar Plataforma")
    
    docker_mgr = DockerManager(logger, args.dry_run)
    if not docker_mgr.connect():
        return False
    
    logger.info("Obteniendo últimas imágenes...")
    
    try:
        import subprocess
        base_cmd = docker_mgr._detect_compose_cmd()
        result = subprocess.run(
            base_cmd + ["pull"],
            cwd=str(DOCKER_DIR),
            capture_output=True,
            text=True
        )
        
        if result.returncode == 0:
            logger.success("Imágenes actualizadas exitosamente")
            logger.info("Reiniciando servicios...")
            return docker_mgr.restart_containers()
        else:
            logger.error(f"Fallo al obtener imágenes: {result.stderr}")
            return False
    except Exception as e:
        logger.error(f"Fallo en la actualización: {e}")
        return False


def main():
    """Punto de entrada principal."""
    parser = argparse.ArgumentParser(
        description="Plataforma de Coche Compartido - Herramienta de Despliegue de Administración",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Ejemplos:
  python admin_tool.py setup                    # Ejecutar asistente de configuración interactivo
  python admin_tool.py deploy                   # Desplegar la plataforma
  python admin_tool.py start                    # Iniciar todos los contenedores
  python admin_tool.py logs --service web       # Ver registros del servidor web
  python admin_tool.py health                   # Verificar salud del sistema
  python admin_tool.py db-backup                # Copia de seguridad de bases de datos
        """
    )
    
    parser.add_argument('--version', action='version', version=f'%(prog)s {VERSION}')
    parser.add_argument('--verbose', '-v', action='store_true', help='Habilitar salida detallada')
    parser.add_argument('--dry-run', action='store_true', help='Previsualizar acciones sin ejecutar')
    
    subparsers = parser.add_subparsers(dest='command', help='Comandos disponibles')
    
    # Setup command
    subparsers.add_parser('setup', help='Asistente de configuración interactivo')
    
    # Deploy command
    subparsers.add_parser('deploy', help='Despliegue completo')
    
    # Start command
    subparsers.add_parser('start', help='Iniciar todos los contenedores')
    
    # Stop command
    subparsers.add_parser('stop', help='Detener todos los contenedores')
    
    # Restart command
    subparsers.add_parser('restart', help='Reiniciar todos los contenedores')
    
    # Status command
    subparsers.add_parser('status', help='Mostrar estado del contenedor')
    
    # Logs command
    logs_parser = subparsers.add_parser('logs', help='Ver registros del contenedor')
    logs_parser.add_argument('--service', '-s', help='Nombre del servicio (web, mariadb, mongodb)')
    logs_parser.add_argument('--follow', '-f', action='store_true', help='Seguir la salida del registro')
    logs_parser.add_argument('--tail', '-t', type=int, default=100, help='Número de líneas a mostrar')
    
    # Health command
    subparsers.add_parser('health', help='Verificación de salud completa')
    
    # Database backup command
    subparsers.add_parser('db-backup', help='Copia de seguridad de bases de datos')
    
    # Database restore command
    restore_parser = subparsers.add_parser('db-restore', help='Restaurar bases de datos')
    restore_parser.add_argument('--backup-file', '-b', help='Archivo de copia de seguridad a restaurar')
    
    # Database init command
    subparsers.add_parser('db-init', help='Inicializar bases de datos')
    
    # Clean command
    subparsers.add_parser('clean', help='Eliminar contenedores y volúmenes')
    
    # Update command
    subparsers.add_parser('update', help='Obtener últimas imágenes y reiniciar')
    
    args = parser.parse_args()
    
    # Crear logger
    logger = Logger(LOG_FILE, args.verbose)
    
    # Mostrar encabezado
    if not args.dry_run:
        print(f"{Fore.CYAN}{Style.BRIGHT}")
        print("╔════════════════════════════════════════════════════════════╗")
        print("║     Plataforma de Coche Compartido - Herramienta de Adm.  ║")
        print(f"║                    Versión {VERSION}                          ║")
        print("╚════════════════════════════════════════════════════════════╝")
        print(f"{Style.RESET_ALL}")
    
    # Comprobar si se proporciona el comando
    if not args.command:
        parser.print_help()
        return 1
    
    # Ejecutar comando
    commands = {
        'setup': cmd_setup,
        'deploy': cmd_deploy,
        'start': cmd_start,
        'stop': cmd_stop,
        'restart': cmd_restart,
        'status': cmd_status,
        'logs': cmd_logs,
        'health': cmd_health,
        'db-backup': cmd_db_backup,
        'db-restore': cmd_db_restore,
        'db-init': cmd_db_init,
        'clean': cmd_clean,
        'update': cmd_update
    }
    
    try:
        command_func = commands.get(args.command)
        if command_func:
            success = command_func(args, logger)
            return 0 if success else 1
        else:
            logger.error(f"Comando desconocido: {args.command}")
            return 1
    except KeyboardInterrupt:
        logger.warning("\nOperación cancelada por el usuario")
        return 130
    except Exception as e:
        logger.error(f"Error inesperado: {e}")
        if args.verbose:
            import traceback
            traceback.print_exc()
        return 1


if __name__ == '__main__':
    sys.exit(main())