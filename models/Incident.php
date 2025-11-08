<?php 

class Incident {
    private $db;
    
    public function __construct($dbConnection = null){
        $this->db = $dbConnection ?? Database::getMariaDBConnection();
    }

    public function getAllIncidents() {
        $stmt = $this->db->prepare("
            SELECT 
                i.id,
                i.type,
                i.status,
                i.description,
                i.notes,
                i.incident_creator,
                i.incident_assignee,
                i.resolved_by,
                i.created_at,
                i.updated_at,
                i.resolved_at,
                creator.username as creator_name,
                assignee.username as assignee_name,
                resolver.username as resolver_name
            FROM incidents i
            LEFT JOIN users creator ON i.incident_creator = creator.id
            LEFT JOIN users assignee ON i.incident_assignee = assignee.id
            LEFT JOIN users resolver ON i.resolved_by = resolver.id
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getIncidentById($incidentId) {
        $stmt = $this->db->prepare("
            SELECT
                i.id,
                i.type,
                i.status,
                i.description,
                i.notes,
                i.incident_creator,
                i.incident_assignee,
                i.resolved_by,
                i.created_at,
                i.updated_at,
                i.resolved_at,
                creator.username as creator_name,
                assignee.username as assignee_name,
                resolver.username as resolver_name
            FROM incidents i
            LEFT JOIN users creator ON i.incident_creator = creator.id
            LEFT JOIN users assignee ON i.incident_assignee = assignee.id
            LEFT JOIN users resolver ON i.resolved_by = resolver.id
            WHERE i.id = ?
        ");
        $stmt->bind_param('i', $incidentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $incident = $result->fetch_assoc();
        
        return $incident;
    }

    public function createIncident($data) {
        if (!isset($data['type']) || !isset($data['description']) || !isset($data['incident_creator'])) {
            return false;
        }

        $userModel = new User();
        if (!$userModel->exists($data['incident_creator'])) {
            return false;
        }

        if (isset($data['incident_assignee']) && $data['incident_assignee'] !== null) {
            if (!$userModel->exists($data['incident_assignee'])) {
                return false;
            }
        }

        $stmt = $this->db->prepare("INSERT INTO incidents
            (type, status, description, notes, incident_creator, incident_assignee, created_at)
            VALUES (?, 'pending', ?, ?, ?, ?, NOW())"
        );

        $type = $data['type'] ?? null;
        $description = $data['description'] ?? null;
        $notes = $data['notes'] ?? null;
        $incident_creator = $data['incident_creator'] ?? null;
        $incident_assignee = $data['incident_assignee'] ?? null;

        $stmt->bind_param('sssii',
            $type,
            $description,
            $notes,
            $incident_creator,
            $incident_assignee
        );

        return $stmt->execute();
    }

    public function updateIncident($incidentId, $data) {
        $existing = $this->getIncidentById($incidentId);
        if (!$existing) {
            return false;
        }

        if (isset($data['description'])) {
            $data['description'] = htmlspecialchars(strip_tags($data['description']));
        }
        if (isset($data['notes'])) {
            $data['notes'] = htmlspecialchars(strip_tags($data['notes']));
        }

        $userFields = ['incident_assignee', 'resolved_by'];
        foreach ($userFields as $field) {
            if (isset($data[$field]) && $data[$field] !== null) {
                $userModel = new User();
                if (!$userModel->exists($data[$field])) {
                    return false;
                }
            }
        }

        $setParts = [];
        $params = [':id' => $incidentId];

        if (isset($data['type'])) {
            $setParts[] = "type = :type";
            $params[':type'] = $data['type'];
        }

        if (isset($data['status'])) {
            $setParts[] = "status = :status";
            $params[':status'] = $data['status'];

            if ($data['status'] === 'resolved') {
                $setParts[] = "resolved_at = NOW()";
                if (isset($data['resolved_by'])) {
                    $setParts[] = "resolved_by = :resolved_by";
                    $params[':resolved_by'] = $data['resolved_by'];
                }
            } elseif (in_array($data['status'], ['pending', 'in_progress'])) {
                $setParts[] = "resolved_by = NULL";
                $setParts[] = "resolved_at = NULL";
            }
        }

        if (isset($data['description'])) {
            $setParts[] = "description = :description";
            $params[':description'] = $data['description'];
        }

        if (isset($data['notes'])) {
            $setParts[] = "notes = :notes";
            $params[':notes'] = $data['notes'];
        }

        if (isset($data['incident_assignee'])) {
            $setParts[] = "incident_assignee = :incident_assignee";
            $params[':incident_assignee'] = $data['incident_assignee'];
        }

        if (isset($data['resolved_by']) && !isset($data['status'])) {
            $setParts[] = "resolved_by = :resolved_by_independent";
            $params[':resolved_by_independent'] = $data['resolved_by'];
        }

        $setParts[] = "updated_at = NOW()";

        if (empty($setParts)) {
            return false; 
        }

        $sql = "UPDATE incidents SET " . implode(', ', $setParts) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $param => $value) {
            $stmt->bindParam($param, $params[$param]);
        }

        return $stmt->execute();
    }

    // public function deleteIncident($id)

    // $stmt = $this->db->prepare(DELETE FROM incidents where id = :id)
}