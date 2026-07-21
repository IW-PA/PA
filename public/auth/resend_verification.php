<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/EmailVerificationService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('verify_notice.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Session expirée, veuillez réessayer.');
    redirect('verify_notice.php');
}

$email = sanitizeInput($_POST['email'] ?? '');
if (empty($email) || !validateEmail($email)) {
    setFlashMessage('error', 'Adresse email invalide.');
    redirect('verify_notice.php');
}

if (!checkRateLimit("verify_resend_{$email}", 3, 3600)) {
    setFlashMessage('error', 'Trop de demandes. Veuillez réessayer dans 1 heure.');
    redirect('verify_notice.php?email=' . urlencode($email));
}

// Resend only for an existing, still-unverified account. Generic message either way (no enumeration).
$user = fetchOne("SELECT id, first_name, email_verified_at FROM users WHERE email = ?", [$email]);
if ($user && empty($user['email_verified_at'])) {
    EmailVerificationService::createAndSend((int) $user['id'], $email, $user['first_name']);
}

setFlashMessage('success', 'Si un compte non vérifié existe pour cette adresse, un nouvel email de vérification a été envoyé.');
redirect('verify_notice.php?email=' . urlencode($email));
