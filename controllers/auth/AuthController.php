<?php
/**
 * 🎮 AuthController
 * Gestiona l'autenticació i autorització d'usuaris
 */

require_once MODELS_PATH . '/User.php';

class AuthController {
    // Constantes de rols
    const ROLE_SUPERADMIN = 1;
    const ROLE_ADMIN = 2;
    const ROLE_USER = 3;
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Login d'usuari
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Acceptar JSON i form-data
            $data = [];
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
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
                    // 🎯 Redirigir segons el rol
                    $roleId = $_SESSION['role_id'] ?? self::ROLE_USER;
                    if ($roleId === self::ROLE_SUPERADMIN || $roleId === self::ROLE_ADMIN) {
                        // SuperAdmin i Admins → Dashboard Admin
                        return Router::redirect('/admin/dashboard');                    
                    } else {
                        return Router::redirect('/dashboard');
                    }
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

        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Incorrect password'];
        }

        // Guardar dades de sessió
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'] ?? self::ROLE_USER;
        $_SESSION['role_name'] = $user['role_name'] ?? 'Client';

        return [
            'success' => true, 
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role_id' => $user['role_id'] ?? 3,
                'role_name' => $user['role_name'] ?? 'Client'
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
            
            // Validar format de email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                if (strpos($contentType, 'application/json') !== false) {
                    return Router::json([
                        'success' => false,
                        'message' => 'Invalid email format'
                    ], 400);
                } else {
                    $_SESSION['error'] = 'Format de correu electrònic invàlid';
                    return Router::redirect('/register');
                }
            }
            
            // Validar contrasenya (mínim 8 caràcters)
            if (strlen($data['password']) < 8) {
                if (strpos($contentType, 'application/json') !== false) {
                    return Router::json([
                        'success' => false,
                        'message' => 'Password must be at least 8 characters long'
                    ], 400);
                } else {
                    $_SESSION['error'] = 'La contrasenya ha de tenir almenys 8 caràcters';
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
                // Obtenir usuari creat
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
                    $_SESSION['role_id'] = $user['role_id'] ?? self::ROLE_USER;
                    $_SESSION['role_name'] = $user['role_name'] ?? 'Client';
                    
                    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                    if (strpos($contentType, 'application/json') !== false) {
                        return Router::json([
                            'success' => true, 
                            'message' => 'User registered successfully',
                            'auto_login' => true,
                            'user' => [
                                'id' => $user['id'],
                                'username' => $user['username'],
                                'role_id' => $user['role_id'] ?? self::ROLE_USER,
                                'role_name' => $user['role_name'] ?? 'Client'
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
        
        // Detectar si és API o navegador
        if (self::isApiRequest()) {
            // Petició API: retornar JSON
            return Router::json([
                'success' => true,
                'message' => 'Session closed'
            ], 200);
        } else {
            // Petició navegador: redirigir a la pàgina principal
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
                'role_id' => $_SESSION['role_id'] ?? 3,
                'role_name' => $_SESSION['role_name'] ?? 'Client'
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
     * Detecta si la petició és API
     * 
     * @return bool True si és una petició API
     */
    private static function isApiRequest() {
        return (
            isset($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        ) || (
            isset($_SERVER['CONTENT_TYPE']) && 
            strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
        ) || (
            strpos($_SERVER['REQUEST_URI'], '/api/') !== false
        );
    }
    
    /**
     * Middleware per comprovar autenticació
     */
    public static function requireAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            // Detectar si és una petició API o navegador
            $isApiRequest = self::isApiRequest();
            
            if ($isApiRequest) {
                // Petició API: retornar JSON
                Router::json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
                exit;
            } else {
                // Petició navegador: redirigir a login
                $_SESSION['error'] = 'Has d\'iniciar sessió per accedir';
                Router::redirect('/login');
                exit;
            }
        }
        
        return $_SESSION['user_id'];
    }
    
    /**
     * Middleware per comprovar si és admin
     */
    public static function requireAdmin() {
        $userId = self::requireAuth();
        
        // Comprovar si és Staff (SuperAdmin o Admin)
        $roleId = $_SESSION['role_id'] ?? self::ROLE_USER;
        if (!in_array($roleId, [self::ROLE_SUPERADMIN, self::ROLE_ADMIN])) {
            // Detectar si és una petició API o navegador
            $isApiRequest = self::isApiRequest();
            
            if ($isApiRequest) {
                Router::json([
                    'success' => false,
                    'message' => 'Admin access required'
                ], 403);
                exit;
            } else {
                $_SESSION['error'] = 'Accés denegat. Només per administradors.';
                Router::redirect('/dashboard');
                exit;
            }
        }
        
        return $userId;
    }
}
