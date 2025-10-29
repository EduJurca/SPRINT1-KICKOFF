<?php
/**
 * 🎮 AuthController
 * Gestiona l'autenticació i autorització d'usuaris
 */

require_once MODELS_PATH . '/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Login d'usuari
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Acceptar tanto JSON como form-data
            $data = [];
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                // JSON
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                // Form-data
                $data = $_POST;
            }
            
            if (!isset($data['username']) || !isset($data['password'])) {
                if (strpos($contentType, 'application/json') !== false) {
                    return Router::json([
                        'success' => false,
                        'message' => 'Missing username or password'
                    ], 400);
                } else {
                    $_SESSION['error'] = 'Omple tots els camps';
                    return Router::redirect('/login');
                }
            }
            
            $result = $this->attemptLogin($data['username'], $data['password']);
            
            if ($result['success']) {
                if (strpos($contentType, 'application/json') !== false) {
                    return Router::json($result, 200);
                } else {
                    return Router::redirect('/dashboard');
                }
            } else {
                if (strpos($contentType, 'application/json') !== false) {
                    return Router::json($result, 401);
                } else {
                    $_SESSION['error'] = 'Usuari o contrasenya incorrectes';
                    return Router::redirect('/login');
                }
            }
        }
    }
    
    /**
     * Intentar login
     */
    private function attemptLogin($username, $password) {
        // Iniciar sessió si no està iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 3600, // 1 hora
                'path' => '/',
                'domain' => '',
                'secure' => false, // false per HTTP, true per HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Incorrect password'];
        }

        // Guardar dades a la sessió
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;

        return [
            'success' => true, 
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'is_admin' => $user['is_admin'] ?? 0
            ],
            'session_id' => session_id()
        ];
    }
    
    /**
     * Registre d'usuari
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Acceptar tanto JSON como form-data
            $data = [];
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                $data = $_POST;
            }
            
            // Validar camps requerits
            if (!isset($data['username'], $data['password'], $data['email'])) {
                if (strpos($contentType, 'application/json') !== false) {
                    return Router::json([
                        'success' => false,
                        'message' => 'Missing required fields'
                    ], 400);
                } else {
                    $_SESSION['error'] = 'Omple tots els camps obligatoris';
                    return Router::redirect('/register');
                }
            }
            
            // Comprovar si l'usuari ja existeix
            if ($this->userModel->findByUsernameOrEmail($data['username'], $data['email'])) {
                if (strpos($contentType, 'application/json') !== false) {
                    return Router::json([
                        'success' => false,
                        'message' => 'Username or email already exists'
                    ], 409);
                } else {
                    $_SESSION['error'] = 'Aquest usuari o correu ja existeix';
                    return Router::redirect('/register');
                }
            }
            
            // Crear usuari
            if ($this->userModel->create($data)) {
                // Després de registrar exitosament, iniciar sessió automàticament
                $user = $this->userModel->findByUsername($data['username']);
                
                if ($user) {
                    // Iniciar sessió
                    if (session_status() === PHP_SESSION_NONE) {
                        session_set_cookie_params([
                            'lifetime' => 3600,
                            'path' => '/',
                            'domain' => '',
                            'secure' => false,
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                        session_start();
                    }
                    
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
                    
                    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                    if (strpos($contentType, 'application/json') !== false) {
                        return Router::json([
                            'success' => true, 
                            'message' => 'User registered successfully',
                            'auto_login' => true,
                            'user' => [
                                'id' => $user['id'],
                                'username' => $user['username'],
                                'is_admin' => $user['is_admin'] ?? 0
                            ]
                        ], 201);
                    } else {
                        return Router::redirect('/dashboard');
                    }
                }
                
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (strpos($contentType, 'application/json') !== false) {
                    return Router::json([
                        'success' => true,
                        'message' => 'User registered successfully'
                    ], 201);
                } else {
                    return Router::redirect('/dashboard');
                }
            }
            
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                return Router::json([
                    'success' => false,
                    'message' => 'Error registering user'
                ], 500);
            } else {
                $_SESSION['error'] = 'Error al registrar usuari';
                return Router::redirect('/register');
            }
        }
    }
    
    /**
     * Logout d'usuari
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_unset();
        session_destroy();
        
        // Verificar si es una petición JSON
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false || 
            strpos($acceptHeader, 'application/json') !== false) {
            // Respuesta JSON para AJAX
            return Router::json([
                'success' => true,
                'message' => 'Session closed'
            ], 200);
        } else {
            // Redirigir a la página principal para formularios HTML
            return Router::redirect('/');
        }
    }
    
    /**
     * Comprovar estat de la sessió
     */
    public function checkSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $authenticated = isset($_SESSION['user_id']);
        
        return Router::json([
            'authenticated' => $authenticated,
            'user' => $authenticated ? [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'is_admin' => $_SESSION['is_admin'] ?? 0
            ] : null
        ], 200);
    }
    
    /**
     * Obtenir estat de la sessió
     */
    public function getSessionStatus() {
        return $this->checkSession();
    }
    
    /**
     * Middleware per comprovar autenticació
     */
    public static function requireAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            Router::json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            exit;
        }
        
        return $_SESSION['user_id'];
    }
    
    /**
     * Middleware per comprovar si és admin
     */
    public static function requireAdmin() {
        $userId = self::requireAuth();
        
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            Router::json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
            exit;
        }
        
        return $userId;
    }
}
