<?php
/**
 * API Response Utility
 * Provides consistent JSON response formatting
 * Following 2025 REST API best practices
 */

class Response {
    
    /**
     * Send JSON response
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     * @param array $headers Additional headers
     */
    public static function json($data, $statusCode = 200, $headers = []) {
        http_response_code($statusCode);
        
        // Set default headers
        header('Content-Type: application/json; charset=UTF-8');
        
        // Set additional headers
        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
    
    /**
     * Send success response
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     */
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        self::json($response, $statusCode);
    }
    
    /**
     * Send error response
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param string $errorCode Error code
     * @param array $details Additional error details
     */
    public static function error($message = 'An error occurred', $statusCode = 400, $errorCode = null, $details = []) {
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($details)) {
            $response['details'] = $details;
        }
        
        self::json($response, $statusCode);
    }
    
    /**
     * Send validation error response
     * @param array $errors Validation errors
     * @param string $message Error message
     */
    public static function validationError($errors, $message = 'Validation failed') {
        self::error($message, 422, 'VALIDATION_ERROR', $errors);
    }
    
    /**
     * Send not found response
     * @param string $message Error message
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404, 'NOT_FOUND');
    }
    
    /**
     * Send unauthorized response
     * @param string $message Error message
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401, 'UNAUTHORIZED');
    }
    
    /**
     * Send forbidden response
     * @param string $message Error message
     */
    public static function forbidden($message = 'Forbidden') {
        self::error($message, 403, 'FORBIDDEN');
    }
    
    /**
     * Send server error response
     * @param string $message Error message
     */
    public static function serverError($message = 'Internal server error') {
        self::error($message, 500, 'SERVER_ERROR');
    }
    
    /**
     * Send created response
     * @param mixed $data Created resource data
     * @param string $message Success message
     */
    public static function created($data = null, $message = 'Resource created successfully') {
        self::success($data, $message, 201);
    }
    
    /**
     * Send no content response
     */
    public static function noContent() {
        http_response_code(204);
        exit();
    }
    
    /**
     * Send paginated response
     * @param array $items Items array
     * @param int $total Total count
     * @param int $page Current page
     * @param int $perPage Items per page
     * @param string $message Success message
     */
    public static function paginated($items, $total, $page, $perPage, $message = 'Success') {
        $totalPages = ceil($total / $perPage);
        
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        self::json($response, 200);
    }
    
    /**
     * Send rate limit exceeded response
     * @param int $retryAfter Seconds until retry is allowed
     */
    public static function rateLimitExceeded($retryAfter = 300) {
        $headers = [
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => '100',
            'X-RateLimit-Remaining' => '0'
        ];
        
        $response = [
            'success' => false,
            'message' => 'Rate limit exceeded',
            'error_code' => 'RATE_LIMIT_EXCEEDED',
            'retry_after' => $retryAfter,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        self::json($response, 429, $headers);
    }
    
    /**
     * Handle exceptions and send appropriate error response
     * @param Exception $e Exception object
     * @param bool $debug Whether to include debug information
     */
    public static function handleException($e, $debug = false) {
        // Log the exception
        error_log("Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        
        $statusCode = 500;
        $message = 'An unexpected error occurred';
        $errorCode = 'SERVER_ERROR';
        
        // Handle specific exception types
        if (method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        }
        
        if ($e->getCode() > 0) {
            $statusCode = $e->getCode();
        }
        
        // In debug mode, include more details
        $details = [];
        if ($debug) {
            $details = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
            $message = $e->getMessage();
        }
        
        self::error($message, $statusCode, $errorCode, $details);
    }
    
    /**
     * Send method not allowed response
     * @param array $allowedMethods Allowed HTTP methods
     */
    public static function methodNotAllowed($allowedMethods = []) {
        $headers = [];
        if (!empty($allowedMethods)) {
            $headers['Allow'] = implode(', ', $allowedMethods);
        }
        
        $response = [
            'success' => false,
            'message' => 'Method not allowed',
            'error_code' => 'METHOD_NOT_ALLOWED',
            'allowed_methods' => $allowedMethods,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        self::json($response, 405, $headers);
    }
    
    /**
     * Send conflict response
     * @param string $message Error message
     */
    public static function conflict($message = 'Resource conflict') {
        self::error($message, 409, 'CONFLICT');
    }
    
    /**
     * Send bad request response
     * @param string $message Error message
     * @param array $details Additional details
     */
    public static function badRequest($message = 'Bad request', $details = []) {
        self::error($message, 400, 'BAD_REQUEST', $details);
    }
}
