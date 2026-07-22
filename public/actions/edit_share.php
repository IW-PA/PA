<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('sharing.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('sharing.php');
}

$share_id = intval($_POST['share_id'] ?? 0);
// Guard the type before trim(): access_type[] would raise a TypeError here,
// outside the try/catch below, and surface as a blank 500.
$access_type = is_string($_POST['access_type'] ?? null) ? trim($_POST['access_type']) : '';

if ($share_id <= 0) {
    setFlashMessage('error', 'ID de partage invalide.');
    redirect('sharing.php');
}

// Whitelist against the ENUM: an unexpected value is rejected by MySQL in
// strict mode instead of being silently coerced.
if (!in_array($access_type, ['read_only', 'read_write'], true)) {
    setFlashMessage('error', "Type d'accès invalide.");
    redirect('sharing.php');
}

try {
    $share = fetchOne("SELECT id FROM account_shares WHERE id = ? AND owner_id = ?", [$share_id, $_SESSION['user_id']]);
    if (!$share) {
        setFlashMessage('error', 'Partage non trouvé.');
        redirect('sharing.php');
    }

    executeQuery("UPDATE account_shares SET access_type = ? WHERE id = ?", [$access_type, $share_id]);

    ActivityLogger::log((int) $_SESSION['user_id'], 'account_share.update', 'account_share', $share_id);

    setFlashMessage('success', 'Permissions de partage mises à jour !');

} catch (Exception $e) {
    error_log("Edit share error: " . $e->getMessage());
    setFlashMessage('error', 'Erreur lors de la modification du partage.');
}

redirect('sharing.php');
