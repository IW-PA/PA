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

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = strtolower(trim($_POST['email'] ?? ''));

$errors = [];

if (empty($first_name)) {
    $errors[] = 'Le prénom est requis.';
}

if (empty($last_name)) {
    $errors[] = 'Le nom est requis.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Adresse email invalide.';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    redirect('profile.php');
}

try {
    // Check if email is already used by another account
    $existingUser = fetchOne(
        "SELECT id FROM users WHERE email = ? AND id != ?",
        [$email, $_SESSION['user_id']]
    );

    if ($existingUser) {
        setFlashMessage('error', 'Cette adresse email est déjà utilisée par un autre compte.');
        redirect('profile.php');
    }

    // Update user record
    $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, updated_at = NOW() WHERE id = ?";
    executeQuery($sql, [$first_name, $last_name, $email, $_SESSION['user_id']]);

    // Update session variables
    $_SESSION['user_name']  = $first_name . ' ' . $last_name;
    $_SESSION['user_email'] = $email;

    ActivityLogger::log(
        (int) $_SESSION['user_id'],
        'profile.update',
        'user',
        (int) $_SESSION['user_id'],
        ['email' => $email]
    );

    setFlashMessage('success', 'Profil mis à jour avec succès !');

} catch (Exception $e) {
    error_log("Update profile error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la mise à jour du profil.');
}

redirect('profile.php');
