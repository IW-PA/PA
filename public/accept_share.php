<?php
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/ShareInvitationService.php';

// Type-guarded: token[]=x would otherwise reach peek(string $token) as an array.
$token = '';
foreach ([$_GET['token'] ?? null, $_POST['token'] ?? null] as $candidate) {
    if (is_string($candidate) && $candidate !== '') {
        $token = $candidate;
        break;
    }
}

// IMPORTANT: only a real POST (the invited person clicking a button) answers the
// invitation. A GET — including an email scanner pre-fetching the link — merely
// displays it, so an invitation is never silently accepted or declined.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decision = is_string($_POST['decision'] ?? null) ? $_POST['decision'] : '';
    // Only an explicit, valid decision is acted on: never default to accepting.
    if (in_array($decision, ['accepted', 'declined'], true)
        && validateCSRFToken($_POST['csrf_token'] ?? '')
        && isLoggedIn()
    ) {
        ShareInvitationService::respond(
            $token,
            (int) $_SESSION['user_id'],
            (string) ($_SESSION['user_email'] ?? ''),
            $decision
        );
    }
}

$share = ShareInvitationService::peek($token);

$owner_name   = $share ? trim(($share['owner_first_name'] ?? '') . ' ' . ($share['owner_last_name'] ?? '')) : '';
$account_name = $share['account_name'] ?? '';
$invited_mail = strtolower(trim((string) ($share['shared_with_email'] ?? '')));
$my_mail      = strtolower(trim((string) ($_SESSION['user_email'] ?? '')));

// Identity is resolved BEFORE the status views: every view that names the account
// is reserved for the invited address, so merely holding the link reveals nothing.
if (!$share) {
    $view = 'invalid';
} elseif (!isLoggedIn()) {
    $view = 'login_required';
} elseif ($my_mail === '' || $my_mail !== $invited_mail) {
    $view = 'wrong_account';
} elseif ($share['status'] === 'accepted') {
    $view = 'accepted';
} elseif ($share['status'] === 'declined') {
    $view = 'declined';
} elseif ($share['status'] === 'revoked') {
    $view = 'revoked';
} else {
    $view = 'confirm';
}

$page_title = 'Invitation de partage';
include SRC_PATH . '/includes/header.php';
?>
<div class="auth-layout">
    <div class="auth-container">
        <div class="text-center mb-4"><h1 style="color: var(--primary-color); margin-bottom:.5rem;">🐦 Budgie</h1></div>

        <?php if ($view === 'confirm'): ?>
            <div class="text-center">
                <h2>🤝 Invitation de partage</h2>
                <p class="text-muted">
                    <strong><?php echo htmlspecialchars($owner_name); ?></strong> souhaite partager avec vous
                    le compte <strong><?php echo htmlspecialchars($account_name); ?></strong>.
                </p>
                <p class="text-muted">Vous y aurez accès <strong>en lecture seule</strong>.</p>
                <form method="POST" action="accept_share.php">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES); ?>">
                    <button type="submit" name="decision" value="accepted" class="btn btn-primary btn-lg" style="width:100%; margin-top:1rem;">Accepter le partage</button>
                    <button type="submit" name="decision" value="declined" class="btn btn-secondary" style="width:100%; margin-top:.5rem;">Refuser</button>
                </form>
            </div>

        <?php elseif ($view === 'accepted'): ?>
            <div class="text-center">
                <h2>✅ Partage accepté</h2>
                <p class="text-muted">
                    Le compte <strong><?php echo htmlspecialchars($account_name); ?></strong> est disponible
                    dans « Comptes Partagés avec Moi », en lecture seule.
                </p>
                <a href="sharing.php" class="btn btn-primary btn-lg" style="width:100%; margin-top:1rem;">Voir mes partages</a>
            </div>

        <?php elseif ($view === 'declined'): ?>
            <div class="text-center">
                <h2>Invitation refusée</h2>
                <p class="text-muted">Vous avez refusé ce partage. Aucune donnée ne vous est accessible.</p>
                <a href="index.php" class="btn btn-secondary btn-lg" style="width:100%; margin-top:1rem;">Retour à l'accueil</a>
            </div>

        <?php elseif ($view === 'revoked'): ?>
            <div class="text-center">
                <h2>❌ Partage révoqué</h2>
                <p class="text-muted">Le propriétaire a révoqué ce partage&nbsp;: l'invitation n'est plus valable.</p>
                <a href="index.php" class="btn btn-secondary btn-lg" style="width:100%; margin-top:1rem;">Retour à l'accueil</a>
            </div>

        <?php elseif ($view === 'login_required'): ?>
            <div class="text-center">
                <h2>🔒 Connectez-vous pour répondre</h2>
                <p class="text-muted">
                    Cette invitation a été envoyée à <strong><?php echo htmlspecialchars($invited_mail); ?></strong>.
                    Connectez-vous avec cette adresse (ou créez votre compte), puis rouvrez ce lien.
                </p>
                <a href="login.php" class="btn btn-primary btn-lg" style="width:100%; margin-top:1rem;">Se connecter</a>
                <a href="signup.php" class="btn btn-secondary" style="width:100%; margin-top:.5rem;">Créer un compte</a>
            </div>

        <?php elseif ($view === 'wrong_account'): ?>
            <div class="text-center">
                <h2>Mauvais compte</h2>
                <p class="text-muted">
                    Cette invitation est destinée à <strong><?php echo htmlspecialchars($invited_mail); ?></strong>,
                    mais vous êtes connecté avec <strong><?php echo htmlspecialchars($my_mail); ?></strong>.
                </p>
                <a href="logout.php" class="btn btn-primary btn-lg" style="width:100%; margin-top:1rem;">Changer de compte</a>
            </div>

        <?php else: ?>
            <div class="text-center">
                <h2>❌ Invitation introuvable</h2>
                <p class="text-muted">Ce lien d'invitation n'est pas valide.</p>
                <a href="index.php" class="btn btn-secondary btn-lg" style="width:100%; margin-top:1rem;">Retour à l'accueil</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include SRC_PATH . '/includes/footer.php'; ?>
