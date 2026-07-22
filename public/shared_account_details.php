<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();

// NOTE: every guard below runs BEFORE the header is included, because
// redirect() sends a Location header and that is impossible once output began.

$account_id  = (int) ($_GET['id'] ?? 0);
$viewer_id   = (int) $_SESSION['user_id'];
$viewer_mail = strtolower(trim($_SESSION['user_email'] ?? ''));

if ($account_id <= 0) {
    setFlashMessage('error', 'Compte invalide.');
    redirect('sharing.php');
}

$account = fetchOne(
    "SELECT a.id, a.user_id, a.name, a.description, a.balance, a.interest_rate, a.tax_rate,
            u.first_name AS owner_first_name, u.last_name AS owner_last_name, u.email AS owner_email
     FROM accounts a
     JOIN users u ON u.id = a.user_id
     WHERE a.id = ? AND a.deleted_at IS NULL",
    [$account_id]
);

if (!$account) {
    setFlashMessage('error', 'Compte non trouvé.');
    redirect('sharing.php');
}

// Authorisation. The id in the URL is chosen by the caller, so the grant is
// re-derived here on every request instead of trusting the link that was
// followed: the viewer must own the account, or hold an accepted share on it.
$is_owner = ((int) $account['user_id'] === $viewer_id);
$share    = null;

if (!$is_owner) {
    $share = fetchOne(
        "SELECT id, access_type, shared_at
         FROM account_shares
         WHERE account_id = ? AND status = 'accepted'
           AND (shared_with_user_id = ? OR shared_with_email = ?)
         ORDER BY shared_at DESC
         LIMIT 1",
        [$account_id, $viewer_id, $viewer_mail]
    );

    if (!$share) {
        setFlashMessage('error', "Vous n'avez pas accès à ce compte.");
        redirect('sharing.php');
    }
}

// ---- authorised past this point ----
$page_title = 'Compte partagé';
include SRC_PATH . '/includes/header.php';

$expenses = fetchAll(
    "SELECT name, description, amount, frequency, start_date, end_date
     FROM expenses WHERE account_id = ? AND deleted_at IS NULL ORDER BY created_at DESC",
    [$account_id]
);

$incomes = fetchAll(
    "SELECT name, description, amount, frequency, start_date, end_date
     FROM incomes WHERE account_id = ? AND deleted_at IS NULL ORDER BY created_at DESC",
    [$account_id]
);
?>

<div class="container">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo htmlspecialchars($account['name']); ?></h3>
            <a href="sharing.php" class="btn btn-sm btn-secondary">Retour au partage</a>
        </div>

        <p style="color: var(--gray-600); margin-bottom: 1rem;">
            Consultation en <strong>lecture seule</strong> —
            <?php if ($is_owner): ?>
                ce compte vous appartient.
            <?php else: ?>
                compte de
                <strong><?php echo htmlspecialchars($account['owner_first_name'] . ' ' . $account['owner_last_name']); ?></strong>
                (<?php echo htmlspecialchars($account['owner_email']); ?>),
                partagé avec vous le <?php echo formatDate($share['shared_at']); ?>.
            <?php endif; ?>
        </p>

        <?php if (!empty($account['description'])): ?>
        <p style="color: var(--gray-600); margin-bottom: 1rem;"><?php echo htmlspecialchars($account['description']); ?></p>
        <?php endif; ?>

        <div class="card-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo formatCurrency($account['balance']); ?></div>
                <div class="stat-label">Solde</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format((float) $account['interest_rate'], 2, ',', ' '); ?> %</div>
                <div class="stat-label">Taux d'intérêt</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format((float) $account['tax_rate'], 2, ',', ' '); ?> %</div>
                <div class="stat-label">Imposition</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Revenus</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Fréquence</th>
                        <th>Début</th>
                        <th>Fin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($incomes)): ?>
                    <tr><td colspan="6" class="text-center">Aucun revenu sur ce compte.</td></tr>
                    <?php else: ?>
                    <?php foreach ($incomes as $income): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($income['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($income['description'] ?? ''); ?></td>
                        <td class="text-success"><?php echo formatCurrency($income['amount']); ?></td>
                        <td><?php echo htmlspecialchars($income['frequency']); ?></td>
                        <td><?php echo $income['start_date'] ? formatDate($income['start_date']) : '—'; ?></td>
                        <td><?php echo $income['end_date'] ? formatDate($income['end_date']) : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dépenses</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Fréquence</th>
                        <th>Début</th>
                        <th>Fin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                    <tr><td colspan="6" class="text-center">Aucune dépense sur ce compte.</td></tr>
                    <?php else: ?>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($expense['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($expense['description'] ?? ''); ?></td>
                        <td><?php echo formatCurrency($expense['amount']); ?></td>
                        <td><?php echo htmlspecialchars($expense['frequency']); ?></td>
                        <td><?php echo $expense['start_date'] ? formatDate($expense['start_date']) : '—'; ?></td>
                        <td><?php echo $expense['end_date'] ? formatDate($expense['end_date']) : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
