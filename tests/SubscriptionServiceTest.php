<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/SubscriptionService.php';

/**
 * Subscription state transitions must be correct and idempotent, since both the
 * checkout redirect and the Stripe webhook drive them.
 */
class SubscriptionServiceTest extends TestCase
{
    private function makeUser(): int
    {
        return (int) insertRecord('users', [
            'first_name'        => 'Sub',
            'last_name'         => 'Test',
            'email'             => 'sub-test-' . uniqid('', true) . '@example.invalid',
            'password_hash'     => 'x',
            'subscription_type' => 'free',
        ]);
    }

    private function dropUser(int $id): void
    {
        executeQuery("DELETE FROM users WHERE id = ?", [$id]);
    }

    public function testActivatePremiumSetsPlanEndDateAndSubscriptionId()
    {
        $uid = $this->makeUser();
        $end = time() + 3600 * 24 * 30;
        SubscriptionService::activatePremium($uid, 'sub_unit_test', $end);

        $u = fetchOne("SELECT subscription_type, subscription_end_date, stripe_subscription_id FROM users WHERE id = ?", [$uid]);
        $this->dropUser($uid);

        $this->assertEquals('premium', $u['subscription_type']);
        $this->assertEquals('sub_unit_test', $u['stripe_subscription_id']);
        $this->assertEquals(date('Y-m-d H:i:s', $end), $u['subscription_end_date']);
    }

    public function testDowngradeToFreeClearsSubscription()
    {
        $uid = $this->makeUser();
        SubscriptionService::activatePremium($uid, 'sub_unit_test', time() + 3600);
        SubscriptionService::downgradeToFree($uid);

        $u = fetchOne("SELECT subscription_type, subscription_end_date, stripe_subscription_id FROM users WHERE id = ?", [$uid]);
        $this->dropUser($uid);

        $this->assertEquals('free', $u['subscription_type']);
        $this->assertSame(null, $u['stripe_subscription_id']);
        $this->assertSame(null, $u['subscription_end_date']);
    }

    public function testRecordPaymentIsIdempotentForSameStripeReference()
    {
        $uid = $this->makeUser();
        $ref = 'in_unit_test_' . uniqid('', true);
        SubscriptionService::recordPayment($uid, 9.99, 'eur', 'succeeded', $ref);
        SubscriptionService::recordPayment($uid, 9.99, 'eur', 'succeeded', $ref);

        $count = count(fetchAll("SELECT id FROM subscription_payments WHERE stripe_payment_intent_id = ?", [$ref]));
        $this->dropUser($uid); // cascades subscription_payments

        $this->assertEquals(1, $count, "same Stripe reference must yield exactly one payment row, got {$count}");
    }
}
