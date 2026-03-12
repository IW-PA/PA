<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Comptes';
include SRC_PATH . '/includes/header.php';

// Dummy data for accounts
$accounts = [
    [
        'id' => 1,
        'name' => 'Compte Courant',
        'description' => 'Compte courant Société Générale',
        'balance' => 3500.00,
        'interest_rate' => 0.0,
        'tax_rate' => 0.0,
        'created_date' => '2020-01-15'
    ],
    [
        'id' => 2,
        'name' => 'Livret A',
        'description' => 'Livret A individuel',
        'balance' => 8500.00,
        'interest_rate' => 1.7,
        'tax_rate' => 0.0,
        'created_date' => '2018-03-10'
    ],
    [
        'id' => 3,
        'name' => 'CTO',
        'description' => 'Compte Titre Ordinaire',
        'balance' => 450.00,
        'interest_rate' => 7.0,
        'tax_rate' => 30.0,
        'created_date' => '2022-06-20'
    ]
];
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mes Comptes</h3>
            <button class="btn btn-primary" onclick="openModal('addAccountModal')">
                <span>➕</span> Ajouter un Compte
            </button>
        </div>

        <div class="table-container">
            <table class="table">
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
                    <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td><?php echo $account['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($account['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($account['description']); ?></td>
                        <td class="text-success"><strong>€<?php echo number_format($account['balance'], 2); ?></strong></td>
                        <td><?php echo $account['interest_rate']; ?>%</td>
                        <td><?php echo $account['tax_rate']; ?>%</td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="openModal('editAccountModal')">
                                <span>✏️</span> Modifier
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')) { /* Delete logic */ }">
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
            <input type="hidden" name="account_id" value="">
            <div class="form-group">
                <label for="edit_account_name" class="form-label">Nom du compte</label>
                <input type="text" id="edit_account_name" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_account_description" class="form-label">Description</label>
                <textarea id="edit_account_description" name="description" class="form-input" rows="3"></textarea>
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

<?php include SRC_PATH . '/includes/footer.php'; ?>
