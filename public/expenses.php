<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Dépenses';
include SRC_PATH . '/includes/header.php';

// Dummy data for expenses
$expenses = [
    [
        'id' => 1,
        'name' => 'Crédit Moto',
        'description' => 'Crédit pour la Triumph Tiger 660 Sport 2023',
        'account' => 'Compte Courant',
        'amount' => 250.00,
        'frequency' => 'Mensuel',
        'start_date' => '2023-01-01',
        'end_date' => '2028-12-31'
    ],
    [
        'id' => 2,
        'name' => 'iPhone 19',
        'description' => 'iPhone 19 Pro Max Limited Hanna Montana Edition',
        'account' => 'Compte Courant',
        'amount' => 4321.00,
        'frequency' => 'Ponctuel',
        'start_date' => '2025-09-01',
        'end_date' => null
    ],
    [
        'id' => 3,
        'name' => 'Courses Alimentaires',
        'description' => 'Courses hebdomadaires',
        'account' => 'Compte Courant',
        'amount' => 150.00,
        'frequency' => 'Hebdomadaire',
        'start_date' => '2025-01-01',
        'end_date' => null
    ],
    [
        'id' => 4,
        'name' => 'Essence',
        'description' => 'Carburant voiture',
        'account' => 'Compte Courant',
        'amount' => 80.00,
        'frequency' => 'Bi-mensuel',
        'start_date' => '2025-01-01',
        'end_date' => null
    ]
];
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
        <div class="filters">
            <div class="filter-group">
                <label for="search_expenses" class="form-label">Rechercher</label>
                <input type="text" id="search_expenses" class="filter-input" placeholder="Nom ou description...">
            </div>
            <div class="filter-group">
                <label for="filter_account" class="form-label">Compte</label>
                <select id="filter_account" class="filter-input">
                    <option value="">Tous les comptes</option>
                    <option value="Compte Courant">Compte Courant</option>
                    <option value="Livret A">Livret A</option>
                    <option value="CTO">CTO</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter_frequency" class="form-label">Fréquence</label>
                <select id="filter_frequency" class="filter-input">
                    <option value="">Toutes les fréquences</option>
                    <option value="Ponctuel">Ponctuel</option>
                    <option value="Mensuel">Mensuel</option>
                    <option value="Hebdomadaire">Hebdomadaire</option>
                    <option value="Bi-mensuel">Bi-mensuel</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
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
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo $expense['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($expense['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($expense['description']); ?></td>
                        <td><?php echo htmlspecialchars($expense['account']); ?></td>
                        <td class="text-danger"><strong>€<?php echo number_format($expense['amount'], 2); ?></strong></td>
                        <td><?php echo $expense['frequency']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($expense['start_date'])); ?></td>
                        <td><?php echo $expense['end_date'] ? date('d/m/Y', strtotime($expense['end_date'])) : 'N/A'; ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="openModal('editExpenseModal')">
                                <span>✏️</span> Modifier
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cette dépense ?')) { /* Delete logic */ }">
                                <span>🗑️</span> Supprimer
                            </button>
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
                    <option value="1">Compte Courant</option>
                    <option value="2">Livret A</option>
                    <option value="3">CTO</option>
                </select>
            </div>
            <div class="form-group">
                <label for="expense_amount" class="form-label">Montant (€)</label>
                <input type="number" id="expense_amount" name="amount" class="form-input" step="0.01" min="0" required>
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
            <input type="hidden" name="expense_id" value="">
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
                    <option value="1">Compte Courant</option>
                    <option value="2">Livret A</option>
                    <option value="3">CTO</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_expense_amount" class="form-label">Montant (€)</label>
                <input type="number" id="edit_expense_amount" name="amount" class="form-input" step="0.01" min="0" required>
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

<?php include SRC_PATH . '/includes/footer.php'; ?>
