<?php
/**
 * 🎮 ProfileController
 * Gestiona el perfil d'usuari
 */

require_once MODELS_PATH . '/User.php';
require_once CONTROLLERS_PATH . '/AuthController.php';

class ProfileController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Obtenir perfil de l'usuari
     */
    public function getProfile() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        $profile = $this->userModel->getProfile($userId);
        
        if (!$profile) {
            return Router::json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }
        
        return Router::json([
            'success' => true,
            'profile' => $profile
        ], 200);
    }
    
    /**
     * Obtenir dades del perfil per completar
     */
    public function getProfileData() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        $profile = $this->userModel->getProfile($userId);
        
        return Router::json([
            'success' => true,
            'data' => $profile ?? []
        ], 200);
    }
    
    /**
     * Actualitzar perfil
     */
    public function updateProfile() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Acceptar tanto JSON como form-data
        $data = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            $data = $_POST;
        }
        
        if ($this->userModel->updateProfile($userId, $data)) {
            if (strpos($contentType, 'application/json') !== false) {
                return Router::json([
                    'success' => true,
                    'msg' => 'Perfil actualitzat correctament'
                ], 200);
            } else {
                $_SESSION['success'] = 'Perfil actualitzat correctament';
                return Router::redirect('/perfil');
            }
        }
        
        if (strpos($contentType, 'application/json') !== false) {
            return Router::json([
                'success' => false,
                'msg' => 'Error en actualitzar el perfil'
            ], 500);
        } else {
            $_SESSION['error'] = 'Error en actualitzar el perfil';
            return Router::redirect('/completar-perfil');
        }
    }
    
    /**
     * Completar perfil
     */
    public function completeProfile() {
        return $this->updateProfile();
    }
    
    /**
     * Mostrar vista de perfil amb dades
     */
    public function showProfile() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Obtenir dades del perfil
        $profile = $this->userModel->getProfile($userId);
        
        // Obtenir informació de l'usuari (username, email)
        $userInfo = $this->userModel->getUserInfo($userId);
        
        // Combinar dades
        $data = array_merge(
            $profile ?? [],
            $userInfo ?? []
        );
        
        // Passar dades a la vista
        return Router::view('public.profile.perfil', $data);
    }
    
    /**
     * Mostrar vista de completar perfil amb dades
     */
    public function showCompleteProfile() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Obtenir dades del perfil
        $profile = $this->userModel->getProfile($userId);
        
        // Passar dades a la vista
        return Router::view('public.profile.completar-perfil', $profile ?? []);
    }
    
    /**
     * Verificar carnet de conduir
     */
    public function verifyLicense() {
        // Requerir autenticació
        $userId = AuthController::requireAuth();
        
        // Processar fitxer pujat
        if (!isset($_FILES['driver_license_photo'])) {
            return Router::json([
                'success' => false,
                'message' => 'License photo is required'
            ], 400);
        }
        
        $file = $_FILES['driver_license_photo'];
        
        // Validar fitxer
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            return Router::json([
                'success' => false,
                'message' => 'Invalid file type. Only JPG and PNG are allowed.'
            ], 400);
        }
        
        // Guardar fitxer (implementa la lògica segons les teves necessitats)
        $uploadDir = PUBLIC_PATH . '/uploads/licenses/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = $userId . '_' . time() . '_' . basename($file['name']);
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Actualitzar base de dades amb la ruta del fitxer
            // (implementa segons la teva estructura de BD)
            
            return Router::json([
                'success' => true,
                'message' => 'License uploaded successfully',
                'file_path' => '/uploads/licenses/' . $fileName
            ], 200);
        }
        
        return Router::json([
            'success' => false,
            'message' => 'Error uploading license'
        ], 500);
    }
}
