<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Revenus';
include SRC_PATH . '/includes/header.php';

// Dummy data for incomes
$incomes = [
    [
        'id' => 1,
        'name' => 'Salaire',
        'description' => 'Salaire Alternant Développeur Web',
        'account' => 'Compte Courant',
        'amount' => 1170.00,
        'frequency' => 'Mensuel',
        'start_date' => '2025-01-01',
        'end_date' => '2027-12-31'
    ],
    [
        'id' => 2,
        'name' => 'Prime de fin d\'année',
        'description' => 'Prime de fin d\'année',
        'account' => 'Compte Courant',
        'amount' => 150.00,
        'frequency' => 'Annuel',
        'start_date' => '2025-01-01',
        'end_date' => '2027-12-31'
    ],
    [
        'id' => 3,
        'name' => 'Alimentation CTO',
        'description' => 'Alimentation mensuelle du compte titre ordinaire',
        'account' => 'CTO',
        'amount' => 50.00,
        'frequency' => 'Mensuel',
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31'
    ],
    [
        'id' => 4,
        'name' => 'Freelance',
        'description' => 'Projets freelance occasionnels',
        'account' => 'Compte Courant',
        'amount' => 500.00,
        'frequency' => 'Ponctuel',
        'start_date' => '2025-01-15',
        'end_date' => null
    ]
];
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mes Revenus</h3>
            <button class="btn btn-primary" onclick="openModal('addIncomeModal')">
                <span>➕</span> Ajouter un Revenu
            </button>
        </div>

        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label for="search_incomes" class="form-label">Rechercher</label>
                <input type="text" id="search_incomes" class="filter-input" placeholder="Nom ou description...">
            </div>
            <div class="filter-group">
                <label for="filter_account_income" class="form-label">Compte</label>
                <select id="filter_account_income" class="filter-input">
                    <option value="">Tous les comptes</option>
                    <option value="Compte Courant">Compte Courant</option>
                    <option value="Livret A">Livret A</option>
                    <option value="CTO">CTO</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter_frequency_income" class="form-label">Fréquence</label>
                <select id="filter_frequency_income" class="filter-input">
                    <option value="">Toutes les fréquences</option>
                    <option value="Ponctuel">Ponctuel</option>
                    <option value="Mensuel">Mensuel</option>
                    <option value="Annuel">Annuel</option>
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
                    <?php foreach ($incomes as $income): ?>
                    <tr>
                        <td><?php echo $income['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($income['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($income['description']); ?></td>
                        <td><?php echo htmlspecialchars($income['account']); ?></td>
                        <td class="text-success"><strong>€<?php echo number_format($income['amount'], 2); ?></strong></td>
                        <td><?php echo $income['frequency']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($income['start_date'])); ?></td>
                        <td><?php echo $income['end_date'] ? date('d/m/Y', strtotime($income['end_date'])) : 'N/A'; ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="openModal('editIncomeModal')">
                                <span>✏️</span> Modifier
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer ce revenu ?')) { /* Delete logic */ }">
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
                    <option value="1">Compte Courant</option>
                    <option value="2">Livret A</option>
                    <option value="3">CTO</option>
                </select>
            </div>
            <div class="form-group">
                <label for="income_amount" class="form-label">Montant (€)</label>
                <input type="number" id="income_amount" name="amount" class="form-input" step="0.01" min="0" required>
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
            <input type="hidden" name="income_id" value="">
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
                    <option value="1">Compte Courant</option>
                    <option value="2">Livret A</option>
                    <option value="3">CTO</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_income_amount" class="form-label">Montant (€)</label>
                <input type="number" id="edit_income_amount" name="amount" class="form-input" step="0.01" min="0" required>
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

<?php include SRC_PATH . '/includes/footer.php'; ?>
