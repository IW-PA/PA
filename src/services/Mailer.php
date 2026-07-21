<?php
// Minimal, dependency-free SMTP client (no Composer / PHPMailer).
// Supports implicit TLS (port 465, SMTP_SECURE=ssl) and STARTTLS (port 587, SMTP_SECURE=tls),
// AUTH LOGIN, UTF-8 HTML bodies. Never throws into the caller — returns bool.

class Mailer
{
    public static function isConfigured(): bool
    {
        return defined('SMTP_HOST') && SMTP_HOST !== ''
            && defined('SMTP_USERNAME') && SMTP_USERNAME !== ''
            && defined('SMTP_PASSWORD') && SMTP_PASSWORD !== '';
    }

    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        if (!self::isConfigured()) {
            error_log('Mailer: SMTP not configured; email to ' . $toEmail . ' not sent.');
            return false;
        }
        try {
            return self::deliver($toEmail, $toName, $subject, $htmlBody);
        } catch (Throwable $e) {
            error_log('Mailer error sending to ' . $toEmail . ': ' . $e->getMessage());
            return false;
        }
    }

    private static function deliver(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $host   = SMTP_HOST;
        $port   = (int) SMTP_PORT;
        $secure = defined('SMTP_SECURE') ? strtolower((string) SMTP_SECURE) : 'tls';
        $timeout = 20;

        $transport = ($secure === 'ssl') ? "ssl://{$host}:{$port}" : "tcp://{$host}:{$port}";
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true]]);
        $conn = @stream_socket_client($transport, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
        if (!$conn) {
            throw new Exception("connect failed: {$errstr} ({$errno})");
        }
        stream_set_timeout($conn, $timeout);

        self::expect($conn, 220);
        self::cmd($conn, 'EHLO budgie', 250);

        if ($secure === 'tls') {
            self::cmd($conn, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('STARTTLS negotiation failed');
            }
            self::cmd($conn, 'EHLO budgie', 250);
        }

        self::cmd($conn, 'AUTH LOGIN', 334);
        self::cmd($conn, base64_encode(SMTP_USERNAME), 334);
        self::cmd($conn, base64_encode(SMTP_PASSWORD), 235);

        $fromEmail = FROM_EMAIL;
        $fromName  = defined('FROM_NAME') ? FROM_NAME : 'Budgie';

        self::cmd($conn, "MAIL FROM:<{$fromEmail}>", 250);
        self::cmd($conn, "RCPT TO:<{$toEmail}>", 250);
        self::cmd($conn, 'DATA', 354);

        fwrite($conn, self::buildMessage($fromName, $fromEmail, $toName, $toEmail, $subject, $htmlBody) . "\r\n.\r\n");
        self::expect($conn, 250);

        self::cmd($conn, 'QUIT', 221);
        fclose($conn);
        return true;
    }

    private static function buildMessage($fromName, $fromEmail, $toName, $toEmail, $subject, $htmlBody): string
    {
        $body = preg_replace('/\r\n|\r|\n/', "\r\n", $htmlBody);
        $body = preg_replace('/^\./m', '..', $body); // SMTP dot-stuffing
        $lines = [
            'From: ' . self::encodeName($fromName) . " <{$fromEmail}>",
            'To: ' . self::encodeName($toName) . " <{$toEmail}>",
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $body,
        ];
        return implode("\r\n", $lines);
    }

    private static function encodeName($name): string
    {
        return $name === '' ? '' : '=?UTF-8?B?' . base64_encode($name) . '?=';
    }

    private static function cmd($conn, string $cmd, int $expectCode): string
    {
        fwrite($conn, $cmd . "\r\n");
        return self::expect($conn, $expectCode);
    }

    private static function expect($conn, int $code): string
    {
        $response = self::readResponse($conn);
        if ((int) substr($response, 0, 3) !== $code) {
            throw new Exception("expected {$code}, got: " . trim($response));
        }
        return $response;
    }

    private static function readResponse($conn): string
    {
        $data = '';
        while (($line = fgets($conn, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') { // final line of a (possibly multi-line) reply
                break;
            }
        }
        if ($data === '') {
            $meta = stream_get_meta_data($conn);
            throw new Exception(!empty($meta['timed_out']) ? 'read timeout' : 'connection closed');
        }
        return $data;
    }
}
