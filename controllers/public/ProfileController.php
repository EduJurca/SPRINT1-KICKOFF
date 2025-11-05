<?php
/**
 * ProfileController
 * Controlador minimal per gestionar el perfil d'usuari públic
 */

require_once MODELS_PATH . '/User.php';
require_once CONTROLLERS_PATH . '/auth/AuthController.php';
require_once __DIR__ . '/../../database/Database.php';

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
            return Router::redirect('/perfil/pagaments');
        }
        
        if (empty($expiry)) {
            $_SESSION['error'] = 'Data d\'expiració requerida';
            return Router::redirect('/perfil/pagaments');
        }
        
        if (!preg_match('/^\d{3,4}$/', $cvc)) {
            $_SESSION['error'] = 'CVC invàlid';
            return Router::redirect('/perfil/pagaments');
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
            return Router::redirect('/perfil/pagaments');
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
            return Router::redirect('/perfil/pagaments');
            
        } catch (Exception $e) {
            error_log('Error adding payment method: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al guardar el mètode de pagament';
            return Router::redirect('/perfil/pagaments');
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
                return Router::redirect('/perfil/pagaments');
            } else {
                $stmt->close();
                $_SESSION['error'] = 'No s\'ha pogut eliminar la targeta';
                return Router::redirect('/perfil/pagaments');
            }
            
        } catch (Exception $e) {
            error_log('Error deleting payment method: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al eliminar la targeta';
            return Router::redirect('/perfil/pagaments');
        }
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
            return Router::redirect('/pagaments');
        }
        
        if (empty($expiry)) {
            $_SESSION['error'] = 'Data d\'expiració requerida';
            return Router::redirect('/pagaments');
        }
        
        if (!preg_match('/^\d{3,4}$/', $cvc)) {
            $_SESSION['error'] = 'CVC invàlid';
            return Router::redirect('/pagaments');
        }
        
        // Extreure dades per guardar (només metadades segures)
        $last4 = substr($cardNumber, -4);
        $brand = $this->detectCardBrand($cardNumber);
        
        // Parsejar data d'expiració (format YYYY-MM)
        $expiryParts = explode('-', $expiry);
        $expMonth = isset($expiryParts[1]) ? (int)$expiryParts[1] : 0;
        $expYear = isset($expiryParts[0]) ? (int)$expiryParts[0] : 0;
        
        // ⚠️ IMPORTANT: En producció, aquí hauries de:
        // 1. Enviar les dades a Stripe/Adyen/etc i obtenir un token
        // 2. Xifrar el token abans de guardar-lo
        // 3. MAI guardar el número complet de targeta ni el CVC
        
        // Per ara, creem un token simulat (NO FER EN PRODUCCIÓ)
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
                "isssssii",
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
            return Router::redirect('/pagaments');
            
        } catch (Exception $e) {
            error_log('Error adding payment method: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al guardar el mètode de pagament';
            return Router::redirect('/pagaments');
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
                return Router::json([
                    'success' => true,
                    'message' => 'Targeta eliminada correctament'
                ]);
            } else {
                $stmt->close();
                return Router::json([
                    'success' => false,
                    'message' => 'No s\'ha pogut eliminar la targeta'
                ], 404);
            }
            
        } catch (Exception $e) {
            error_log('Error deleting payment method: ' . $e->getMessage());
            return Router::json([
                'success' => false,
                'message' => 'Error al eliminar la targeta'
            ], 500);
        }
    }
}
