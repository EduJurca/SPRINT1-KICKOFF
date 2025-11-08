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
        $userId = AuthController::requireAuth();

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (empty($user) || !$user['is_admin']) {
                return Router::redirect('/');
        }

    $incidents = $this->incidentModel->getAllIncidents();
    require_once VIEWS_PATH . '/admin/incidents/index.php';
    }


    public function createIncident() {
        $userId = AuthController::requireAuth();

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (empty($user) || !$user['is_admin']) {
            header('Location: /report-incident');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'type' => $_POST['type'] ?? null,
                'description' => $_POST['description'] ?? null,
                'notes' => $_POST['notes'] ?? null, 
                'incident_creator' => $userId,
                'incident_assignee' => $_POST['incident_assignee'] ?? null
            ];

            if ($this->incidentModel->createIncident($data)) {
                return Router::redirect('/admin/incidents');                
            } else {
                $error = "Error al crear la incidencia";
            }
        }

    require_once VIEWS_PATH . '/admin/incidents/create.php';
    }

    /**
     * Public endpoint for clients to report incidents (assigns creator from session)
     */
    public function createPublicIncident() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = AuthController::requireAuth();

            $userModel = new User();
            $user = $userModel->findById($userId);
            if (!empty($user['is_admin']) && $user['is_admin']) {
                return Router::redirect('/admin/incidents');
            }

            $data = [
                'type' => $_POST['type'] ?? null,
                'description' => $_POST['description'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'incident_creator' => $userId,
                'incident_assignee' => null
            ];

            if ($this->incidentModel->createIncident($data)) {
                $_SESSION['success'] = __('incident.created_success');
                return Router::redirect('/dashboard');
            } else {
                $_SESSION['error'] = __('incident.created_error');
                require_once VIEWS_PATH . '/public/incidents/create.php';
                return;
            }
        } else {
            require_once VIEWS_PATH . '/public/incidents/create.php';
        }
    }
};