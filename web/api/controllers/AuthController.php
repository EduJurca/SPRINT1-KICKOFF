<?php
/**
 * Authentication Controller
 * Handles user authentication endpoints
 * Implements 2025 JWT authentication best practices
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * User login
     * POST /api/auth/login
     */
    public function login() {
        try {
            // Get request body
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                Response::badRequest('Invalid JSON data');
                return;
            }
            
            // Validate input
            $validation = Validator::validate($data, [
                'email' => ['required' => true, 'type' => 'email'],
                'password' => ['required' => true, 'type' => 'string']
            ]);
            
            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }
            
            $email = $validation['data']['email'];
            $password = $data['password']; // Don't sanitize password
            
            // Check rate limiting (disabled for development)
            // TODO: Re-enable in production
            // if (isRateLimited($email, 5, 300)) {
            //     Response::rateLimitExceeded(300);
            //     return;
            // }
            
            // Authenticate user
            $user = $this->userModel->authenticate($email, $password);
            
            if (!$user) {
                // Log failed attempt
                logAuthAttempt($email, false, getClientIP());
                
                Response::error('Invalid email or password', 401, 'INVALID_CREDENTIALS');
                return;
            }
            
            // Log successful attempt
            logAuthAttempt($email, true, getClientIP());
            
            // Generate JWT token
            $tokenPayload = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            $token = generateJWT($tokenPayload);
            
            // Return response
            Response::success([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ],
                'expires_in' => JWT_EXPIRATION
            ], 'Login successful');
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            Response::serverError('An error occurred during login');
        }
    }
    
    /**
     * User registration
     * POST /api/auth/register
     */
    public function register() {
        try {
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
                'license_number' => ['required' => false, 'type' => 'string', 'max_length' => 50]
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
                'password' => $data['password'], // Will be hashed in model
                'full_name' => $validation['data']['full_name'],
                'phone' => $validation['data']['phone'] ?? null,
                'license_number' => $validation['data']['license_number'] ?? null,
                'role' => 'user' // Default role
            ];
            
            $userId = $this->userModel->create($userData);
            
            if (!$userId) {
                Response::serverError('Failed to create user');
                return;
            }
            
            // Get created user
            $user = $this->userModel->findById($userId);
            
            // Generate JWT token
            $tokenPayload = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            $token = generateJWT($tokenPayload);
            
            // Log registration
            logAuthAttempt($user['email'], true, getClientIP());
            
            // Return response
            Response::created([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ],
                'expires_in' => JWT_EXPIRATION
            ], 'Registration successful');
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            
            if ($e->getCode() == 409) {
                Response::conflict($e->getMessage());
            } else {
                Response::serverError('An error occurred during registration');
            }
        }
    }
    
    /**
     * User logout
     * POST /api/auth/logout
     */
    public function logout() {
        try {
            // Get authenticated user
            $user = authenticate();
            
            // Log logout action
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'info',
                'message' => 'User logged out',
                'user_id' => $user['id'],
                'action' => 'logout',
                'ip_address' => getClientIP()
            ]);
            
            // In a stateless JWT system, logout is handled client-side
            // by removing the token. We just log the action here.
            
            Response::success(null, 'Logout successful');
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            Response::serverError('An error occurred during logout');
        }
    }
    
    /**
     * Get current user profile
     * GET /api/auth/me
     */
    public function me() {
        try {
            // Get authenticated user
            $user = authenticate();
            
            // Get full user details
            $userDetails = $this->userModel->findById($user['id']);
            
            if (!$userDetails) {
                Response::notFound('User not found');
                return;
            }
            
            Response::success($userDetails, 'User profile retrieved');
            
        } catch (Exception $e) {
            error_log("Get profile error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving profile');
        }
    }
    
    /**
     * Refresh JWT token
     * POST /api/auth/refresh
     */
    public function refresh() {
        try {
            // Get authenticated user
            $user = authenticate();
            
            // Generate new JWT token
            $tokenPayload = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            $token = generateJWT($tokenPayload);
            
            Response::success([
                'token' => $token,
                'expires_in' => JWT_EXPIRATION
            ], 'Token refreshed successfully');
            
        } catch (Exception $e) {
            error_log("Token refresh error: " . $e->getMessage());
            Response::serverError('An error occurred while refreshing token');
        }
    }
    
    /**
     * Verify token
     * GET /api/auth/verify
     */
    public function verify() {
        try {
            // Get authenticated user
            $user = authenticate();
            
            Response::success([
                'valid' => true,
                'user' => $user
            ], 'Token is valid');
            
        } catch (Exception $e) {
            error_log("Token verification error: " . $e->getMessage());
            Response::error('Invalid token', 401, 'INVALID_TOKEN');
        }
    }
}
