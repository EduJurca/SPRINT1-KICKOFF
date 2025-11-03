<?php
/**
 * 👥 Admin User Controller
 * CRUD d'usuaris per administradors
 */

require_once MODELS_PATH . '/User.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';
require_once CORE_PATH . '/Permissions.php';

class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
        
        // 🔐 Verificar que l'usuari és Staff (SuperAdmin o Treballador)
        // Solo usuarios con role_id 1 o 2 pueden acceder
        $userId = AuthController::requireAuth();
        $roleId = $_SESSION['role_id'] ?? 3;
        
        if (!in_array($roleId, [1, 2])) {
            $_SESSION['error'] = 'Accés denegat. Només per personal autoritzat.';
            Router::redirect('/dashboard');
            exit;
        }
    }
    
    /**
     * Llistar tots els usuaris
     */
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        
        $users = $this->userModel->getAll($limit, $offset, $search);
        $total = $this->userModel->count($search);
        $totalPages = ceil($total / $limit);
        
        require_once VIEWS_PATH . '/admin/users/index.php';
    }
    
    /**
     * Mostrar formulari de creació
     */
    public function create() {
        // 🔐 Només SuperAdmin pot crear usuaris
        Permissions::authorize('users.create');
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $roles = $this->userModel->getAllRoles();
            require_once VIEWS_PATH . '/admin/users/create.php';
        }
    }
    
    /**
     * Guardar nou usuari
     */
    public function store() {
        // 🔐 Només SuperAdmin pot crear usuaris
        Permissions::authorize('users.create');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/admin/users');
            return;
        }
        
        // Validar dades
        if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
            $_SESSION['error'] = 'Els camps username, email i password són obligatoris';
            Router::redirect('/admin/users/create');
            return;
        }
        
        // Verificar si l'usuari ja existeix
        if ($this->userModel->findByUsernameOrEmail($_POST['username'], $_POST['email'])) {
            $_SESSION['error'] = 'L\'usuari o email ja existeix';
            Router::redirect('/admin/users/create');
            return;
        }
        
        // Determinar role_id i is_admin segons el rol seleccionat
        $role_id = !empty($_POST['role_id']) ? (int)$_POST['role_id'] : 3;
        $is_admin = in_array($role_id, [1, 2]) ? 1 : 0;
        
        // Crear usuari
        $data = [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'fullname' => $_POST['fullname'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'role_id' => $role_id,
            'is_admin' => $is_admin
        ];
        
        if ($this->userModel->create($data)) {
            $_SESSION['success'] = 'Usuari creat correctament';
            Router::redirect('/admin/users');
        } else {
            $_SESSION['error'] = 'Error al crear l\'usuari';
            Router::redirect('/admin/users/create');
        }
    }
    
    /**
     * Mostrar formulari d'edició
     */
    public function edit() {
        // 🔐 Només SuperAdmin pot editar usuaris
        Permissions::authorize('users.edit');
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            Router::redirect('/admin/users');
            return;
        }
        
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuari no trobat';
            Router::redirect('/admin/users');
            return;
        }
        
        $roles = $this->userModel->getAllRoles();
        require_once VIEWS_PATH . '/admin/users/edit.php';
    }
    
    /**
     * Actualitzar usuari
     */
    public function update() {
        // 🔐 Només SuperAdmin pot editar usuaris
        Permissions::authorize('users.edit');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/admin/users');
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            Router::redirect('/admin/users');
            return;
        }
        
        // Validar dades
        if (empty($_POST['username']) || empty($_POST['email'])) {
            $_SESSION['error'] = 'Els camps username i email són obligatoris';
            Router::redirect('/admin/users/edit?id=' . $id);
            return;
        }
        
        // Determinar role_id i is_admin segons el rol seleccionat
        $role_id = !empty($_POST['role_id']) ? (int)$_POST['role_id'] : 3;
        $is_admin = in_array($role_id, [1, 2]) ? 1 : 0;
        
        $data = [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'fullname' => $_POST['fullname'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'role_id' => $role_id,
            'is_admin' => $is_admin
        ];
        
        if ($this->userModel->update($id, $data)) {
            $_SESSION['success'] = 'Usuari actualitzat correctament';
        } else {
            $_SESSION['error'] = 'Error al actualitzar l\'usuari';
        }
        
        Router::redirect('/admin/users');
    }
    
    /**
     * Eliminar usuari
     */
    public function delete() {
        // 🔐 Només SuperAdmin pot eliminar usuaris
        Permissions::authorize('users.delete');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/admin/users');
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            Router::redirect('/admin/users');
            return;
        }
        
        if ($id == 1) {
            $_SESSION['error'] = 'No es pot eliminar l\'usuari administrador principal';
            Router::redirect('/admin/users');
            return;
        }
        
        if ($this->userModel->delete($id)) {
            $_SESSION['success'] = 'Usuari eliminat correctament';
        } else {
            $_SESSION['error'] = 'Error al eliminar l\'usuari';
        }
        
        Router::redirect('/admin/users');
    }
}
