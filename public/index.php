<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Dashboard';
include SRC_PATH . '/includes/header.php';
?>

<div class="container">
    <!-- Welcome Section -->
    <div class="card">
        <h2><?php e('dashboard.welcome'); ?>, <?php echo $_SESSION['user_name']; ?> !</h2>
        <p class="text-muted"><?php e('dashboard.overview'); ?></p>
    </div>

    <!-- Stats Cards -->
    <div class="card-grid">
        <div class="stat-card">
            <div class="stat-value">€12,450</div>
            <div class="stat-label"><?php e('dashboard.total_balance'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€2,100</div>
            <div class="stat-label"><?php e('dashboard.monthly_expenses'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€3,500</div>
            <div class="stat-label"><?php e('dashboard.monthly_income'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€1,400</div>
            <div class="stat-label"><?php e('dashboard.monthly_savings'); ?></div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('dashboard.financial_evolution'); ?></h3>
        </div>
        <div class="chart-container">
            <canvas id="dashboardChart" style="height: 300px;"></canvas>
        </div>
    </div>

    <!-- Account Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('dashboard.account_distribution'); ?></h3>
        </div>
        <div class="chart-container">
            <canvas id="accountBalanceChart" style="height: 300px;"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('dashboard.quick_actions'); ?></h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="accounts.php" class="btn btn-primary">
                <span>▬</span> <?php e('dashboard.manage_accounts'); ?>
            </a>
            <a href="expenses.php" class="btn btn-secondary">
                <span>▼</span> <?php e('dashboard.add_expense'); ?>
            </a>
            <a href="incomes.php" class="btn btn-secondary">
                <span>▲</span> <?php e('dashboard.add_income'); ?>
            </a>
            <a href="forecasts.php" class="btn btn-secondary">
                <span>▤</span> <?php e('dashboard.view_forecasts'); ?>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('dashboard.recent_activity'); ?></h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php e('common.date'); ?></th>
                        <th><?php e('dashboard.type'); ?></th>
                        <th><?php e('common.description'); ?></th>
                        <th><?php e('common.amount'); ?></th>
                        <th><?php e('dashboard.account'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>15/01/2025</td>
                        <td><span class="text-danger"><?php e('dashboard.expense'); ?></span></td>
                        <td><?php e('dashboard.groceries'); ?></td>
                        <td class="text-danger">-€150</td>
                        <td><?php e('dashboard.current_account'); ?></td>
                    </tr>
                    <tr>
                        <td>14/01/2025</td>
                        <td><span class="text-success"><?php e('dashboard.income'); ?></span></td>
                        <td><?php e('dashboard.salary'); ?></td>
                        <td class="text-success">+€2,500</td>
                        <td><?php e('dashboard.current_account'); ?></td>
                    </tr>
                    <tr>
                        <td>13/01/2025</td>
                        <td><span class="text-danger"><?php e('dashboard.expense'); ?></span></td>
                        <td><?php e('dashboard.gas'); ?></td>
                        <td class="text-danger">-€80</td>
                        <td><?php e('dashboard.current_account'); ?></td>
                    </tr>
                    <tr>
                        <td>12/01/2025</td>
                        <td><span class="text-danger"><?php e('dashboard.expense'); ?></span></td>
                        <td><?php e('dashboard.restaurant'); ?></td>
                        <td class="text-danger">-€45</td>
                        <td><?php e('dashboard.current_account'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
