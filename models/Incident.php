<?php 

class Incident {
    private const ALLOWED_TYPES = ['mechanical', 'electrical', 'other'];
    private const ALLOWED_STATUSES = ['pending', 'in_progress', 'resolved'];

    private $db;
    private $userModel;
    
    public function __construct($dbConnection = null, $userModel = null){
        $this->db = $dbConnection ?? Database::getMariaDBConnection();
        $this->userModel = $userModel ?? new User();
    }

    public function getAllIncidents($limit = 10, $offset = 0, $search = '', $filters = []) {
        $query = "SELECT 
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
            WHERE 1=1";

        $params = [];
        $types = '';

        // Global search by description
        if (!empty($search)) {
            $searchWords = array_filter(explode(' ', $search));
            $searchConditions = [];
            foreach ($searchWords as $word) {
                $searchConditions[] = "i.description LIKE ?";
                $params[] = '%' . $word . '%';
                $types .= 's';
            }
            if (!empty($searchConditions)) {
                $query .= " AND (" . implode(' OR ', $searchConditions) . ")";
            }
        }

        // Advanced filters
        if (isset($filters['type']) && $filters['type'] !== '') {
            $query .= " AND i.type = ?";
            $params[] = $filters['type'];
            $types .= 's';
        }

        if (isset($filters['assignee']) && $filters['assignee'] !== '') {
            $query .= " AND i.incident_assignee = ?";
            $params[] = $filters['assignee'];
            $types .= 'i';
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query .= " AND i.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (isset($filters['created_date']) && $filters['created_date'] !== '') {
            // Filter by exact creation date (YYYY-MM-DD)
            $query .= " AND DATE(i.created_at) = ?";
            $params[] = $filters['created_date'];
            $types .= 's';
        }

        $query .= " ORDER BY i.created_at DESC";
        $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        if (!empty($params)) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $result = $this->db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    public function countIncidents($search = '', $filters = []): int {
        $query = "SELECT COUNT(*) as total
                FROM incidents i
                WHERE 1=1";

        $params = [];
        $types = '';

        // Global search by description
        if (!empty($search)) {
            $searchWords = array_filter(explode(' ', $search));
            $searchConditions = [];
            foreach ($searchWords as $word) {
                $searchConditions[] = "i.description LIKE ?";
                $params[] = '%' . $word . '%';
                $types .= 's';
            }
            if (!empty($searchConditions)) {
                $query .= " AND (" . implode(' OR ', $searchConditions) . ")";
            }
        }

        // Advanced filters
        if (isset($filters['type']) && $filters['type'] !== '') {
            $query .= " AND i.type = ?";
            $params[] = $filters['type'];
            $types .= 's';
        }

        if (isset($filters['assignee']) && $filters['assignee'] !== '') {
            $query .= " AND i.incident_assignee = ?";
            $params[] = $filters['assignee'];
            $types .= 'i';
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query .= " AND i.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (isset($filters['created_date']) && $filters['created_date'] !== '') {
            // Filter by exact creation date (YYYY-MM-DD)
            $query .= " AND DATE(i.created_at) = ?";
            $params[] = $filters['created_date'];
            $types .= 's';
        }

        if (!empty($params)) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($query);
        }
        
        $row = $result->fetch_assoc();
        return $row['total'];
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

        if (!in_array($data['type'], self::ALLOWED_TYPES, true)) {
            return false;
        }

        if (!$this->userModel->findById($data['incident_creator'])) {
            return false;
        }

        $incident_assignee = null;
        if (isset($data['incident_assignee']) && $data['incident_assignee'] !== '' && $data['incident_assignee'] !== null) {
            $incident_assignee = (int)$data['incident_assignee'];
            if (!$this->userModel->findById($incident_assignee)) {
                return false;
            }
        }

        $type = $data['type'] ?? null;
        $description = isset($data['description']) ? htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8') : null;
        $notes = isset($data['notes']) ? htmlspecialchars($data['notes'], ENT_QUOTES, 'UTF-8') : null;
        $incident_creator = (int)($data['incident_creator'] ?? 0);

        $stmt = $this->db->prepare("INSERT INTO incidents
            (type, status, description, notes, incident_creator, incident_assignee, created_at)
            VALUES (?, 'pending', ?, ?, ?, ?, NOW())");

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
            if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                if (!$this->userModel->findById($data[$field])) {
                    return false;
                }
            }
        }

        $setParts = [];
        $types = '';
        $values = [];

        if (isset($data['type'])) {
            if (!in_array($data['type'], self::ALLOWED_TYPES, true)) {
                return false;
            }
            $setParts[] = "type = ?";
            $types .= 's';
            $values[] = $data['type'];
        }

        if (isset($data['status'])) {
            if (!in_array($data['status'], self::ALLOWED_STATUSES, true)) {
                return false;
            }
            $setParts[] = "status = ?";
            $types .= 's';
            $values[] = $data['status'];

            if ($data['status'] === 'resolved') {
                $setParts[] = "resolved_at = NOW()";
                if (isset($data['resolved_by']) && $data['resolved_by']) {
                    $setParts[] = "resolved_by = ?";
                    $types .= 'i';
                    $values[] = $data['resolved_by'];
                }
            } elseif (in_array($data['status'], ['pending', 'in_progress'])) {
                $setParts[] = "resolved_by = NULL";
                $setParts[] = "resolved_at = NULL";
            }
        }

        if (isset($data['description'])) {
            $setParts[] = "description = ?";
            $types .= 's';
            $values[] = $data['description'];
        }

        if (isset($data['notes'])) {
            $setParts[] = "notes = ?";
            $types .= 's';
            $values[] = $data['notes'];
        }

        if (isset($data['incident_assignee'])) {
            if ($data['incident_assignee'] === '' || $data['incident_assignee'] === null) {
                $setParts[] = "incident_assignee = NULL";
            } else {
                $setParts[] = "incident_assignee = ?";
                $types .= 'i';
                $values[] = $data['incident_assignee'];
            }
        }

        $setParts[] = "updated_at = NOW()";

        if (empty($setParts)) {
            return false;
        }

        $types .= 'i';
        $values[] = $incidentId;

        $sql = "UPDATE incidents SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        if (!empty($types)) {
            $stmt->bind_param($types, ...$values);
        }

        return $stmt->execute();
    }

    public function deleteIncident($id) {
        $stmt = $this->db->prepare("DELETE FROM incidents WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getActiveIncidents() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM incidents WHERE status != 'resolved'");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

}