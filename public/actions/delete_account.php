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

if ($account_id <= 0) {
    setFlashMessage('error', 'ID de compte invalide.');
    redirect('accounts.php');
}

try {
    // Check if account belongs to user
    $account = fetchOne(
        "SELECT id, name FROM accounts WHERE id = ? AND user_id = ?",
        [$account_id, $_SESSION['user_id']]
    );

    if (!$account) {
        setFlashMessage('error', 'Compte non trouvé ou accès non autorisé.');
        redirect('accounts.php');
    }

    // Check if account has transactions
    $transactionCount = fetchOne(
        "SELECT COUNT(*) as count FROM transactions WHERE account_id = ?",
        [$account_id]
    )['count'];

    if ($transactionCount > 0) {
        setFlashMessage('error', 'Ce compte ne peut pas être supprimé car il contient des transactions. Supprimez d\'abord toutes les transactions associées.');
        redirect('accounts.php');
    }

    // Check if account has active expenses or incomes
    $expenseCount = fetchOne(
        "SELECT COUNT(*) as count FROM expenses WHERE account_id = ? AND is_active = 1",
        [$account_id]
    )['count'];

    $incomeCount = fetchOne(
        "SELECT COUNT(*) as count FROM incomes WHERE account_id = ? AND is_active = 1",
        [$account_id]
    )['count'];

    if ($expenseCount > 0 || $incomeCount > 0) {
        setFlashMessage('error', 'Ce compte ne peut pas être supprimé car il est associé à des dépenses ou revenus actifs.');
        redirect('accounts.php');
    }

    // Delete account
    $rowsAffected = deleteRecord('accounts', 'id = :id', ['id' => $account_id]);

    if ($rowsAffected > 0) {
        ActivityLogger::log(
            (int) $_SESSION['user_id'],
            'account.delete',
            'account',
            $account_id,
            ['name' => $account['name']]
        );
        setFlashMessage('success', 'Compte "' . $account['name'] . '" supprimé avec succès !');
    } else {
        setFlashMessage('error', 'Erreur lors de la suppression du compte.');
    }

} catch (Exception $e) {
    error_log("Delete account error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la suppression du compte.');
}

redirect('accounts.php');
?>
