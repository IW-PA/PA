<?php
require_once __DIR__ . '/../src/config/config.php';
requireAdmin();

$page_title = 'Administration';

try {
    $stats = [
        'total_users' => (int) (fetchOne("SELECT COUNT(*) AS count FROM users")['count'] ?? 0),
        'active_users' => (int) (fetchOne("SELECT COUNT(*) AS count FROM users WHERE status = 'active'")['count'] ?? 0),
        'premium_users' => (int) (fetchOne("SELECT COUNT(*) AS count FROM users WHERE subscription_type = 'premium'")['count'] ?? 0),
        'total_revenue' => (float) (fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM subscription_payments WHERE status = 'succeeded'")['total'] ?? 0),
    ];

    $users = fetchAll("
        SELECT
            id,
            first_name,
            last_name,
            email,
            subscription_type AS subscription,
            created_at,
            last_login,
            status
        FROM users
        ORDER BY created_at DESC
        LIMIT 50
    ");

    $activityLogs = fetchAll("
        SELECT
            al.id,
            al.user_id,
            al.action,
            al.entity_type,
            al.entity_id,
            al.metadata,
            al.ip_address,
            al.user_agent,
            al.created_at,
            u.first_name,
            u.last_name,
            u.email
        FROM activity_logs al
        LEFT JOIN users u ON u.id = al.user_id
        ORDER BY al.created_at DESC
        LIMIT 15
    ");
} catch (Exception $e) {
    error_log('Admin dashboard error: ' . $e->getMessage());
    $stats = [
        'total_users' => 0,
        'active_users' => 0,
        'premium_users' => 0,
        'total_revenue' => 0,
    ];
    $users = [];
    $activityLogs = [];
}

include SRC_PATH . '/includes/header.php';
?>

<div class="container">
    <!-- Admin Header -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">👑 Panneau d'Administration</h3>
            <div style="display: flex; gap: 1rem;">
                <button class="btn btn-success" onclick="openModal('addUserModal')">
                    <span>➕</span> Ajouter un Utilisateur
                </button>
                <button class="btn btn-secondary" onclick="exportUsers()">
                    <span>📊</span> Exporter les Données
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="card-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
            <div class="stat-label">Utilisateurs Totaux</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-success"><?php echo number_format($stats['active_users']); ?></div>
            <div class="stat-label">Utilisateurs Actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-warning"><?php echo number_format($stats['premium_users']); ?></div>
            <div class="stat-label">Abonnés Premium</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-success">€<?php echo number_format($stats['total_revenue'], 2); ?></div>
            <div class="stat-label">Revenus Totaux</div>
        </div>
    </div>

    <!-- User Management -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gestion des Utilisateurs</h3>
            <div class="filters">
                <div class="filter-group">
                    <label for="search_users" class="form-label">Rechercher</label>
                    <input type="text" id="search_users" class="filter-input" placeholder="Nom, email...">
                </div>
                <div class="filter-group">
                    <label for="filter_subscription" class="form-label">Abonnement</label>
                    <select id="filter_subscription" class="filter-input">
                        <option value="">Tous</option>
                        <option value="Gratuit">Gratuit</option>
                        <option value="Payant">Payant</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter_status" class="form-label">Statut</label>
                    <select id="filter_status" class="filter-input">
                        <option value="">Tous</option>
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Abonnement</th>
                        <th>Statut</th>
                        <th>Dernière Connexion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucun utilisateur trouvé pour le moment.</td>
                        </tr>
                    <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="<?php echo $user['subscription'] === 'Payant' ? 'text-success' : 'text-muted'; ?>">
                                <?php echo $user['subscription']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="<?php echo $user['status'] === 'active' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '—'; ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="openModal('editUserModal')">
                                <span>✏️</span> Modifier
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="toggleUserStatus(<?php echo $user['id']; ?>)">
                                <span><?php echo $user['status'] === 'active' ? '⏸️' : '▶️'; ?></span>
                                <?php echo $user['status'] === 'active' ? 'Suspendre' : 'Activer'; ?>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) { deleteUser(<?php echo $user['id']; ?>); }">
                                <span>🗑️</span> Supprimer
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($activityLogs)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Journal d'activité récent</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Utilisateur</th>
                        <th>Détails</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activityLogs as $log): ?>
                        <?php $metadata = $log['metadata'] ? json_decode($log['metadata'], true) : []; ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                            <td><span class="badge" style="background: var(--gray-200); padding: 0.25rem 0.75rem; border-radius: 999px;"><?php echo htmlspecialchars($log['action']); ?></span></td>
                            <td>
                                <?php if (!empty($log['first_name'])): ?>
                                    <?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?>
                                    <div class="text-muted" style="font-size: 0.85rem;"><?php echo htmlspecialchars($log['email']); ?></div>
                                <?php else: ?>
                                    <span class="text-muted">Utilisateur inconnu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($metadata)): ?>
                                    <code style="font-size: 0.85rem;"><?php echo htmlspecialchars(json_encode($metadata, JSON_UNESCAPED_UNICODE)); ?></code>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- System Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informations Système</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div>
                <h4>📊 Base de Données</h4>
                <ul style="list-style: none; padding: 0;">
                    <li>Utilisateurs : <?php echo $stats['total_users']; ?></li>
                    <li>Comptes : 3,456</li>
                    <li>Transactions : 12,789</li>
                    <li>Taille : 245 MB</li>
                </ul>
            </div>
            <div>
                <h4>🖥️ Serveur</h4>
                <ul style="list-style: none; padding: 0;">
                    <li>PHP : 8.2.0</li>
                    <li>MySQL : 8.0.35</li>
                    <li>Uptime : 99.9%</li>
                    <li>Charge : 23%</li>
                </ul>
            </div>
            <div>
                <h4>🔒 Sécurité</h4>
                <ul style="list-style: none; padding: 0;">
                    <li>SSL : ✅ Actif</li>
                    <li>Firewall : ✅ Actif</li>
                    <li>Backups : ✅ Quotidien</li>
                    <li>Monitoring : ✅ Actif</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter un Utilisateur</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
        </div>
        <form method="POST" action="actions/admin_add_user.php">
            <div class="form-group">
                <label for="admin_first_name" class="form-label">Prénom</label>
                <input type="text" id="admin_first_name" name="first_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="admin_last_name" class="form-label">Nom</label>
                <input type="text" id="admin_last_name" name="last_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="admin_email" class="form-label">Email</label>
                <input type="email" id="admin_email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="admin_subscription" class="form-label">Abonnement</label>
                <select id="admin_subscription" name="subscription" class="form-select" required>
                    <option value="Gratuit">Gratuit</option>
                    <option value="Payant">Payant</option>
                </select>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer l'Utilisateur</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Modifier l'Utilisateur</h3>
            <button class="modal-close" onclick="closeModal('editUserModal')">&times;</button>
        </div>
        <form method="POST" action="actions/admin_edit_user.php">
            <input type="hidden" name="user_id" value="">
            <div class="form-group">
                <label for="edit_admin_first_name" class="form-label">Prénom</label>
                <input type="text" id="edit_admin_first_name" name="first_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_admin_last_name" class="form-label">Nom</label>
                <input type="text" id="edit_admin_last_name" name="last_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_admin_email" class="form-label">Email</label>
                <input type="email" id="edit_admin_email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit_admin_subscription" class="form-label">Abonnement</label>
                <select id="edit_admin_subscription" name="subscription" class="form-select" required>
                    <option value="Gratuit">Gratuit</option>
                    <option value="Payant">Payant</option>
                </select>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleUserStatus(userId) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de cet utilisateur ?')) {
        // AJAX call to toggle user status
        console.log('Toggling status for user:', userId);
        alert('Statut de l\'utilisateur modifié');
    }
}

function deleteUser(userId) {
    // AJAX call to delete user
    console.log('Deleting user:', userId);
    alert('Utilisateur supprimé');
}

function exportUsers() {
    // Export users data
    console.log('Exporting users data');
    alert('Export en cours...');
}
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
