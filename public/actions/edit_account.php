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

$account_id = intval($_POST['account_id'] ?? 0);
$name = sanitizeInput($_POST['name'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$interest_rate = floatval($_POST['interest_rate'] ?? 0);
$tax_rate = floatval($_POST['tax_rate'] ?? 0);

// Validate input
$errors = [];

if ($account_id <= 0) {
    $errors[] = 'ID de compte invalide.';
}

if (empty($name)) {
    $errors[] = 'Le nom du compte est requis.';
}

if ($interest_rate < 0 || $interest_rate > 100) {
    $errors[] = 'Le taux de rémunération doit être entre 0 et 100%.';
}

if ($tax_rate < 0 || $tax_rate > 100) {
    $errors[] = 'Le taux d\'imposition doit être entre 0 et 100%.';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    redirect('accounts.php');
}

try {
    // Check if account belongs to user
    $account = fetchOne(
        "SELECT id FROM accounts WHERE id = ? AND user_id = ?",
        [$account_id, $_SESSION['user_id']]
    );

    if (!$account) {
        setFlashMessage('error', 'Compte non trouvé ou accès non autorisé.');
        redirect('accounts.php');
    }

    // Check if account name already exists for this user (excluding current account)
    $existingAccount = fetchOne(
        "SELECT id FROM accounts WHERE user_id = ? AND name = ? AND id != ?",
        [$_SESSION['user_id'], $name, $account_id]
    );

    if ($existingAccount) {
        setFlashMessage('error', 'Un compte avec ce nom existe déjà.');
        redirect('accounts.php');
    }

    // Update account
    $rowsAffected = updateRecord('accounts', [
        'name' => $name,
        'description' => $description,
        'interest_rate' => $interest_rate,
        'tax_rate' => $tax_rate
    ], 'id = :id', ['id' => $account_id]);

    if ($rowsAffected > 0) {
        ActivityLogger::log(
            (int) $_SESSION['user_id'],
            'account.update',
            'account',
            $account_id,
            ['name' => $name]
        );
        setFlashMessage('success', 'Compte mis à jour avec succès !');
    } else {
        setFlashMessage('error', 'Aucune modification apportée.');
    }

} catch (Exception $e) {
    error_log("Edit account error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la mise à jour du compte.');
}

redirect('accounts.php');
?>
