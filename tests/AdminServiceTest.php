<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/AdminService.php';

/**
 * Admin operations (role/status transitions, stats). Runs as the seeded admin
 * and operates on a throwaway user it creates and deletes.
 */
class AdminServiceTest extends TestCase
{
    private function asAdmin(): int
    {
        $admin = fetchOne("SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1");
        $adminId = $admin ? (int) $admin['id'] : 1;
        $_SESSION['user_id']   = $adminId;
        $_SESSION['user_role'] = 'admin';
        return $adminId;
    }

    private function makeUser(): int
    {
        return (int) insertRecord('users', [
            'first_name'        => 'Admin',
            'last_name'         => 'Target',
            'email'             => 'admin-target-' . uniqid('', true) . '@example.invalid',
            'password_hash'     => 'x',
            'subscription_type' => 'free',
        ]);
    }

    private function cleanup(int $id): void
    {
        executeQuery("DELETE FROM admin_activity_log WHERE target_id = ?", [$id]);
        executeQuery("DELETE FROM users WHERE id = ?", [$id]);
    }

    public function testGlobalStatsExposeExpectedCounters()
    {
        $this->asAdmin();
        $stats = AdminService::getGlobalStats();
        foreach (['total_users', 'active_users', 'premium_users', 'admin_count', 'total_accounts'] as $key) {
            $this->assertArrayHasKey($key, $stats);
        }
        $this->assertSame(true, (int) $stats['total_users'] >= 1);
        $this->assertSame(true, (int) $stats['admin_count'] >= 1);
    }

    public function testGetAllUsersReturnsRows()
    {
        $this->asAdmin();
        $users = AdminService::getAllUsers();
        $this->assertSame(true, is_array($users));
        $this->assertSame(true, count($users) >= 1);
    }

    public function testRoleAndStatusTransitions()
    {
        $this->asAdmin();
        $uid = $this->makeUser();

        AdminService::promoteToAdmin($uid);
        $this->assertEquals('admin', fetchOne("SELECT role FROM users WHERE id = ?", [$uid])['role']);

        AdminService::demoteToUser($uid); // allowed: seeded admin + this one => >1 admin
        $this->assertEquals('user', fetchOne("SELECT role FROM users WHERE id = ?", [$uid])['role']);

        AdminService::deactivateUser($uid);
        $this->assertEquals('inactive', fetchOne("SELECT status FROM users WHERE id = ?", [$uid])['status']);

        AdminService::activateUser($uid);
        $this->assertEquals('active', fetchOne("SELECT status FROM users WHERE id = ?", [$uid])['status']);

        $this->assertSame(true, AdminService::getUserDetails($uid) !== null);

        AdminService::deleteUser($uid);
        $this->assertSame(false, fetchOne("SELECT id FROM users WHERE id = ?", [$uid]), 'deleted user must be gone');

        $this->cleanup($uid);
    }

    public function testSelfMutationIsBlocked()
    {
        $adminId = $this->asAdmin();
        // An admin cannot deactivate or delete their own account.
        $this->assertSame(false, AdminService::deactivateUser($adminId));
        $this->assertSame(false, AdminService::deleteUser($adminId));
        $this->assertEquals('active', fetchOne("SELECT status FROM users WHERE id = ?", [$adminId])['status']);
    }
}
