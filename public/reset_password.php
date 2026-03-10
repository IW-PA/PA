<?php
require_once __DIR__ . '/../src/config/config.php';
$page_title = 'Réinitialiser le mot de passe';

$token = sanitizeInput($_GET['token'] ?? '');

if (empty($token)) {
    setFlashMessage('error', 'Lien de réinitialisation invalide.');
    redirect('login.php');
}

// Verify token
$resetToken = fetchOne(
    "SELECT rt.*, u.email, u.first_name 
     FROM password_reset_tokens rt 
     JOIN users u ON rt.user_id = u.id 
     WHERE rt.token = ? AND rt.used_at IS NULL AND rt.expires_at > NOW()",
    [$token]
);

if (!$resetToken) {
    setFlashMessage('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
    redirect('login.php');
}

include SRC_PATH . '/includes/header.php';
?>

<div class="auth-layout">
    <div class="auth-container">
        <div class="text-center mb-4">
            <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">🔒 Nouveau mot de passe</h1>
            <p class="text-muted">Créez un nouveau mot de passe pour votre compte</p>
        </div>

        <div style="background: var(--gray-100); padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
            <p style="margin: 0; font-size: 0.875rem;">
                <strong>Compte :</strong> <?php echo htmlspecialchars($resetToken['email']); ?>
            </p>
        </div>

        <form method="POST" action="auth/reset_password_process.php">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required minlength="8">
                <small class="form-hint">Minimum 8 caractères</small>
                <div id="password-strength" style="margin-top: 0.5rem; display: none;">
                    <div style="height: 4px; background: var(--gray-200); border-radius: 2px; overflow: hidden;">
                        <div id="password-strength-bar" style="height: 100%; width: 0%; transition: all 0.3s;"></div>
                    </div>
                    <small id="password-strength-text" style="display: block; margin-top: 0.25rem;"></small>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                <small id="password-match-message" style="display: none; margin-top: 0.25rem;"></small>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: 1rem;">
                Réinitialiser le mot de passe
            </button>

            <div class="text-center">
                <a href="login.php" style="color: var(--primary-color); font-size: 0.875rem;">← Retour à la connexion</a>
            </div>
        </form>
    </div>
</div>

<script>
// Same password strength checker as signup
const passwordInput = document.getElementById('password');
const passwordStrengthDiv = document.getElementById('password-strength');
const passwordStrengthBar = document.getElementById('password-strength-bar');
const passwordStrengthText = document.getElementById('password-strength-text');
const confirmPasswordInput = document.getElementById('confirm_password');
const passwordMatchMessage = document.getElementById('password-match-message');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    
    if (password.length === 0) {
        passwordStrengthDiv.style.display = 'none';
        return;
    }
    
    passwordStrengthDiv.style.display = 'block';
    
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.length >= 12) strength += 25;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
    if (/\d/.test(password)) strength += 15;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
    
    let color, text;
    if (strength < 40) {
        color = '#ef4444';
        text = 'Faible';
    } else if (strength < 70) {
        color = '#f59e0b';
        text = 'Moyen';
    } else {
        color = '#10b981';
        text = 'Fort';
    }
    
    passwordStrengthBar.style.width = strength + '%';
    passwordStrengthBar.style.background = color;
    passwordStrengthText.textContent = 'Force : ' + text;
    passwordStrengthText.style.color = color;
});

confirmPasswordInput.addEventListener('input', function() {
    const password = passwordInput.value;
    const confirmPassword = this.value;
    
    if (confirmPassword.length === 0) {
        passwordMatchMessage.style.display = 'none';
        return;
    }
    
    passwordMatchMessage.style.display = 'block';
    
    if (password === confirmPassword) {
        passwordMatchMessage.textContent = '✓ Les mots de passe correspondent';
        passwordMatchMessage.style.color = '#10b981';
    } else {
        passwordMatchMessage.textContent = '✗ Les mots de passe ne correspondent pas';
        passwordMatchMessage.style.color = '#ef4444';
    }
});
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
