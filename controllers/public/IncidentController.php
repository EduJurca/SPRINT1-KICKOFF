<?php

require_once MODELS_PATH . '/Incident.php';
require_once MODELS_PATH . '/User.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

class IncidentController {
    private $incidentModel;

    public function __construct() {
        $db = Database::getMariaDBConnection();
        $this->incidentModel = new Incident($db);

    }

    public function getAllIncidents() {
        // Verificar que el usuario esté autenticado y sea admin
        $userId = AuthController::requireAuth();

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (empty($user) || !$user['is_admin']) {
            header('Location: /');
            exit;
        }

        $incidents = $this->incidentModel->getAllIncidents();
        require_once __DIR__ . '/../views/admin/incidents/index.php';
    }
    /**
     * Create incident (admin-only form) - admins can create incidents from admin area
     */
    public function createIncident() {
        // Verificar que el usuario esté autenticado
        $userId = AuthController::requireAuth();

        // Cargar usuario para comprobar rol
        $userModel = new User();
        $user = $userModel->findById($userId);

        // Si no es admin, redirigir al formulario público
        if (empty($user) || !$user['is_admin']) {
            header('Location: /report-incident');
            exit;
        }

        // Admin flow: mostrar formulario con lista de usuarios para asignar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'type' => $_POST['type'] ?? null,
                'description' => $_POST['description'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'incident_creator' => $userId,
                'incident_assignee' => $_POST['incident_assignee'] ?? null
            ];

            if ($this->incidentModel->createIncident($data)) {
                header('Location: /admin/incidents?success=created');
                exit;
            } else {
                $error = "Error al crear la incidencia";
            }
        }

        require_once __DIR__ . '/../views/admin/incidents/create.php';
    }

    /**
     * Public endpoint for clients to report incidents (assigns creator from session)
     */
    public function createPublicIncident() {
        $userId = AuthController::requireAuth();

        // Prevent admins from using public reporter (admins use admin area)
        $userModel = new User();
        $user = $userModel->findById($userId);
        if (empty($user)) {
            header('Location: /login');
            exit;
        }
        if (!empty($user['is_admin']) && $user['is_admin']) {
            header('Location: /admin/incidents');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'type' => $_POST['type'] ?? null,
                'description' => $_POST['description'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'incident_creator' => $userId,
                'incident_assignee' => null
            ];

            if ($this->incidentModel->createIncident($data)) {
                header('Location: /perfil?success=incident_created');
                exit;
            } else {
                $error = "Error al crear la incidencia";
            }
        }

        require_once __DIR__ . '/../views/public/incidents/create.php';
    }
};