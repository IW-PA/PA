<?php
// Centralizes subscription state changes so the success-redirect handler and the
// webhook handler stay consistent and idempotent.

require_once __DIR__ . '/StripeClient.php';

class SubscriptionService
{
    /**
     * Return the user's Stripe customer id, creating the Stripe customer if needed.
     */
    public static function ensureCustomer(int $userId, StripeClient $stripe): string
    {
        $user = fetchOne(
            "SELECT id, email, first_name, last_name, stripe_customer_id FROM users WHERE id = ?",
            [$userId]
        );
        if (!$user) {
            throw new Exception('User not found.');
        }
        if (!empty($user['stripe_customer_id'])) {
            return $user['stripe_customer_id'];
        }

        $customer = $stripe->createCustomer(
            $user['email'],
            trim($user['first_name'] . ' ' . $user['last_name']),
            ['user_id' => (string) $userId]
        );
        $customerId = $customer['id'];
        updateRecord('users', ['stripe_customer_id' => $customerId], 'id = :id', ['id' => $userId]);
        return $customerId;
    }

    /**
     * Mark a user as premium. Idempotent: safe to call from both the redirect and webhook.
     */
    public static function activatePremium(int $userId, ?string $subscriptionId, ?int $currentPeriodEnd): void
    {
        $endDate = $currentPeriodEnd
            ? date('Y-m-d H:i:s', $currentPeriodEnd)
            : date('Y-m-d H:i:s', strtotime('+1 month'));

        updateRecord('users', [
            'subscription_type'       => 'premium',
            'subscription_start_date' => date('Y-m-d H:i:s'),
            'subscription_end_date'   => $endDate,
            'stripe_subscription_id'  => $subscriptionId,
        ], 'id = :id', ['id' => $userId]);
    }

    /**
     * Revert a user to the free plan (cancellation / failed renewal).
     */
    public static function downgradeToFree(int $userId): void
    {
        updateRecord('users', [
            'subscription_type'      => 'free',
            'subscription_end_date'  => null,
            'stripe_subscription_id' => null,
        ], 'id = :id', ['id' => $userId]);
    }

    /**
     * Record a payment, avoiding duplicates by Stripe reference.
     */
    public static function recordPayment(int $userId, float $amount, string $currency, string $status, ?string $stripeRef): void
    {
        if ($stripeRef) {
            $existing = fetchOne(
                "SELECT id FROM subscription_payments WHERE stripe_payment_intent_id = ?",
                [$stripeRef]
            );
            if ($existing) {
                return;
            }
        }
        insertRecord('subscription_payments', [
            'user_id'                  => $userId,
            'amount'                   => $amount,
            'currency'                 => strtoupper($currency),
            'stripe_payment_intent_id' => $stripeRef,
            'status'                   => $status,
        ]);
    }
}
