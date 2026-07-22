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

if ($share_id <= 0) {
    setFlashMessage('error', 'ID de partage invalide.');
    redirect('sharing.php');
}

try {
    // Only the owner of the share may revoke it. The same message is used for a
    // missing share and for someone else's share, so this cannot be used to
    // probe which share ids exist.
    $share = fetchOne(
        "SELECT s.id, s.shared_with_email, a.name AS account_name
         FROM account_shares s
         JOIN accounts a ON a.id = s.account_id
         WHERE s.id = ? AND s.owner_id = ? AND s.status != 'revoked'",
        [$share_id, $_SESSION['user_id']]
    );

    if (!$share) {
        setFlashMessage('error', 'Partage non trouvé ou accès non autorisé.');
        redirect('sharing.php');
    }

    // The row is kept for traceability; both listings in sharing.php filter
    // revoked shares out. owner_id is repeated here so the write can never be
    // re-targeted even if this query and the check above ever drift apart.
    executeQuery(
        "UPDATE account_shares SET status = 'revoked', responded_at = NOW() WHERE id = ? AND owner_id = ?",
        [$share_id, $_SESSION['user_id']]
    );

    ActivityLogger::log(
        (int) $_SESSION['user_id'],
        'account_share.revoke',
        'account_share',
        $share_id,
        ['shared_with' => $share['shared_with_email'], 'account' => $share['account_name']]
    );

    setFlashMessage('success', 'Accès révoqué avec succès.');

} catch (Exception $e) {
    error_log("Revoke share error: " . $e->getMessage());
    setFlashMessage('error', 'Erreur lors de la révocation du partage.');
}

redirect('sharing.php');
