<?php
/**
 * User Controller
 * Handles user management endpoints
 * Implements 2025 RBAC best practices
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Response.php';

class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Get all users (admin only)
     * GET /api/users
     */
    public function index() {
        try {
            // Require admin role
            $currentUser = requireAdmin();
            
            // Get pagination parameters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            
            // Validate pagination
            $page = max(1, $page);
            $perPage = min(100, max(1, $perPage));
            
            // Get users
            if ($search) {
                $users = $this->userModel->search($search, $page, $perPage);
            } else {
                $users = $this->userModel->getAll($page, $perPage);
            }
            
            $total = $this->userModel->count();
            
            Response::paginated($users, $total, $page, $perPage, 'Users retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Get users error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving users');
        }
    }
    
    /**
     * Get user by ID
     * GET /api/users/{id}
     */
    public function show($id) {
        try {
            // Get authenticated user
            $currentUser = authenticate();
            
            // Check if user can access this resource
            if (!canAccessUserResource($currentUser, $id)) {
                Response::forbidden('You do not have permission to access this user');
                return;
            }
            
            // Get user
            $user = $this->userModel->findById($id);
            
            if (!$user) {
                Response::notFound('User not found');
                return;
            }
            
            Response::success($user, 'User retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving user');
        }
    }
    
    /**
     * Update user
     * PUT /api/users/{id}
     */
    public function update($id) {
        try {
            // Get authenticated user
            $currentUser = authenticate();
            
            // Check if user can access this resource
            if (!canAccessUserResource($currentUser, $id)) {
                Response::forbidden('You do not have permission to update this user');
                return;
            }
            
            // Get request body
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                Response::badRequest('Invalid JSON data');
                return;
            }
            
            // Check if user exists
            $user = $this->userModel->findById($id);
            if (!$user) {
                Response::notFound('User not found');
                return;
            }
            
            // Build validation rules
            $rules = [];
            
            if (isset($data['email'])) {
                $rules['email'] = ['required' => true, 'type' => 'email'];
            }
            
            if (isset($data['full_name'])) {
                $rules['full_name'] = ['required' => true, 'type' => 'string', 'min_length' => 2, 'max_length' => 100];
            }
            
            if (isset($data['phone'])) {
                $rules['phone'] = ['required' => false, 'type' => 'phone'];
            }
            
            if (isset($data['license_number'])) {
                $rules['license_number'] = ['required' => false, 'type' => 'string', 'max_length' => 50];
            }
            
            if (isset($data['password'])) {
                $rules['password'] = ['required' => true, 'type' => 'string', 'min_length' => 8];
            }
            
            // Only admin can change roles
            if (isset($data['role'])) {
                if (!isAdmin($currentUser)) {
                    Response::forbidden('Only administrators can change user roles');
                    return;
                }
                
                $rules['role'] = ['required' => true, 'type' => 'enum', 'values' => ['user', 'technician', 'admin']];
            }
            
            // Validate input
            if (!empty($rules)) {
                $validation = Validator::validate($data, $rules);
                
                if (!$validation['valid']) {
                    Response::validationError($validation['errors']);
                    return;
                }
                
                // Validate password strength if provided
                if (isset($data['password'])) {
                    $passwordValidation = Validator::validatePassword($data['password']);
                    if (!$passwordValidation['valid']) {
                        Response::validationError(['password' => $passwordValidation['errors']]);
                        return;
                    }
                }
                
                // Check if email is already taken by another user
                if (isset($validation['data']['email']) && $validation['data']['email'] !== $user['email']) {
                    if ($this->userModel->emailExists($validation['data']['email'])) {
                        Response::conflict('Email already in use');
                        return;
                    }
                }
                
                // Update user
                $updateData = $validation['data'];
                if (isset($data['password'])) {
                    $updateData['password'] = $data['password'];
                }
                
                $success = $this->userModel->update($id, $updateData);
                
                if (!$success) {
                    Response::serverError('Failed to update user');
                    return;
                }
            }
            
            // Get updated user
            $updatedUser = $this->userModel->findById($id);
            
            Response::success($updatedUser, 'User updated successfully');
            
        } catch (Exception $e) {
            error_log("Update user error: " . $e->getMessage());
            
            if ($e->getCode() == 409) {
                Response::conflict($e->getMessage());
            } else {
                Response::serverError('An error occurred while updating user');
            }
        }
    }
    
    /**
     * Delete user (admin only)
     * DELETE /api/users/{id}
     */
    public function delete($id) {
        try {
            // Require admin role
            $currentUser = requireAdmin();
            
            // Check if user exists
            $user = $this->userModel->findById($id);
            if (!$user) {
                Response::notFound('User not found');
                return;
            }
            
            // Prevent deleting yourself
            if ($currentUser['id'] == $id) {
                Response::badRequest('You cannot delete your own account');
                return;
            }
            
            // Delete user
            $success = $this->userModel->delete($id);
            
            if (!$success) {
                Response::serverError('Failed to delete user');
                return;
            }
            
            // Log deletion
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'warning',
                'message' => "User deleted: {$user['email']}",
                'user_id' => $currentUser['id'],
                'action' => 'delete_user',
                'ip_address' => getClientIP()
            ]);
            
            Response::success(null, 'User deleted successfully');
            
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            Response::serverError('An error occurred while deleting user');
        }
    }
    
    /**
     * Create user (admin only)
     * POST /api/users
     */
    public function create() {
        try {
            // Require admin role
            $currentUser = requireAdmin();
            
            // Get request body
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                Response::badRequest('Invalid JSON data');
                return;
            }
            
            // Validate input
            $validation = Validator::validate($data, [
                'email' => ['required' => true, 'type' => 'email'],
                'password' => ['required' => true, 'type' => 'string', 'min_length' => 8],
                'full_name' => ['required' => true, 'type' => 'string', 'min_length' => 2, 'max_length' => 100],
                'phone' => ['required' => false, 'type' => 'phone'],
                'license_number' => ['required' => false, 'type' => 'string', 'max_length' => 50],
                'role' => ['required' => false, 'type' => 'enum', 'values' => ['user', 'technician', 'admin']]
            ]);
            
            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }
            
            // Validate password strength
            $passwordValidation = Validator::validatePassword($data['password']);
            if (!$passwordValidation['valid']) {
                Response::validationError(['password' => $passwordValidation['errors']]);
                return;
            }
            
            // Check if email already exists
            if ($this->userModel->emailExists($validation['data']['email'])) {
                Response::conflict('Email already registered');
                return;
            }
            
            // Create user
            $userData = [
                'email' => $validation['data']['email'],
                'password' => $data['password'],
                'full_name' => $validation['data']['full_name'],
                'phone' => $validation['data']['phone'] ?? null,
                'license_number' => $validation['data']['license_number'] ?? null,
                'role' => $validation['data']['role'] ?? 'user'
            ];
            
            $userId = $this->userModel->create($userData);
            
            if (!$userId) {
                Response::serverError('Failed to create user');
                return;
            }
            
            // Get created user
            $user = $this->userModel->findById($userId);
            
            // Log creation
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'info',
                'message' => "User created: {$user['email']}",
                'user_id' => $currentUser['id'],
                'action' => 'create_user',
                'ip_address' => getClientIP()
            ]);
            
            Response::created($user, 'User created successfully');
            
        } catch (Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            
            if ($e->getCode() == 409) {
                Response::conflict($e->getMessage());
            } else {
                Response::serverError('An error occurred while creating user');
            }
        }
    }
}
