<?php
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/EmailVerificationService.php';

// Consume the token BEFORE any output (verify() only writes to the DB, never echoes).
$userId = EmailVerificationService::verify($_GET['token'] ?? '');

$page_title = 'Vérification email';
include SRC_PATH . '/includes/header.php';
?>
<div class="auth-layout">
    <div class="auth-container">
        <div class="text-center mb-4">
            <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">🐦 Budgie</h1>
        </div>
        <?php if ($userId): ?>
            <div class="text-center">
                <h2>✅ Email confirmé</h2>
                <p class="text-muted">Votre adresse a bien été vérifiée. Vous pouvez maintenant vous connecter.</p>
                <a href="login.php" class="btn btn-primary btn-lg" style="width:100%;margin-top:1rem;">Se connecter</a>
            </div>
        <?php else: ?>
            <div class="text-center">
                <h2>❌ Lien invalide ou expiré</h2>
                <p class="text-muted">Ce lien de vérification n'est plus valide. Vous pouvez en demander un nouveau.</p>
                <a href="verify_notice.php" class="btn btn-secondary btn-lg" style="width:100%;margin-top:1rem;">Renvoyer un lien</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include SRC_PATH . '/includes/footer.php'; ?>
