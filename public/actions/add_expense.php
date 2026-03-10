<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('expenses.php');
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('expenses.php');
}

$name = sanitizeInput($_POST['name'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$account_id = intval($_POST['account_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$frequency = sanitizeInput($_POST['frequency'] ?? '');
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

// Validate input
$errors = [];

if (empty($name)) {
    $errors[] = 'Le nom de la dépense est requis.';
}

if ($account_id <= 0) {
    $errors[] = 'Veuillez sélectionner un compte.';
}

if ($amount <= 0) {
    $errors[] = 'Le montant doit être supérieur à 0.';
}

if (empty($frequency)) {
    $errors[] = 'Veuillez sélectionner une fréquence.';
}

if (empty($start_date)) {
    $errors[] = 'La date de début est requise.';
}

// Validate dates
if (!empty($start_date) && !strtotime($start_date)) {
    $errors[] = 'Date de début invalide.';
}

if (!empty($end_date) && !strtotime($end_date)) {
    $errors[] = 'Date de fin invalide.';
}

if (!empty($start_date) && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
    $errors[] = 'La date de début doit être antérieure à la date de fin.';
}

// Check subscription limits
if (!checkSubscriptionLimits($_SESSION['user_id'], 'expenses')) {
    $errors[] = 'Vous avez atteint la limite de dépenses pour votre abonnement gratuit. Passez au Premium pour créer plus de dépenses.';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    redirect('expenses.php');
}

try {
    // Check if account belongs to user
    $account = fetchOne(
        "SELECT id FROM accounts WHERE id = ? AND user_id = ?",
        [$account_id, $_SESSION['user_id']]
    );

    if (!$account) {
        setFlashMessage('error', 'Compte non trouvé ou accès non autorisé.');
        redirect('expenses.php');
    }

    // Create expense
    $expenseId = insertRecord('expenses', [
        'user_id' => $_SESSION['user_id'],
        'account_id' => $account_id,
        'name' => $name,
        'description' => $description,
        'amount' => $amount,
        'frequency' => $frequency,
        'start_date' => $start_date,
        'end_date' => !empty($end_date) ? $end_date : null,
        'is_active' => true
    ]);

    if ($expenseId) {
        ActivityLogger::log(
            (int) $_SESSION['user_id'],
            'expense.create',
            'expense',
            (int) $expenseId,
            [
                'name' => $name,
                'amount' => $amount,
                'account_id' => $account_id,
            ]
        );
        setFlashMessage('success', 'Dépense créée avec succès !');
    } else {
        throw new Exception('Failed to create expense');
    }

} catch (Exception $e) {
    error_log("Add expense error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la création de la dépense.');
}

redirect('expenses.php');
?>
