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
        AuthController::requireAuth();
        Permissions::authorize('incidents.view_all');

        $incidents = $this->incidentModel->getAllIncidents();
        require_once VIEWS_PATH . '/admin/incidents/index.php';
    }


    public function createIncident() {
        AuthController::requireAuth();
        
        $isAdmin = Permissions::can('incidents.create');
        
        if ($isAdmin) {
            Permissions::authorize('incidents.create');
        } else {
            Permissions::authorize('incidents.create_public');
        }
        
        $viewPath = $isAdmin 
            ? VIEWS_PATH . '/admin/incidents/create.php'
            : VIEWS_PATH . '/public/incidents/create.php';

        if ($isAdmin) {
            $userModel = new User();
            $admins = $userModel->getUsersByRole(1);
            $workers = $userModel->getUsersByRole(2);
            $workers = array_merge($admins, $workers);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'type' => $_POST['type'] ?? null,
                'description' => $_POST['description'] ?? null,
                'incident_creator' => $_SESSION['user_id'],
            ];

            if ($isAdmin) {
                $data['notes'] = $_POST['notes'] ?? null;
                $data['incident_assignee'] = $_POST['incident_assignee'] ?? null;
                $data['status'] = $_POST['status'] ?? 'pending';
            } else {
                $data['notes'] = null;
                $data['incident_assignee'] = $_SESSION['user_id'];
                $data['status'] = 'pending';
            }

            if ($this->incidentModel->createIncident($data)) {
                $_SESSION['success'] = __('incident.created_success');
                
                $redirectUrl = $isAdmin ? '/admin/incidents' : '/dashboard';
                return Router::redirect($redirectUrl);
            } else {
                $_SESSION['error'] = __('incident.created_error');
            }
        }
        
        require_once $viewPath;
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

    public function getIncident($id) {
        AuthController::requireAuth();
        Permissions::authorize('incidents.edit');

        $incident = $this->incidentModel->getIncidentById($id);
        
        if (!$incident) {
            $_SESSION['error'] = 'Incidència no trobada.';
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
            $_SESSION['success'] = 'Incidència actualitzada correctament.';
        } else {
            $_SESSION['error'] = 'Error al actualitzar la incidència.';
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
            $_SESSION['success'] = 'Incidència marcada com resolta.';
        } else {
            $_SESSION['error'] = 'Error al resoldre la incidència.';
        }

        return Router::redirect('/admin/incidents');
    }
};