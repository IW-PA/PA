<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('exceptions.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('exceptions.php');
}

$exception_id = intval($_POST['exception_id'] ?? 0);

if ($exception_id <= 0) {
    setFlashMessage('error', 'Exception invalide.');
    redirect('exceptions.php');
}

try {
    // Same message for "missing" and "not yours" so the endpoint cannot be
    // used to probe which exception ids exist.
    $exception = fetchOne(
        "SELECT id, name FROM exceptions WHERE id = ? AND user_id = ?",
        [$exception_id, $_SESSION['user_id']]
    );

    if (!$exception) {
        setFlashMessage('error', 'Exception non trouvée ou accès non autorisé.');
        redirect('exceptions.php');
    }

    // The exceptions table has no soft-delete column: removing the row simply
    // restores the dépense/revenu to its original amount in the forecast.
    executeQuery("DELETE FROM exceptions WHERE id = ? AND user_id = ?", [$exception_id, $_SESSION['user_id']]);

    ActivityLogger::log(
        (int) $_SESSION['user_id'],
        'exception.delete',
        'exception',
        $exception_id,
        ['name' => $exception['name']]
    );

    setFlashMessage('success', 'Exception supprimée avec succès !');

} catch (Exception $e) {
    error_log("Delete exception error: " . $e->getMessage());
    setFlashMessage('error', 'Erreur lors de la suppression de l\'exception.');
}

redirect('exceptions.php');
