<?php
/**
 * AdminService
 * Business logic for admin operations
 */

require_once __DIR__ . '/../middleware/AdminGuard.php';

class AdminService {
    
    /**
     * Get all users with their statistics
     * @param array $filters Optional filters (role, status, subscription)
     * @return array
     */
    public static function getAllUsers($filters = []) {
        $sql = "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.role,
                    u.status,
                    u.subscription_type,
                    u.created_at,
                    u.last_login,
                    COUNT(DISTINCT a.id) as accounts_count,
                    COUNT(DISTINCT e.id) as expenses_count,
                    COUNT(DISTINCT i.id) as incomes_count
                FROM users u
                LEFT JOIN accounts a ON u.id = a.user_id
                LEFT JOIN expenses e ON u.id = e.user_id
                LEFT JOIN incomes i ON u.id = i.user_id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['role']) && $filters['role'] !== '') {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['subscription']) && $filters['subscription'] !== '') {
            $sql .= " AND u.subscription_type = ?";
            $params[] = $filters['subscription'];
        }
        
        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
        
        return fetchAll($sql, $params);
    }
    
    /**
     * Get global statistics
     * @return array
     */
    public static function getGlobalStats() {
        $stats = [];
        
        // Total users
        $stats['total_users'] = fetchOne("SELECT COUNT(*) as count FROM users")['count'];
        
        // Active users
        $stats['active_users'] = fetchOne("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'];
        
        // Inactive users
        $stats['inactive_users'] = fetchOne("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'")['count'];
        
        // Free vs Premium users
        $stats['free_users'] = fetchOne("SELECT COUNT(*) as count FROM users WHERE subscription_type = 'free'")['count'];
        $stats['premium_users'] = fetchOne("SELECT COUNT(*) as count FROM users WHERE subscription_type = 'premium'")['count'];
        
        // Admin count
        $stats['admin_count'] = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count'];
        
        // Total accounts
        $stats['total_accounts'] = fetchOne("SELECT COUNT(*) as count FROM accounts")['count'];
        
        // Total expenses
        $expenseData = fetchOne("SELECT COUNT(*) as count, SUM(amount) as total FROM expenses");
        $stats['total_expenses_count'] = $expenseData['count'];
        $stats['total_expenses_amount'] = $expenseData['total'] ?? 0;
        
        // Total incomes
        $incomeData = fetchOne("SELECT COUNT(*) as count, SUM(amount) as total FROM incomes");
        $stats['total_incomes_count'] = $incomeData['count'];
        $stats['total_incomes_amount'] = $incomeData['total'] ?? 0;
        
        // Users registered in last 30 days
        $stats['new_users_30d'] = fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        )['count'];
        
        // Recent activity (last 7 days)
        $stats['active_users_7d'] = fetchOne(
            "SELECT COUNT(DISTINCT user_id) as count FROM expenses WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )['count'];
        
        return $stats;
    }
    
    /**
     * Deactivate a user
     * @param int $userId
     * @return bool
     */
    public static function deactivateUser($userId) {
        // Prevent deactivating yourself
        if ($userId == $_SESSION['user_id']) {
            return false;
        }

        $result = executeQuery("UPDATE users SET status = 'inactive' WHERE id = ?", [$userId]);

        if ($result) {
            AdminGuard::logActivity('deactivate_user', 'user', $userId, "User deactivated");
        }

        return $result;
    }
    
    /**
     * Activate a user
     * @param int $userId
     * @return bool
     */
    public static function activateUser($userId) {
        $result = executeQuery("UPDATE users SET status = 'active' WHERE id = ?", [$userId]);

        if ($result) {
            AdminGuard::logActivity('activate_user', 'user', $userId, "User activated");
        }

        return $result;
    }
    
    /**
     * Promote user to admin
     * @param int $userId
     * @return bool
     */
    public static function promoteToAdmin($userId) {
        // Prevent promoting yourself (you're already admin)
        if ($userId == $_SESSION['user_id']) {
            return false;
        }

        $result = executeQuery("UPDATE users SET role = 'admin' WHERE id = ?", [$userId]);

        if ($result) {
            AdminGuard::logActivity('promote_to_admin', 'user', $userId, "User promoted to admin");
        }

        return $result;
    }
    
    /**
     * Demote admin to user
     * @param int $userId
     * @return bool
     */
    public static function demoteToUser($userId) {
        // Prevent demoting yourself
        if ($userId == $_SESSION['user_id']) {
            return false;
        }
        
        // Check if this is the last admin
        $adminCount = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count'];
        if ($adminCount <= 1) {
            return false; // Don't allow removing the last admin
        }

        $result = executeQuery("UPDATE users SET role = 'user' WHERE id = ?", [$userId]);

        if ($result) {
            AdminGuard::logActivity('demote_to_user', 'user', $userId, "Admin demoted to user");
        }

        return $result;
    }
    
    /**
     * Delete a user permanently
     * @param int $userId
     * @return bool
     */
    public static function deleteUser($userId) {
        // Prevent deleting yourself
        if ($userId == $_SESSION['user_id']) {
            return false;
        }
        
        // Get user info for logging
        $user = fetchOne("SELECT email FROM users WHERE id = ?", [$userId]);

        $result = executeQuery("DELETE FROM users WHERE id = ?", [$userId]);

        if ($result) {
            AdminGuard::logActivity('delete_user', 'user', $userId, "User deleted: " . ($user['email'] ?? 'unknown'));
        }

        return $result;
    }
    
    /**
     * Get recent admin activity log
     * @param int $limit
     * @return array
     */
    public static function getRecentActivity($limit = 50) {
        $sql = "SELECT 
                    aal.*,
                    CONCAT(u.first_name, ' ', u.last_name) as admin_name,
                    u.email as admin_email
                FROM admin_activity_log aal
                JOIN users u ON aal.admin_id = u.id
                ORDER BY aal.created_at DESC
                LIMIT ?";
        
        return fetchAll($sql, [$limit]);
    }
    
    /**
     * Get user details by ID
     * @param int $userId
     * @return array|null
     */
    public static function getUserDetails($userId) {
        $sql = "SELECT 
                    u.*,
                    COUNT(DISTINCT a.id) as accounts_count,
                    COUNT(DISTINCT e.id) as expenses_count,
                    COUNT(DISTINCT i.id) as incomes_count,
                    SUM(DISTINCT a.balance) as total_balance
                FROM users u
                LEFT JOIN accounts a ON u.id = a.user_id
                LEFT JOIN expenses e ON u.id = e.user_id
                LEFT JOIN incomes i ON u.id = i.user_id
                WHERE u.id = ?
                GROUP BY u.id";
        
        return fetchOne($sql, [$userId]);
    }
}
