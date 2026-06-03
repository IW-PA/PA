<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Dépenses';
include SRC_PATH . '/includes/header.php';

// Fetch user's expenses with account names
$expenses = fetchAll(
    "SELECT e.*, a.name as account_name 
     FROM expenses e 
     JOIN accounts a ON e.account_id = a.id 
     WHERE e.user_id = ? AND e.deleted_at IS NULL 
     ORDER BY e.start_date DESC",
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
            <h3 class="card-title">Mes Dépenses</h3>
            <button class="btn btn-primary" onclick="openModal('addExpenseModal')">
                <span>➕</span> Ajouter une Dépense
            </button>
        </div>

        <!-- Filters -->
        <div class="filters" id="expense-filters">
            <div class="filter-group">
                <label for="search_expenses" class="form-label">Rechercher</label>
                <input type="text" id="search_expenses" class="filter-input" placeholder="Nom ou description...">
            </div>
            <div class="filter-group">
                <label for="filter_account" class="form-label">Compte</label>
                <select id="filter_account" class="filter-input">
                    <option value="">Tous les comptes</option>
                    <?php foreach ($user_accounts as $acc): ?>
                        <option value="<?php echo htmlspecialchars($acc['name']); ?>"><?php echo htmlspecialchars($acc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter_frequency" class="form-label">Fréquence</label>
                <select id="filter_frequency" class="filter-input">
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

        <div class="table-container" id="expense-table-container">
            <table class="table" id="expenses-table">
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
                    <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding: 2rem; color: var(--text-muted);">
                            Aucune dépense enregistrée. Commencez par en ajouter une !
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo $expense['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($expense['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($expense['description']); ?></td>
                        <td><?php echo htmlspecialchars($expense['account_name']); ?></td>
                        <td class="text-danger"><strong>€<?php echo number_format($expense['amount'], 2); ?></strong></td>
                        <td><?php echo htmlspecialchars($expense['frequency']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($expense['start_date'])); ?></td>
                        <td><?php echo $expense['end_date'] ? date('d/m/Y', strtotime($expense['end_date'])) : 'N/A'; ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary"
                                onclick="openEditExpenseModal(
                                    <?php echo $expense['id']; ?>,
                                    <?php echo htmlspecialchars(json_encode($expense['name']), ENT_QUOTES); ?>,
                                    <?php echo htmlspecialchars(json_encode($expense['description']), ENT_QUOTES); ?>,
                                    <?php echo $expense['account_id']; ?>,
                                    <?php echo $expense['amount']; ?>,
                                    <?php echo htmlspecialchars(json_encode($expense['frequency']), ENT_QUOTES); ?>,
                                    <?php echo htmlspecialchars(json_encode($expense['start_date']), ENT_QUOTES); ?>,
                                    <?php echo htmlspecialchars(json_encode($expense['end_date'] ?? ''), ENT_QUOTES); ?>
                                )">
                                <span>✏️</span> Modifier
                            </button>
                            <form method="POST" action="actions/delete_expense.php" style="display:inline;"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette dépense ?')">
                                <?php echo CSRFProtection::getTokenField(); ?>
                                <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <span>🗑️</span> Supprimer
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

<!-- Add Expense Modal -->
<div id="addExpenseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter une Dépense</h3>
            <button class="modal-close" onclick="closeModal('addExpenseModal')">&times;</button>
        </div>
        <form method="POST" action="actions/add_expense.php">
            <?php echo CSRFProtection::getTokenField(); ?>
            <div class="form-group">
                <label for="expense_name" class="form-label">Nom de la dépense</label>
                <input type="text" id="expense_name" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="expense_description" class="form-label">Description</label>
                <textarea id="expense_description" name="description" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="expense_account" class="form-label">Compte</label>
                <select id="expense_account" name="account_id" class="form-select" required>
                    <option value="">Sélectionner un compte</option>
                    <?php foreach ($user_accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="expense_amount" class="form-label">Montant (€)</label>
                <input type="number" id="expense_amount" name="amount" class="form-input" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="expense_frequency" class="form-label">Fréquence</label>
                <select id="expense_frequency" name="frequency" class="form-select" required>
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
                <label for="expense_start_date" class="form-label">Date de début</label>
                <input type="date" id="expense_start_date" name="start_date" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="expense_end_date" class="form-label">Date de fin (optionnel)</label>
                <input type="date" id="expense_end_date" name="end_date" class="form-input">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addExpenseModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer la Dépense</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Expense Modal -->
<div id="editExpenseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Modifier la Dépense</h3>
            <button class="modal-close" onclick="closeModal('editExpenseModal')">&times;</button>
        </div>
        <form method="POST" action="actions/edit_expense.php">
            <?php echo CSRFProtection::getTokenField(); ?>
            <input type="hidden" id="edit_expense_id" name="expense_id" value="">
            <div class="form-group">
                <label for="edit_expense_name" class="form-label">Nom de la dépense</label>
                <input type="text" id="edit_expense_name" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_expense_description" class="form-label">Description</label>
                <textarea id="edit_expense_description" name="description" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="edit_expense_account" class="form-label">Compte</label>
                <select id="edit_expense_account" name="account_id" class="form-select" required>
                    <option value="">Sélectionner un compte</option>
                    <?php foreach ($user_accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_expense_amount" class="form-label">Montant (€)</label>
                <input type="number" id="edit_expense_amount" name="amount" class="form-input" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="edit_expense_frequency" class="form-label">Fréquence</label>
                <select id="edit_expense_frequency" name="frequency" class="form-select" required>
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
                <label for="edit_expense_start_date" class="form-label">Date de début</label>
                <input type="date" id="edit_expense_start_date" name="start_date" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_expense_end_date" class="form-label">Date de fin (optionnel)</label>
                <input type="date" id="edit_expense_end_date" name="end_date" class="form-input">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editExpenseModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditExpenseModal(id, name, description, accountId, amount, frequency, startDate, endDate) {
    document.getElementById('edit_expense_id').value = id;
    document.getElementById('edit_expense_name').value = name;
    document.getElementById('edit_expense_description').value = description;
    document.getElementById('edit_expense_amount').value = amount;
    document.getElementById('edit_expense_start_date').value = startDate;
    document.getElementById('edit_expense_end_date').value = endDate || '';

    // Set account select
    var accountSelect = document.getElementById('edit_expense_account');
    for (var i = 0; i < accountSelect.options.length; i++) {
        if (accountSelect.options[i].value == accountId) {
            accountSelect.selectedIndex = i;
            break;
        }
    }

    // Set frequency select
    var freqSelect = document.getElementById('edit_expense_frequency');
    for (var i = 0; i < freqSelect.options.length; i++) {
        if (freqSelect.options[i].value === frequency) {
            freqSelect.selectedIndex = i;
            break;
        }
    }

    openModal('editExpenseModal');
}

// Filtering for expenses table
document.addEventListener('DOMContentLoaded', function() {
    var filterInputs = document.querySelectorAll('#expense-filters .filter-input');
    var table = document.getElementById('expenses-table');

    filterInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            filterExpenses();
        });
    });

    function filterExpenses() {
        var searchVal = document.getElementById('search_expenses').value.toLowerCase();
        var accountSelect = document.getElementById('filter_account');
        var accountVal = accountSelect.value ? accountSelect.options[accountSelect.selectedIndex].text.toLowerCase() : '';
        var freqSelect = document.getElementById('filter_frequency');
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
