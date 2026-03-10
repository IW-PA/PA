<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$token = sanitizeInput($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($token) || empty($password) || empty($confirmPassword)) {
    setFlashMessage('error', 'Tous les champs sont requis.');
    redirect('reset_password.php?token=' . urlencode($token));
}

if (strlen($password) < PASSWORD_MIN_LENGTH) {
    setFlashMessage('error', 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères.');
    redirect('reset_password.php?token=' . urlencode($token));
}

if ($password !== $confirmPassword) {
    setFlashMessage('error', 'Les mots de passe ne correspondent pas.');
    redirect('reset_password.php?token=' . urlencode($token));
}

try {
    // Verify token
    $resetToken = fetchOne(
        "SELECT rt.*, u.email 
         FROM password_reset_tokens rt 
         JOIN users u ON rt.user_id = u.id 
         WHERE rt.token = ? AND rt.used_at IS NULL AND rt.expires_at > NOW()",
        [$token]
    );
    
    if (!$resetToken) {
        setFlashMessage('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
        redirect('login.php');
    }
    
    // Hash new password
    $passwordHash = hashPassword($password);
    
    // Update user password
    executeQuery(
        "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?",
        [$passwordHash, $resetToken['user_id']]
    );
    
    // Mark token as used
    executeQuery(
        "UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?",
        [$resetToken['id']]
    );
    
    // Log activity
    ActivityLogger::log(
        (int) $resetToken['user_id'],
        'auth.password_reset_completed',
        'user',
        (int) $resetToken['user_id'],
        ['email' => $resetToken['email']]
    );
    
    setFlashMessage('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    redirect('login.php');
    
} catch (Exception $e) {
    error_log("Password reset completion error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue. Veuillez réessayer.');
    redirect('reset_password.php?token=' . urlencode($token));
}
?>
