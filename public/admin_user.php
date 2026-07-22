<?php
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/AdminService.php';
requireAdmin();

// Read-only administrator drill-down into a single user's financial data.
$userId = (int) ($_GET['id'] ?? 0);
$user   = $userId > 0 ? AdminService::getUserDetails($userId) : null;

if (!$user) {
    setFlashMessage('error', 'Utilisateur introuvable.');
    redirect('admin.php');
}

$accounts = AdminService::getUserAccounts($userId);
$expenses = AdminService::getUserExpenses($userId);
$incomes  = AdminService::getUserIncomes($userId);

$fullName   = trim($user['first_name'] . ' ' . $user['last_name']);
$page_title = 'Utilisateur : ' . $fullName;
include SRC_PATH . '/includes/header.php';
?>

<div class="container">
    <!-- Header -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">👤 <?php echo htmlspecialchars($fullName); ?> — vue lecture seule</h3>
            <a href="admin.php" class="btn btn-secondary">← Retour à l'administration</a>
        </div>
        <div style="padding: 0 1rem 1rem;">
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Rôle :</strong>
                <span class="<?php echo $user['role'] === 'admin' ? 'text-warning' : 'text-muted'; ?>">
                    <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                </span>
                &nbsp;·&nbsp; <strong>Statut :</strong>
                <span class="<?php echo $user['status'] === 'active' ? 'text-success' : 'text-danger'; ?>">
                    <?php echo htmlspecialchars(ucfirst($user['status'])); ?>
                </span>
                &nbsp;·&nbsp; <strong>Abonnement :</strong> <?php echo htmlspecialchars(ucfirst($user['subscription_type'])); ?>
            </p>
            <p class="text-muted"><em>Consultation administrateur — aucune modification possible depuis cette page.</em></p>
        </div>
    </div>

    <!-- Accounts -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Comptes (<?php echo count($accounts); ?>)</h3></div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr><th>Nom</th><th>Description</th><th>Solde</th><th>Taux d'intérêt</th><th>Taux d'imposition</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Aucun compte.</td></tr>
                    <?php else: foreach ($accounts as $a): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($a['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($a['description'] ?? ''); ?></td>
                            <td><?php echo formatCurrency($a['balance']); ?></td>
                            <td><?php echo htmlspecialchars($a['interest_rate']); ?> %</td>
                            <td><?php echo htmlspecialchars($a['tax_rate']); ?> %</td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expenses -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Dépenses (<?php echo count($expenses); ?>)</h3></div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr><th>Nom</th><th>Compte</th><th>Montant</th><th>Fréquence</th><th>Début</th><th>Fin</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr><td colspan="6" class="text-center text-muted">Aucune dépense.</td></tr>
                    <?php else: foreach ($expenses as $e): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($e['name']); ?></td>
                            <td><?php echo htmlspecialchars($e['account_name']); ?></td>
                            <td><?php echo formatCurrency($e['amount']); ?></td>
                            <td><?php echo formatFrequency($e['frequency'], $e['interval_months'] ?? null); ?></td>
                            <td><?php echo htmlspecialchars($e['start_date']); ?></td>
                            <td><?php echo $e['end_date'] ? htmlspecialchars($e['end_date']) : '—'; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Incomes -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Revenus (<?php echo count($incomes); ?>)</h3></div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr><th>Nom</th><th>Compte</th><th>Montant</th><th>Fréquence</th><th>Début</th><th>Fin</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($incomes)): ?>
                        <tr><td colspan="6" class="text-center text-muted">Aucun revenu.</td></tr>
                    <?php else: foreach ($incomes as $i): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($i['name']); ?></td>
                            <td><?php echo htmlspecialchars($i['account_name']); ?></td>
                            <td><?php echo formatCurrency($i['amount']); ?></td>
                            <td><?php echo formatFrequency($i['frequency'], $i['interval_months'] ?? null); ?></td>
                            <td><?php echo htmlspecialchars($i['start_date']); ?></td>
                            <td><?php echo $i['end_date'] ? htmlspecialchars($i['end_date']) : '—'; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
