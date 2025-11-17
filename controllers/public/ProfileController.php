<?php

require_once MODELS_PATH . '/User.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';
require_once __DIR__ . '/../../database/Database.php';

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
                return Router::redirect('/profile');
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
        
        return Router::view('public.profile.profile', $data);
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
            return Router::redirect('/profile');
        }
        
        if ($this->userModel->updateProfile($userId, ['lang' => $language])) {
            $_SESSION['user']['lang'] = $language;
            $_SESSION['lang'] = $language;
            
            return Router::redirect('/profile');
        }
        
        $_SESSION['error'] = 'Error updating language';
        return Router::redirect('/profile');
    }
    
    /**
     * Mostrar la pàgina de pagaments amb les targetes de l'usuari
     */
    public function showPayments() {
        $userId = AuthController::requireAuth();
        
        // Obtenir les targetes de l'usuari
        $paymentMethods = $this->getUserPaymentMethods($userId);
        
        return Router::view('public.profile.pagaments', [
            'payment_methods' => $paymentMethods
        ]);
    }
    
    /**
     * Obtenir els mètodes de pagament d'un usuari
     */
    private function getUserPaymentMethods($userId) {
        try {
            $db = Database::getMariaDBConnection();
            
            $stmt = $db->prepare("
                SELECT id, provider, last4, brand, exp_month, exp_year, is_default, created_at
                FROM payment_methods
                WHERE user_id = ?
                ORDER BY is_default DESC, created_at DESC
            ");
            
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $methods = [];
            while ($row = $result->fetch_assoc()) {
                $methods[] = $row;
            }
            
            $stmt->close();
            
            return $methods;
            
        } catch (Exception $e) {
            error_log('Error fetching payment methods: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Afegir un nou mètode de pagament
     * IMPORTANT: Aquesta implementació és un exemple simplificat
     * En producció, usa un gateway de pagament (Stripe, Adyen, etc.)
     */
    public function addPaymentMethod() {
        // Verificar autenticació
        $userId = AuthController::requireAuth();
        
        // Obtenir dades del formulari
        $cardNumber = $_POST['card_number'] ?? '';
        $expiry = $_POST['expiry'] ?? '';
        $cvc = $_POST['cvc'] ?? '';
        
        // Validació bàsica
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);
        
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            $_SESSION['error'] = 'Número de targeta invàlid';
            return Router::redirect('/profile/pagaments');
        }
        
        if (empty($expiry)) {
            $_SESSION['error'] = 'Data d\'expiració requerida';
            return Router::redirect('/profile/pagaments');
        }
        
        if (!preg_match('/^\d{3,4}$/', $cvc)) {
            $_SESSION['error'] = 'CVC invàlid';
            return Router::redirect('/profile/pagaments');
        }
        
        // Extreure dades per guardar (només metadades segures)
        $last4 = substr($cardNumber, -4);
        $brand = $this->detectCardBrand($cardNumber);
        
        // Parsejar data d'expiració (format YYYY-MM)
        $expiryParts = explode('-', $expiry);
        $expMonth = isset($expiryParts[1]) ? (int)$expiryParts[1] : 0;
        $expYear = isset($expiryParts[0]) ? (int)$expiryParts[0] : 0;
        
            $currentYear = (int)date('Y');
            $currentMonth = (int)date('m');
        if ($expYear < $currentYear || ($expYear === $currentYear && $expMonth < $currentMonth)) {
            $_SESSION['error'] = 'La data d\'expiració no pot ser en el passat';
            return Router::redirect('/profile/pagaments');
            }
        
            $simulatedToken = 'tok_' . bin2hex(random_bytes(16));
        
        try {
            $db = Database::getMariaDBConnection();
            
            // Comprovar si és el primer mètode de pagament
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM payment_methods WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $isFirst = $row['count'] == 0;
            $stmt->close();
            
            // Inserir el mètode de pagament
            $stmt = $db->prepare("
                INSERT INTO payment_methods 
                (user_id, provider, provider_token, last4, brand, exp_month, exp_year, is_default)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $provider = 'manual';
            $isDefaultInt = $isFirst ? 1 : 0;
            $stmt->bind_param(
                "issssiii",
                $userId,
                $provider,
                $simulatedToken,
                $last4,
                $brand,
                $expMonth,
                $expYear,
                $isDefaultInt
            );
            
            $stmt->execute();
            $stmt->close();
            
            $_SESSION['success'] = 'Mètode de pagament afegit correctament';
            return Router::redirect('/profile/pagaments');
            
        } catch (Exception $e) {
            error_log('Error adding payment method: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al guardar el mètode de pagament';
            return Router::redirect('/profile/pagaments');
        }
    }
    
    /**
     * Detectar la marca de la targeta pel número
     */
    private function detectCardBrand($cardNumber) {
        $patterns = [
            '/^4/' => 'VISA',
            '/^5[1-5]/' => 'MASTERCARD',
            '/^3[47]/' => 'AMERICAN EXPRESS',
            '/^6(?:011|5)/' => 'DISCOVER',
        ];
        
        foreach ($patterns as $pattern => $brand) {
            if (preg_match($pattern, $cardNumber)) {
                return $brand;
            }
        }
        
        return 'UNKNOWN';
    }
    
    /**
     * Eliminar un mètode de pagament
     */
    public function deletePaymentMethod($cardId) {
        $userId = AuthController::requireAuth();
        
        try {
            $db = Database::getMariaDBConnection();
            
            // Verificar que la targeta pertany a l'usuari
            $stmt = $db->prepare("
                DELETE FROM payment_methods 
                WHERE id = ? AND user_id = ?
            ");
            
            $stmt->bind_param("ii", $cardId, $userId);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                $_SESSION['success'] = 'Targeta eliminada correctament';
                return Router::redirect('/profile/pagaments');
            } else {
                $stmt->close();
                $_SESSION['error'] = 'No s\'ha pogut eliminar la targeta';
                return Router::redirect('/profile/pagaments');
            }
            
        } catch (Exception $e) {
            error_log('Error deleting payment method: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al eliminar la targeta';
            return Router::redirect('/profile/pagaments');
        }
    }
}
