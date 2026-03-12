<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/security/CSRFProtection.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin_login.php');
}

// Check CSRF
if (!CSRFProtection::validate($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid CSRF token.');
    redirect('../admin_login.php');
}

$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    setFlashMessage('error', 'Please fill all fields.');
    redirect('../admin_login.php');
}

try {
    // fetch user including role
    $user = fetchOne("SELECT id, first_name, last_name, email, password_hash, role, status FROM users WHERE email = ?", [$email]);

    if (!$user) {
        ActivityLogger::log(null, 'admin.auth_failed', 'user', null, ['email' => $email]);
        setFlashMessage('error', 'Invalid credentials.');
        redirect('../admin_login.php');
    }

    // verify role is admin
    if (($user['role'] ?? 'user') !== 'admin') {
        ActivityLogger::log((int) $user['id'], 'admin.auth_failed_not_admin', 'user', (int) $user['id']);
        setFlashMessage('error', 'Account is not an administrator.');
        redirect('../admin_login.php');
    }

    // check active
    if (($user['status'] ?? 'active') !== 'active') {
        setFlashMessage('error', 'Admin account is not active.');
        redirect('../admin_login.php');
    }

    if (!verifyPassword($password, $user['password_hash'])) {
        ActivityLogger::log((int) $user['id'], 'admin.auth_failed', 'user', (int) $user['id']);
        setFlashMessage('error', 'Invalid credentials.');
        redirect('../admin_login.php');
    }

    // success: create admin session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_status'] = 'active';

    // update last_login
    executeQuery("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

    ActivityLogger::log((int) $user['id'], 'admin.login', 'user', (int) $user['id']);

    // Redirect to admin panel
    header('Location: ../admin.php');
    exit;

} catch (Exception $e) {
    error_log('Admin login error: ' . $e->getMessage());
    setFlashMessage('error', 'An error occurred.');
    redirect('../admin_login.php');
}
