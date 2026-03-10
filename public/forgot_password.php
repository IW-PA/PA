<?php
require_once __DIR__ . '/../src/config/config.php';
$page_title = 'Mot de passe oublié';
include SRC_PATH . '/includes/header.php';
?>

<div class="auth-layout">
    <div class="auth-container">
        <div class="text-center mb-4">
            <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">🔑 Réinitialisation</h1>
            <p class="text-muted">Entrez votre email pour recevoir un lien de réinitialisation</p>
        </div>

        <form method="POST" action="auth/forgot_password_process.php">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" required placeholder="votre@email.com">
                <small class="form-hint">Nous vous enverrons un lien pour réinitialiser votre mot de passe</small>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: 1rem;">
                Envoyer le lien
            </button>

            <div class="text-center">
                <a href="login.php" style="color: var(--primary-color); font-size: 0.875rem;">← Retour à la connexion</a>
            </div>
        </form>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
