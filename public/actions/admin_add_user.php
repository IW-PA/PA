<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/middleware/AdminGuard.php';
require_once SRC_PATH . '/services/AdminService.php';

AdminGuard::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin.php');
}
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('admin.php');
}

$first_name   = trim($_POST['first_name'] ?? '');
$last_name    = trim($_POST['last_name'] ?? '');
$email        = strtolower(trim($_POST['email'] ?? ''));
$password     = $_POST['password'] ?? '';
$subscription = ($_POST['subscription'] ?? 'free') === 'premium' ? 'premium' : 'free';
$role         = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

if ($first_name === '' || $last_name === '' || $email === '' || $password === '') {
    setFlashMessage('error', 'Tous les champs sont obligatoires.');
    redirect('admin.php');
}
if (!validateEmail($email)) {
    setFlashMessage('error', 'Adresse email invalide.');
    redirect('admin.php');
}
if (strlen($password) < PASSWORD_MIN_LENGTH) {
    setFlashMessage('error', 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères.');
    redirect('admin.php');
}

try {
    if (fetchOne("SELECT id FROM users WHERE email = ?", [$email])) {
        setFlashMessage('error', 'Un utilisateur avec cette adresse email existe déjà.');
        redirect('admin.php');
    }

    // Admin-created accounts are active and already email-verified.
    executeQuery(
        "INSERT INTO users (first_name, last_name, email, password_hash, subscription_type, role, status, email_verified_at)
         VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())",
        [$first_name, $last_name, $email, hashPassword($password), $subscription, $role]
    );
    AdminGuard::logActivity('create_user', 'user', (int) getDB()->lastInsertId(), 'Created ' . $email);
    setFlashMessage('success', 'Utilisateur créé (actif, vérifié).');
} catch (Throwable $e) {
    error_log('Admin add user error: ' . $e->getMessage());
    setFlashMessage('error', 'Erreur lors de la création de l\'utilisateur.');
}

redirect('admin.php');
