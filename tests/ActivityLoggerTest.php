<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

/**
 * ActivityLogger must persist an audit row (and never throw into the caller).
 */
class ActivityLoggerTest extends TestCase
{
    public function testLogPersistsAnAuditRow()
    {
        $uid = (int) insertRecord('users', [
            'first_name'        => 'Log',
            'last_name'         => 'Test',
            'email'             => 'log-test-' . uniqid('', true) . '@example.invalid',
            'password_hash'     => 'x',
            'subscription_type' => 'free',
        ]);

        $action = 'unit.test.' . uniqid();
        ActivityLogger::log($uid, $action, 'user', $uid, ['k' => 'v']);

        $row = fetchOne(
            "SELECT action, entity_type, entity_id FROM activity_logs WHERE user_id = ? AND action = ? ORDER BY id DESC LIMIT 1",
            [$uid, $action]
        );

        executeQuery("DELETE FROM activity_logs WHERE user_id = ?", [$uid]);
        executeQuery("DELETE FROM users WHERE id = ?", [$uid]);

        $this->assertSame(true, $row !== null && $row !== false, 'log() should insert an activity row');
        $this->assertEquals($action, $row['action']);
        $this->assertEquals('user', $row['entity_type']);
    }
}
