<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('forgot_password.php');
}

$email = sanitizeInput($_POST['email'] ?? '');

if (empty($email) || !validateEmail($email)) {
    setFlashMessage('error', 'Veuillez entrer une adresse email valide.');
    redirect('forgot_password.php');
}

// Check rate limiting
if (!checkRateLimit("password_reset_{$email}", 3, 3600)) {
    setFlashMessage('error', 'Trop de demandes de réinitialisation. Veuillez réessayer dans 1 heure.');
    redirect('forgot_password.php');
}

try {
    // Check if user exists
    $user = fetchOne("SELECT id, first_name, email FROM users WHERE email = ?", [$email]);
    
    // Always show success message for security (don't reveal if email exists)
    if (!$user) {
        ActivityLogger::log(null, 'auth.password_reset_unknown_email', 'user', null, ['email' => $email]);
        setFlashMessage('success', 'Si cette adresse email existe, un lien de réinitialisation a été envoyé.');
        redirect('login.php');
        exit;
    }
    
    // Generate reset token
    $token = generateToken(32);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Delete old tokens for this user
    executeQuery("DELETE FROM password_reset_tokens WHERE user_id = ?", [$user['id']]);
    
    // Insert new token
    executeQuery(
        "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)",
        [$user['id'], $token, $expiresAt]
    );
    
    // In real app: send email with reset link
    // For now, we'll just log it (for demo purposes)
    $resetLink = APP_URL . "/reset_password.php?token=" . $token;
    
    // Log the activity
    ActivityLogger::log(
        (int) $user['id'],
        'auth.password_reset_requested',
        'user',
        (int) $user['id'],
        ['email' => $email]
    );
    
    // For development: show the link in session (remove in production!)
    if (APP_ENV === 'development') {
        $_SESSION['dev_reset_link'] = $resetLink;
    }
    
    setFlashMessage('success', 'Un email avec un lien de réinitialisation a été envoyé à votre adresse.');
    redirect('login.php');
    
} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue. Veuillez réessayer.');
    redirect('forgot_password.php');
}
?>
