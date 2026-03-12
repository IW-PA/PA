<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Partage';
include SRC_PATH . '/includes/header.php';

// Dummy data for shared accounts
$shared_accounts = [
    [
        'id' => 1,
        'account_name' => 'Compte Courant',
        'owner' => 'Jean Dupont',
        'shared_with' => 'marie.martin@example.com',
        'access_type' => 'Lecture seule',
        'shared_date' => '2024-12-15',
        'status' => 'active'
    ],
    [
        'id' => 2,
        'account_name' => 'Livret A',
        'owner' => 'Jean Dupont',
        'shared_with' => 'pierre.durand@example.com',
        'access_type' => 'Lecture seule',
        'shared_date' => '2024-11-20',
        'status' => 'active'
    ]
];

// Dummy data for accounts I can share
$my_accounts = [
    ['id' => 1, 'name' => 'Compte Courant'],
    ['id' => 2, 'name' => 'Livret A'],
    ['id' => 3, 'name' => 'CTO']
];

// Dummy data for accounts shared with me
$accounts_shared_with_me = [
    [
        'id' => 4,
        'account_name' => 'Compte Famille',
        'owner' => 'Sophie Bernard',
        'access_type' => 'Lecture seule',
        'shared_date' => '2024-10-10'
    ]
];
?>

<div class="container">
    <!-- Share Account -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Partager un Compte</h3>
        </div>
        <form method="POST" action="actions/share_account.php">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label for="share_account" class="form-label">Compte à partager</label>
                    <select id="share_account" name="account_id" class="form-select" required>
                        <option value="">Sélectionner un compte</option>
                        <?php foreach ($my_accounts as $account): ?>
                        <option value="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="share_email" class="form-label">Email de la personne</label>
                    <input type="email" id="share_email" name="email" class="form-input" placeholder="exemple@email.com" required>
                </div>
            </div>
            <div class="form-group">
                <label for="share_message" class="form-label">Message d'invitation (optionnel)</label>
                <textarea id="share_message" name="message" class="form-input" rows="3" placeholder="Bonjour, je vous invite à consulter mon compte..."></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">
                    <span>📧</span> Envoyer l'Invitation
                </button>
            </div>
        </form>
    </div>

    <!-- Accounts I'm Sharing -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Comptes que je Partage</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Compte</th>
                        <th>Partagé avec</th>
                        <th>Type d'Accès</th>
                        <th>Date de Partage</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shared_accounts as $share): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($share['account_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($share['shared_with']); ?></td>
                        <td><?php echo $share['access_type']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($share['shared_date'])); ?></td>
                        <td>
                            <span class="<?php echo $share['status'] === 'active' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ucfirst($share['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="openModal('editShareModal')">
                                <span>✏️</span> Modifier
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir révoquer l\'accès ?')) { revokeAccess(<?php echo $share['id']; ?>); }">
                                <span>🚫</span> Révoquer
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Accounts Shared With Me -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Comptes Partagés avec Moi</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Compte</th>
                        <th>Propriétaire</th>
                        <th>Type d'Accès</th>
                        <th>Date de Partage</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts_shared_with_me as $share): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($share['account_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($share['owner']); ?></td>
                        <td><?php echo $share['access_type']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($share['shared_date'])); ?></td>
                        <td>
                            <a href="shared_account_details.php?id=<?php echo $share['id']; ?>" class="btn btn-sm btn-primary">
                                <span>👁️</span> Consulter
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sharing Guidelines -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📋 Règles de Partage</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div>
                <h4>✅ Ce qui est autorisé</h4>
                <ul style="list-style: none; padding: 0;">
                    <li>📊 Consultation des soldes</li>
                    <li>📈 Visualisation des transactions</li>
                    <li>📋 Accès aux prévisions</li>
                    <li>📧 Notifications par email</li>
                </ul>
            </div>
            <div>
                <h4>❌ Ce qui est interdit</h4>
                <ul style="list-style: none; padding: 0;">
                    <li>✏️ Modification des données</li>
                    <li>➕ Ajout de transactions</li>
                    <li>🗑️ Suppression d'éléments</li>
                    <li>⚙️ Modification des paramètres</li>
                </ul>
            </div>
            <div>
                <h4>🔒 Sécurité</h4>
                <ul style="list-style: none; padding: 0;">
                    <li>🔐 Accès en lecture seule uniquement</li>
                    <li>📧 Invitation par email obligatoire</li>
                    <li>⏰ Accès révocable à tout moment</li>
                    <li>📝 Traçabilité des accès</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Sharing Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activité Récente</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
                <div>
                    <strong>Invitation envoyée</strong>
                    <p style="margin: 0; color: var(--gray-600);">Compte Courant → marie.martin@example.com</p>
                </div>
                <span style="color: var(--gray-500); font-size: 0.875rem;">Il y a 2 heures</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
                <div>
                    <strong>Accès révoqué</strong>
                    <p style="margin: 0; color: var(--gray-600);">Livret A → pierre.durand@example.com</p>
                </div>
                <span style="color: var(--gray-500); font-size: 0.875rem;">Il y a 1 jour</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
                <div>
                    <strong>Nouvel accès reçu</strong>
                    <p style="margin: 0; color: var(--gray-600);">Compte Famille ← sophie.bernard@example.com</p>
                </div>
                <span style="color: var(--gray-500); font-size: 0.875rem;">Il y a 3 jours</span>
            </div>
        </div>
    </div>
</div>

<!-- Edit Share Modal -->
<div id="editShareModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Modifier le Partage</h3>
            <button class="modal-close" onclick="closeModal('editShareModal')">&times;</button>
        </div>
        <form method="POST" action="actions/edit_share.php">
            <input type="hidden" name="share_id" value="">
            <div class="form-group">
                <label for="edit_access_type" class="form-label">Type d'Accès</label>
                <select id="edit_access_type" name="access_type" class="form-select" required>
                    <option value="read_only">Lecture seule</option>
                    <option value="read_write" disabled>Lecture/Écriture (bientôt disponible)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_share_message" class="form-label">Message (optionnel)</label>
                <textarea id="edit_share_message" name="message" class="form-input" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editShareModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
function revokeAccess(shareId) {
    // AJAX call to revoke access
    console.log('Revoking access for share:', shareId);
    alert('Accès révoqué avec succès');
}

// Auto-refresh sharing activity every 30 seconds
setInterval(function() {
    // This would fetch new sharing activity
    console.log('Checking for new sharing activity...');
}, 30000);
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
