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

if ($income_id <= 0) {
    setFlashMessage('error', 'Identifiant de revenu invalide.');
    redirect('incomes.php');
}

try {
    // Verify income belongs to user
    $income = fetchOne(
        "SELECT id, name, amount, account_id FROM incomes WHERE id = ? AND user_id = ? AND deleted_at IS NULL",
        [$income_id, $_SESSION['user_id']]
    );

    if (!$income) {
        setFlashMessage('error', 'Revenu non trouvé ou accès non autorisé.');
        redirect('incomes.php');
    }

    // Soft delete
    $updated = executeQuery(
        "UPDATE incomes SET deleted_at = NOW() WHERE id = ? AND user_id = ?",
        [$income_id, $_SESSION['user_id']]
    );

    if ($updated) {
        executeQuery(
            "UPDATE accounts SET balance = balance - ? WHERE id = ?",
            [(float)$income['amount'], (int)$income['account_id']]
        );
    }

    ActivityLogger::log(
        (int) $_SESSION['user_id'],
        'income.delete',
        'income',
        $income_id,
        ['name' => $income['name']]
    );

    setFlashMessage('success', 'Revenu supprimé avec succès !');

} catch (Exception $e) {
    error_log("Delete income error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la suppression du revenu.');
}

redirect('incomes.php');
?>
