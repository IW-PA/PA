<?php
require_once __DIR__ . '/../src/config/config.php';
$page_title = 'Connexion';
include SRC_PATH . '/includes/header.php';
?>

<div class="auth-layout">
    <div class="auth-container">
        <div class="text-center mb-4">
            <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">🐦 Budgie</h1>
            <p class="text-muted">Connectez-vous à votre espace personnel</p>
        </div>

<form method="POST" action="auth/login_process.php">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="remember_me" value="1">
                    <span style="font-size: 0.875rem;">Se souvenir de moi</span>
                </label>
                <a href="forgot_password.php" style="font-size: 0.875rem; color: var(--primary-color);">Mot de passe oublié ?</a>
            </div>

            <?php if (APP_ENV === 'development' && isset($_SESSION['dev_reset_link'])): ?>
            <div style="background: #fef3c7; border: 1px solid #fbbf24; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1rem;">
                <strong style="color: #92400e;">🔧 Mode Développement</strong>
                <p style="font-size: 0.875rem; margin: 0.5rem 0 0 0; color: #92400e;">
                    Lien de réinitialisation : <a href="<?php echo htmlspecialchars($_SESSION['dev_reset_link']); ?>" style="color: #2563eb;">Cliquez ici</a>
                </p>
                <?php unset($_SESSION['dev_reset_link']); ?>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: 1rem;">
                Se connecter
            </button>

            <div class="text-center">
                <p class="text-muted">Pas encore de compte ? <a href="signup.php" style="color: var(--primary-color);">Créer un compte</a></p>
            </div>
        </form>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
