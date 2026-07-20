<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Comptes';
include SRC_PATH . '/includes/header.php';

// Fetch user's accounts from database
$accounts = fetchAll(
    "SELECT * FROM accounts WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC",
    [$_SESSION['user_id']]
);
?>

<div class="container">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mes Comptes</h3>
            <button class="btn btn-primary" onclick="openModal('addAccountModal')">
                Ajouter un Compte
            </button>
        </div>

        <div class="table-container">
            <table class="table" id="accounts-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Solde</th>
                        <th>Taux Rémunération</th>
                        <th>Taux Imposition</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 2rem; color: var(--text-muted);">
                            Aucun compte enregistré. Commencez par en créer un !
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td><?php echo $account['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($account['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($account['description']); ?></td>
                        <td class="text-success"><strong>€<?php echo number_format($account['balance'], 2); ?></strong></td>
                        <td><?php echo $account['interest_rate']; ?>%</td>
                        <td><?php echo $account['tax_rate']; ?>%</td>
                        <td>
                            <button class="btn btn-sm btn-secondary"
                                onclick="openEditAccountModal(
                                    <?php echo $account['id']; ?>,
                                    <?php echo htmlspecialchars(json_encode($account['name']), ENT_QUOTES); ?>,
                                    <?php echo htmlspecialchars(json_encode($account['description']), ENT_QUOTES); ?>,
                                    <?php echo $account['balance']; ?>,
                                    <?php echo $account['interest_rate']; ?>,
                                    <?php echo $account['tax_rate']; ?>
                                )">
                                Modifier
                            </button>
                            <form method="POST" action="actions/delete_account.php" style="display:inline;"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ? Cette action est irréversible.')">
                                <?php echo CSRFProtection::getTokenField(); ?>
                                <input type="hidden" name="account_id" value="<?php echo $account['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Account Modal -->
<div id="addAccountModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter un Compte</h3>
            <button class="modal-close" onclick="closeModal('addAccountModal')">&times;</button>
        </div>
        <form method="POST" action="actions/add_account.php">
            <?php echo CSRFProtection::getTokenField(); ?>
            <div class="form-group">
                <label for="account_name" class="form-label">Nom du compte</label>
                <input type="text" id="account_name" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="account_description" class="form-label">Description</label>
                <textarea id="account_description" name="description" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="account_balance" class="form-label">Solde initial (€)</label>
                <input type="number" id="account_balance" name="balance" class="form-input" step="0.01" value="0.00" required>
            </div>
            <div class="form-group">
                <label for="interest_rate" class="form-label">Taux de rémunération (%)</label>
                <input type="number" id="interest_rate" name="interest_rate" class="form-input" step="0.01" min="0" max="100" value="0">
            </div>
            <div class="form-group">
                <label for="tax_rate" class="form-label">Taux d'imposition (%)</label>
                <input type="number" id="tax_rate" name="tax_rate" class="form-input" step="0.01" min="0" max="100" value="0">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addAccountModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer le Compte</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Account Modal -->
<div id="editAccountModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Modifier le Compte</h3>
            <button class="modal-close" onclick="closeModal('editAccountModal')">&times;</button>
        </div>
        <form method="POST" action="actions/edit_account.php">
            <?php echo CSRFProtection::getTokenField(); ?>
            <input type="hidden" id="edit_account_id" name="account_id" value="">
            <div class="form-group">
                <label for="edit_account_name" class="form-label">Nom du compte</label>
                <input type="text" id="edit_account_name" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_account_description" class="form-label">Description</label>
                <textarea id="edit_account_description" name="description" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="edit_account_balance" class="form-label">Solde (€)</label>
                <input type="number" id="edit_account_balance" name="balance" class="form-input" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="edit_interest_rate" class="form-label">Taux de rémunération (%)</label>
                <input type="number" id="edit_interest_rate" name="interest_rate" class="form-input" step="0.01" min="0" max="100">
            </div>
            <div class="form-group">
                <label for="edit_tax_rate" class="form-label">Taux d'imposition (%)</label>
                <input type="number" id="edit_tax_rate" name="tax_rate" class="form-input" step="0.01" min="0" max="100">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editAccountModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditAccountModal(id, name, description, balance, interestRate, taxRate) {
    document.getElementById('edit_account_id').value = id;
    document.getElementById('edit_account_name').value = name;
    document.getElementById('edit_account_description').value = description;
    document.getElementById('edit_account_balance').value = balance;
    document.getElementById('edit_interest_rate').value = interestRate;
    document.getElementById('edit_tax_rate').value = taxRate;
    openModal('editAccountModal');
}
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
