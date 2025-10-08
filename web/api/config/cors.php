<?php
/**
 * CORS Configuration
 * Implements secure CORS policy following 2025 best practices
 * Uses origin whitelist and strict header controls
 */

// Allowed origins whitelist (configure based on your domains)
$allowedOrigins = [
    'http://localhost',
    'http://localhost:8080',
    'http://localhost:3000',
    'https://carsharing.local',
    getenv('FRONTEND_URL') ?: 'http://localhost:8080'
];

// Allowed HTTP methods
$allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

// Allowed headers
$allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'];

/**
 * Set CORS headers with security best practices
 */
function setCORSHeaders() {
    global $allowedOrigins, $allowedMethods, $allowedHeaders;
    
    // Get the origin of the request
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    // Validate origin against whitelist
    if (in_array($origin, $allowedOrigins)) {
        // Safely echo the validated origin
        header("Access-Control-Allow-Origin: {$origin}");
        header("Access-Control-Allow-Credentials: true");
    } else {
        // For development, allow localhost variations
        if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header("Access-Control-Allow-Credentials: true");
        }
    }
    
    // Set allowed methods
    header("Access-Control-Allow-Methods: " . implode(', ', $allowedMethods));
    
    // Set allowed headers
    header("Access-Control-Allow-Headers: " . implode(', ', $allowedHeaders));
    
    // Set max age for preflight cache (24 hours)
    header("Access-Control-Max-Age: 86400");
    
    // Vary header for proper caching and security
    header("Vary: Origin");
    
    // Content type
    header("Content-Type: application/json; charset=UTF-8");
}

/**
 * Handle preflight OPTIONS requests
 */
function handlePreflightRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        setCORSHeaders();
        http_response_code(200);
        exit();
    }
}

/**
 * Validate origin against whitelist
 * @param string $origin Origin to validate
 * @return bool True if origin is allowed
 */
function isOriginAllowed($origin) {
    global $allowedOrigins;
    
    // Check exact match
    if (in_array($origin, $allowedOrigins)) {
        return true;
    }
    
    // Check localhost variations for development
    if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
        return true;
    }
    
    return false;
}

/**
 * Add origin to whitelist dynamically
 * @param string $origin Origin to add
 */
function addAllowedOrigin($origin) {
    global $allowedOrigins;
    
    if (!in_array($origin, $allowedOrigins)) {
        $allowedOrigins[] = $origin;
    }
}

// Initialize CORS
setCORSHeaders();
handlePreflightRequest();
