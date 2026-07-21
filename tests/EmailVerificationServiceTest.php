<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/EmailVerificationService.php';

/**
 * Email-verification token lifecycle: issue, single-use verify, expiry/invalid rejection.
 * (SMTP is not configured in tests, so Mailer::send is a no-op — the token logic still runs.)
 */
class EmailVerificationServiceTest extends TestCase
{
    private function makeUnverifiedUser(): int
    {
        // email_verified_at defaults to NULL => unverified.
        return (int) insertRecord('users', [
            'first_name'        => 'Verify',
            'last_name'         => 'Test',
            'email'             => 'verify-' . uniqid('', true) . '@example.invalid',
            'password_hash'     => 'x',
            'subscription_type' => 'free',
        ]);
    }

    private function dropUser(int $id): void
    {
        executeQuery("DELETE FROM users WHERE id = ?", [$id]); // cascades tokens
    }

    // $sqlExpiry is a trusted literal SQL expression relative to NOW() (avoids PHP/MySQL tz skew).
    private function insertToken(int $userId, string $token, string $sqlExpiry): void
    {
        executeQuery(
            "INSERT INTO email_verification_tokens (user_id, token, expires_at) VALUES (?, ?, {$sqlExpiry})",
            [$userId, $token]
        );
    }

    public function testCreateAndSendIssuesExactlyOneToken()
    {
        $uid   = $this->makeUnverifiedUser();
        $token = EmailVerificationService::createAndSend($uid, 'verify@example.invalid', 'Verify');
        $count = count(fetchAll("SELECT id FROM email_verification_tokens WHERE user_id = ? AND token = ?", [$uid, $token]));
        $this->dropUser($uid);

        $this->assertEquals(1, $count);
        $this->assertSame(true, strlen($token) >= 32);
    }

    public function testVerifyMarksUserVerifiedAndConsumesToken()
    {
        $uid   = $this->makeUnverifiedUser();
        $token = 'evt_ok_' . uniqid('', true);
        $this->insertToken($uid, $token, 'DATE_ADD(NOW(), INTERVAL 1 HOUR)');

        $before  = EmailVerificationService::isVerified($uid);
        $return  = EmailVerificationService::verify($token);
        $after   = EmailVerificationService::isVerified($uid);
        $replay  = EmailVerificationService::verify($token); // single-use

        $this->dropUser($uid);
        $this->assertSame(false, $before, 'account starts unverified');
        $this->assertEquals($uid, $return);
        $this->assertSame(true, $after, 'account becomes verified');
        $this->assertSame(null, $replay, 'a used token cannot be reused');
    }

    public function testPeekReportsStateWithoutConsumingTheToken()
    {
        $uid   = $this->makeUnverifiedUser();
        $valid = 'evt_peek_' . uniqid('', true);
        $this->insertToken($uid, $valid, 'DATE_ADD(NOW(), INTERVAL 1 HOUR)');

        // A GET/peek must NOT burn the single-use token (the whole point of the fix).
        $this->assertEquals('valid', EmailVerificationService::peek($valid));
        $this->assertEquals('valid', EmailVerificationService::peek($valid), 'peek is non-consuming');
        $this->assertEquals($uid, EmailVerificationService::verify($valid), 'token still usable after peeking');

        // Once the user is verified, a re-peek reports success (idempotent, scanner-safe).
        $this->assertEquals('already_verified', EmailVerificationService::peek($valid));

        $this->assertEquals('invalid', EmailVerificationService::peek('does-not-exist-' . uniqid()));

        $uid2 = $this->makeUnverifiedUser();
        $exp  = 'evt_peekexp_' . uniqid('', true);
        $this->insertToken($uid2, $exp, 'DATE_SUB(NOW(), INTERVAL 1 HOUR)');
        $this->assertEquals('invalid', EmailVerificationService::peek($exp));

        $this->dropUser($uid);
        $this->dropUser($uid2);
    }

    public function testExpiredAndInvalidTokensRejected()
    {
        $uid     = $this->makeUnverifiedUser();
        $expired = 'evt_exp_' . uniqid('', true);
        $this->insertToken($uid, $expired, 'DATE_SUB(NOW(), INTERVAL 1 HOUR)');

        $expiredResult   = EmailVerificationService::verify($expired);
        $invalidResult   = EmailVerificationService::verify('does-not-exist');
        $stillUnverified = EmailVerificationService::isVerified($uid);

        $this->dropUser($uid);
        $this->assertSame(null, $expiredResult, 'expired token rejected');
        $this->assertSame(null, $invalidResult, 'unknown token rejected');
        $this->assertSame(false, $stillUnverified);
    }
}
