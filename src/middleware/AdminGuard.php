<?php
/**
 * AdminGuard Middleware
 * Ensures only administrators can access protected routes
 */

class AdminGuard {
    
    /**
     * Check if the current user is an admin
     * @return bool
     */
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Check if the current user is active
     * @return bool
     */
    public static function isActive() {
        return isset($_SESSION['user_status']) && $_SESSION['user_status'] === 'active';
    }
    
    /**
     * Require admin access or redirect to 403
     * @param bool $checkActive Also check if user is active
     */
    public static function requireAdmin($checkActive = true) {
        if (!isLoggedIn()) {
            redirect('login.php');
            exit();
        }
        
        if ($checkActive && !self::isActive()) {
            self::showForbidden('Your account has been deactivated. Please contact support.');
            exit();
        }
        
        if (!self::isAdmin()) {
            self::showForbidden('Access Denied: Administrator privileges required.');
            exit();
        }
    }
    
    /**
     * Display 403 Forbidden page
     * @param string $message Custom error message
     */
    private static function showForbidden($message = 'Access Denied') {
        http_response_code(403);
        include __DIR__ . '/../views/errors/403.php';
        exit();
    }
    
    /**
     * Log admin activity for audit trail
     * @param string $action Action performed
     * @param string $targetType Type of target (user, expense, etc.)
     * @param int|null $targetId ID of the target
     * @param string|null $details Additional details
     */
    public static function logActivity($action, $targetType, $targetId = null, $details = null) {
        if (!self::isAdmin()) {
            return false;
        }
        
        try {
            $sql = "INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details, ip_address) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            return executeQuery($sql, [
                $_SESSION['user_id'],
                $action,
                $targetType,
                $targetId,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Admin activity log error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify CSRF token for admin actions
     * @param string|null $token Token to verify
     * @return bool
     */
    public static function verifyCsrfToken($token = null) {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
        }
        
        if (!$token) {
            return false;
        }
        
        return validateCSRFToken($token);
    }
    
    /**
     * Require valid CSRF token or show error
     */
    public static function requireCsrfToken() {
        if (!self::verifyCsrfToken()) {
            http_response_code(403);
            die('CSRF token validation failed. Please refresh and try again.');
        }
    }
}
