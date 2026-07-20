<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/middleware/AdminGuard.php';

AdminGuard::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('admin.php');
}

$user_id      = intval($_POST['user_id'] ?? 0);
$first_name   = trim($_POST['first_name'] ?? '');
$last_name    = trim($_POST['last_name'] ?? '');
$email        = strtolower(trim($_POST['email'] ?? ''));
$subscription = trim($_POST['subscription'] ?? 'free');

if ($user_id <= 0 || empty($first_name) || empty($last_name) || empty($email)) {
    setFlashMessage('error', 'Champs invalides.');
    redirect('admin.php');
}

try {
    $existing = fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
    if ($existing) {
        setFlashMessage('error', 'Cet email appartient à un autre compte.');
        redirect('admin.php');
    }

    $subType = (strtolower($subscription) === 'payant' || strtolower($subscription) === 'premium') ? 'premium' : 'free';
    executeQuery(
        "UPDATE users SET first_name = ?, last_name = ?, email = ?, subscription_type = ? WHERE id = ?",
        [$first_name, $last_name, $email, $subType, $user_id]
    );

    setFlashMessage('success', 'Utilisateur mis à jour avec succès !');

} catch (Exception $e) {
    error_log("Admin edit user error: " . $e->getMessage());
    setFlashMessage('error', 'Erreur lors de la mise à jour de l\'utilisateur.');
}

redirect('admin.php');
