<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Dashboard';
include SRC_PATH . '/includes/header.php';
?>

<div class="container">
    <!-- Welcome Section -->
    <div class="card">
        <h2>Bienvenue, <?php echo $_SESSION['user_name']; ?> ! 👋</h2>
        <p class="text-muted">Voici un aperçu de votre situation financière.</p>
    </div>

    <!-- Stats Cards -->
    <div class="card-grid">
        <div class="stat-card">
            <div class="stat-value">€12,450</div>
            <div class="stat-label">Solde Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€2,100</div>
            <div class="stat-label">Dépenses Mensuelles</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€3,500</div>
            <div class="stat-label">Revenus Mensuels</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€1,400</div>
            <div class="stat-label">Épargne Mensuelle</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Évolution des Finances</h3>
        </div>
        <div class="chart-container">
            <canvas id="dashboardChart" style="height: 300px;"></canvas>
        </div>
    </div>

    <!-- Account Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Répartition des Comptes</h3>
        </div>
        <div class="chart-container">
            <canvas id="accountBalanceChart" style="height: 300px;"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Actions Rapides</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="accounts.php" class="btn btn-primary">
                <span>💳</span> Gérer les Comptes
            </a>
            <a href="expenses.php" class="btn btn-secondary">
                <span>💸</span> Ajouter une Dépense
            </a>
            <a href="incomes.php" class="btn btn-secondary">
                <span>💰</span> Ajouter un Revenu
            </a>
            <a href="forecasts.php" class="btn btn-secondary">
                <span>📊</span> Voir les Prévisions
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
                    <tr>
                        <td>15/01/2025</td>
                        <td><span class="text-danger">Dépense</span></td>
                        <td>Courses alimentaires</td>
                        <td class="text-danger">-€150</td>
                        <td>Compte Courant</td>
                    </tr>
                    <tr>
                        <td>14/01/2025</td>
                        <td><span class="text-success">Revenu</span></td>
                        <td>Salaire</td>
                        <td class="text-success">+€2,500</td>
                        <td>Compte Courant</td>
                    </tr>
                    <tr>
                        <td>13/01/2025</td>
                        <td><span class="text-danger">Dépense</span></td>
                        <td>Essence</td>
                        <td class="text-danger">-€80</td>
                        <td>Compte Courant</td>
                    </tr>
                    <tr>
                        <td>12/01/2025</td>
                        <td><span class="text-danger">Dépense</span></td>
                        <td>Restaurant</td>
                        <td class="text-danger">-€45</td>
                        <td>Compte Courant</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
