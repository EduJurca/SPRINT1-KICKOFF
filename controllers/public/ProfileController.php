<?php

require_once MODELS_PATH . '/User.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

class ProfileController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function getProfile() {
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
    
    public function getProfileData() {
        $userId = AuthController::requireAuth();
        
        $profile = $this->userModel->getProfile($userId);
        
        return Router::json([
            'success' => true,
            'data' => $profile ?? []
        ], 200);
    }
    
    public function updateProfile() {
        $userId = AuthController::requireAuth();
        
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
    
    public function completeProfile() {
        return $this->updateProfile();
    }
    
    public function showProfile() {
        $userId = AuthController::requireAuth();
        
        $profile = $this->userModel->getProfile($userId);
        
        $userInfo = $this->userModel->getUserInfo($userId);
        
        $data = array_merge(
            $profile ?? [],
            $userInfo ?? []
        );
        
        return Router::view('public.profile.perfil', $data);
    }
    
    public function showCompleteProfile() {
        $userId = AuthController::requireAuth();
        
        $profile = $this->userModel->getProfile($userId);
        
        return Router::view('public.profile.completar-perfil', $profile ?? []);
    }
    
    public function verifyLicense() {
        $userId = AuthController::requireAuth();
        
        if (!isset($_FILES['driver_license_photo'])) {
            return Router::json([
                'success' => false,
                'message' => 'License photo is required'
            ], 400);
        }
        
        $file = $_FILES['driver_license_photo'];
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            return Router::json([
                'success' => false,
                'message' => 'Invalid file type. Only JPG and PNG are allowed.'
            ], 400);
        }
        
        $uploadDir = PUBLIC_PATH . '/uploads/licenses/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = $userId . '_' . time() . '_' . basename($file['name']);
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
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

    public function updateLanguage() {
        $userId = AuthController::requireAuth();
        
        $language = $_POST['language'] ?? null;
        
        if (!in_array($language, ['en', 'ca'])) {
            $_SESSION['error'] = 'Invalid language';
            return Router::redirect('/perfil');
        }
        
        if ($this->userModel->updateProfile($userId, ['lang' => $language])) {
            $_SESSION['user']['lang'] = $language;
            $_SESSION['lang'] = $language;
            
            return Router::redirect('/perfil');
        }
        
        $_SESSION['error'] = 'Error updating language';
        return Router::redirect('/perfil');
    }
}
