<?php

require_once MODELS_PATH . '/Incident.php';
require_once MODELS_PATH . '/User.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

class AdminIncidentController {
    private $incidentModel;

    public function __construct() {
        $db = Database::getMariaDBConnection();
        $this->incidentModel = new Incident($db);
    }

    public function getAllIncidents() {
        AuthController::requireAuth();
        Permissions::authorize('incidents.view_all');

        $incidents = $this->incidentModel->getAllIncidents();
        require_once VIEWS_PATH . '/admin/incidents/index.php';
    }

    public function createIncident() {
        AuthController::requireAuth();
        Permissions::authorize('incidents.create');

        $userModel = new User();
        $admins = $userModel->getUsersByRole(1);
        $workers = $userModel->getUsersByRole(2);
        $workers = array_merge($admins, $workers);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'type' => $_POST['type'] ?? null,
                'description' => $_POST['description'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'status' => $_POST['status'] ?? 'pending',
                'incident_creator' => $_SESSION['user_id'],
                'incident_assignee' => $_POST['incident_assignee'] ?? null,
            ];

            if ($this->incidentModel->createIncident($data)) {
                $_SESSION['success'] = __('incident.created_success');
                return Router::redirect('/admin/incidents');
            } else {
                $_SESSION['error'] = __('incident.created_error');
            }
        }

        require_once VIEWS_PATH . '/admin/incidents/create.php';
    }

    public function getIncident($id) {
        AuthController::requireAuth();
        Permissions::authorize('incidents.edit');

        $incident = $this->incidentModel->getIncidentById($id);
        
        if (!$incident) {
            $_SESSION['error'] = __('incident.not_found');
            return Router::redirect('/admin/incidents');
        }

        $userModel = new User();
        $admins = $userModel->getUsersByRole(1);
        $workers = $userModel->getUsersByRole(2);
        $workers = array_merge($admins, $workers);

        require_once VIEWS_PATH . '/admin/incidents/edit.php';
    }

    public function updateIncident($id) {
        AuthController::requireAuth();
        Permissions::authorize('incidents.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Router::redirect('/admin/incidents');
        }

        $data = [
            'type' => $_POST['type'] ?? null,
            'status' => $_POST['status'] ?? null,
            'description' => $_POST['description'] ?? null,
            'notes' => $_POST['notes'] ?? null,
            'incident_assignee' => $_POST['incident_assignee'] ?? null,
        ];

        if ($data['status'] === 'resolved') {
            $data['resolved_by'] = $_SESSION['user_id'];
        }

        if ($this->incidentModel->updateIncident($id, $data)) {
            $_SESSION['success'] = __('incident.updated_success');
        } else {
            $_SESSION['error'] = __('incident.updated_error');
        }

        return Router::redirect('/admin/incidents');
    }

    public function resolveIncident($id) {
        AuthController::requireAuth();
        Permissions::authorize('incidents.resolve');

        $data = [
            'status' => 'resolved',
            'resolved_by' => $_SESSION['user_id']
        ];

        if ($this->incidentModel->updateIncident($id, $data)) {
            $_SESSION['success'] = __('incident.resolved_success');
        } else {
            $_SESSION['error'] = __('incident.resolve_error');
        }

        return Router::redirect('/admin/incidents');
    }

    public function deleteIncident($id) {
        AuthController::requireAuth();
        Permissions::authorize('incidents.delete');
        
        if ($this->incidentModel->deleteIncident($id)) {
            $_SESSION['success'] = __('incident.deleted_success');
        } else {
            $_SESSION['error'] = __('incident.deleted_error');
        }

        return Router::redirect('/admin/incidents');
    }
}
