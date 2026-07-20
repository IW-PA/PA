<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/SubscriptionService.php';

/**
 * Regression guard for the "duplicate premium invoice" bug.
 *
 * A single subscription payment must produce exactly ONE row in
 * subscription_payments, even if the same Stripe reference is delivered more
 * than once (webhook retry, or invoice.paid + invoice.payment_succeeded).
 */
class SubscriptionPaymentTest extends TestCase
{
    private const REF = 'test_dedup_ref_regression';

    private function reset(): void
    {
        executeQuery("DELETE FROM subscription_payments WHERE stripe_payment_intent_id = ?", [self::REF]);
    }

    public function testRecordPaymentIsIdempotentForSameStripeReference()
    {
        $user   = fetchOne("SELECT id FROM users LIMIT 1");
        $userId = $user ? (int) $user['id'] : 1;

        $this->reset();
        // Same invoice payment delivered twice (e.g. Stripe retries the webhook).
        SubscriptionService::recordPayment($userId, 9.99, 'eur', 'succeeded', self::REF);
        SubscriptionService::recordPayment($userId, 9.99, 'eur', 'succeeded', self::REF);

        $rows  = fetchAll("SELECT id FROM subscription_payments WHERE stripe_payment_intent_id = ?", [self::REF]);
        $count = count($rows);
        $this->reset();

        $this->assertEquals(1, $count, "Expected exactly one payment row per Stripe reference, got {$count}");
    }
}
