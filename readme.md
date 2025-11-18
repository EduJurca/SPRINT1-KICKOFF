# SIMS — Gestió de Mobilitat Intel·ligent

Projecte web per a la gestió de flotes, usuaris i administradors. Backend en PHP, frontend amb HTML/CSS/JS i suport per MariaDB i MongoDB. El sistema s'orquestra amb Docker per facilitar desplegament i desenvolupament local.

---

## 📁 Estructura principal

El repositori conté els principals directoris i fitxers que segueixen una organització MVC:

- `config/` : configuració de l'aplicació, fitxers d'inicialització i Docker (`docker-compose.yml`, Dockerfile-web, constants).
- `assets/` : arxius públics (CSS, imatges, JS). La carpeta `assets/css` i `assets/js` contenen els estils i scripts utilitzats per les vistes.
- `controllers/` : controladors PHP (admin, auth, public).
- `core/` : helpers, router i components centrals (`Router.php`, `Authorization.php`).
- `database/` : connexió i scripts d'inicialització (ex. `mariadb-init.sql`).
- `models/` : models del domini (User, Vehicle, Booking, Incident, etc.).
- `routes/` : definició de rutes (`web.php`).
- `views/` : plantilles i vistes separades per mòduls (admin, auth, public, errors).
- `test/` : proves unitàries amb PHPUnit (ex. `UserCrudTest.php`, `IncidentTest.php`).
- `vendor/` : dependències gestionades per Composer.
- arxius root: `composer.json`, `phpunit.xml`, `index.php`, `docker-compose.yml`, `Dockerfile-web`, `README_MVC.md`.
---

## ⚙️ Requisits i execució

Prerequisits mínims:

- `Docker` i `docker-compose` instal·lats.
- `PHP 8+` per a desenvolupament local (si no s'utilitza el contenidor).
- `Composer` per a dependències PHP.
 
## ⚙️ Configuració d'entorn (`.env`)

L'aplicació llegeix variables d'entorn des d'un fitxer `.env` a l'arrel (o `config/.env` segons la teva configuració). Assegura't d'afegir-hi les claus privades que fa servir l'aplicació, entre les quals:

- `GROQ_API_KEY` (o similar): clau del client per al servei GROQ que alimenta el chatbot del client.
- `USERWAY_KEY`: clau per al widget d'accessibilitat UserWay.


Notes de seguretat:

- Gestiona les claus en un gestor de secrets en producció (Vault, Secrets Manager, CI/CD variables, etc.).
- Si canvies claus o l'esquema, reinicia els contenidors amb `docker-compose down -v` i `docker-compose up -d --build` per assegurar consistència.

Arrancar l'entorn amb Docker (recomanat):

```zsh
docker-compose down -v
docker-compose up -d --build
```

Per reinicis normals (sense reset de volums):

```zsh
docker-compose up -d
```

Accés a l'aplicació: `http://localhost:8080` (o el port configurat a `docker-compose.yml`).


---

## 🚢 Usar amb Docker (detall)

Aquest projecte està pensat per executar-se en contenidors; els serveis habituals són `web` (PHP + Apache), `mariadb` i `mongodb`.

- Arrencar i construir imatges (el `-v` elimina volums antics quan cal reinicialitzar l'esquema):

```zsh
docker-compose down -v
docker-compose up -d --build
```

- Arrencar sense eliminar volums (ús habitual):

```zsh
docker-compose up -d
```

Comandos útils dins de l'entorn Docker:

Entrar al contenidor web (shell):

```zsh
docker-compose exec web bash
# o si el servei té un altre nom: docker exec -it <web_container_name> bash
```

Instal·lar dependències amb Composer dins del contenidor:

```zsh
docker-compose exec web composer install
```

Executar les proves PHPUnit des del contenidor:

```zsh
docker-compose exec web ./vendor/bin/phpunit --configuration phpunit.xml
```

Veure logs en temps real:

```zsh
docker-compose logs -f web mariadb
```

Si tens un servei `phpmyadmin` a `docker-compose.yml`, normalment queda exposat i es pot consultar des del navegador (ex.: `http://localhost:8081/phpmyadmin` o el port configurat). Per veure els logs del servei:

```zsh
docker-compose logs -f phpmyadmin
```

Accedir a la base de dades MariaDB des del contenidor:

```zsh
docker-compose exec mariadb mysql -u root -p
```

Volums i persistència: el `docker-compose.yml` probablement defineix volums per a MariaDB (dades persistents). Si modifiques l'esquema SQL (`database/mariadb-init.sql`) i vols forçar la re-inicialització, fes `docker-compose down -v` abans d'aixecar els serveis.

### ⚠️ IMPORTANT: Quan canviïs de branca Git

Quan facis `git checkout` a una altra branca amb canvis en `mariadb-init.sql`, **sempre** has d'executar:

```sh
docker-compose down -v  # El -v elimina els volums antics
docker-compose up -d --build
```

O simplement:
```sh
./reset-db.sh
```

**Per què?** Docker guarda la base de dades en un volum persistent. Si no l'elimines, seguirà usant l'esquema antic encara que hagis canviat de branca.

---

## 🏛 Arquitectura MVC i estructura del projecte

L'aplicació segueix un patró MVC (Model-View-Controller) amb una estructura clara per separar responsabilitats:

- `controllers/` — Lògica de negoci i gestió de peticions. Hi ha controladors d'administració (`controllers/admin/*`), d'autenticació (`auth/`) i públics (`controllers/public` o `controllers/` segons la ruta).
- `models/` — Classes que representen les entitats del domini i encapsulen accés a dades (ex.: `User`, `Vehicle`, `Booking`, `Incident`, `ChargingStation`).
- `views/` — Plantilles i fragments HTML/PHP que presenten dades a l'usuari (vistes per `admin`, `auth`, `public`, `errors`).
- `core/` — Infraestructura: `Router.php`, `Authorization.php`, helpers comuns i gestió de permisos.
- `routes/web.php` — Assignació d'URL a controladors/mètodes.
- `assets/` — Recursos públics (JS, CSS, imatges) consumits per les vistes.

Flux bàsic d'una petició HTTP:
1. El servidor web rep la sol·licitud i la redirigeix al `Router`.
2. El `Router` resol la ruta a un mètode d'un controlador.
3. El controlador crida el model corresponent per obtenir o persistir dades.
4. El controlador selecciona una vista i la retorna amb les dades per ser renderitzades.

Si vols afegir un nou recurs, normalment cal:
1. Crear el model a `models/`.
2. Afegir les operacions al controlador corresponent a `controllers/`.
3. Crear vistes a `views/` (index, form, show, edit).
4. Registrar les rutes a `routes/web.php`.

---

## 🔁 CRUD i rutes principals (resum)

El projecte implementa CRUD per les entitats principals. A continuació hi ha un resum de recursos i on buscar/afegir funcionalitats.

- **Usuaris (`User`)**
	- Models: `models/User.php`
	- Controladors: `controllers/admin/UserController.php`, possiblement `controllers/auth/AuthController.php` per registre/login.
	- Operacions típiques: llistar usuaris, crear / registrar, veure perfil, editar, eliminar, canviar rol/permís.

- **Vehicles (`Vehicle`)**
	- Models: `models/Vehicle.php`
	- Controladors: `controllers/admin/AdminVehicleController.php`, `controllers/VehicleController.php` per accés públic/usuari.
	- Operacions típiques: llistar vehicles, crear, editar, esborrar, veure detall, marcar com a disponible/no disponible.

- **Reserves (`Booking`)**
	- Models: `models/Booking.php`
	- Controladors: `controllers/public/BookingController.php` o `controllers/BookingController.php`.
	- Operacions típiques: crear reserva, llistar reserves d'un usuari, cancel·lar reserva, acceptar/rebutjar (admin).

- **Incidències (`Incident`)**
	- Models: `models/Incident.php`
	- Controladors: `controllers/IncidentController.php`, `controllers/admin/AdminIncidentController.php`.
	- Operacions típiques: reportar incidència, llistar, assignar/fer seguiment, tancar incidència.

- **Estacions de càrrega (`ChargingStation`)**
	- Models: `models/ChargingStation.php`
	- Controladors: `controllers/public/ChargingStationController.php` o `controllers/ChargingStationController.php`.
	- Operacions típiques: llistar estacions, veure detall, marcar estació com operativa/no operativa.

- **Xat / Missatgeria (`Chat`)**
	- Controlador: `controllers/ChatController.php`
	- Operacions típiques: enviar/recebre missatges, llistar xats, historial.

Notes sobre rutes: la implementació segueix convencions típiques (per exemple `/vehicles`, `/vehicles/create`, `/vehicles/{id}/edit`, `/bookings`, `/incidents`). Revisa `routes/web.php` per veure les rutes exactes i els verbs HTTP (`GET`, `POST`, `PUT/PATCH`, `DELETE`).

---


---

## 🗄 Bases de dades

- MariaDB: dades relacionals (usuaris, vehicles, reserves, pagaments). Els scripts d'inicialització es troben a `database/mariadb-init.sql`.
- MongoDB: usat per a logs i dades no relacionals (pot variar segons la configuració).

Important:

- MariaDB és l'origen de dades relacional en ús per l'aplicació; assegura't de revisar `database/mariadb-init.sql` si modifiques l'esquema.
- MongoDB està instal·lat a l'entorn Docker però, en l'estat actual del codi, encara **no** està integrat a l'aplicació. La integració està prevista per una fase futura per emmagatzemar dades de sensors (identificats com a `emes`).

Quan canvies d'una branca amb canvis en l'esquema SQL, fes `docker-compose down -v` per eliminar volums persistents abans d'arrencar si vols garantir l'esquema actualitzat.

---

## 🧪 Tests

El projecte inclou proves amb PHPUnit. Per executar-les localment (sense Docker):

```zsh
composer install
./vendor/bin/phpunit --configuration phpunit.xml
```

Si utilitzes Docker, pots executar phpunit dins del contenidor web o un contenidor de CI configurat.


## 📚 Documentació i seguiment

- `README_MVC.md` conté informació específica sobre l'arquitectura MVC usada.
- `docs/` inclou guies específiques (chatbot, execució de tests, rutes explicades).

---

##  Notes finals i següents passos suggerits

- Revisa `config/` per ajustar les variables d'entorn abans d'arrencar.

