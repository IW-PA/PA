<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Mailer.php';

/**
 * Email verification token lifecycle: issue a single-use token, email the link,
 * and consume it to mark the account verified. Mirrors the password-reset pattern.
 */
class EmailVerificationService
{
    private const TOKEN_TTL_HOURS = 24;

    /**
     * Issue a fresh verification token for a user and email the confirmation link.
     * Returns the token string (useful for tests / dev fallback display).
     */
    public static function createAndSend(int $userId, string $email, string $name): string
    {
        $token = generateToken(32);

        // Only one active token per user. Expiry is computed by the DB via NOW() so it
        // matches the verify() comparison regardless of PHP vs MySQL timezone differences.
        executeQuery("DELETE FROM email_verification_tokens WHERE user_id = ?", [$userId]);
        executeQuery(
            "INSERT INTO email_verification_tokens (user_id, token, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))",
            [$userId, $token, self::TOKEN_TTL_HOURS]
        );

        $link = self::buildLink($token);
        Mailer::send($email, $name, 'Confirmez votre adresse email — Budgie', self::buildEmail($name, $link));

        return $token;
    }

    /**
     * Validate a token; on success mark the user verified, consume the token,
     * and return the user id. Returns null for an invalid/expired/used token.
     */
    public static function verify(string $token): ?int
    {
        if ($token === '') {
            return null;
        }
        $row = fetchOne(
            "SELECT id, user_id FROM email_verification_tokens
             WHERE token = ? AND used_at IS NULL AND expires_at > NOW()",
            [$token]
        );
        if (!$row) {
            return null;
        }
        updateRecord('users', ['email_verified_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $row['user_id']]);
        executeQuery("UPDATE email_verification_tokens SET used_at = NOW() WHERE id = ?", [$row['id']]);

        return (int) $row['user_id'];
    }

    public static function isVerified(int $userId): bool
    {
        $u = fetchOne("SELECT email_verified_at FROM users WHERE id = ?", [$userId]);
        return $u && $u['email_verified_at'] !== null;
    }

    private static function buildLink(string $token): string
    {
        $script    = $_SERVER['SCRIPT_NAME'] ?? '';
        $publicPos = strpos($script, '/public');
        $basePath  = $publicPos !== false ? substr($script, 0, $publicPos) : '';
        return getBaseUrl() . $basePath . '/public/verify_email.php?token=' . urlencode($token);
    }

    private static function buildEmail(string $name, string $link): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        return '<!DOCTYPE html><html lang="fr"><body style="font-family:Arial,Helvetica,sans-serif;color:#1e293b;line-height:1.6;">'
            . '<h2>🐦 Bienvenue sur Budgie, ' . $safeName . ' !</h2>'
            . '<p>Merci de votre inscription. Pour activer votre compte, confirmez votre adresse email :</p>'
            . '<p><a href="' . $safeLink . '" style="display:inline-block;padding:12px 22px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;">Confirmer mon email</a></p>'
            . '<p>Ou copiez ce lien dans votre navigateur&nbsp;:<br><a href="' . $safeLink . '">' . $safeLink . '</a></p>'
            . '<p style="color:#64748b;font-size:0.9em;">Ce lien expire dans 24&nbsp;heures. Si vous n\'êtes pas à l\'origine de cette inscription, ignorez cet email.</p>'
            . '</body></html>';
    }
}
