<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('profile.php');
}

$current_password     = $_POST['current_password'] ?? '';
$new_password         = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

$errors = [];

if (empty($current_password)) {
    $errors[] = 'Le mot de passe actuel est requis.';
}

if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
    $errors[] = 'Le nouveau mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères.';
}

if ($new_password !== $confirm_new_password) {
    $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    redirect('profile.php');
}

try {
    // Get stored password hash
    $user = fetchOne("SELECT password_hash FROM users WHERE id = ?", [$_SESSION['user_id']]);

    if (!$user || !password_verify($current_password, $user['password_hash'])) {
        setFlashMessage('error', 'Le mot de passe actuel est incorrect.');
        redirect('profile.php');
    }

    // Hash new password and update
    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
    executeQuery("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?", [$new_hash, $_SESSION['user_id']]);

    ActivityLogger::log(
        (int) $_SESSION['user_id'],
        'password.change',
        'user',
        (int) $_SESSION['user_id']
    );

    setFlashMessage('success', 'Votre mot de passe a été modifié avec succès !');

} catch (Exception $e) {
    error_log("Change password error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors du changement de mot de passe.');
}

redirect('profile.php');
