<?php
// Stripe webhook receiver. Handles subscription lifecycle events so the database
// stays in sync on renewals, cancellations and async payment confirmations.
// Configure the endpoint URL and signing secret in the Stripe Dashboard / CLI.

require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/SubscriptionService.php';

// Always respond quickly; never render HTML here.
header('Content-Type: application/json');

$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = StripeClient::constructWebhookEvent($payload, $sigHeader, STRIPE_WEBHOOK_SECRET);
} catch (Exception $e) {
    error_log('Stripe webhook rejected: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'invalid signature']);
    exit;
}

$type   = $event['type'] ?? '';
$object = $event['data']['object'] ?? [];

/** Resolve the local user id from event metadata or the Stripe customer id. */
$resolveUserId = function (array $object): ?int {
    $uid = $object['metadata']['user_id'] ?? ($object['client_reference_id'] ?? null);
    if ($uid) {
        return (int) $uid;
    }
    $customerId = $object['customer'] ?? null;
    if ($customerId) {
        $row = fetchOne("SELECT id FROM users WHERE stripe_customer_id = ?", [$customerId]);
        if ($row) {
            return (int) $row['id'];
        }
    }
    return null;
};

try {
    switch ($type) {
        case 'checkout.session.completed':
            if (($object['mode'] ?? '') === 'subscription' && ($object['payment_status'] ?? '') === 'paid') {
                $userId = $resolveUserId($object);
                if ($userId) {
                    SubscriptionService::activatePremium($userId, $object['subscription'] ?? null, null);
                }
            }
            break;

        case 'invoice.paid':
        case 'invoice.payment_succeeded':
            $userId = $resolveUserId($object);
            if ($userId) {
                $periodEnd = $object['lines']['data'][0]['period']['end'] ?? ($object['period_end'] ?? null);
                SubscriptionService::activatePremium($userId, $object['subscription'] ?? null, $periodEnd ? (int) $periodEnd : null);
                $amount = isset($object['amount_paid']) ? $object['amount_paid'] / 100 : 0;
                SubscriptionService::recordPayment(
                    $userId,
                    (float) $amount,
                    $object['currency'] ?? 'eur',
                    'succeeded',
                    $object['id'] ?? null
                );
            }
            break;

        case 'invoice.payment_failed':
            $userId = $resolveUserId($object);
            if ($userId) {
                SubscriptionService::recordPayment(
                    $userId,
                    isset($object['amount_due']) ? $object['amount_due'] / 100 : 0,
                    $object['currency'] ?? 'eur',
                    'failed',
                    $object['id'] ?? null
                );
            }
            break;

        case 'customer.subscription.deleted':
            $userId = $resolveUserId($object);
            if ($userId) {
                SubscriptionService::downgradeToFree($userId);
            }
            break;
    }
} catch (Exception $e) {
    error_log('Stripe webhook handler error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'handler failure']);
    exit;
}

http_response_code(200);
echo json_encode(['received' => true]);
