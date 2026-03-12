<?php

require_once __DIR__ . '/../config/config.php';

class ActivityLogger
{
    public static function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $metadata = []
    ): void {
        try {
            $payload = [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'metadata' => !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            ];

            insertRecord('activity_logs', $payload);
        } catch (Throwable $e) {
            error_log('ActivityLogger failure: ' . $e->getMessage());
        }
    }
}

