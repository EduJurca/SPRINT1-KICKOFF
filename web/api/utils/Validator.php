<?php
/**
 * Input Validation Utility
 * Implements 2025 security best practices for input validation and sanitization
 * Uses whitelist approach and PHP's filter_var() functions
 */

class Validator {
    
    /**
     * Validate email address
     * @param string $email Email to validate
     * @return bool True if valid
     */
    public static function validateEmail($email) {
        if (empty($email)) {
            return false;
        }
        
        // Use PHP's built-in email validation (2025 best practice)
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (international format)
     * @param string $phone Phone number to validate
     * @return bool True if valid
     */
    public static function validatePhone($phone) {
        if (empty($phone)) {
            return false;
        }
        
        // Remove spaces, dashes, and parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Check if it contains only digits and optional + at start
        return preg_match('/^\+?[0-9]{10,15}$/', $phone) === 1;
    }
    
    /**
     * Validate required field
     * @param mixed $value Value to check
     * @return bool True if not empty
     */
    public static function validateRequired($value) {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        
        return !empty($value);
    }
    
    /**
     * Validate string length
     * @param string $value String to validate
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @return bool True if valid
     */
    public static function validateLength($value, $min = 0, $max = PHP_INT_MAX) {
        $length = mb_strlen($value);
        return $length >= $min && $length <= $max;
    }
    
    /**
     * Validate password strength
     * @param string $password Password to validate
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate URL
     * @param string $url URL to validate
     * @return bool True if valid
     */
    public static function validateUrl($url) {
        if (empty($url)) {
            return false;
        }
        
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate integer
     * @param mixed $value Value to validate
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @return bool True if valid
     */
    public static function validateInteger($value, $min = PHP_INT_MIN, $max = PHP_INT_MAX) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $intValue = (int)$value;
        return $intValue >= $min && $intValue <= $max;
    }
    
    /**
     * Validate float/decimal
     * @param mixed $value Value to validate
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @return bool True if valid
     */
    public static function validateFloat($value, $min = PHP_FLOAT_MIN, $max = PHP_FLOAT_MAX) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $floatValue = (float)$value;
        return $floatValue >= $min && $floatValue <= $max;
    }
    
    /**
     * Validate date format
     * @param string $date Date string
     * @param string $format Expected format (default: Y-m-d)
     * @return bool True if valid
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate datetime format
     * @param string $datetime Datetime string
     * @return bool True if valid
     */
    public static function validateDatetime($datetime) {
        return self::validateDate($datetime, 'Y-m-d H:i:s');
    }
    
    /**
     * Validate enum value
     * @param mixed $value Value to validate
     * @param array $allowedValues Allowed values
     * @return bool True if valid
     */
    public static function validateEnum($value, $allowedValues) {
        return in_array($value, $allowedValues, true);
    }
    
    /**
     * Sanitize string input
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    public static function sanitizeString($input) {
        // Remove HTML tags and encode special characters
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize email
     * @param string $email Email to sanitize
     * @return string Sanitized email
     */
    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize URL
     * @param string $url URL to sanitize
     * @return string Sanitized URL
     */
    public static function sanitizeUrl($url) {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }
    
    /**
     * Sanitize integer
     * @param mixed $value Value to sanitize
     * @return int Sanitized integer
     */
    public static function sanitizeInteger($value) {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitize float
     * @param mixed $value Value to sanitize
     * @return float Sanitized float
     */
    public static function sanitizeFloat($value) {
        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    /**
     * Validate and sanitize input array
     * @param array $data Input data
     * @param array $rules Validation rules
     * @return array ['valid' => bool, 'errors' => array, 'data' => array]
     */
    public static function validate($data, $rules) {
        $errors = [];
        $sanitized = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            // Check required
            if (isset($fieldRules['required']) && $fieldRules['required']) {
                if (!self::validateRequired($value)) {
                    $errors[$field] = "Field {$field} is required";
                    continue;
                }
            }
            
            // Skip validation if field is optional and empty
            if (empty($value) && (!isset($fieldRules['required']) || !$fieldRules['required'])) {
                $sanitized[$field] = null;
                continue;
            }
            
            // Validate type
            if (isset($fieldRules['type'])) {
                switch ($fieldRules['type']) {
                    case 'email':
                        if (!self::validateEmail($value)) {
                            $errors[$field] = "Invalid email format";
                        } else {
                            $sanitized[$field] = self::sanitizeEmail($value);
                        }
                        break;
                        
                    case 'phone':
                        if (!self::validatePhone($value)) {
                            $errors[$field] = "Invalid phone number format";
                        } else {
                            $sanitized[$field] = self::sanitizeString($value);
                        }
                        break;
                        
                    case 'url':
                        if (!self::validateUrl($value)) {
                            $errors[$field] = "Invalid URL format";
                        } else {
                            $sanitized[$field] = self::sanitizeUrl($value);
                        }
                        break;
                        
                    case 'integer':
                        $min = $fieldRules['min'] ?? PHP_INT_MIN;
                        $max = $fieldRules['max'] ?? PHP_INT_MAX;
                        if (!self::validateInteger($value, $min, $max)) {
                            $errors[$field] = "Invalid integer value";
                        } else {
                            $sanitized[$field] = self::sanitizeInteger($value);
                        }
                        break;
                        
                    case 'float':
                        $min = $fieldRules['min'] ?? PHP_FLOAT_MIN;
                        $max = $fieldRules['max'] ?? PHP_FLOAT_MAX;
                        if (!self::validateFloat($value, $min, $max)) {
                            $errors[$field] = "Invalid decimal value";
                        } else {
                            $sanitized[$field] = self::sanitizeFloat($value);
                        }
                        break;
                        
                    case 'string':
                        $min = $fieldRules['min_length'] ?? 0;
                        $max = $fieldRules['max_length'] ?? PHP_INT_MAX;
                        if (!self::validateLength($value, $min, $max)) {
                            $errors[$field] = "String length must be between {$min} and {$max}";
                        } else {
                            $sanitized[$field] = self::sanitizeString($value);
                        }
                        break;
                        
                    case 'enum':
                        if (!isset($fieldRules['values']) || !self::validateEnum($value, $fieldRules['values'])) {
                            $errors[$field] = "Invalid value for {$field}";
                        } else {
                            $sanitized[$field] = $value;
                        }
                        break;
                        
                    case 'date':
                        if (!self::validateDate($value)) {
                            $errors[$field] = "Invalid date format";
                        } else {
                            $sanitized[$field] = $value;
                        }
                        break;
                        
                    case 'datetime':
                        if (!self::validateDatetime($value)) {
                            $errors[$field] = "Invalid datetime format";
                        } else {
                            $sanitized[$field] = $value;
                        }
                        break;
                        
                    default:
                        $sanitized[$field] = self::sanitizeString($value);
                }
            } else {
                $sanitized[$field] = self::sanitizeString($value);
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized
        ];
    }
}
