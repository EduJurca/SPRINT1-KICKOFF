<?php
/**
 * Authentication Middleware
 * Validates JWT tokens and authenticates requests
 * Implements 2025 security best practices for token validation
 */

require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../utils/Response.php';

/**
 * Authenticate request using JWT token
 * @return array|null User data if authenticated, null otherwise
 */
function authenticate() {
    $token = extractTokenFromHeader();
    
    if (!$token) {
        Response::error('Authentication required', 401, 'MISSING_TOKEN');
        exit();
    }
    
    $payload = validateJWT($token);
    
    if (!$payload) {
        Response::error('Invalid or expired token', 401, 'INVALID_TOKEN');
        exit();
    }
    
    if (!isset($payload['data'])) {
        Response::error('Invalid token payload', 401, 'INVALID_PAYLOAD');
        exit();
    }
    
    return $payload['data'];
}

/**
 * Optional authentication - doesn't fail if no token
 * @return array|null User data if authenticated, null if not
 */
function optionalAuthenticate() {
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
 * Check if user is authenticated
 * @return bool True if authenticated
 */
function isAuthenticated() {
    $token = extractTokenFromHeader();
    
    if (!$token) {
        return false;
    }
    
    $payload = validateJWT($token);
    
    return $payload !== false && isset($payload['data']);
}

/**
 * Get authenticated user or fail
 * @return array User data
 */
function requireAuth() {
    return authenticate();
}

/**
 * Middleware function to protect routes
 * @param callable $callback Function to execute if authenticated
 */
function protectedRoute($callback) {
    $user = authenticate();
    
    if ($user) {
        return $callback($user);
    }
}

/**
 * Log authentication attempt
 * @param string $email User email
 * @param bool $success Success status
 * @param string $ip IP address
 */
function logAuthAttempt($email, $success, $ip) {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        $mongodb = getMongoDBConnection();
        $logsCollection = $mongodb->selectCollection('system_logs');
        
        $logEntry = [
            'level' => $success ? 'info' : 'warning',
            'message' => $success ? 'Successful login' : 'Failed login attempt',
            'email' => $email,
            'action' => 'login',
            'ip_address' => $ip,
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $logsCollection->insertOne($logEntry);
        
    } catch (Exception $e) {
        error_log("Failed to log auth attempt: " . $e->getMessage());
    }
}

/**
 * Get client IP address
 * @return string IP address
 */
function getClientIP() {
    $ipAddress = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    return $ipAddress;
}

/**
 * Rate limiting check (simple implementation)
 * @param string $identifier User identifier (email or IP)
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if rate limit exceeded
 */
function isRateLimited($identifier, $maxAttempts = 5, $timeWindow = 300) {
    // This is a simple file-based rate limiting
    // In production, use Redis or Memcached
    
    $cacheDir = sys_get_temp_dir() . '/carsharing_rate_limit';
    
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/' . md5($identifier) . '.json';
    
    $attempts = [];
    
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        $attempts = $data['attempts'] ?? [];
    }
    
    // Remove old attempts outside time window
    $currentTime = time();
    $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $timeWindow) {
        return ($currentTime - $timestamp) < $timeWindow;
    });
    
    // Check if rate limit exceeded
    if (count($attempts) >= $maxAttempts) {
        return true;
    }
    
    // Add current attempt
    $attempts[] = $currentTime;
    
    // Save attempts
    file_put_contents($cacheFile, json_encode(['attempts' => $attempts]));
    
    return false;
}
