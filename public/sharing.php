<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Partage';
include SRC_PATH . '/includes/header.php';

// Fetch accounts I own that I can share
$my_accounts = fetchAll(
    "SELECT id, name FROM accounts WHERE user_id = ? AND deleted_at IS NULL",
    [$_SESSION['user_id']]
);

// Fetch accounts I am sharing with others
$shared_by_me = fetchAll(
    "SELECT s.*, a.name as account_name 
     FROM account_shares s 
     JOIN accounts a ON s.account_id = a.id 
     WHERE s.owner_id = ? AND s.status != 'revoked'",
    [$_SESSION['user_id']]
);

// Fetch accounts shared with me
$shared_with_me = fetchAll(
    "SELECT s.*, a.name as account_name, u.first_name, u.last_name 
     FROM account_shares s 
     JOIN accounts a ON s.account_id = a.id 
     JOIN users u ON s.owner_id = u.id 
     WHERE (s.shared_with_user_id = ? OR s.shared_with_email = ?) 
     AND s.status = 'accepted'",
    [$_SESSION['user_id'], $_SESSION['user_email']]
);

// Fetch recent sharing activity
$recent_shares = fetchAll(
    "SELECT action, metadata, created_at FROM activity_logs 
     WHERE user_id = ? AND entity_type = 'account_share' 
     ORDER BY created_at DESC LIMIT 5",
    [$_SESSION['user_id']]
);
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
                    <?php if (empty($shared_by_me)): ?>
                    <tr><td colspan="6" class="text-center">Vous ne partagez aucun compte pour le moment.</td></tr>
                    <?php else: ?>
                    <?php foreach ($shared_by_me as $share): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($share['account_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($share['shared_with_email']); ?></td>
                        <td><?php echo $share['access_type'] === 'read_only' ? 'Lecture seule' : 'Lecture/Écriture'; ?></td>
                        <td><?php echo formatDate($share['shared_at']); ?></td>
                        <td>
                            <span class="<?php echo $share['status'] === 'accepted' ? 'text-success' : 'text-warning'; ?>">
                                <?php echo ucfirst($share['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="openModal('editShareModal', <?php echo $share['id']; ?>)">
                                <span>✏️</span> Modifier
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir révoquer l\'accès ?')) { revokeAccess(<?php echo $share['id']; ?>); }">
                                <span>🚫</span> Révoquer
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
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
                    <?php if (empty($shared_with_me)): ?>
                    <tr><td colspan="5" class="text-center">Aucun compte ne vous est partagé.</td></tr>
                    <?php else: ?>
                    <?php foreach ($shared_with_me as $share): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($share['account_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($share['first_name'] . ' ' . $share['last_name']); ?></td>
                        <td><?php echo $share['access_type'] === 'read_only' ? 'Lecture seule' : 'Lecture/Écriture'; ?></td>
                        <td><?php echo formatDate($share['shared_at']); ?></td>
                        <td>
                            <a href="shared_account_details.php?id=<?php echo $share['account_id']; ?>" class="btn btn-sm btn-primary">
                                <span>👁️</span> Consulter
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
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
            <?php if (empty($recent_shares)): ?>
                <p class="text-center text-muted">Aucune activité récente.</p>
            <?php else: ?>
                <?php foreach ($recent_shares as $log): ?>
                <?php $meta = json_decode($log['metadata'], true); ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
                    <div>
                        <strong><?php echo htmlspecialchars($log['action']); ?></strong>
                        <p style="margin: 0; color: var(--gray-600);"><?php echo htmlspecialchars($meta['email'] ?? ''); ?></p>
                    </div>
                    <span style="color: var(--gray-500); font-size: 0.875rem;"><?php echo formatDate($log['created_at']); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
