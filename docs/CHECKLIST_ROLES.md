# ✅ Checklist d'Implementació del Sistema de Rols

## 📋 Què s'ha fet

### 1. ✅ Sistema d'Autorització Creat
- **Fitxer**: `/core/Authorization.php`
- **Contingut**: Classe completa amb rols, permisos i jerarquies
- **Rols definits**: Guest, User, Premium, Manager, Admin, Superadmin
- **Permisos**: 40+ permisos específics per funcionalitat

### 2. ✅ AuthController Actualitzat
- **Login actualitzat**: Ara guarda `role_id` i `role_name` a la sessió
- **Nous mètodes afegits**:
  - `requireRole($role)` - Requereix un rol específic
  - `requirePermission($permission)` - Requereix un permís específic
  - `can($permission)` - Comprova permís sense aturar
  - `hasRole($role)` - Comprova rol sense aturar

### 3. ✅ Router Actualitzat
- **Auto-inject d'autorització**: Totes les vistes reben automàticament `$auth`
- **Variable `$auth` disponible a totes les vistes** amb:
  - `$auth['role']` - Nom del rol
  - `$auth['is_admin']`, `$auth['is_premium']`, etc.
  - `$auth['can']($permission)` - Funció per comprovar permisos

### 4. ✅ Vista Exemple Actualitzada
- **Fitxer**: `/views/dashboard/gestio.php`
- **Millores**:
  - Botó Admin només visible per admins
  - Botó Gestió Flota només per managers/admins
  - Botó Premium només per usuaris premium

### 5. ✅ Controller Exemple Creat
- **Fitxer**: `/controllers/FleetController.php`
- **Funcionalitat**: Gestió completa de flota
- **Protecció**: Tots els mètodes protegits per rol/permís

### 6. ✅ Script SQL Creat
- **Fitxer**: `/config/setup-roles.sql`
- **Contingut**:
  - Crea taula `roles`
  - Insereix els 6 rols
  - Afegeix `role_id` a `users`
  - Crea foreign key
  - Assigna rols a usuaris existents
  - Crea vista `users_with_roles`

### 7. ✅ Documentació Completa
- **Fitxer**: `/AUTORIZACION_ROLES.md`
- **Contingut**:
  - Descripció de tots els rols
  - Tots els permisos per rol
  - Exemples pràctics d'ús
  - Jerarquia explicada
  - Checklist d'implementació

---

## 🚀 Passos per Aplicar-ho al Projecte

### Pas 1: Executar Script SQL
```bash
# Connecta a la base de dades i executa:
mysql -u root -p voltiacar < /home/sabina/SIMS---GRUP-2/config/setup-roles.sql

# O des de Docker:
docker exec -i voltiacar-db mysql -u root -proot voltiacar < /home/sabina/SIMS---GRUP-2/config/setup-roles.sql
```

**Resultat**: Taula `roles` creada, usuaris amb `role_id` assignat.

### Pas 2: Verificar que funciona
1. Inicia sessió amb un usuari existent
2. Navega a `/gestio` (dashboard)
3. Si ets admin, hauries de veure el botó "Admin Panel"
4. Si no ets admin, no hauries de veure-ho

### Pas 3: Protegir Routes Existents
Edita `/routes/web.php` i afegeix protecció:

```php
// Exemple: Protegir admin
$router->get('/admin', [AdminController::class, 'index'], function() {
    AuthController::requireAdmin(); // Només admins
});

// Exemple: Protegir fleet (nou)
$router->get('/fleet', [FleetController::class, 'index'], function() {
    AuthController::requireRole('manager'); // Només managers+
});
```

### Pas 4: Actualitzar Vistes Existents
Revisa les vistes i afegeix condicionals:

**Exemple en `/views/dashboard/gestio.php`** (ja fet):
```php
<?php if ($auth['is_admin']): ?>
    <a href="/admin">Admin Panel</a>
<?php endif; ?>
```

**Exemple en `/views/profile/profile.php`**:
```php
<?php if ($auth['is_premium']): ?>
    <div class="badge-premium">
        ⭐ Compte Premium
    </div>
<?php endif; ?>
```

### Pas 5: Protegir Controllers Existents
Afegeix protecció als mètodes dels controllers:

**Exemple en VehicleController**:
```php
public function claimVehicle($id) {
    // Abans només comprovava auth:
    $userId = AuthController::requireAuth();
    
    // Ara pots afegir més control:
    if (AuthController::hasRole('premium')) {
        // Lògica especial per premium
    }
}
```

### Pas 6: Crear Usuaris de Prova
```sql
-- Admin
UPDATE users SET role_id = 5, is_admin = 1 WHERE username = 'admin';

-- Manager
UPDATE users SET role_id = 4 WHERE username = 'manager';

-- User estàndard (ja és el default)
UPDATE users SET role_id = 2 WHERE username = 'user';
```

---

## 🎯 Tasques Pendents

### Prioritat ALTA (Fer ara)
- [ ] Executar script SQL (`setup-roles.sql`)
- [ ] Provar login i veure si `$auth` està disponible
- [ ] Protegir rutes existents a `/routes/web.php`
- [ ] Afegir condicionals a vistes principals

### Prioritat MITJANA (Fer aviat)
- [ ] Crear panell d'administració (`/admin`)
- [ ] Implementar gestió d'usuaris (canviar rols)
- [ ] Implementar gestió de flota (`/fleet`) amb FleetController
- [ ] Afegir pàgina de premium (`/premium`)

### Prioritat BAIXA (Opcional)
- [ ] Sistema de subscripcions premium
- [ ] Log d'accions dels admins
- [ ] Notificacions per rol
- [ ] Dashboard de stats per managers

---

## 🧪 Com Provar-ho

### Test 1: Verificar Rols a la BD
```sql
SELECT u.id, u.username, r.name AS rol 
FROM users u 
LEFT JOIN roles r ON u.role_id = r.id;
```

Hauries de veure tots els usuaris amb el seu rol assignat.

### Test 2: Verificar Sessió
1. Inicia sessió
2. Afegeix a qualsevol vista: `<?php var_dump($_SESSION); ?>`
3. Hauries de veure: `user_id`, `username`, `is_admin`, `role_id`, `role_name`

### Test 3: Verificar `$auth` a Vistes
1. Afegeix a qualsevol vista: `<?php var_dump($auth); ?>`
2. Hauries de veure tota la info del rol

### Test 4: Verificar Protecció de Rutes
1. Crea una ruta protegida:
```php
$router->get('/test-admin', function() {
    AuthController::requireAdmin();
    echo "Ets admin!";
});
```
2. Prova-ho amb un usuari normal (hauria de donar error 403)
3. Prova-ho amb un admin (hauria de funcionar)

### Test 5: Verificar Visualització Condicional
1. Inicia sessió amb un user estàndard
2. Navega a `/gestio`
3. NO hauries de veure el botó "Admin Panel"
4. Canvia el teu usuari a admin: `UPDATE users SET role_id = 5, is_admin = 1 WHERE id = X;`
5. Recarrega la pàgina
6. Ara SÍ hauries de veure el botó "Admin Panel"

---

## 📊 Estructura Final

```
/home/sabina/SIMS---GRUP-2/
├── core/
│   ├── Authorization.php          ← ✅ NOU - Sistema de permisos
│   ├── Router.php                 ← ✅ MODIFICAT - Auto-inject $auth
│   └── ...
├── controllers/
│   ├── AuthController.php         ← ✅ MODIFICAT - Nous mètodes de rol
│   ├── FleetController.php        ← ✅ NOU - Exemple de controller protegit
│   └── ...
├── views/
│   └── dashboard/
│       └── gestio.php             ← ✅ MODIFICAT - Botons condicionals
├── config/
│   └── setup-roles.sql            ← ✅ NOU - Script de configuració BD
├── AUTORIZACION_ROLES.md          ← ✅ NOU - Documentació completa
└── CHECKLIST_ROLES.md             ← ✅ Aquest fitxer
```

---

## 🆘 Troubleshooting

### Problema: `$auth` no està disponible a la vista
**Solució**: Assegura't que `Router::view()` s'està utilitzant. Si fas `require` directament, no funcionarà.

### Problema: `Authorization class not found`
**Solució**: Verifica que `/core/Authorization.php` existeix i que `Router.php` fa el `require_once`.

### Problema: Sempre em diu "Permission denied"
**Solució**: 
1. Verifica que tens `role_id` a la sessió: `var_dump($_SESSION['role_id']);`
2. Comprova que el rol està correcte a la BD
3. Verifica que el permís està definit a `Authorization.php`

### Problema: Els usuaris existents no tenen rol
**Solució**: Executa:
```sql
UPDATE users SET role_id = 2 WHERE role_id IS NULL;  -- Assigna user per defecte
UPDATE users SET role_id = 5 WHERE is_admin = 1;     -- Admins existents
```

---

## 🎉 Resultat Final

Després d'aplicar tot això, tindràs:

✅ **Sistema complet de rols** amb 6 nivells jerarquitzats
✅ **40+ permisos** específics per funcionalitat
✅ **Protecció automàtica** de routes i controllers
✅ **Visualització adaptativa** segons rol a totes les vistes
✅ **Middleware flexible** per comprovar permisos
✅ **Documentació completa** amb exemples pràctics
✅ **Exemple real** (FleetController) de com implementar-ho

**Ara tens control total sobre qui pot fer què a l'aplicació! 🚀**

---

## 📞 Dubtes?

Revisa:
1. `/AUTORIZACION_ROLES.md` - Documentació completa
2. `/core/Authorization.php` - Codi font amb comentaris
3. `/controllers/FleetController.php` - Exemple pràctic
4. `/views/dashboard/gestio.php` - Exemple de vista

**Tot està preparat per utilitzar-se! Només cal executar el SQL i començar a protegir routes/vistes! 🎯**
