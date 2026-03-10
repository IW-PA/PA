<?php
// Security utilities for Budgie

// Input sanitization functions
function sanitizeString($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function sanitizeNumber($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

function sanitizeInteger($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

// XSS Protection
function preventXSS($input) {
    return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// SQL Injection Protection (using prepared statements)
function validateSQLInput($input) {
    // Remove potential SQL injection characters
    $dangerous = ['<script', '</script', 'javascript:', 'vbscript:', 'onload=', 'onerror=', 'onclick='];
    foreach ($dangerous as $pattern) {
        if (stripos($input, $pattern) !== false) {
            return false;
        }
    }
    return true;
}

// File upload security
function validateFileUpload($file) {
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['valid' => false, 'error' => 'No file uploaded'];
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        return ['valid' => false, 'error' => 'File type not allowed'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File too large'];
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf'
    ];
    
    if (!in_array($mimeType, $allowedMimes)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }
    
    return ['valid' => true];
}

// Rate limiting
class RateLimiter {
    private static $limits = [];
    
    public static function checkLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        $key = "rate_limit_{$identifier}";
        $now = time();
        
        if (!isset(self::$limits[$key])) {
            self::$limits[$key] = [
                'count' => 0,
                'reset_time' => $now + $timeWindow
            ];
        }
        
        $limit = self::$limits[$key];
        
        if ($now > $limit['reset_time']) {
            self::$limits[$key] = [
                'count' => 0,
                'reset_time' => $now + $timeWindow
            ];
            return true;
        }
        
        if ($limit['count'] >= $maxAttempts) {
            return false;
        }
        
        self::$limits[$key]['count']++;
        return true;
    }
}

// Password strength validation
function validatePasswordStrength($password) {
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
    
    return $errors;
}

// CSRF Token management
class CSRFProtection {
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function getTokenField() {
        return '<input type="hidden" name="csrf_token" value="' . self::generateToken() . '">';
    }
}

// Session security
function secureSession() {
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
}

// Content Security Policy
function setCSPHeaders() {
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline'; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' https://fonts.gstatic.com; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none';";
    
    header("Content-Security-Policy: $csp");
}

// Security headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// Log security events
function logSecurityEvent($event, $details = []) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'event' => $event,
        'details' => $details
    ];
    
    $logFile = __DIR__ . '/../logs/security.log';
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

// Initialize security
function initSecurity() {
    secureSession();
    setSecurityHeaders();
    setCSPHeaders();
}
?>
