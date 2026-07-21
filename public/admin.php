<?php
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/AdminService.php';
requireAdmin();

$page_title = 'Administration';

try {
    $s = AdminService::getGlobalStats();
    $s['verified_users'] = (int) (fetchOne("SELECT COUNT(*) c FROM users WHERE email_verified_at IS NOT NULL")['c'] ?? 0);
    $s['total_revenue']  = (float) (fetchOne("SELECT COALESCE(SUM(amount),0) t FROM subscription_payments WHERE status='succeeded'")['t'] ?? 0);

    $users        = AdminService::getAllUsers();
    $activityLogs = fetchAll("
        SELECT al.action, al.metadata, al.ip_address, al.created_at,
               u.first_name, u.last_name, u.email
        FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id
        ORDER BY al.created_at DESC LIMIT 15");

    $sys = [
        'php'     => PHP_VERSION,
        'mysql'   => (string) (fetchOne("SELECT VERSION() v")['v'] ?? '—'),
        'db'      => DB_NAME,
        'db_size' => (float) (fetchOne("SELECT ROUND(SUM(data_length+index_length)/1024/1024,2) mb FROM information_schema.tables WHERE table_schema = DATABASE()")['mb'] ?? 0),
        'env'     => APP_ENV,
    ];
} catch (Throwable $e) {
    error_log('Admin dashboard error: ' . $e->getMessage());
    $s = ['total_users'=>0,'active_users'=>0,'inactive_users'=>0,'premium_users'=>0,'free_users'=>0,'admin_count'=>0,'total_accounts'=>0,'total_expenses_count'=>0,'total_incomes_count'=>0,'new_users_30d'=>0,'verified_users'=>0,'total_revenue'=>0];
    $users = []; $activityLogs = [];
    $sys = ['php'=>PHP_VERSION,'mysql'=>'—','db'=>DB_NAME,'db_size'=>0,'env'=>APP_ENV];
}

$csrf   = generateCSRFToken();
$selfId = (int) ($_SESSION['user_id'] ?? 0);

/** Small helper: a CSRF-protected inline action form with one button. */
function actionForm(string $action, int $userId, string $csrf, string $label, string $btnClass, string $confirm = ''): string {
    $onsubmit = $confirm !== '' ? ' onsubmit="return confirm(' . htmlspecialchars(json_encode($confirm), ENT_QUOTES) . ')"' : '';
    return '<form method="POST" action="actions/' . $action . '" style="display:inline"' . $onsubmit . '>'
        . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf, ENT_QUOTES) . '">'
        . '<input type="hidden" name="user_id" value="' . $userId . '">'
        . '<button type="submit" class="btn btn-sm ' . $btnClass . '">' . $label . '</button></form>';
}

include SRC_PATH . '/includes/header.php';
?>

<div class="container">
    <!-- Header -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Panneau d'Administration</h3>
            <div style="display:flex; gap:.75rem; flex-wrap:wrap;">
                <button class="btn btn-success" onclick="openModal('addUserModal')">+ Ajouter un utilisateur</button>
                <a class="btn btn-secondary" href="actions/admin_export_users.php">⇩ Exporter (CSV)</a>
            </div>
        </div>
    </div>

    <!-- Statistics (real data) -->
    <div class="card-grid">
        <div class="stat-card"><div class="stat-value"><?php echo number_format($s['total_users']); ?></div><div class="stat-label">Utilisateurs</div></div>
        <div class="stat-card"><div class="stat-value text-success"><?php echo number_format($s['active_users']); ?></div><div class="stat-label">Actifs</div></div>
        <div class="stat-card"><div class="stat-value text-danger"><?php echo number_format((int)$s['inactive_users']); ?></div><div class="stat-label">Inactifs</div></div>
        <div class="stat-card"><div class="stat-value text-warning"><?php echo number_format($s['premium_users']); ?></div><div class="stat-label">Premium</div></div>
        <div class="stat-card"><div class="stat-value"><?php echo number_format($s['verified_users']); ?></div><div class="stat-label">Emails vérifiés</div></div>
        <div class="stat-card"><div class="stat-value"><?php echo number_format((int)$s['admin_count']); ?></div><div class="stat-label">Admins</div></div>
    </div>

    <!-- User Management -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gestion des utilisateurs</h3>
        </div>
        <div class="filters">
            <div class="filter-group">
                <label for="f_search" class="form-label">Rechercher</label>
                <input type="text" id="f_search" class="filter-input" placeholder="Nom ou email…">
            </div>
            <div class="filter-group">
                <label for="f_sub" class="form-label">Abonnement</label>
                <select id="f_sub" class="filter-input"><option value="">Tous</option><option value="free">Gratuit</option><option value="premium">Payant</option></select>
            </div>
            <div class="filter-group">
                <label for="f_status" class="form-label">Statut</label>
                <select id="f_status" class="filter-input"><option value="">Tous</option><option value="active">Actif</option><option value="inactive">Inactif</option></select>
            </div>
            <div class="filter-group">
                <label for="f_role" class="form-label">Rôle</label>
                <select id="f_role" class="filter-input"><option value="">Tous</option><option value="user">Utilisateur</option><option value="admin">Admin</option></select>
            </div>
        </div>

        <div class="table-container">
            <table class="table" id="usersTable">
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Email</th><th>Abo.</th><th>Rôle</th><th>Statut</th><th>Données</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u):
                        $isSelf   = ((int)$u['id'] === $selfId);
                        $isAdmin  = (($u['role'] ?? 'user') === 'admin');
                        $isActive = (($u['status'] ?? 'active') === 'active');
                        $search   = strtolower($u['first_name'] . ' ' . $u['last_name'] . ' ' . $u['email']);
                    ?>
                    <tr data-search="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>"
                        data-sub="<?php echo htmlspecialchars($u['subscription_type'], ENT_QUOTES); ?>"
                        data-status="<?php echo htmlspecialchars($u['status'], ENT_QUOTES); ?>"
                        data-role="<?php echo htmlspecialchars($u['role'], ENT_QUOTES); ?>">
                        <td><?php echo (int)$u['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="<?php echo $u['subscription_type'] === 'premium' ? 'text-warning' : 'text-muted'; ?>"><?php echo $u['subscription_type'] === 'premium' ? 'Payant' : 'Gratuit'; ?></span></td>
                        <td><span class="<?php echo $isAdmin ? 'text-primary' : 'text-muted'; ?>"><?php echo $isAdmin ? 'Admin' : 'User'; ?></span></td>
                        <td><span class="<?php echo $isActive ? 'text-success' : 'text-danger'; ?>"><?php echo $isActive ? 'Actif' : 'Inactif'; ?></span></td>
                        <td class="text-muted" style="font-size:.85em; white-space:nowrap;">
                            <?php echo (int)$u['accounts_count']; ?>c · <?php echo (int)$u['expenses_count']; ?>d · <?php echo (int)$u['incomes_count']; ?>r
                        </td>
                        <td>
                            <div style="display:flex; gap:.35rem; flex-wrap:wrap;">
                                <a href="admin_user.php?id=<?php echo (int)$u['id']; ?>" class="btn btn-sm btn-secondary" title="Voir les données">👁 Voir</a>
                                <?php if (!$isSelf): ?>
                                    <?php echo actionForm($isAdmin ? 'admin_demote_user.php' : 'admin_promote_user.php', (int)$u['id'], $csrf, $isAdmin ? '↓ Rétrograder' : '↑ Admin', $isAdmin ? 'btn-warning' : 'btn-primary'); ?>
                                    <?php echo actionForm($isActive ? 'admin_deactivate_user.php' : 'admin_activate_user.php', (int)$u['id'], $csrf, $isActive ? '⏸ Suspendre' : '▶ Activer', $isActive ? 'btn-warning' : 'btn-success'); ?>
                                    <?php echo actionForm('admin_delete_user.php', (int)$u['id'], $csrf, '🗑 Supprimer', 'btn-danger', 'Supprimer définitivement ' . $u['email'] . ' ? Cette action est irréversible.'); ?>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:.85em; align-self:center;">(vous)</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="noUsersRow" style="display:none;"><td colspan="8" class="text-center text-muted">Aucun utilisateur ne correspond aux filtres.</td></tr>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="text-center text-muted">Aucun utilisateur.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent activity -->
    <?php if (!empty($activityLogs)): ?>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Journal d'activité récent</h3></div>
        <div class="table-container">
            <table class="table">
                <thead><tr><th>Date</th><th>Action</th><th>Utilisateur</th><th>IP</th></tr></thead>
                <tbody>
                    <?php foreach ($activityLogs as $log): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                        <td><span class="badge" style="background:var(--gray-200); padding:.2rem .6rem; border-radius:999px;"><?php echo htmlspecialchars($log['action']); ?></span></td>
                        <td><?php echo !empty($log['email']) ? htmlspecialchars($log['first_name'] . ' ' . $log['last_name'] . ' (' . $log['email'] . ')') : '<span class="text-muted">—</span>'; ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- System Information (real values) -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Informations système</h3></div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.5rem; padding:0 .25rem 1rem;">
            <div>
                <h4>Base de données</h4>
                <ul style="list-style:none; padding:0; line-height:1.9;">
                    <li>Utilisateurs : <strong><?php echo number_format($s['total_users']); ?></strong></li>
                    <li>Comptes : <strong><?php echo number_format((int)$s['total_accounts']); ?></strong></li>
                    <li>Dépenses : <strong><?php echo number_format((int)$s['total_expenses_count']); ?></strong></li>
                    <li>Revenus : <strong><?php echo number_format((int)$s['total_incomes_count']); ?></strong></li>
                    <li>Taille : <strong><?php echo number_format($sys['db_size'], 2); ?> Mo</strong></li>
                </ul>
            </div>
            <div>
                <h4>Serveur</h4>
                <ul style="list-style:none; padding:0; line-height:1.9;">
                    <li>PHP : <strong><?php echo htmlspecialchars($sys['php']); ?></strong></li>
                    <li>MySQL : <strong><?php echo htmlspecialchars($sys['mysql']); ?></strong></li>
                    <li>Base : <strong><?php echo htmlspecialchars($sys['db']); ?></strong></li>
                    <li>Environnement : <strong><?php echo htmlspecialchars($sys['env']); ?></strong></li>
                </ul>
            </div>
            <div>
                <h4>Comptes</h4>
                <ul style="list-style:none; padding:0; line-height:1.9;">
                    <li>Admins : <strong><?php echo number_format((int)$s['admin_count']); ?></strong></li>
                    <li>Emails vérifiés : <strong><?php echo number_format($s['verified_users']); ?>/<?php echo number_format($s['total_users']); ?></strong></li>
                    <li>Premium / Gratuit : <strong><?php echo number_format($s['premium_users']); ?> / <?php echo number_format((int)$s['free_users']); ?></strong></li>
                    <li>Nouveaux (30 j) : <strong><?php echo number_format((int)$s['new_users_30d']); ?></strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter un utilisateur</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
        </div>
        <form method="POST" action="actions/admin_add_user.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
            <div class="form-group"><label class="form-label">Prénom</label><input type="text" name="first_name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Nom</label><input type="text" name="last_name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-input" minlength="8" required></div>
            <div class="form-group"><label class="form-label">Abonnement</label>
                <select name="subscription" class="form-select"><option value="free">Gratuit</option><option value="premium">Payant</option></select></div>
            <div class="form-group"><label class="form-label">Rôle</label>
                <select name="role" class="form-select"><option value="user">Utilisateur</option><option value="admin">Admin</option></select></div>
            <p class="text-muted" style="font-size:.85rem;">Le compte est créé actif et déjà vérifié.</p>
            <div style="display:flex; gap:1rem; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var search = document.getElementById('f_search'),
        fSub = document.getElementById('f_sub'),
        fStatus = document.getElementById('f_status'),
        fRole = document.getElementById('f_role'),
        rows = Array.prototype.slice.call(document.querySelectorAll('#usersTable tbody tr[data-search]')),
        noRow = document.getElementById('noUsersRow');

    function apply() {
        var q = (search.value || '').toLowerCase().trim(),
            sub = fSub.value, st = fStatus.value, role = fRole.value, shown = 0;
        rows.forEach(function (tr) {
            var ok = (!q || tr.dataset.search.indexOf(q) !== -1)
                  && (!sub || tr.dataset.sub === sub)
                  && (!st || tr.dataset.status === st)
                  && (!role || tr.dataset.role === role);
            tr.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        if (noRow) noRow.style.display = (shown === 0 && rows.length > 0) ? '' : 'none';
    }
    [search, fSub, fStatus, fRole].forEach(function (el) {
        if (el) { el.addEventListener('input', apply); el.addEventListener('change', apply); }
    });
})();
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
