<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Profile';
include SRC_PATH . '/includes/header.php';

// Get real user data
$user = getCurrentUser();
if (!$user) {
    redirect('login.php');
}

// Get last login info
$lastLoginInfo = fetchOne(
    "SELECT last_login FROM users WHERE id = ?",
    [$_SESSION['user_id']]
);

// Count actual resources
$accountsCount = fetchOne("SELECT COUNT(*) as count FROM accounts WHERE user_id = ?", [$_SESSION['user_id']])['count'];
$expensesCount = fetchOne("SELECT COUNT(*) as count FROM expenses WHERE user_id = ?", [$_SESSION['user_id']])['count'];
$incomesCount = fetchOne("SELECT COUNT(*) as count FROM incomes WHERE user_id = ?", [$_SESSION['user_id']])['count'];

$user_data = [
    'first_name' => $user['first_name'],
    'last_name' => $user['last_name'],
    'email' => $user['email'],
    'subscription' => $user['subscription_type'],
    'accounts_limit' => $user['subscription_type'] === 'premium' ? t('subscriptions.unlimited') : FREE_ACCOUNTS_LIMIT,
    'expenses_limit' => $user['subscription_type'] === 'premium' ? t('subscriptions.unlimited') : FREE_EXPENSES_LIMIT,
    'incomes_limit' => $user['subscription_type'] === 'premium' ? t('subscriptions.unlimited') : FREE_INCOMES_LIMIT,
    'last_login' => $lastLoginInfo['last_login'] ?? null,
    'accounts_count' => $accountsCount,
    'expenses_count' => $expensesCount,
    'incomes_count' => $incomesCount,
];
?>

<div class="container">
    <!-- User Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('profile.personal_info'); ?></h3>
        </div>
        <form method="POST" action="actions/update_profile.php">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label for="first_name" class="form-label"><?php e('profile.first_name'); ?></label>
                    <input type="text" id="first_name" name="first_name" class="form-input" value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name" class="form-label"><?php e('profile.last_name'); ?></label>
                    <input type="text" id="last_name" name="last_name" class="form-input" value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email" class="form-label"><?php e('profile.email'); ?></label>
                <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" title="<?php e('profile.save_changes'); ?>">
                    <span>▼</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('profile.change_password'); ?></h3>
        </div>
        <form method="POST" action="actions/change_password.php">
            <div class="form-group">
                <label for="current_password" class="form-label"><?php e('profile.current_password'); ?></label>
                <input type="password" id="current_password" name="current_password" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="new_password" class="form-label"><?php e('profile.new_password'); ?></label>
                <input type="password" id="new_password" name="new_password" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="confirm_new_password" class="form-label"><?php e('profile.confirm_password'); ?></label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-input" required>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" title="<?php e('profile.change_password'); ?>">
                    <span>◆</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Subscription Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('subscriptions.current_subscription'); ?></h3>
            <a href="subscriptions.php" class="btn btn-secondary" title="<?php e('profile.manage_subscription'); ?>">
                <span>★</span>
            </a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div class="stat-card">
                <div class="stat-value"><?php echo t('subscriptions.plan_' . $user_data['subscription']); ?></div>
                <div class="stat-label"><?php e('subscriptions.current_plan'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $user_data['accounts_limit']; ?></div>
                <div class="stat-label"><?php e('subscriptions.accounts_allowed'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $user_data['expenses_limit']; ?></div>
                <div class="stat-label"><?php e('subscriptions.expenses_per_account'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $user_data['incomes_limit']; ?></div>
                <div class="stat-label"><?php e('subscriptions.incomes_per_account'); ?></div>
            </div>
        </div>

        <?php if ($user_data['subscription'] === 'free'): ?>
        <div style="margin-top: 1.5rem; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
            <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">★ <?php e('profile.upgrade_to_premium'); ?></h4>
            <p style="color: var(--gray-600); margin-bottom: 1rem;">
                <?php e('profile.unlock_features'); ?>
            </p>
            <a href="subscriptions.php" class="btn btn-primary" title="<?php e('profile.view_plans'); ?>">
                <span>★</span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Account Statistics -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('profile.account_stats'); ?></h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div class="stat-card">
                <div class="stat-value"><?php echo $user_data['accounts_count']; ?></div>
                <div class="stat-label"><?php e('profile.accounts_created'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $user_data['expenses_count']; ?></div>
                <div class="stat-label"><?php e('profile.active_expenses'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $user_data['incomes_count']; ?></div>
                <div class="stat-label"><?php e('profile.active_incomes'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $user_data['last_login'] ? formatDate($user_data['last_login'], 'd/m/Y H:i') : t('profile.first_login'); ?></div>
                <div class="stat-label"><?php e('profile.last_login'); ?></div>
            </div>
        </div>
    </div>

    <!-- Activity & Security -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('profile.activity_security'); ?></h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div>
                <h4>◉ <?php e('profile.recent_activity'); ?></h4>
                <p style="color: var(--gray-600); margin-bottom: 0.5rem;">
                    <strong><?php e('profile.last_login'); ?>:</strong><br>
                    <?php echo $user_data['last_login'] ? formatDate($user_data['last_login'], 'd/m/Y ' . t('profile.at') . ' H:i') : t('profile.first_login'); ?>
                </p>
                <p style="color: var(--gray-600); margin-bottom: 1rem;">
                    <strong><?php e('profile.ip_address'); ?>:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'N/A'; ?>
                </p>
            </div>
            <div>
                <h4>● <?php e('profile.active_sessions'); ?></h4>
                <p style="color: var(--gray-600); margin-bottom: 1rem;">
                    <?php e('profile.manage_sessions_desc'); ?>
                </p>
                <button class="btn btn-secondary" onclick="alert('<?php e('profile.sessions_alert'); ?>')" title="<?php e('profile.view_sessions'); ?>">
                    <span>◉</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card" style="border: 2px solid var(--danger-color);">
        <div class="card-header">
            <h3 class="card-title" style="color: var(--danger-color);"><?php e('profile.danger_zone'); ?></h3>
        </div>
        <div>
            <h4 style="color: var(--danger-color); margin-bottom: 0.5rem;">✕ <?php e('profile.delete_account'); ?></h4>
            <p style="color: var(--gray-600); margin-bottom: 1rem;">
                <?php e('profile.delete_warning'); ?>
            </p>
            <button class="btn btn-danger" onclick="if(confirm('<?php e('profile.delete_confirm'); ?>')) { alert('<?php e('profile.coming_soon'); ?>'); }" title="<?php e('profile.delete_account'); ?>">
                <span>✕</span>
            </button>
        </div>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
