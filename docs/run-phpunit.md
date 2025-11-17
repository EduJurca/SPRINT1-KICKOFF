# Cómo ejecutar tests PHPUnit (CRUD Usuarios)

Instrucciones para ejecutar las pruebas unitarias en el proyecto.

Requisitos:
- PHP 8.x (CLI)
- Composer

Pasos:
1. Instalar dependencias de desarrollo (phpunit):

```bash
composer install --no-interaction --prefer-dist
```

2. Ejecutar todas las pruebas:

```bash
vendor/bin/phpunit --colors=always --testdox
```

3. Ejecutar solamente el test de usuarios:

```bash
vendor/bin/phpunit --filter UserCrudTest --colors=always --testdox
```

Notas:
- El proyecto ya incluye un archivo `phpunit.xml` que apunta a `test` como directorio de pruebas.
- Las pruebas usan clases FakeDB/FakeStmt para simular consultas de base de datos sin necesidad de un servidor real.
- Si quieres ejecutar las pruebas con una BD real (MariaDB), crea un `.env.testing` y ejecuta los scripts de migración para crear tablas y datos de prueba antes de ejecutar los tests.

Ejemplo de migración local (opcional):

```bash
# Crea BD de pruebas y aplica migraciones
mysql -u root -p < database/create_test_db.sql
# (Luego corre las migraciones de tu proyecto si existen)
```

Si necesitas que escriba pruebas adicionales (controladores/PSR-7 integration o pruebas funcionales con Selenium), dímelo y lo añado.
