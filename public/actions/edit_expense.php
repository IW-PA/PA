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

$expense_id = intval($_POST['expense_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$account_id = intval($_POST['account_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$frequency = trim($_POST['frequency'] ?? '');
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

$errors = [];

if ($expense_id <= 0) {
    $errors[] = 'Identifiant de dépense invalide.';
}
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
if (!empty($start_date) && !strtotime($start_date)) {
    $errors[] = 'Date de début invalide.';
}
if (!empty($end_date) && !strtotime($end_date)) {
    $errors[] = 'Date de fin invalide.';
}
if (!empty($start_date) && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
    $errors[] = 'La date de début doit être antérieure à la date de fin.';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    redirect('expenses.php');
}

try {
    // Verify expense belongs to user
    $expense = fetchOne(
        "SELECT id FROM expenses WHERE id = ? AND user_id = ? AND deleted_at IS NULL",
        [$expense_id, $_SESSION['user_id']]
    );

    if (!$expense) {
        setFlashMessage('error', 'Dépense non trouvée ou accès non autorisé.');
        redirect('expenses.php');
    }

    // Verify account belongs to user
    $account = fetchOne(
        "SELECT id FROM accounts WHERE id = ? AND user_id = ? AND deleted_at IS NULL",
        [$account_id, $_SESSION['user_id']]
    );

    if (!$account) {
        setFlashMessage('error', 'Compte non trouvé ou accès non autorisé.');
        redirect('expenses.php');
    }

    $updated = updateRecord(
        'expenses',
        [
            'account_id'  => $account_id,
            'name'        => $name,
            'description' => $description,
            'amount'      => $amount,
            'frequency'   => $frequency,
            'start_date'  => $start_date,
            'end_date'    => !empty($end_date) ? $end_date : null,
        ],
        'id = :id AND user_id = :user_id',
        ['id' => $expense_id, 'user_id' => $_SESSION['user_id']]
    );

    if ($updated !== false) {
        ActivityLogger::log(
            (int) $_SESSION['user_id'],
            'expense.update',
            'expense',
            $expense_id,
            ['name' => $name, 'amount' => $amount]
        );
        setFlashMessage('success', 'Dépense mise à jour avec succès !');
    } else {
        throw new Exception('Failed to update expense');
    }

} catch (Exception $e) {
    error_log("Edit expense error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la modification de la dépense.');
}

redirect('expenses.php');
?>
