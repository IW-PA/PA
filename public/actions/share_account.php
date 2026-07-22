<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';
require_once SRC_PATH . '/services/ShareInvitationService.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('sharing.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('sharing.php');
}

$account_id  = intval($_POST['account_id'] ?? 0);
// Guard the type before trim(): email[] would raise a TypeError outside the
// try/catch below and surface as a blank 500.
$share_email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';

if ($account_id <= 0 || empty($share_email) || !filter_var($share_email, FILTER_VALIDATE_EMAIL)) {
    setFlashMessage('error', 'Veuillez remplir correctement tous les champs.');
    redirect('sharing.php');
}

try {
    $account = fetchOne("SELECT id, name FROM accounts WHERE id = ? AND user_id = ? AND deleted_at IS NULL", [$account_id, $_SESSION['user_id']]);
    if (!$account) {
        setFlashMessage('error', 'Compte non trouvé.');
        redirect('sharing.php');
    }

    if ($share_email === $_SESSION['user_email']) {
        setFlashMessage('error', 'Vous ne pouvez pas partager un compte avec vous-même.');
        redirect('sharing.php');
    }

    // users are hard-deleted (AdminService::deleteUser), so there is no deleted_at column on this table
    $targetUser = fetchOne("SELECT id FROM users WHERE email = ?", [$share_email]);
    $targetUserId = $targetUser ? (int)$targetUser['id'] : null;

    $invitationToken = bin2hex(random_bytes(32));

    // The share stays PENDING until the invited address accepts it from the
    // emailed link: nobody gets sight of an account without agreeing to it.
    executeQuery(
        "INSERT INTO account_shares (account_id, owner_id, shared_with_email, shared_with_user_id, status, invitation_token) VALUES (?, ?, ?, ?, 'pending', ?)",
        [$account_id, $_SESSION['user_id'], $share_email, $targetUserId, $invitationToken]
    );

    ActivityLogger::log(
        (int) $_SESSION['user_id'],
        'account.share',
        'account_share',
        $account_id,
        ['shared_with' => $share_email]
    );

    $ownerName = trim(($_SESSION['user_name'] ?? '') !== '' ? $_SESSION['user_name'] : ($_SESSION['user_email'] ?? 'Un utilisateur'));
    $sent = ShareInvitationService::send($share_email, $account['name'], $ownerName, $invitationToken);

    // Flash messages are escaped at render time (header.php), so no escaping here.
    if ($sent) {
        setFlashMessage('success', 'Invitation envoyée à ' . $share_email . '. Le partage sera actif dès son acceptation.');
    } elseif (APP_ENV === 'development') {
        // No SMTP in dev: surface the link so the flow stays testable locally.
        setFlashMessage('success', 'Invitation créée. [DEV] Aucun SMTP configuré, lien d\'invitation : '
            . ShareInvitationService::buildLink($invitationToken));
    } else {
        setFlashMessage('error', 'Le partage a été créé mais l\'email d\'invitation n\'a pas pu être envoyé.');
    }

} catch (Exception $e) {
    error_log("Share account error: " . $e->getMessage());
    setFlashMessage('error', 'Erreur lors du partage du compte.');
}

redirect('sharing.php');
