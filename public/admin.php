<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/middleware/AdminGuard.php';
require_once __DIR__ . '/../src/services/AdminService.php';

// Require admin access
AdminGuard::requireAdmin();

$page_title = 'Administration';

// Get filters from query string
$filters = [
    'role' => $_GET['role'] ?? '',
    'status' => $_GET['status'] ?? '',
    'subscription' => $_GET['subscription'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Get global stats
$stats = AdminService::getGlobalStats();

// Get all users
$users = AdminService::getAllUsers($filters);

// Get recent activity
$recentActivity = AdminService::getRecentActivity(10);

include SRC_PATH . '/includes/header.php';
?>

<style>
/* Admin-specific styles */
.admin-container {
    padding: 2rem;
}

.admin-header {
    background: linear-gradient(135deg, #22333B 0%, #0A0908 100%);
    color: var(--cream);
    padding: 2rem;
    border-radius: var(--border-radius);
    margin-bottom: 2rem;
    box-shadow: var(--shadow-xl);
}

.admin-header h1 {
    color: var(--cream);
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
}

.admin-header p {
    color: var(--tan);
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.admin-stat-card {
    background: rgba(255, 255, 255, 0.98);
    border: 2px solid rgba(198, 172, 142, 0.3);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.admin-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
    border-color: var(--brown);
}

.admin-stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark-navy);
    margin-bottom: 0.5rem;
}

.admin-stat-label {
    color: var(--gray-600);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.admin-filters {
    background: rgba(255, 255, 255, 0.98);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    margin-bottom: 2rem;
    border: 2px solid rgba(198, 172, 142, 0.3);
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge-admin {
    background: linear-gradient(135deg, #22333B 0%, #0A0908 100%);
    color: var(--cream);
}

.badge-user {
    background: rgba(198, 172, 142, 0.2);
    color: var(--brown);
}

.badge-active {
    background: rgba(34, 139, 34, 0.2);
    color: #228B22;
}

.badge-inactive {
    background: rgba(220, 53, 69, 0.2);
    color: #DC3545;
}

.badge-free {
    background: rgba(108, 117, 125, 0.2);
    color: #6C757D;
}

.badge-premium {
    background: linear-gradient(135deg, #C6AC8E 0%, #5E503F 100%);
    color: white;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    min-width: auto;
}

.activity-log {
    background: rgba(255, 255, 255, 0.98);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-top: 2rem;
    border: 2px solid rgba(198, 172, 142, 0.3);
}

.activity-item {
    padding: 1rem;
    border-bottom: 1px solid rgba(198, 172, 142, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-time {
    color: var(--gray-500);
    font-size: 0.813rem;
}
</style>

<div class="admin-container">
    <!-- Admin Header -->
    <div class="admin-header">
        <h1>⚙ <?php e('nav.administration'); ?></h1>
        <p>Welcome to the Administration Panel, <?php echo $_SESSION['user_name']; ?></p>
    </div>

    <!-- Global Statistics -->
    <div class="stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?php echo number_format($stats['total_users']); ?></div>
            <div class="admin-stat-label">Total Users</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?php echo number_format($stats['active_users']); ?></div>
            <div class="admin-stat-label">Active Users</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?php echo number_format($stats['free_users']); ?></div>
            <div class="admin-stat-label">Free Users</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?php echo number_format($stats['premium_users']); ?></div>
            <div class="admin-stat-label">Premium Users</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?php echo number_format($stats['total_accounts']); ?></div>
            <div class="admin-stat-label">Total Accounts</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?php echo number_format($stats['total_expenses_count']); ?></div>
            <div class="admin-stat-label">Total Expenses</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?php echo number_format($stats['total_incomes_count']); ?></div>
            <div class="admin-stat-label">Total Incomes</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?php echo number_format($stats['new_users_30d']); ?></div>
            <div class="admin-stat-label">New Users (30d)</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-filters">
        <form method="GET" action="admin.php">
            <div class="filter-row">
                <div class="form-group">
                    <label class="form-label"><?php e('common.search'); ?></label>
                    <input type="text" name="search" class="form-input" placeholder="Search users..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="user" <?php echo $filters['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo $filters['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><?php e('common.status'); ?></label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Subscription</label>
                    <select name="subscription" class="form-select">
                        <option value="">All Plans</option>
                        <option value="free" <?php echo $filters['subscription'] === 'free' ? 'selected' : ''; ?>>Free</option>
                        <option value="premium" <?php echo $filters['subscription'] === 'premium' ? 'selected' : ''; ?>>Premium</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><?php e('common.filter'); ?></button>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Management</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php e('common.name'); ?></th>
                        <th><?php e('profile.email'); ?></th>
                        <th>Role</th>
                        <th><?php e('common.status'); ?></th>
                        <th>Subscription</th>
                        <th>Accounts</th>
                        <th>Expenses</th>
                        <th>Incomes</th>
                        <th>Registered</th>
                        <th><?php e('common.actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $user['status'] === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo $user['status']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $user['subscription_type'] === 'premium' ? 'badge-premium' : 'badge-free'; ?>">
                                <?php echo $user['subscription_type']; ?>
                            </span>
                        </td>
                        <td><?php echo $user['accounts_count']; ?></td>
                        <td><?php echo $user['expenses_count']; ?></td>
                        <td><?php echo $user['incomes_count']; ?></td>
                        <td><?php echo formatDate($user['created_at'], 'd/m/Y'); ?></td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <div class="action-buttons">
                                <!-- Toggle Status -->
                                <?php if ($user['status'] === 'active'): ?>
                                <form method="POST" action="actions/admin_deactivate_user.php" style="display: inline;">
                                    <?php echo CSRFProtection::getTokenField(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary btn-action" onclick="return confirm('Deactivate this user?')">
                                        Deactivate
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" action="actions/admin_activate_user.php" style="display: inline;">
                                    <?php echo CSRFProtection::getTokenField(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-action">
                                        Activate
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <!-- Toggle Role -->
                                <?php if ($user['role'] === 'user'): ?>
                                <form method="POST" action="actions/admin_promote_user.php" style="display: inline;">
                                    <?php echo CSRFProtection::getTokenField(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-action" onclick="return confirm('Promote to admin?')">
                                        Promote
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" action="actions/admin_demote_user.php" style="display: inline;">
                                    <?php echo CSRFProtection::getTokenField(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary btn-action" onclick="return confirm('Demote to user?')">
                                        Demote
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity Log -->
    <div class="activity-log">
        <h3>Recent Activity</h3>
        <?php foreach ($recentActivity as $activity): ?>
        <div class="activity-item">
            <div>
                <strong><?php echo htmlspecialchars($activity['admin_name']); ?></strong>
                <span><?php echo htmlspecialchars($activity['action']); ?></span>
                <?php if ($activity['details']): ?>
                <span class="text-muted"> - <?php echo htmlspecialchars($activity['details']); ?></span>
                <?php endif; ?>
            </div>
            <div class="activity-time">
                <?php echo formatDate($activity['created_at'], 'd/m/Y H:i'); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
