<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Exceptions';
include SRC_PATH . '/includes/header.php';

// Exceptions with the name of the dépense / revenu they apply to
$exceptions = fetchAll(
    "SELECT ex.*, e.name AS expense_name, i.name AS income_name
     FROM exceptions ex
     LEFT JOIN expenses e ON e.id = ex.expense_id
     LEFT JOIN incomes  i ON i.id = ex.income_id
     WHERE ex.user_id = ?
     ORDER BY ex.start_date DESC",
    [$_SESSION['user_id']]
);

$user_expenses = fetchAll(
    "SELECT id, name, amount FROM expenses WHERE user_id = ? AND deleted_at IS NULL ORDER BY name",
    [$_SESSION['user_id']]
);

$user_incomes = fetchAll(
    "SELECT id, name, amount FROM incomes WHERE user_id = ? AND deleted_at IS NULL ORDER BY name",
    [$_SESSION['user_id']]
);

$has_targets = !empty($user_expenses) || !empty($user_incomes);
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mes Exceptions</h3>
            <?php if ($has_targets): ?>
            <button class="btn btn-primary" onclick="openModal('addExceptionModal')">
                Ajouter une Exception
            </button>
            <?php endif; ?>
        </div>

        <p style="color: var(--gray-600); margin-bottom: 1rem;">
            Une exception remplace le montant d'une dépense ou d'un revenu sur une période donnée,
            sans modifier son montant initial ni les mois situés en dehors de l'exception.
            Les prévisions en tiennent compte automatiquement.
        </p>

        <?php if (!$has_targets && empty($exceptions)): ?>
            <p class="text-center">Créez d'abord une dépense ou un revenu pour pouvoir y ajouter une exception.</p>
        <?php else: ?>
            <?php if (!$has_targets): ?>
            <p class="text-center">La dépense ou le revenu concerné a été supprimé : ces exceptions ne s'appliquent plus et peuvent être supprimées.</p>
            <?php endif; ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>S'applique à</th>
                        <th>Montant</th>
                        <th>Fréquence</th>
                        <th>Date de début</th>
                        <th>Date de fin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($exceptions)): ?>
                    <tr><td colspan="8" class="text-center">Aucune exception pour le moment.</td></tr>
                    <?php else: ?>
                    <?php foreach ($exceptions as $exception): ?>
                    <?php
                        $is_expense  = !empty($exception['expense_id']);
                        $target_name = $is_expense ? $exception['expense_name'] : $exception['income_name'];
                        $target_val  = $is_expense
                            ? 'expense:' . (int) $exception['expense_id']
                            : 'income:' . (int) $exception['income_id'];
                        $payload = [
                            'id'          => (int) $exception['id'],
                            'target'      => $target_val,
                            'name'        => $exception['name'],
                            'description' => $exception['description'] ?? '',
                            'amount'      => $exception['amount'],
                            'frequency'   => $exception['frequency'],
                            'interval'    => $exception['interval_months'] ?? null,
                            'start_date'  => $exception['start_date'],
                            'end_date'    => $exception['end_date'] ?? '',
                        ];
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($exception['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($exception['description'] ?? ''); ?></td>
                        <td>
                            <span class="<?php echo $is_expense ? 'text-danger' : 'text-success'; ?>">
                                <?php echo $is_expense ? 'Dépense' : 'Revenu'; ?>
                            </span>
                            — <?php echo htmlspecialchars($target_name ?? ''); ?>
                        </td>
                        <td><?php echo formatCurrency($exception['amount']); ?></td>
                        <td><?php echo formatFrequency($exception['frequency'], $exception['interval_months'] ?? null); ?></td>
                        <td><?php echo formatDate($exception['start_date']); ?></td>
                        <td><?php echo $exception['end_date'] ? formatDate($exception['end_date']) : '—'; ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary"
                                onclick="openEditExceptionModal(<?php echo htmlspecialchars(json_encode($payload), ENT_QUOTES); ?>)">
                                Modifier
                            </button>
                            <form method="POST" action="actions/delete_exception.php" style="display:inline;"
                                  onsubmit="return confirmSubmit(event, 'Êtes-vous sûr de vouloir supprimer cette exception ? Le montant initial sera de nouveau appliqué.', 'Supprimer l\'exception', 'Supprimer');">
                                <?php echo CSRFProtection::getTokenField(); ?>
                                <input type="hidden" name="exception_id" value="<?php echo (int) $exception['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($has_targets): ?>
<!-- Add Exception Modal -->
<div id="addExceptionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter une Exception</h3>
            <button class="modal-close" onclick="closeModal('addExceptionModal')">&times;</button>
        </div>
        <form method="POST" action="actions/add_exception.php">
            <?php echo CSRFProtection::getTokenField(); ?>
            <div class="form-group">
                <label for="exception_target" class="form-label">S'applique à</label>
                <select id="exception_target" name="target" class="form-select" required>
                    <option value="">Sélectionner une dépense ou un revenu</option>
                    <?php if (!empty($user_expenses)): ?>
                    <optgroup label="Dépenses">
                        <?php foreach ($user_expenses as $exp): ?>
                        <option value="expense:<?php echo (int) $exp['id']; ?>">
                            <?php echo htmlspecialchars($exp['name']); ?> (<?php echo formatCurrency($exp['amount']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                    <?php if (!empty($user_incomes)): ?>
                    <optgroup label="Revenus">
                        <?php foreach ($user_incomes as $inc): ?>
                        <option value="income:<?php echo (int) $inc['id']; ?>">
                            <?php echo htmlspecialchars($inc['name']); ?> (<?php echo formatCurrency($inc['amount']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="exception_name" class="form-label">Nom de l'exception</label>
                <input type="text" id="exception_name" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="exception_description" class="form-label">Description</label>
                <textarea id="exception_description" name="description" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="exception_amount" class="form-label">Montant de remplacement (€)</label>
                <input type="number" id="exception_amount" name="amount" class="form-input" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="exception_recurrence" class="form-label">Durée</label>
                <select id="exception_recurrence" name="recurrence" class="form-select" onchange="toggleInterval(this, 'exception_interval_group')" required>
                    <option value="recurrent">Tous les N mois</option>
                    <option value="ponctuel">Ponctuelle</option>
                </select>
            </div>
            <div class="form-group" id="exception_interval_group">
                <label for="exception_interval" class="form-label">Tous les combien de mois&nbsp;?</label>
                <input type="number" id="exception_interval" name="interval_months" class="form-input" min="1" max="120" value="1">
            </div>
            <div class="form-group">
                <label for="exception_start_date" class="form-label">Date de début</label>
                <input type="date" id="exception_start_date" name="start_date" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="exception_end_date" class="form-label">Date de fin (optionnel)</label>
                <input type="date" id="exception_end_date" name="end_date" class="form-input">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addExceptionModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer l'Exception</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Exception Modal -->
<div id="editExceptionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Modifier l'Exception</h3>
            <button class="modal-close" onclick="closeModal('editExceptionModal')">&times;</button>
        </div>
        <form method="POST" action="actions/edit_exception.php">
            <?php echo CSRFProtection::getTokenField(); ?>
            <input type="hidden" id="edit_exception_id" name="exception_id" value="">
            <div class="form-group">
                <label for="edit_exception_target" class="form-label">S'applique à</label>
                <select id="edit_exception_target" name="target" class="form-select" required>
                    <?php if (!empty($user_expenses)): ?>
                    <optgroup label="Dépenses">
                        <?php foreach ($user_expenses as $exp): ?>
                        <option value="expense:<?php echo (int) $exp['id']; ?>"><?php echo htmlspecialchars($exp['name']); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                    <?php if (!empty($user_incomes)): ?>
                    <optgroup label="Revenus">
                        <?php foreach ($user_incomes as $inc): ?>
                        <option value="income:<?php echo (int) $inc['id']; ?>"><?php echo htmlspecialchars($inc['name']); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_exception_name" class="form-label">Nom de l'exception</label>
                <input type="text" id="edit_exception_name" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_exception_description" class="form-label">Description</label>
                <textarea id="edit_exception_description" name="description" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="edit_exception_amount" class="form-label">Montant de remplacement (€)</label>
                <input type="number" id="edit_exception_amount" name="amount" class="form-input" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="edit_exception_recurrence" class="form-label">Durée</label>
                <select id="edit_exception_recurrence" name="recurrence" class="form-select" onchange="toggleInterval(this, 'edit_exception_interval_group')" required>
                    <option value="recurrent">Tous les N mois</option>
                    <option value="ponctuel">Ponctuelle</option>
                </select>
            </div>
            <div class="form-group" id="edit_exception_interval_group">
                <label for="edit_exception_interval" class="form-label">Tous les combien de mois&nbsp;?</label>
                <input type="number" id="edit_exception_interval" name="interval_months" class="form-input" min="1" max="120" value="1">
            </div>
            <div class="form-group">
                <label for="edit_exception_start_date" class="form-label">Date de début</label>
                <input type="date" id="edit_exception_start_date" name="start_date" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_exception_end_date" class="form-label">Date de fin (optionnel)</label>
                <input type="date" id="edit_exception_end_date" name="end_date" class="form-input">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editExceptionModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
// openModal() only takes a modal id, so the fields are filled in beforehand.
function openEditExceptionModal(exception) {
    document.getElementById('edit_exception_id').value          = exception.id;
    document.getElementById('edit_exception_target').value      = exception.target;
    document.getElementById('edit_exception_name').value        = exception.name;
    document.getElementById('edit_exception_description').value = exception.description || '';
    document.getElementById('edit_exception_amount').value      = exception.amount;
    var legacy = { mensuel: 1, bimensuel: 2, trimestriel: 3, semestriel: 6, annuel: 12 };
    var n = (exception.interval !== null && exception.interval !== undefined) ? parseInt(exception.interval, 10) : (legacy[exception.frequency] || 0);
    var recSelect = document.getElementById('edit_exception_recurrence');
    recSelect.value = (n > 0) ? 'recurrent' : 'ponctuel';
    document.getElementById('edit_exception_interval').value = (n > 0) ? n : 1;
    toggleInterval(recSelect, 'edit_exception_interval_group');
    document.getElementById('edit_exception_start_date').value  = exception.start_date || '';
    document.getElementById('edit_exception_end_date').value    = exception.end_date || '';
    openModal('editExceptionModal');
}
</script>
<?php endif; ?>

<?php include SRC_PATH . '/includes/footer.php'; ?>
