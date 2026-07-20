<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Revenus';
include SRC_PATH . '/includes/header.php';

// Fetch user's incomes with account names
$incomes = fetchAll(
    "SELECT i.*, a.name as account_name 
     FROM incomes i 
     JOIN accounts a ON i.account_id = a.id 
     WHERE i.user_id = ? AND i.deleted_at IS NULL 
     ORDER BY i.start_date DESC",
    [$_SESSION['user_id']]
);

// Fetch user's accounts for the dropdowns
$user_accounts = fetchAll(
    "SELECT id, name FROM accounts WHERE user_id = ? AND deleted_at IS NULL",
    [$_SESSION['user_id']]
);

?>

<div class="container">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mes Revenus</h3>
            <button class="btn btn-primary" onclick="openModal('addIncomeModal')">
                Ajouter un Revenu
            </button>
        </div>

        <!-- Filters -->
        <div class="filters" id="income-filters">
            <div class="filter-group">
                <label for="search_incomes" class="form-label">Rechercher</label>
                <input type="text" id="search_incomes" class="filter-input" placeholder="Nom ou description...">
            </div>
            <div class="filter-group">
                <label for="filter_account_income" class="form-label">Compte</label>
                <select id="filter_account_income" class="filter-input">
                    <option value="">Tous les comptes</option>
                    <?php foreach ($user_accounts as $acc): ?>
                        <option value="<?php echo htmlspecialchars($acc['name']); ?>"><?php echo htmlspecialchars($acc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter_frequency_income" class="form-label">Fréquence</label>
                <select id="filter_frequency_income" class="filter-input">
                    <option value="">Toutes les fréquences</option>
                    <option value="ponctuel">Ponctuel</option>
                    <option value="mensuel">Mensuel</option>
                    <option value="bimensuel">Bi-mensuel</option>
                    <option value="trimestriel">Trimestriel</option>
                    <option value="semestriel">Semestriel</option>
                    <option value="annuel">Annuel</option>
                </select>
            </div>
        </div>

        <div class="table-container" id="income-table-container">
            <table class="table" id="incomes-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Compte</th>
                        <th>Montant</th>
                        <th>Fréquence</th>
                        <th>Date Début</th>
                        <th>Date Fin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($incomes)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding: 2rem; color: var(--text-muted);">
                            Aucun revenu enregistré. Commencez par en ajouter un !
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($incomes as $income): ?>
                    <tr>
                        <td><?php echo $income['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($income['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($income['description']); ?></td>
                        <td><?php echo htmlspecialchars($income['account_name']); ?></td>
                        <td class="text-success"><strong>€<?php echo number_format($income['amount'], 2); ?></strong></td>
                        <td><?php echo htmlspecialchars($income['frequency']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($income['start_date'])); ?></td>
                        <td><?php echo $income['end_date'] ? date('d/m/Y', strtotime($income['end_date'])) : 'N/A'; ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary"
                                onclick="openEditIncomeModal(
                                    <?php echo $income['id']; ?>,
                                    <?php echo htmlspecialchars(json_encode($income['name']), ENT_QUOTES); ?>,
                                    <?php echo htmlspecialchars(json_encode($income['description']), ENT_QUOTES); ?>,
                                    <?php echo $income['account_id']; ?>,
                                    <?php echo $income['amount']; ?>,
                                    <?php echo htmlspecialchars(json_encode($income['frequency']), ENT_QUOTES); ?>,
                                    <?php echo htmlspecialchars(json_encode($income['start_date']), ENT_QUOTES); ?>,
                                    <?php echo htmlspecialchars(json_encode($income['end_date'] ?? ''), ENT_QUOTES); ?>
                                )">
                                Modifier
                            </button>
                            <form method="POST" action="actions/delete_income.php" style="display:inline;"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce revenu ?')">
                                <?php echo CSRFProtection::getTokenField(); ?>
                                <input type="hidden" name="income_id" value="<?php echo $income['id']; ?>">
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

<!-- Add Income Modal -->
<div id="addIncomeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter un Revenu</h3>
            <button class="modal-close" onclick="closeModal('addIncomeModal')">&times;</button>
        </div>
        <form method="POST" action="actions/add_income.php">
            <?php echo CSRFProtection::getTokenField(); ?>
            <div class="form-group">
                <label for="income_name" class="form-label">Nom du revenu</label>
                <input type="text" id="income_name" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="income_description" class="form-label">Description</label>
                <textarea id="income_description" name="description" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="income_account" class="form-label">Compte</label>
                <select id="income_account" name="account_id" class="form-select" required>
                    <option value="">Sélectionner un compte</option>
                    <?php foreach ($user_accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="income_amount" class="form-label">Montant (€)</label>
                <input type="number" id="income_amount" name="amount" class="form-input" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="income_frequency" class="form-label">Fréquence</label>
                <select id="income_frequency" name="frequency" class="form-select" required>
                    <option value="">Sélectionner une fréquence</option>
                    <option value="ponctuel">Ponctuel</option>
                    <option value="mensuel">Tous les mois</option>
                    <option value="bimensuel">Tous les 2 mois</option>
                    <option value="trimestriel">Tous les 3 mois</option>
                    <option value="semestriel">Tous les 6 mois</option>
                    <option value="annuel">Tous les 12 mois</option>
                </select>
            </div>
            <div class="form-group">
                <label for="income_start_date" class="form-label">Date de début</label>
                <input type="date" id="income_start_date" name="start_date" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="income_end_date" class="form-label">Date de fin (optionnel)</label>
                <input type="date" id="income_end_date" name="end_date" class="form-input">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addIncomeModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer le Revenu</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Income Modal -->
<div id="editIncomeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Modifier le Revenu</h3>
            <button class="modal-close" onclick="closeModal('editIncomeModal')">&times;</button>
        </div>
        <form method="POST" action="actions/edit_income.php">
            <?php echo CSRFProtection::getTokenField(); ?>
            <input type="hidden" id="edit_income_id" name="income_id" value="">
            <div class="form-group">
                <label for="edit_income_name" class="form-label">Nom du revenu</label>
                <input type="text" id="edit_income_name" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_income_description" class="form-label">Description</label>
                <textarea id="edit_income_description" name="description" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="edit_income_account" class="form-label">Compte</label>
                <select id="edit_income_account" name="account_id" class="form-select" required>
                    <option value="">Sélectionner un compte</option>
                    <?php foreach ($user_accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_income_amount" class="form-label">Montant (€)</label>
                <input type="number" id="edit_income_amount" name="amount" class="form-input" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="edit_income_frequency" class="form-label">Fréquence</label>
                <select id="edit_income_frequency" name="frequency" class="form-select" required>
                    <option value="">Sélectionner une fréquence</option>
                    <option value="ponctuel">Ponctuel</option>
                    <option value="mensuel">Tous les mois</option>
                    <option value="bimensuel">Tous les 2 mois</option>
                    <option value="trimestriel">Tous les 3 mois</option>
                    <option value="semestriel">Tous les 6 mois</option>
                    <option value="annuel">Tous les 12 mois</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_income_start_date" class="form-label">Date de début</label>
                <input type="date" id="edit_income_start_date" name="start_date" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_income_end_date" class="form-label">Date de fin (optionnel)</label>
                <input type="date" id="edit_income_end_date" name="end_date" class="form-input">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editIncomeModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditIncomeModal(id, name, description, accountId, amount, frequency, startDate, endDate) {
    document.getElementById('edit_income_id').value = id;
    document.getElementById('edit_income_name').value = name;
    document.getElementById('edit_income_description').value = description;
    document.getElementById('edit_income_amount').value = amount;
    document.getElementById('edit_income_start_date').value = startDate;
    document.getElementById('edit_income_end_date').value = endDate || '';

    // Set account select
    var accountSelect = document.getElementById('edit_income_account');
    for (var i = 0; i < accountSelect.options.length; i++) {
        if (accountSelect.options[i].value == accountId) {
            accountSelect.selectedIndex = i;
            break;
        }
    }

    // Set frequency select
    var freqSelect = document.getElementById('edit_income_frequency');
    for (var i = 0; i < freqSelect.options.length; i++) {
        if (freqSelect.options[i].value === frequency) {
            freqSelect.selectedIndex = i;
            break;
        }
    }

    openModal('editIncomeModal');
}

// Filtering for incomes table
document.addEventListener('DOMContentLoaded', function() {
    var filterInputs = document.querySelectorAll('#income-filters .filter-input');
    var table = document.getElementById('incomes-table');

    filterInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            filterIncomes();
        });
    });

    function filterIncomes() {
        var searchVal = document.getElementById('search_incomes').value.toLowerCase();
        var accountSelect = document.getElementById('filter_account_income');
        var accountVal = accountSelect.value ? accountSelect.options[accountSelect.selectedIndex].text.toLowerCase() : '';
        var freqSelect = document.getElementById('filter_frequency_income');
        var freqVal = freqSelect.value ? freqSelect.options[freqSelect.selectedIndex].text.toLowerCase() : '';
        var rows = table.querySelectorAll('tbody tr');

        rows.forEach(function(row) {
            if (row.cells.length < 7) { row.style.display = ''; return; }
            var name = row.cells[1].textContent.toLowerCase();
            var desc = row.cells[2].textContent.toLowerCase();
            var account = row.cells[3].textContent.toLowerCase();
            var freq = row.cells[5].textContent.toLowerCase();

            var matchSearch = !searchVal || name.includes(searchVal) || desc.includes(searchVal);
            var matchAccount = !accountVal || account.includes(accountVal);
            var matchFreq = !freqVal || freq.includes(freqVal);

            row.style.display = (matchSearch && matchAccount && matchFreq) ? '' : 'none';
        });
    }
});
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
