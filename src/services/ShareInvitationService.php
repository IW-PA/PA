<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Mailer.php';

/**
 * Account-share invitations: email a link carrying the share's invitation_token,
 * then let the invited address accept or decline it. Mirrors the token/link
 * lifecycle of EmailVerificationService; the token lives on account_shares.
 */
class ShareInvitationService
{
    /**
     * Email the invitation link. Returns true only when the mail actually went
     * out (Mailer returns false when SMTP is not configured).
     */
    public static function send(string $email, string $accountName, string $ownerName, string $token): bool
    {
        return Mailer::send(
            $email,
            $email,
            $ownerName . ' partage un compte avec vous — Budgie',
            self::buildEmail($ownerName, $accountName, self::buildLink($token))
        );
    }

    /**
     * Look an invitation up by token WITHOUT modifying it, so a GET — including a
     * mail gateway pre-fetching the URL — never consumes or answers it.
     */
    public static function peek(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $row = fetchOne(
            "SELECT s.id, s.status, s.account_id, s.shared_with_email, s.shared_with_user_id,
                    a.name AS account_name,
                    u.first_name AS owner_first_name, u.last_name AS owner_last_name
             FROM account_shares s
             JOIN accounts a ON a.id = s.account_id AND a.deleted_at IS NULL
             JOIN users u ON u.id = s.owner_id
             WHERE s.invitation_token = ?",
            [$token]
        );

        // fetchOne() returns PDO's false (not null) when nothing matches.
        return $row ?: null;
    }

    /**
     * Answer a pending invitation. Only the invited address may respond, and only
     * while the invitation is still pending. Returns true when the status changed.
     */
    public static function respond(string $token, int $userId, string $userEmail, string $decision): bool
    {
        if (!in_array($decision, ['accepted', 'declined'], true)) {
            return false;
        }

        $share = self::peek($token);
        if (!$share || $share['status'] !== 'pending') {
            return false;
        }

        // The invitation belongs to an email address, so the logged-in user must
        // own that address before it grants them access to someone's finances.
        if (strtolower(trim($userEmail)) !== strtolower(trim((string) $share['shared_with_email']))) {
            return false;
        }

        executeQuery(
            "UPDATE account_shares
             SET status = ?, shared_with_user_id = ?, responded_at = NOW()
             WHERE id = ? AND status = 'pending'",
            [$decision, $userId, $share['id']]
        );

        return true;
    }

    public static function buildLink(string $token): string
    {
        $script    = $_SERVER['SCRIPT_NAME'] ?? '';
        $publicPos = strpos($script, '/public');
        $basePath  = $publicPos !== false ? substr($script, 0, $publicPos) : '';

        return getBaseUrl() . $basePath . '/public/accept_share.php?token=' . urlencode($token);
    }

    private static function buildEmail(string $ownerName, string $accountName, string $link): string
    {
        $safeOwner   = htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8');
        $safeAccount = htmlspecialchars($accountName, ENT_QUOTES, 'UTF-8');
        $safeLink    = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html><html lang="fr"><body style="font-family:Arial,Helvetica,sans-serif;color:#1e293b;line-height:1.6;">'
            . '<h2>🐦 Un compte Budgie est partagé avec vous</h2>'
            . '<p><strong>' . $safeOwner . '</strong> souhaite partager avec vous la visibilité du compte '
            . '<strong>' . $safeAccount . '</strong>.</p>'
            . '<p>En acceptant, vous pourrez consulter ce compte, ses dépenses et ses revenus '
            . '<strong>en lecture seule</strong>. Vous ne pourrez rien y modifier.</p>'
            . '<p><a href="' . $safeLink . '" style="display:inline-block;padding:12px 22px;background:#8d2b5c;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;">Voir l\'invitation</a></p>'
            . '<p>Ou copiez ce lien dans votre navigateur&nbsp;:<br><a href="' . $safeLink . '">' . $safeLink . '</a></p>'
            . '<p style="color:#64748b;font-size:0.9em;">Si vous ne connaissez pas l\'expéditeur, ignorez simplement cet email : sans votre accord, aucune donnée ne vous est partagée.</p>'
            . '</body></html>';
    }
}
