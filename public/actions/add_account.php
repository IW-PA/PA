<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('accounts.php');
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('accounts.php');
}

$name = sanitizeInput($_POST['name'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$interest_rate = floatval($_POST['interest_rate'] ?? 0);
$tax_rate = floatval($_POST['tax_rate'] ?? 0);

// Validate input
$errors = [];

if (empty($name)) {
    $errors[] = 'Le nom du compte est requis.';
}

if ($interest_rate < 0 || $interest_rate > 100) {
    $errors[] = 'Le taux de rémunération doit être entre 0 et 100%.';
}

if ($tax_rate < 0 || $tax_rate > 100) {
    $errors[] = 'Le taux d\'imposition doit être entre 0 et 100%.';
}

// Check subscription limits
if (!checkSubscriptionLimits($_SESSION['user_id'], 'accounts')) {
    $errors[] = 'Vous avez atteint la limite de comptes pour votre abonnement gratuit. Passez au Premium pour créer plus de comptes.';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    redirect('accounts.php');
}

try {
    // Check if account name already exists for this user
    $existingAccount = fetchOne(
        "SELECT id FROM accounts WHERE user_id = ? AND name = ?",
        [$_SESSION['user_id'], $name]
    );

    if ($existingAccount) {
        setFlashMessage('error', 'Un compte avec ce nom existe déjà.');
        redirect('accounts.php');
    }

    // Create account
    $accountId = insertRecord('accounts', [
        'user_id' => $_SESSION['user_id'],
        'name' => $name,
        'description' => $description,
        'balance' => 0.00,
        'interest_rate' => $interest_rate,
        'tax_rate' => $tax_rate
    ]);

    if ($accountId) {
        ActivityLogger::log(
            (int) $_SESSION['user_id'],
            'account.create',
            'account',
            (int) $accountId,
            ['name' => $name]
        );
        setFlashMessage('success', 'Compte créé avec succès !');
    } else {
        throw new Exception('Failed to create account');
    }

} catch (Exception $e) {
    error_log("Add account error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la création du compte.');
}

redirect('accounts.php');
?>
