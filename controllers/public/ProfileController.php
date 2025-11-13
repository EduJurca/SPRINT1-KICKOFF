<?php
/**
 * ProfileController
 * Controlador minimal per gestionar el perfil d'usuari públic
 */

require_once MODELS_PATH . '/User.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';

class ProfileController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Mostrar perfil
    public function showProfile() {
        $userId = AuthController::requireAuth();

        $profile = $this->userModel->getProfile($userId);
        $info = $this->userModel->getUserInfo($userId);

        $username = $info['username'] ?? null;
        $email = $info['email'] ?? null;
        $fullname = $profile['fullname'] ?? null;
        $dni = $profile['dni'] ?? null;
        $phone = $profile['phone'] ?? null;
        $birthdate = $profile['birthdate'] ?? null;
        $address = $profile['address'] ?? null;
        $sex = $profile['sex'] ?? null;

        require_once VIEWS_PATH . '/public/profile/perfil.php';
    }

    // Mostrar completar perfil
    public function showCompleteProfile() {
        $userId = AuthController::requireAuth();

        $profile = $this->userModel->getProfile($userId);

        $fullname = $profile['fullname'] ?? null;
        $dni = $profile['dni'] ?? null;
        $phone = $profile['phone'] ?? null;
        $birthdate = $profile['birthdate'] ?? null;
        $address = $profile['address'] ?? null;
        $sex = $profile['sex'] ?? null;

        require_once VIEWS_PATH . '/public/profile/completar-perfil.php';
    }

    // Procesar actualización del perfil
    public function completeProfile() {
        $userId = AuthController::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/perfil');
            return;
        }

        $data = [
            'fullname' => $_POST['fullname'] ?? '',
            'dni' => $_POST['dni'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'birthdate' => $_POST['birthdate'] ?? null,
            'address' => $_POST['address'] ?? '',
            'sex' => $_POST['sex'] ?? null
        ];

        if ($this->userModel->updateProfile($userId, $data)) {
            $_SESSION['success'] = 'Perfil actualitzat correctament';
        } else {
            $_SESSION['error'] = 'Error a l\'actualitzar el perfil';
        }

        Router::redirect('/perfil');
    }

    // Verificar carnet (simulat)
    public function verifyLicense() {
        $userId = AuthController::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/verificar-conduir');
            return;
        }

        // Aquí podríem processar l'upload. Ara només marquem com a sol·licitat.
        $_SESSION['success'] = 'Sol·licitud de verificació enviada. Revisarem aviat.';
        Router::redirect('/perfil');
    }
}
