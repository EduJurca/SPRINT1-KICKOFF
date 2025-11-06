<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sistema de Rols</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.5em;
        }
        .section {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            border-left: 5px solid #667eea;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .info-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .info-card strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            margin: 3px;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-primary { background: #667eea; color: white; }
        .permissions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .status { font-size: 1.5em; }
        .success-icon { color: #28a745; }
        .error-icon { color: #dc3545; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        tr:hover { background: #f5f5f5; }
        .test-result {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .test-pass {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .test-fail {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .back-btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Test Sistema d'Autorització</h1>

        <?php
        // Comprovar si està autenticat
        if (!isset($_SESSION['user_id'])) {
            echo '<div class="test-fail test-result">';
            echo '<span class="status error-icon">❌</span>';
            echo '<div><strong>No estàs autenticat</strong><br>Inicia sessió per veure la informació d\'autorització</div>';
            echo '</div>';
            echo '<a href="/login" class="back-btn">Iniciar Sessió</a>';
            exit;
        }
        ?>

        <!-- Informació de la Sessió -->
        <div class="section">
            <h2>📋 Informació de la Sessió</h2>
            <div class="info-grid">
                <div class="info-card">
                    <strong>User ID</strong>
                    <?= $_SESSION['user_id'] ?? 'N/A' ?>
                </div>
                <div class="info-card">
                    <strong>Username</strong>
                    <?= $_SESSION['username'] ?? 'N/A' ?>
                </div>
                <div class="info-card">
                    <strong>Is Admin</strong>
                    <?= isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? '✅ Sí' : '❌ No' ?>
                </div>
                <div class="info-card">
                    <strong>Role ID</strong>
                    <?= $_SESSION['role_id'] ?? '⚠️ No definit' ?>
                </div>
                <div class="info-card">
                    <strong>Role Name</strong>
                    <?= $_SESSION['role_name'] ?? '⚠️ No definit' ?>
                </div>
            </div>
        </div>

        <!-- Informació del Rol -->
        <div class="section">
            <h2>🎭 Informació del Rol</h2>
            <div class="info-grid">
                <div class="info-card">
                    <strong>Rol Actual</strong>
                    <span class="badge badge-primary"><?= strtoupper($auth['role']) ?></span>
                </div>
                <div class="info-card">
                    <strong>Nom Display</strong>
                    <?= $auth['role_display'] ?>
                </div>
            </div>

            <div class="info-grid" style="margin-top: 20px;">
                <div class="info-card">
                    <strong>És Guest?</strong>
                    <?= $auth['is_guest'] ? '✅' : '❌' ?>
                </div>
                <div class="info-card">
                    <strong>És User?</strong>
                    <?= $auth['is_user'] ? '✅' : '❌' ?>
                </div>
                <div class="info-card">
                    <strong>És Premium?</strong>
                    <?= $auth['is_premium'] ? '✅' : '❌' ?>
                </div>
                <div class="info-card">
                    <strong>És Manager?</strong>
                    <?= $auth['is_manager'] ? '✅' : '❌' ?>
                </div>
                <div class="info-card">
                    <strong>És Admin?</strong>
                    <?= $auth['is_admin'] ? '✅' : '❌' ?>
                </div>
                <div class="info-card">
                    <strong>És Superadmin?</strong>
                    <?= $auth['is_superadmin'] ? '✅' : '❌' ?>
                </div>
            </div>
        </div>

        <!-- Permisos del Rol -->
        <div class="section">
            <h2>🔑 Permisos Actius</h2>
            <p><strong>Total:</strong> <?= count($auth['permissions']) ?> permisos</p>
            <div class="permissions-list">
                <?php foreach ($auth['permissions'] as $permission): ?>
                    <span class="badge badge-success"><?= htmlspecialchars($permission) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tests de Permisos Específics -->
        <div class="section">
            <h2>🧪 Tests de Permisos</h2>
            
            <?php
            $tests = [
                'view_dashboard' => 'Veure Dashboard',
                'view_vehicles' => 'Veure Vehicles',
                'claim_vehicle' => 'Reclamar Vehicle',
                'unlimited_minutes' => 'Minuts Il·limitats (Premium)',
                'add_vehicle' => 'Afegir Vehicle (Manager)',
                'edit_vehicle' => 'Editar Vehicle (Manager)',
                'view_admin_panel' => 'Veure Panel Admin (Admin)',
                'manage_admins' => 'Gestionar Admins (Superadmin)',
            ];
            
            foreach ($tests as $permission => $description):
                $hasPermission = $auth['can']($permission);
            ?>
                <div class="test-result <?= $hasPermission ? 'test-pass' : 'test-fail' ?>">
                    <span class="status"><?= $hasPermission ? '✅' : '❌' ?></span>
                    <div>
                        <strong><?= $description ?></strong><br>
                        <small>Permís: <code><?= $permission ?></code></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tests de Rols -->
        <div class="section">
            <h2>🎯 Tests de Comprovació de Rols</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>hasRole()</th>
                        <th>És Superior?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Authorization is loaded centrally at bootstrap (index.php)
                    $testRoles = ['guest', 'user', 'premium', 'manager', 'admin', 'superadmin'];
                    
                    foreach ($testRoles as $testRole):
                        $hasRole = Authorization::hasRole($testRole);
                        $isHigher = Authorization::isHigherRole($testRole);
                    ?>
                        <tr>
                            <td><span class="badge badge-info"><?= strtoupper($testRole) ?></span></td>
                            <td><?= $hasRole ? '<span class="badge badge-success">✅ Sí</span>' : '<span class="badge badge-danger">❌ No</span>' ?></td>
                            <td><?= $isHigher ? '<span class="badge badge-success">✅ Superior</span>' : '<span class="badge badge-danger">❌ No</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Raw Data -->
        <div class="section">
            <h2>📊 Dades Completes ($auth)</h2>
            <pre style="background: #282c34; color: #abb2bf; padding: 20px; border-radius: 8px; overflow-x: auto; font-size: 0.9em;"><?php print_r($auth); ?></pre>
        </div>

        <a href="/gestio" class="back-btn">← Tornar al Dashboard</a>
    </div>
</body>
</html>
