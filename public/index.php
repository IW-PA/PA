<?php
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/ForecastService.php';
if (!isLoggedIn()) {
    require_once __DIR__ . '/landing.php';
    exit;
}

// Calculate financial summary using ForecastService
$forecastService = new ForecastService((int)$_SESSION['user_id']);
$firstOfMonth = new DateTime('first day of this month');
$lastOfMonth  = new DateTime('last day of this month');

// Build summary for current month
$result  = $forecastService->buildForecast($lastOfMonth, $firstOfMonth);
$summary = $result['summary'];

// For the "Total Balance", we want the current real balance of all accounts
$accounts     = fetchAll("SELECT id, name, balance FROM accounts WHERE user_id = ? AND deleted_at IS NULL", [$_SESSION['user_id']]);
$totalBalance = (float) array_sum(array_column($accounts, 'balance'));

// Recent expenses and incomes
$recentExpenses = fetchAll(
    "SELECT 'Dépense' as type, name, amount, start_date as date, 'text-danger' as class, account_id FROM expenses
     WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 5",
    [$_SESSION['user_id']]
);
$recentIncomes = fetchAll(
    "SELECT 'Revenu' as type, name, amount, start_date as date, 'text-success' as class, account_id FROM incomes
     WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 5",
    [$_SESSION['user_id']]
);

$recentActivity = array_merge($recentExpenses, $recentIncomes);
usort($recentActivity, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$recentActivity = array_slice($recentActivity, 0, 5);

// Map account names for activity
$accountMap = [];
foreach (fetchAll("SELECT id, name FROM accounts WHERE user_id = ? AND deleted_at IS NULL", [$_SESSION['user_id']]) as $acc) {
    $accountMap[$acc['id']] = $acc['name'];
}

// Prepare chart data for trends (last 6 months) - use JSON_HEX_* flags for safe inline script use
$trendLabels   = [];
$trendExpenses = [];
$trendIncomes  = [];

for ($i = 5; $i >= 0; $i--) {
    $monthDate = new DateTime("-{$i} months");
    $start = (clone $monthDate)->modify('first day of this month');
    $end   = (clone $monthDate)->modify('last day of this month');
    $res   = $forecastService->buildForecast($end, $start);

    $trendLabels[]   = $monthDate->format('M Y');
    $trendExpenses[] = $res['summary']['total_expense'];
    $trendIncomes[]  = $res['summary']['total_income'];
}

// Safe JSON for inline <script> — use JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT instead of htmlspecialchars
$trendPayload = json_encode([
    'labels'   => $trendLabels,
    'expenses' => $trendExpenses,
    'incomes'  => $trendIncomes,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT);

$distributionPayload = json_encode([
    'labels'   => array_column($accounts, 'name'),
    'balances' => array_map('floatval', array_column($accounts, 'balance')),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT);

$page_title = 'Dashboard';
include SRC_PATH . '/includes/header.php';
?>

<div class="container">
    <!-- Welcome Section -->
    <div class="card">
        <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?> !</h2>
        <p class="text-muted">Voici un aperçu de votre situation financière.</p>
    </div>

    <!-- Stats Cards -->
    <div class="card-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo formatCurrency($totalBalance); ?></div>
            <div class="stat-label">Solde Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo formatCurrency($summary['total_expense']); ?></div>
            <div class="stat-label">Dépenses Mensuelles</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo formatCurrency($summary['total_income']); ?></div>
            <div class="stat-label">Revenus Mensuels</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo formatCurrency($summary['total_income'] - $summary['total_expense']); ?></div>
            <div class="stat-label">Flux de Trésorerie</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Évolution des Finances (6 derniers mois)</h3>
        </div>
        <div class="chart-container">
            <canvas id="dashboardChart" style="height: 300px;"></canvas>
            <script>window.dashboardChartData = <?php echo $trendPayload; ?>;</script>
        </div>
    </div>

    <!-- Account Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Répartition des Comptes</h3>
        </div>
        <div class="chart-container">
            <canvas id="accountBalanceChart" style="height: 300px;"></canvas>
            <script>window.accountBalanceChartData = <?php echo $distributionPayload; ?>;</script>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Actions Rapides</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="accounts.php" class="btn btn-primary">
                Gérer les Comptes
            </a>
            <a href="expenses.php" class="btn btn-secondary">
                Ajouter une Dépense
            </a>
            <a href="incomes.php" class="btn btn-secondary">
                Ajouter un Revenu
            </a>
            <a href="forecasts.php" class="btn btn-secondary">
                Voir les Prévisions
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activité Récente</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Compte</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentActivity)): ?>
                        <tr><td colspan="5" class="text-center" style="padding:2rem;">Aucune activité récente.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentActivity as $activity): ?>
                        <tr>
                            <td><?php echo formatDate($activity['date']); ?></td>
                            <td><span class="<?php echo htmlspecialchars($activity['class']); ?>"><?php echo htmlspecialchars($activity['type']); ?></span></td>
                            <td><?php echo htmlspecialchars($activity['name']); ?></td>
                            <td class="<?php echo htmlspecialchars($activity['class']); ?>">
                                <?php echo ($activity['type'] === 'Dépense' ? '-' : '+') . formatCurrency((float)$activity['amount']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($accountMap[$activity['account_id']] ?? 'Inconnu'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
