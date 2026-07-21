<?php
require_once __DIR__ . '/../src/config/config.php';

$email      = sanitizeInput($_GET['email'] ?? '');
$page_title = 'Vérifiez votre email';
include SRC_PATH . '/includes/header.php';
?>
<div class="auth-layout">
    <div class="auth-container">
        <div class="text-center mb-4">
            <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">🐦 Budgie</h1>
            <h2>📧 Vérifiez votre email</h2>
            <p class="text-muted">
                Un lien de confirmation a été envoyé<?php echo $email !== '' ? ' à <strong>' . htmlspecialchars($email) . '</strong>' : ''; ?>.
                Cliquez dessus pour activer votre compte, puis connectez-vous.
            </p>
        </div>

        <form method="POST" action="auth/resend_verification.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <button type="submit" class="btn btn-secondary btn-lg" style="width:100%;margin-bottom:1rem;">
                Renvoyer l'email de vérification
            </button>
        </form>

        <div class="text-center">
            <a href="login.php" style="color: var(--primary-color);">Retour à la connexion</a>
        </div>
    </div>
</div>
<?php include SRC_PATH . '/includes/footer.php'; ?>
