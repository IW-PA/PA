<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/StripeClient.php';

/**
 * Security-critical: StripeClient::constructWebhookEvent must accept only
 * genuine, fresh, untampered events. A hole here lets anyone forge premium
 * subscriptions.
 */
class StripeWebhookSignatureTest extends TestCase
{
    private const SECRET = 'whsec_unit_test_secret';

    private function sign(string $payload, int $t, string $secret = self::SECRET): string
    {
        return 't=' . $t . ',v1=' . hash_hmac('sha256', $t . '.' . $payload, $secret);
    }

    private function threw(callable $fn): bool
    {
        try {
            $fn();
            return false;
        } catch (Throwable $e) {
            return true;
        }
    }

    public function testValidSignatureReturnsDecodedEvent()
    {
        $payload = '{"type":"invoice.payment_succeeded","data":{"object":{"id":"in_1"}}}';
        $t = time();
        $event = StripeClient::constructWebhookEvent($payload, $this->sign($payload, $t), self::SECRET);
        $this->assertEquals('invoice.payment_succeeded', $event['type']);
        $this->assertEquals('in_1', $event['data']['object']['id']);
    }

    public function testTamperedPayloadIsRejected()
    {
        $payload = '{"type":"invoice.paid","amount":999}';
        $t = time();
        $sig = $this->sign($payload, $t);
        $tampered = '{"type":"invoice.paid","amount":1}';
        $this->assertSame(true, $this->threw(function () use ($tampered, $sig) {
            StripeClient::constructWebhookEvent($tampered, $sig, self::SECRET);
        }));
    }

    public function testWrongSecretIsRejected()
    {
        $payload = '{"type":"x"}';
        $t = time();
        $sig = $this->sign($payload, $t, 'whsec_other');
        $this->assertSame(true, $this->threw(function () use ($payload, $sig) {
            StripeClient::constructWebhookEvent($payload, $sig, self::SECRET);
        }));
    }

    public function testStaleTimestampIsRejected()
    {
        $payload = '{"type":"x"}';
        $t = time() - 4000; // well beyond the 300s tolerance
        $sig = $this->sign($payload, $t);
        $this->assertSame(true, $this->threw(function () use ($payload, $sig) {
            StripeClient::constructWebhookEvent($payload, $sig, self::SECRET);
        }));
    }

    public function testMissingHeaderAndEmptySecretAreRejected()
    {
        $payload = '{"type":"x"}';
        $this->assertSame(true, $this->threw(function () use ($payload) {
            StripeClient::constructWebhookEvent($payload, '', self::SECRET);
        }));
        $this->assertSame(true, $this->threw(function () use ($payload) {
            StripeClient::constructWebhookEvent($payload, $this->sign($payload, time()), '');
        }));
    }
}
