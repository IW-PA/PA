<?php
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/EmailVerificationService.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';

// IMPORTANT: only a real POST (the human clicking "Confirmer") consumes the
// single-use token. A GET — including email-scanner / link pre-fetch — never
// consumes it; it only shows the confirm button. This is what prevents the
// "link already expired" issue caused by mail gateways pre-fetching the URL.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validateCSRFToken($_POST['csrf_token'] ?? '') && EmailVerificationService::verify($token)) {
        $view = 'success';
    } else {
        $view = EmailVerificationService::peek($token) === 'already_verified' ? 'success' : 'error';
    }
} else {
    $state = EmailVerificationService::peek($token);
    $view  = $state === 'valid' ? 'confirm' : ($state === 'already_verified' ? 'success' : 'error');
}

$page_title = 'Vérification email';
include SRC_PATH . '/includes/header.php';
?>
<div class="auth-layout">
    <div class="auth-container">
        <div class="text-center mb-4"><h1 style="color: var(--primary-color); margin-bottom:.5rem;">🐦 Budgie</h1></div>
        <?php if ($view === 'confirm'): ?>
            <div class="text-center">
                <h2>📧 Confirmez votre email</h2>
                <p class="text-muted">Cliquez sur le bouton ci-dessous pour activer votre compte.</p>
                <form method="POST" action="verify_email.php">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES); ?>">
                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%; margin-top:1rem;">Confirmer mon email</button>
                </form>
            </div>
        <?php elseif ($view === 'success'): ?>
            <div class="text-center">
                <h2>✅ Email confirmé</h2>
                <p class="text-muted">Votre adresse est vérifiée. Vous pouvez maintenant vous connecter.</p>
                <a href="login.php" class="btn btn-primary btn-lg" style="width:100%; margin-top:1rem;">Se connecter</a>
            </div>
        <?php else: ?>
            <div class="text-center">
                <h2>❌ Lien invalide ou expiré</h2>
                <p class="text-muted">Ce lien de vérification n'est plus valide. Vous pouvez en demander un nouveau.</p>
                <a href="verify_notice.php" class="btn btn-secondary btn-lg" style="width:100%; margin-top:1rem;">Renvoyer un lien</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include SRC_PATH . '/includes/footer.php'; ?>
