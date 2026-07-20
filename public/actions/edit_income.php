<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('incomes.php');
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('incomes.php');
}

$income_id = intval($_POST['income_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$account_id = intval($_POST['account_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$frequency = trim($_POST['frequency'] ?? '');
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

$errors = [];

if ($income_id <= 0) {
    $errors[] = 'Identifiant de revenu invalide.';
}
if (empty($name)) {
    $errors[] = 'Le nom du revenu est requis.';
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
    redirect('incomes.php');
}

try {
    // Verify income belongs to user
    $income = fetchOne(
        "SELECT id, amount, account_id FROM incomes WHERE id = ? AND user_id = ? AND deleted_at IS NULL",
        [$income_id, $_SESSION['user_id']]
    );

    if (!$income) {
        setFlashMessage('error', 'Revenu non trouvé ou accès non autorisé.');
        redirect('incomes.php');
    }

    // Verify account belongs to user
    $account = fetchOne(
        "SELECT id FROM accounts WHERE id = ? AND user_id = ? AND deleted_at IS NULL",
        [$account_id, $_SESSION['user_id']]
    );

    if (!$account) {
        setFlashMessage('error', 'Compte non trouvé ou accès non autorisé.');
        redirect('incomes.php');
    }

    $updated = updateRecord(
        'incomes',
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
        ['id' => $income_id, 'user_id' => $_SESSION['user_id']]
    );

    if ($updated !== false) {
        // Reverse old income: subtract old amount from old account
        executeQuery(
            "UPDATE accounts SET balance = balance - ? WHERE id = ?",
            [(float)$income['amount'], (int)$income['account_id']]
        );
        // Apply new income: add new amount to new account
        executeQuery(
            "UPDATE accounts SET balance = balance + ? WHERE id = ?",
            [$amount, $account_id]
        );

        ActivityLogger::log(
            (int) $_SESSION['user_id'],
            'income.update',
            'income',
            $income_id,
            ['name' => $name, 'amount' => $amount]
        );
        setFlashMessage('success', 'Revenu mis à jour avec succès !');
    } else {
        throw new Exception('Failed to update income');
    }

} catch (Exception $e) {
    error_log("Edit income error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la modification du revenu.');
}

redirect('incomes.php');
?>
