# SPRINT1-KICKOFF – Gestió de Mobilitat Intel·ligent

Aquest projecte és una plataforma web per gestionar la mobilitat intel·ligent, pensada per flotes de vehicles, usuaris i administradors. Està desenvolupat amb PHP, HTML, CSS, JavaScript i Python, i utilitza MariaDB i MongoDB com a bases de dades. Tot el sistema s’executa en contenidors Docker per facilitar la instal·lació i el desplegament.

---

## 📦 Estructura del Projecte

```
final_editar_usar/
├── config/                # Configuració global, scripts d’inicialització, Docker
├── public_html/           # Frontend i backend web (DocumentRoot Apache)
├── python_gui/            # Eina administrativa en Python
├── database_schema.sql    # Esquema de la base de dades relacional
├── bones_practiques.md    # Bones pràctiques i normes de programació
├── .gitignore             # Exclusió d’arxius sensibles i temporals
├── readme.md              # Documentació principal (aquest fitxer)
├── readme_cat.md          # Documentació en català
```

### Detall de carpetes principals

- **config/**: Scripts SQL, configuració PHP, Dockerfile, docker-compose, inicialització de bases de dades.
- **public_html/**: 
  - **index.html / index.php**: Entrada principal.
  - **css/**: Estils, inclou accessibilitat i personalització.
  - **images/**: Imatges, icones i avatars.
  - **js/**: Scripts JavaScript modulars (autenticació, reserves, vehicles, accessibilitat, etc.).
  - **lang/**: Fitxers d’idioma (JSON) i tutorials per cada idioma.
  - **pages/**: Vistes HTML/PHP organitzades per funcionalitat (auth, dashboard, perfil, vehicle, accessibilitat).
  - **php/**: Backend PHP (API, components, controladors, models, admin, auth, etc.).
- **python_gui/**: Eina GUI per administradors, amb dependències a `requirements.txt`.

---

## 🚀 Instal·lació i Execució

### Requisits previs

- Docker i Docker Compose instal·lats.
- Opcional: Python 3 per la GUI administrativa.

### Passos bàsics

1. **Configura les variables i credencials** a `config/docker-compose.yml` i `config/database.php`.
2. **Inicia els serveis** amb Docker:
   ```sh
   cd config
   docker-compose up --build
   ```
3. **Accedeix a l’aplicació** via navegador a `http://localhost:8080` (o el port configurat).
4. **Administra la flota** amb la GUI Python:
   ```sh
   cd python_gui
   pip install -r requirements.txt
   python admin_tool.py
   ```

---

## 🖥️ Arquitectura i Flux de Treball

- **Frontend**: HTML, CSS (Tailwind, custom), JS modular. Vistes organitzades per mòdul.
- **Backend**: PHP organitzat per components, controladors, models i APIs.
- **Bases de dades**:
  - **MariaDB**: Usuaris, vehicles, reserves, pagaments.
  - **MongoDB**: Logs, historial, dades de sensors.
- **Docker**: Orquestració de serveis web, MariaDB i MongoDB. Scripts d’inicialització automàtica.

### Flux d’usuari

1. L’usuari accedeix a la web i es registra o inicia sessió.
2. Pot gestionar el perfil, reservar vehicles, consultar historial i pagaments.
3. Les accions frontend envien dades a APIs PHP via AJAX/fetch.
4. El backend valida, processa i retorna la resposta (JSON/HTML).
5. L’administrador pot gestionar usuaris, vehicles i reserves via web o GUI Python.

---

## 🌍 Internacionalització i Accessibilitat

- **Idiomes disponibles**: Català, Castellà, Anglès.
- **Traduccions**: Fitxers JSON a `public_html/lang/` i gestió dinàmica en PHP/JS.
- **Accessibilitat**: 
  - Estils dedicats (`accessibility.css`), widget UserWay, navegació per teclat, contrast, mida de text, reducció de moviment.
  - Etiquetes semàntiques i ARIA a les vistes.

---

## 🔒 Seguretat i Bones Pràctiques

- **Autenticació**: Gestió de sessions PHP, control d’accés a zones privades.
- **Validació**: Formularis i APIs validen dades tant al frontend com al backend.
- **Pagaments**: Pre-autorizació de targeta, tarifa de desbloqueig i preu per minut configurables.
- **Control de versions**: `.gitignore` actualitzat, sense credencials ni dades sensibles al repositori.
- **Rate limiting i logging**: Configurables a `config/configuration_template.php`.
- **Estructura MVC**: Separació clara entre models, vistes i controladors.
- **Escalabilitat**: Arquitectura preparada per milers d’usuaris i vehicles.

---

## 🛠️ Desenvolupament i Col·laboració

- **Forks i pull requests** recomanats per col·laborar.
- **Comentaris i documentació** als fitxers clau.
- **bones_practiques.md**: Consulta les normes de programació i estil.

---

## 📚 Recursos Addicionals

- **Resum tècnic**: `public_html/pages/dashboard/resum-projecte.html`
- **Tutorials**: `public_html/lang/ca/tutorial.json`, `en/tutorial.json`, `es/tutorial.json`
- **Panel d’administració**: `public_html/php/admin/`

---

## ⚡ Notes finals

- Utilitza rutes relatives per AJAX/fetch en desenvolupament.
- Revisa la configuració de credencials entre Docker i PHP.
- Realitza proves d’usuari i accessibilitat abans de desplegar.

---