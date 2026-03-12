<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    setFlashMessage('error', 'Veuillez remplir tous les champs.');
    redirect('login.php');
}

if (!validateEmail($email)) {
    setFlashMessage('error', 'Adresse email invalide.');
    redirect('login.php');
}

// Check rate limiting
if (!checkRateLimit("login_{$email}")) {
    setFlashMessage('error', 'Trop de tentatives de connexion. Veuillez réessayer plus tard.');
    redirect('login.php');
}

try {
    // Get user from database
    $user = fetchOne(
        "SELECT id, first_name, last_name, email, password_hash, subscription_type, role, status FROM users WHERE email = ?",
        [$email]
    );

if (!$user) {
    ActivityLogger::log(null, 'auth.login_failed', 'user', null, ['email' => $email]);
    setFlashMessage('error', 'Email ou mot de passe incorrect.');
    redirect('login.php');
}

    // Check if account is active
    if ($user['status'] !== 'active') {
        setFlashMessage('error', 'Votre compte a été suspendu. Contactez le support.');
        redirect('login.php');
    }

    // Verify password
if (!verifyPassword($password, $user['password_hash'])) {
    ActivityLogger::log((int) $user['id'], 'auth.login_failed', 'user', (int) $user['id']);
    setFlashMessage('error', 'Email ou mot de passe incorrect.');
    redirect('login.php');
}

    // Update last login
    executeQuery(
        "UPDATE users SET last_login = NOW() WHERE id = ?",
        [$user['id']]
    );

    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_subscription'] = $user['subscription_type'];
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    $_SESSION['user_status'] = $user['status'] ?? 'active';

    // Generate new session token
    $sessionToken = generateToken();
    $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';
    $sessionDuration = $rememberMe ? 30 : 7; // 30 days if remember me, 7 days otherwise

    executeQuery(
        "INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))",
        [$user['id'], $sessionToken, $sessionDuration]
    );
    $_SESSION['session_token'] = $sessionToken;

    // Set cookie if remember me is checked
    if ($rememberMe) {
        setcookie('remember_token', $sessionToken, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }

ActivityLogger::log(
    (int) $user['id'],
    'auth.login',
    'user',
    (int) $user['id'],
    ['email' => $user['email']]
);

    setFlashMessage('success', 'Connexion réussie ! Bienvenue, ' . $user['first_name'] . ' !');
    redirect('index.php');

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue. Veuillez réessayer.');
    redirect('login.php');
}
?>
