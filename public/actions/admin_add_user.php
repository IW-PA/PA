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
$subscription = trim($_POST['subscription'] ?? 'free');

if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    setFlashMessage('error', 'Tous les champs sont obligatoires.');
    redirect('admin.php');
}

try {
    $existing = fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        setFlashMessage('error', 'Un utilisateur avec cette adresse email existe déjà.');
        redirect('admin.php');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    executeQuery(
        "INSERT INTO users (first_name, last_name, email, password_hash, subscription_type, status) VALUES (?, ?, ?, ?, ?, 'active')",
        [$first_name, $last_name, $email, $hash, strtolower($subscription) === 'payant' ? 'premium' : 'free']
    );

    setFlashMessage('success', 'Utilisateur créé avec succès !');

} catch (Exception $e) {
    error_log("Admin add user error: " . $e->getMessage());
    setFlashMessage('error', 'Erreur lors de la création de l\'utilisateur.');
}

redirect('admin.php');
