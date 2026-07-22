<?php
// Main configuration file for Budgie

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent aggressive browser caching on redirects and page reloads
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('BASE_PATH', dirname(__DIR__, 2));
define('SRC_PATH', BASE_PATH . '/src');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Environment configuration
define('APP_NAME', 'Budgie');
define('APP_VERSION', '1.0.0');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');

// Security settings
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'your-secret-key-change-this-in-production');
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 days
define('PASSWORD_MIN_LENGTH', 8);

// Database settings
// WAMP defaults: user=root, password=empty
if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'budgie_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', $_ENV['DB_USER'] ?? 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');
}

// Stripe settings
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('STRIPE_WEBHOOK_SECRET', $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '');
define('STRIPE_PRICE_ID', $_ENV['STRIPE_PRICE_ID'] ?? ''); // Premium recurring price (9.99 €/mo)
define('PREMIUM_PRICE', 9.99);

// Email settings
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? 'tls'); // 'tls' (STARTTLS/587) or 'ssl' (implicit/465)
define('FROM_EMAIL', $_ENV['FROM_EMAIL'] ?? 'noreply@budgie.com');
define('FROM_NAME', $_ENV['FROM_NAME'] ?? 'Budgie');

// File upload settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Subscription limits
define('FREE_ACCOUNTS_LIMIT', 2);
define('FREE_EXPENSES_LIMIT', 7);
define('FREE_INCOMES_LIMIT', 2);

// Frequencies allowed for dépenses, revenus and exceptions (mirrors the SQL ENUM).
define('ALLOWED_FREQUENCIES', ['ponctuel', 'mensuel', 'bimensuel', 'trimestriel', 'semestriel', 'annuel']);

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Report and LOG everything, but never render it to the user. Silencing
    // error_reporting outright also silenced the logs, which is what made
    // production failures undiagnosable.
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Timezone
date_default_timezone_set('Europe/Paris');

// Include database configuration
require_once __DIR__ . '/database.php';

// Translation helpers. admin_login.php and change_language.php call these and
// nothing ever loaded the file, so both pages fataled on every request.
require_once __DIR__ . '/../helpers/language.php';

// Include CSRF Protection class
require_once __DIR__ . '/../security/CSRFProtection.php';

// Utility functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        redirect('index.php');
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user = fetchOne(
        "SELECT id, first_name, last_name, email, subscription_type FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
    
    return $user;
}

function formatCurrency($amount) {
    return '€' . number_format($amount, 2, ',', ' ');
}

/**
 * Human label for a recurrence. interval_months wins when set; the legacy
 * frequency ENUM is the fallback for rows written before that column existed.
 */
function formatFrequency($frequency, $intervalMonths = null) {
    if ($intervalMonths !== null && (int) $intervalMonths > 0) {
        $n = (int) $intervalMonths;
        return $n === 1 ? 'Tous les mois' : 'Tous les ' . $n . ' mois';
    }

    switch (strtolower(trim((string) $frequency))) {
        case 'mensuel':
        case 'recurrent':   return 'Tous les mois';
        case 'bimensuel':   return 'Tous les 2 mois';
        case 'trimestriel': return 'Tous les 3 mois';
        case 'semestriel':  return 'Tous les 6 mois';
        case 'annuel':      return 'Tous les 12 mois';
        default:            return 'Ponctuel';
    }
}

function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

/**
 * Build the externally-visible base URL (scheme://host:port) from the current
 * request, so redirects back from Stripe land on the same host the user used
 * (works for localhost, a VM IP like 192.168.x.x:8082, or a real domain).
 * Falls back to APP_URL when there is no request context.
 */
function getBaseUrl() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        return rtrim(APP_URL, '/');
    }
    $https  = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
           || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
           || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    return ($https ? 'https' : 'http') . '://' . $host;
}

function redirect($url) {
    // If absolute URL, redirect as-is
    if (preg_match('#^https?://#i', $url)) {
        header("Location: $url");
        exit();
    }
    // Determine project base path by locating '/public' in the script name
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $publicPos = strpos($script, '/public');
    if ($publicPos !== false) {
        $basePath = substr($script, 0, $publicPos);
    } else {
        // Fallback: take first level directory (e.g., /PA) or empty
        $parts = explode('/', trim($script, '/'));
        $basePath = isset($parts[0]) && $parts[0] !== '' ? '/' . $parts[0] : '';
    }

    // Ensure basePath is empty string for root
    if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
        $basePath = '';
    }

    // Build path relative to current host to avoid incorrect hostnames from server links
    if (strpos($url, 'public/') === 0) {
        $path = '/' . ltrim($url, '/');
    } else {
        $path = rtrim($basePath, '/') . '/public/' . ltrim($url, '/');
    }

    header("Location: $path");
    exit();
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

function checkSubscriptionLimits($userId, $type, $accountId = null) {
    $user = fetchOne("SELECT subscription_type FROM users WHERE id = ?", [$userId]);

    if ($user['subscription_type'] === 'premium') {
        return true; // No limits for premium users
    }

    switch ($type) {
        case 'accounts':
            $count = fetchOne("SELECT COUNT(*) as count FROM accounts WHERE user_id = ? AND deleted_at IS NULL", [$userId])['count'];
            return $count < FREE_ACCOUNTS_LIMIT;

        // Dépenses and revenus are capped PER ACCOUNT, not per user: the free
        // plan allows FREE_EXPENSES_LIMIT dépenses and FREE_INCOMES_LIMIT
        // revenus on each of the user's accounts.
        case 'expenses':
            $count = fetchOne("SELECT COUNT(*) as count FROM expenses WHERE user_id = ? AND account_id = ? AND deleted_at IS NULL", [$userId, (int) $accountId])['count'];
            return $count < FREE_EXPENSES_LIMIT;

        case 'incomes':
            $count = fetchOne("SELECT COUNT(*) as count FROM incomes WHERE user_id = ? AND account_id = ? AND deleted_at IS NULL", [$userId, (int) $accountId])['count'];
            return $count < FREE_INCOMES_LIMIT;

        default:
            return false;
    }
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    // is_string() first: a posted csrf_token[] would otherwise reach hash_equals()
    // as an array and raise an uncaught TypeError in every protected action.
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
    $key = "rate_limit_{$identifier}";
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $timeWindow];
    }
    
    $rateLimit = $_SESSION[$key];
    
    if (time() > $rateLimit['reset_time']) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $timeWindow];
        return true;
    }
    
    if ($rateLimit['count'] >= $maxAttempts) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}
?>
