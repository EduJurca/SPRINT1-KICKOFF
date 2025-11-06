<?php
/**
 * 💬 ChatController - Gestiona el chatbot con IA
 * Procesa mensajes del usuario y obtiene respuestas del LLM
 */

class ChatController {
    
    /**
     * Mostrar la vista del chat
     */
    public function index() {
        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['user_id'])) {
            Router::redirect('/login');
            return;
        }
        
        Router::view('public/chat/index');
    }
    
    /**
     * Procesar mensaje y obtener respuesta del chatbot
     * POST /chat/send
     */
    public function send() {
        header('Content-Type: application/json');
        
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }
        
        // Obtener el mensaje del usuario
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['message'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Message is required']);
            return;
        }
        
        $userMessage = trim($input['message']);
        
        // Validar longitud del mensaje
        if (strlen($userMessage) > 1000) {
            http_response_code(400);
            echo json_encode(['error' => 'Message too long (max 1000 characters)']);
            return;
        }
        
        // Obtener la API key desde variables de entorno o config
        $apiKey = getenv('GROQ_API_KEY');
        
        if (!$apiKey) {
            http_response_code(500);
            echo json_encode(['error' => 'API key not configured']);
            return;
        }
        
        // Preparar los mensajes para Groq (formato OpenAI-compatible)
        $messages = [
            [
                'role' => 'system',
                'content' => 'Eres un asistente virtual útil para VoltiaCar, una aplicación de car sharing de vehículos eléctricos. Ayuda a los usuarios con preguntas sobre vehículos, reservas, pagos y uso de la aplicación. Responde de forma concisa y amigable en el idioma del usuario.'
            ],
            [
                'role' => 'user',
                'content' => $userMessage
            ]
        ];
        
        // Preparar la petición a Groq
        $payload = [
            'model' => 'llama-3.1-8b-instant', // Modelo rápido y gratuito
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7
        ];
        
        // Llamar a la API de Groq (compatible con OpenAI)
        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            http_response_code(500);
            echo json_encode(['error' => 'Connection error: ' . $curlError]);
            return;
        }
        
        $data = json_decode($response, true);
        
        // Manejar errores de la API
        if ($httpCode !== 200) {
            echo json_encode(['error' => 'API error', 'details' => $data, 'http_code' => $httpCode]);
            return;
        }
        
        // Verificar la respuesta (formato OpenAI-compatible)
        if (!isset($data['choices'][0]['message']['content'])) {
            http_response_code(500);
            echo json_encode(['error' => 'Invalid API response', 'details' => $data]);
            return;
        }
        
        // Devolver la respuesta
        echo json_encode([
            'success' => true,
            'message' => $data['choices'][0]['message']['content']
        ]);
    }
}
