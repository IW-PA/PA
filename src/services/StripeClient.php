<?php
// Minimal, dependency-free Stripe API client (uses cURL + the secret key).
// Avoids pulling Composer/vendor into the Docker image. Covers exactly the
// endpoints Budgie needs: Customers, Checkout Sessions, Billing Portal, and
// webhook signature verification.

class StripeClient
{
    private string $secretKey;
    private string $apiBase = 'https://api.stripe.com/v1';

    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? (defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '');
        if ($this->secretKey === '') {
            throw new Exception('Stripe secret key is not configured.');
        }
    }

    /**
     * Perform a form-encoded request against the Stripe API.
     */
    public function request(string $method, string $path, array $params = []): array
    {
        $url = $this->apiBase . $path;
        $ch  = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/x-www-form-urlencoded',
            'Stripe-Version: 2026-04-22.dahlia',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encode($params));
        } elseif (!empty($params)) {
            $url .= '?' . $this->encode($params);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new Exception('Stripe request failed: ' . $err);
        }
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new Exception('Invalid response from Stripe.');
        }
        if ($status >= 400) {
            $message = $decoded['error']['message'] ?? 'Unknown Stripe error';
            throw new Exception('Stripe API error: ' . $message);
        }
        return $decoded;
    }

    /** Stripe expects PHP-style nested keys (a[b]=c); http_build_query does this. */
    private function encode(array $params): string
    {
        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function createCustomer(string $email, string $name, array $metadata = []): array
    {
        return $this->request('POST', '/customers', [
            'email'    => $email,
            'name'     => $name,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create a Checkout Session in subscription mode.
     * Note: payment_method_types is intentionally omitted (dynamic payment methods).
     */
    public function createSubscriptionCheckout(string $customerId, string $priceId, string $successUrl, string $cancelUrl, array $metadata = []): array
    {
        return $this->request('POST', '/checkout/sessions', [
            'mode'                => 'subscription',
            'customer'            => $customerId,
            'line_items'          => [['price' => $priceId, 'quantity' => 1]],
            'success_url'         => $successUrl,
            'cancel_url'          => $cancelUrl,
            'client_reference_id' => $metadata['user_id'] ?? null,
            'metadata'            => $metadata,
            'subscription_data'   => ['metadata' => $metadata],
        ]);
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        return $this->request('GET', '/checkout/sessions/' . urlencode($sessionId));
    }

    public function createBillingPortalSession(string $customerId, string $returnUrl): array
    {
        return $this->request('POST', '/billing_portal/sessions', [
            'customer'   => $customerId,
            'return_url' => $returnUrl,
        ]);
    }

    /**
     * Verify a webhook signature and return the decoded event.
     * Throws if the signature is missing, malformed, stale, or invalid.
     */
    public static function constructWebhookEvent(string $payload, string $sigHeader, string $secret, int $tolerance = 300): array
    {
        if ($secret === '') {
            throw new Exception('Webhook secret not configured.');
        }
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($k === 't') {
                $timestamp = (int) $v;
            } elseif ($k === 'v1') {
                $signatures[] = $v;
            }
        }
        if ($timestamp === null || empty($signatures)) {
            throw new Exception('Invalid Stripe signature header.');
        }
        $signedPayload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        $matched = false;
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            throw new Exception('Webhook signature verification failed.');
        }
        if (abs(time() - $timestamp) > $tolerance) {
            throw new Exception('Webhook timestamp outside tolerance.');
        }

        $event = json_decode($payload, true);
        if (!is_array($event)) {
            throw new Exception('Invalid webhook payload.');
        }
        return $event;
    }
}
