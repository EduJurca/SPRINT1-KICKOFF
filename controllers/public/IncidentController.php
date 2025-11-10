<?php

require_once MODELS_PATH . '/Incident.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

class IncidentController {
    private $incidentModel;

    public function __construct() {
        $db = Database::getMariaDBConnection();
        $this->incidentModel = new Incident($db);
    }

    public function createIncident() {
        AuthController::requireAuth();
        Permissions::authorize('incidents.create_public');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'type' => $_POST['type'] ?? null,
                'description' => $_POST['description'] ?? null,
                'incident_creator' => $_SESSION['user_id'],
                'notes' => null,
                'status' => 'pending',
            ];

            if ($this->incidentModel->createIncident($data)) {
                $_SESSION['success'] = __('incident.created_success');
                return Router::redirect('/dashboard');
            } else {
                $_SESSION['error'] = __('incident.created_error');
            }
        }

        require_once VIEWS_PATH . '/public/incidents/create.php';
    }
}