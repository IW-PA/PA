<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/SubscriptionService.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('subscriptions.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('subscriptions.php');
}

if (STRIPE_SECRET_KEY === '' || STRIPE_PRICE_ID === '') {
    setFlashMessage('error', 'Le paiement n\'est pas encore configuré. Contactez l\'administrateur.');
    redirect('subscriptions.php');
}

$userId = (int) $_SESSION['user_id'];

// Already premium? Nothing to buy.
$user = fetchOne("SELECT subscription_type FROM users WHERE id = ?", [$userId]);
if ($user && $user['subscription_type'] === 'premium') {
    setFlashMessage('error', 'Vous êtes déjà abonné au plan Premium.');
    redirect('subscriptions.php');
}

try {
    $stripe     = new StripeClient();
    $customerId = SubscriptionService::ensureCustomer($userId, $stripe);

    $base      = getBaseUrl();
    $successUrl = $base . '/public/subscriptions.php?checkout=success&session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl  = $base . '/public/subscriptions.php?checkout=cancel';

    $session = $stripe->createSubscriptionCheckout(
        $customerId,
        STRIPE_PRICE_ID,
        $successUrl,
        $cancelUrl,
        ['user_id' => (string) $userId]
    );

    ActivityLogger::log($userId, 'subscription.checkout_started', 'user', $userId);

    header('Location: ' . $session['url']);
    exit;
} catch (Exception $e) {
    error_log('Stripe checkout error: ' . $e->getMessage());
    setFlashMessage('error', 'Impossible de démarrer le paiement. Veuillez réessayer.');
    redirect('subscriptions.php');
}
