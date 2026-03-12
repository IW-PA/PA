<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
require_once SRC_PATH . '/services/ForecastService.php';

$page_title = 'Prévisions';

$selectedMonth = $_GET['target_month'] ?? date('Y-m');
$targetDate = DateTime::createFromFormat('Y-m', $selectedMonth);

if (!$targetDate) {
    $targetDate = new DateTime('first day of next month');
    $selectedMonth = $targetDate->format('Y-m');
}

$forecastService = new ForecastService((int) $_SESSION['user_id']);
$forecastData = $forecastService->buildForecast(clone $targetDate);

$summary = $forecastData['summary'];
$accountForecasts = $forecastData['accounts'];
$chartData = $forecastData['chart'];

$netGain = $summary['projected_balance'] - $summary['current_balance'];
$growthPercent = $summary['current_balance'] > 0
    ? round(($netGain / $summary['current_balance']) * 100, 2)
    : 0;

$chartPayload = htmlspecialchars(json_encode($chartData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

include SRC_PATH . '/includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Prévisions Financières</h3>
            <form method="GET" class="d-flex align-center gap-2" style="flex-wrap: wrap;">
                <div>
                    <label for="forecast_month" class="form-label">Mois cible</label>
                    <input
                        type="month"
                        id="forecast_month"
                        name="target_month"
                        class="form-input"
                        value="<?php echo htmlspecialchars($selectedMonth); ?>"
                        min="<?php echo date('Y-m'); ?>"
                        max="<?php echo (new DateTime('+24 months'))->format('Y-m'); ?>"
                        required
                    >
                </div>
                <div style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <span>🔮</span> Calculer
                    </button>
                </div>
            </form>
        </div>

        <?php if (empty($accountForecasts)): ?>
            <div class="empty-state" style="padding: 2rem; text-align: center;">
                <p>Aucun compte n'est disponible pour générer des prévisions. Commencez par ajouter un compte ainsi que vos revenus et dépenses.</p>
                <div style="margin-top: 1rem;">
                    <a href="accounts.php" class="btn btn-primary">Créer un compte</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <div class="stat-card">
                    <div class="stat-label">Solde actuel</div>
                    <div class="stat-value"><?php echo formatCurrency($summary['current_balance']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Solde projeté (<?php echo htmlspecialchars($targetDate->format('F Y')); ?>)</div>
                    <div class="stat-value text-success"><?php echo formatCurrency($summary['projected_balance']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Flux nets</div>
                    <div class="stat-value <?php echo $netGain >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo ($netGain >= 0 ? '+' : '') . formatCurrency(abs($netGain)); ?>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Croissance attendue</div>
                    <div class="stat-value <?php echo $growthPercent >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo ($growthPercent >= 0 ? '+' : '') . $growthPercent . '%'; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($accountForecasts)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Évolution des Soldes</h3>
            </div>
            <div class="chart-container">
                <canvas
                    id="forecastChart"
                    style="height: 400px;"
                    data-chart="<?php echo $chartPayload; ?>"
                ></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Détail par Compte</h3>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Compte</th>
                            <th>Solde actuel</th>
                            <th>Revenus cumulés</th>
                            <th>Dépenses cumulées</th>
                            <th>Intérêts</th>
                            <th>Solde projeté</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accountForecasts as $item): ?>
                            <?php
                                $account = $item['account'];
                                $income = $item['total_income'];
                                $expense = $item['total_expense'];
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($account['name']); ?></strong>
                                    <div class="text-muted" style="font-size: 0.875rem;"><?php echo htmlspecialchars($account['description'] ?? ''); ?></div>
                                </td>
                                <td><?php echo formatCurrency($item['starting_balance']); ?></td>
                                <td class="text-success"><?php echo formatCurrency($income); ?></td>
                                <td class="text-danger"><?php echo formatCurrency($expense); ?></td>
                                <td><?php echo formatCurrency($item['interest_earned']); ?></td>
                                <td><strong><?php echo formatCurrency($item['projected_balance']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Flux cumulés</h3>
            </div>
            <div class="card-grid">
                <div class="stat-card">
                    <div class="stat-label">Revenus cumulés</div>
                    <div class="stat-value text-success"><?php echo formatCurrency($summary['total_income']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Dépenses cumulées</div>
                    <div class="stat-value text-danger"><?php echo formatCurrency($summary['total_expense']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Intérêts attendus</div>
                    <div class="stat-value"><?php echo formatCurrency($summary['interest_earned']); ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
