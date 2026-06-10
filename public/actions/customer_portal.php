<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/SubscriptionService.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('subscriptions.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('subscriptions.php');
}

if (STRIPE_SECRET_KEY === '') {
    setFlashMessage('error', 'Le paiement n\'est pas encore configuré.');
    redirect('subscriptions.php');
}

$userId = (int) $_SESSION['user_id'];
$user   = fetchOne("SELECT stripe_customer_id FROM users WHERE id = ?", [$userId]);

if (!$user || empty($user['stripe_customer_id'])) {
    setFlashMessage('error', 'Aucun abonnement Stripe associé à ce compte.');
    redirect('subscriptions.php');
}

try {
    $stripe = new StripeClient();
    $portal = $stripe->createBillingPortalSession(
        $user['stripe_customer_id'],
        rtrim(APP_URL, '/') . '/public/subscriptions.php'
    );
    header('Location: ' . $portal['url']);
    exit;
} catch (Exception $e) {
    error_log('Stripe portal error: ' . $e->getMessage());
    setFlashMessage('error', 'Impossible d\'ouvrir le portail de gestion. Veuillez réessayer.');
    redirect('subscriptions.php');
}
