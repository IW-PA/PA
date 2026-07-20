<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../src/config/config.php';

/**
 * Covers the pure/helper layer in src/config/config.php: validation, hashing,
 * sanitisation, formatting, base-URL derivation, CSRF and rate limiting.
 */
class ConfigHelpersTest extends TestCase
{
    public function testValidateEmailAcceptsValidAndRejectsInvalid()
    {
        $this->assertEquals('user@example.com', validateEmail('user@example.com'));
        $this->assertSame(false, validateEmail('plainaddress'));
        $this->assertSame(false, validateEmail('foo@'));
    }

    public function testPasswordHashRoundTrip()
    {
        $hash = hashPassword('S3cret!pass');
        $this->assertSame(false, $hash === 'S3cret!pass', 'password must not be stored in clear');
        $this->assertSame(true, verifyPassword('S3cret!pass', $hash));
        $this->assertSame(false, verifyPassword('wrong', $hash));
    }

    public function testSanitizeInputEscapesHtml()
    {
        $this->assertEquals('&lt;b&gt;x&lt;/b&gt;', sanitizeInput('  <b>x</b>  '));
    }

    public function testGenerateTokenLengthAndUniqueness()
    {
        $this->assertEquals(32, strlen(generateToken(16)));   // bin2hex doubles the byte length
        $this->assertSame(false, generateToken() === generateToken());
    }

    public function testFormatCurrencyAndDate()
    {
        $this->assertEquals('€5,00', formatCurrency(5));
        $this->assertEquals('25/12/2026', formatDate('2026-12-25'));
    }

    public function testGetBaseUrlHonoursProxyHttpsAndFallsBack()
    {
        $_SERVER['HTTP_HOST'] = 'budgie.allan-morlet.fr';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        unset($_SERVER['HTTPS'], $_SERVER['SERVER_PORT']);
        $this->assertEquals('https://budgie.allan-morlet.fr', getBaseUrl());

        $_SERVER['HTTP_HOST'] = 'localhost:8080';
        unset($_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['HTTPS'], $_SERVER['SERVER_PORT']);
        $this->assertEquals('http://localhost:8080', getBaseUrl());

        unset($_SERVER['HTTP_HOST']);
        $this->assertEquals(rtrim(APP_URL, '/'), getBaseUrl());
    }

    public function testCsrfTokenRoundTrip()
    {
        unset($_SESSION['csrf_token']);
        $token = generateCSRFToken();
        $this->assertSame(true, strlen($token) > 0);
        $this->assertSame(true, validateCSRFToken($token));
        $this->assertSame(false, validateCSRFToken('not-the-token'));
    }

    public function testRateLimitBlocksAfterMaxAttempts()
    {
        $id = 'unit_test_rate_limit';
        unset($_SESSION["rate_limit_{$id}"]);
        $allowed = 0;
        for ($i = 0; $i < 6; $i++) {
            if (checkRateLimit($id, 3, 300)) {
                $allowed++;
            }
        }
        unset($_SESSION["rate_limit_{$id}"]);
        $this->assertEquals(3, $allowed, 'exactly maxAttempts calls are allowed, the rest blocked');
    }
}
