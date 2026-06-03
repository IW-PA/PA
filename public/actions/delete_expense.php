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

if ($expense_id <= 0) {
    setFlashMessage('error', 'Identifiant de dépense invalide.');
    redirect('expenses.php');
}

try {
    // Verify expense belongs to user
    $expense = fetchOne(
        "SELECT id, name FROM expenses WHERE id = ? AND user_id = ? AND deleted_at IS NULL",
        [$expense_id, $_SESSION['user_id']]
    );

    if (!$expense) {
        setFlashMessage('error', 'Dépense non trouvée ou accès non autorisé.');
        redirect('expenses.php');
    }

    // Soft delete
    $updated = executeQuery(
        "UPDATE expenses SET deleted_at = NOW() WHERE id = ? AND user_id = ?",
        [$expense_id, $_SESSION['user_id']]
    );

    ActivityLogger::log(
        (int) $_SESSION['user_id'],
        'expense.delete',
        'expense',
        $expense_id,
        ['name' => $expense['name']]
    );

    setFlashMessage('success', 'Dépense supprimée avec succès !');

} catch (Exception $e) {
    error_log("Delete expense error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la suppression de la dépense.');
}

redirect('expenses.php');
?>
