<?php
/**
 * JWT Configuration and Token Management
 * Implements JWT authentication following 2025 security best practices
 * Uses strong signing algorithms and proper token validation
 */

// JWT Configuration
define('JWT_SECRET_KEY', getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production-2025');
define('JWT_ALGORITHM', 'HS256'); // Strong signing algorithm
define('JWT_EXPIRATION', 86400); // 24 hours in seconds (recommended in 2025)
define('JWT_ISSUER', 'carsharing-api');

/**
 * Generate JWT token
 * @param array $payload User data (id, email, role)
 * @return string JWT token
 */
function generateJWT($payload) {
    $header = [
        'typ' => 'JWT',
        'alg' => JWT_ALGORITHM
    ];
    
    $issuedAt = time();
    $expirationTime = $issuedAt + JWT_EXPIRATION;
    
    $tokenPayload = [
        'iss' => JWT_ISSUER,
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'data' => $payload
    ];
    
    // Encode header
    $base64UrlHeader = base64UrlEncode(json_encode($header));
    
    // Encode payload
    $base64UrlPayload = base64UrlEncode(json_encode($tokenPayload));
    
    // Create signature
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
    $base64UrlSignature = base64UrlEncode($signature);
    
    // Create JWT token
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    
    return $jwt;
}

/**
 * Validate and decode JWT token
 * @param string $token JWT token
 * @return array|false Decoded payload or false if invalid
 */
function validateJWT($token) {
    try {
        // Split token into parts
        $tokenParts = explode('.', $token);
        
        if (count($tokenParts) !== 3) {
            return false;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;
        
        // Verify signature
        $signature = base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
        
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        // Decode payload
        $payload = json_decode(base64UrlDecode($base64UrlPayload), true);
        
        if (!$payload) {
            return false;
        }
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        // Check issuer
        if (!isset($payload['iss']) || $payload['iss'] !== JWT_ISSUER) {
            return false;
        }
        
        return $payload;
        
    } catch (Exception $e) {
        error_log("JWT Validation Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Extract JWT token from Authorization header
 * @return string|null Token or null if not found
 */
function extractTokenFromHeader() {
    // Try to get headers in a robust way across SAPIs
    $authHeader = null;

    // getallheaders may not exist on some SAPIs
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        // Normalize header keys to lowercase for safety
        if (is_array($headers)) {
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    $authHeader = $value;
                    break;
                }
            }
        }
    }

    // Fallbacks commonly used with Apache/FastCGI
    if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (!$authHeader && isset($_SERVER['Authorization'])) {
        $authHeader = $_SERVER['Authorization'];
    }
    if (!$authHeader && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return $matches[1];
    }

    return null;
}

/**
 * Get current authenticated user from token
 * @return array|null User data or null if not authenticated
 */
function getCurrentUser() {
    $token = extractTokenFromHeader();
    
    if (!$token) {
        return null;
    }
    
    $payload = validateJWT($token);
    
    if (!$payload || !isset($payload['data'])) {
        return null;
    }
    
    return $payload['data'];
}

/**
 * Base64 URL encode
 * @param string $data Data to encode
 * @return string Encoded data
 */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64 URL decode
 * @param string $data Data to decode
 * @return string Decoded data
 */
function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}
