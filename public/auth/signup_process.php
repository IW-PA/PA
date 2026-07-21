<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('signup.php');
}

$first_name = sanitizeInput($_POST['first_name'] ?? '');
$last_name = sanitizeInput($_POST['last_name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate input
$errors = [];

if (empty($first_name)) {
    $errors[] = 'Le prénom est requis.';
}

if (empty($last_name)) {
    $errors[] = 'Le nom est requis.';
}

if (empty($email)) {
    $errors[] = 'L\'email est requis.';
} elseif (!validateEmail($email)) {
    $errors[] = 'Adresse email invalide.';
}

if (empty($password)) {
    $errors[] = 'Le mot de passe est requis.';
} elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
    $errors[] = 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères.';
} else {
    // Check password strength
    $hasLower = preg_match('/[a-z]/', $password);
    $hasUpper = preg_match('/[A-Z]/', $password);
    $hasNumber = preg_match('/\d/', $password);

    if (!$hasLower || !$hasNumber) {
        $errors[] = 'Le mot de passe doit contenir au moins une lettre minuscule et un chiffre.';
    }
}

if ($password !== $confirm_password) {
    $errors[] = 'Les mots de passe ne correspondent pas.';
}

// Check if terms accepted
if (!isset($_POST['terms']) || $_POST['terms'] !== 'on') {
    $errors[] = 'Vous devez accepter les conditions d\'utilisation.';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    redirect('signup.php');
}

// Check rate limiting
if (!checkRateLimit("signup_{$email}")) {
    setFlashMessage('error', 'Trop de tentatives d\'inscription. Veuillez réessayer plus tard.');
    redirect('signup.php');
}

try {
    // Check if email already exists
    $existingUser = fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    
    if ($existingUser) {
        // Do not reveal that the address is already registered (account enumeration):
        // respond exactly like a fresh signup, without creating a duplicate account.
        ActivityLogger::log(null, 'auth.signup_duplicate', 'user', (int) $existingUser['id'], ['email' => $email]);
        setFlashMessage('success', 'Inscription réussie ! Un email de confirmation a été envoyé à ' . $email . '. Cliquez sur le lien pour activer votre compte.');
        redirect('verify_notice.php?email=' . urlencode($email));
    }

    // Hash password
    $passwordHash = hashPassword($password);

    // Create user
    $userId = insertRecord('users', [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'password_hash' => $passwordHash,
        'subscription_type' => 'free',
        'status' => 'active'
    ]);

    if (!$userId) {
        throw new Exception('Failed to create user');
    }

    // Create default account
    insertRecord('accounts', [
        'user_id' => $userId,
        'name' => 'Compte Principal',
        'description' => 'Votre compte principal',
        'balance' => 0.00,
        'interest_rate' => 0.00,
        'tax_rate' => 0.00
    ]);

    // Email verification: the account stays unverified and the user is NOT logged in
    // until they confirm via the emailed link.
    require_once SRC_PATH . '/services/EmailVerificationService.php';
    EmailVerificationService::createAndSend($userId, $email, $first_name);

    ActivityLogger::log($userId, 'auth.signup', 'user', $userId, ['email' => $email]);

    setFlashMessage('success', 'Inscription réussie ! Un email de confirmation a été envoyé à ' . $email . '. Cliquez sur le lien pour activer votre compte.');
    redirect('verify_notice.php?email=' . urlencode($email));

} catch (Exception $e) {
    error_log("Signup error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
    redirect('signup.php');
}
?>
