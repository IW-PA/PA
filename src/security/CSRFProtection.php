<?php
// CSRF Protection Helper Class

class CSRFProtection
{
    /**
     * Generate and return a hidden input field with CSRF token
     * 
     * @return string HTML input field
     */
    public static function getTokenField(): string
    {
        $token = generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Get the current CSRF token value
     * 
     * @return string CSRF token
     */
    public static function getToken(): string
    {
        return generateCSRFToken();
    }
    
    /**
     * Validate CSRF token from request
     * 
     * @param string $token Token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate(string $token): bool
    {
        return validateCSRFToken($token);
    }
}
