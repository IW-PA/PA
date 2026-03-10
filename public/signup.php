<?php
require_once __DIR__ . '/../src/config/config.php';
$page_title = 'Inscription';
include SRC_PATH . '/includes/header.php';
?>

<div class="auth-layout">
    <div class="auth-container">
        <div class="text-center mb-4">
            <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">🐦 Budgie</h1>
            <p class="text-muted">Créez votre compte personnel</p>
        </div>

        <form method="POST" action="auth/signup_process.php">
            <div class="form-group">
                <label for="first_name" class="form-label">Prénom</label>
                <input type="text" id="first_name" name="first_name" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="last_name" class="form-label">Nom</label>
                <input type="text" id="last_name" name="last_name" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required minlength="8">
                <small class="form-hint">Minimum 8 caractères (lettres, chiffres et symboles recommandés)</small>
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

            <div class="form-group" style="display: flex; align-items: flex-start; gap: 0.5rem;">
                <input type="checkbox" id="terms" name="terms" required style="margin-top: 0.25rem;">
                <label for="terms" style="font-size: 0.875rem; color: var(--gray-600); flex: 1;">
                    J'accepte les <a href="terms.php" target="_blank" style="color: var(--primary-color);">Conditions d'utilisation</a> et la <a href="privacy.php" target="_blank" style="color: var(--primary-color);">Politique de confidentialité</a>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: 1rem;">
                Créer le compte
            </button>

            <div class="text-center">
                <p class="text-muted">Déjà un compte ? <a href="login.php" style="color: var(--primary-color);">Se connecter</a></p>
            </div>
        </form>
    </div>
</div>

<script>
// Password strength checker
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
    let feedback = [];

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
    passwordStrengthText.textContent = 'Force du mot de passe : ' + text;
    passwordStrengthText.style.color = color;
});

// Password match checker
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

// Email domain suggestion
const emailInput = document.getElementById('email');
const commonDomains = ['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.fr', 'yahoo.com', 'icloud.com', 'orange.fr', 'free.fr', 'laposte.net'];
const disposableDomains = ['tempmail.com', 'guerrillamail.com', 'mailinator.com', '10minutemail.com', 'throwaway.email'];

emailInput.addEventListener('blur', function() {
    const email = this.value.toLowerCase();
    const parts = email.split('@');

    if (parts.length !== 2) return;

    const domain = parts[1];

    // Check for disposable email
    if (disposableDomains.some(d => domain.includes(d))) {
        alert('⚠️ Les adresses email temporaires ne sont pas autorisées.');
        this.value = '';
        return;
    }

    // Check for common typos
    const suggestions = {
        'gmial.com': 'gmail.com',
        'gmai.com': 'gmail.com',
        'gmil.com': 'gmail.com',
        'hotmial.com': 'hotmail.com',
        'hotmal.com': 'hotmail.com',
        'outloo.com': 'outlook.com',
        'outlok.com': 'outlook.com',
        'yahou.fr': 'yahoo.fr',
        'yahou.com': 'yahoo.com'
    };

    if (suggestions[domain]) {
        if (confirm(`Voulez-vous dire ${parts[0]}@${suggestions[domain]} ?`)) {
            this.value = parts[0] + '@' + suggestions[domain];
        }
    }
});
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
