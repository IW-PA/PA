<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Sharing';
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
            <h3 class="card-title"><?php e('sharing.share_account'); ?></h3>
        </div>
        <form method="POST" action="actions/share_account.php">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label for="share_account" class="form-label"><?php e('sharing.account_to_share'); ?></label>
                    <select id="share_account" name="account_id" class="form-select" required>
                        <option value=""><?php e('sharing.select_account'); ?></option>
                        <?php foreach ($my_accounts as $account): ?>
                        <option value="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="share_email" class="form-label"><?php e('sharing.person_email'); ?></label>
                    <input type="email" id="share_email" name="email" class="form-input" placeholder="example@email.com" required>
                </div>
            </div>
            <div class="form-group">
                <label for="share_message" class="form-label"><?php e('sharing.invitation_message'); ?></label>
                <textarea id="share_message" name="message" class="form-input" rows="3" placeholder="<?php e('sharing.message_placeholder'); ?>"></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" title="<?php e('sharing.send_invitation'); ?>">
                    <span>◉</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Accounts I'm Sharing -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('sharing.accounts_i_share'); ?></h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php e('sharing.account'); ?></th>
                        <th><?php e('sharing.shared_with'); ?></th>
                        <th><?php e('sharing.access_type'); ?></th>
                        <th><?php e('sharing.share_date'); ?></th>
                        <th><?php e('common.status'); ?></th>
                        <th><?php e('common.actions'); ?></th>
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
                            <button class="btn btn-sm btn-secondary" onclick="openModal('editShareModal')" title="<?php e('common.edit'); ?>">
                                <span>✎</span>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="if(confirm('<?php e('sharing.revoke_confirm'); ?>')) { revokeAccess(<?php echo $share['id']; ?>); }" title="<?php e('sharing.revoke'); ?>">
                                <span>✕</span>
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
            <h3 class="card-title"><?php e('sharing.accounts_shared_with_me'); ?></h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php e('sharing.account'); ?></th>
                        <th><?php e('sharing.owner'); ?></th>
                        <th><?php e('sharing.access_type'); ?></th>
                        <th><?php e('sharing.share_date'); ?></th>
                        <th><?php e('common.actions'); ?></th>
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
                            <a href="shared_account_details.php?id=<?php echo $share['id']; ?>" class="btn btn-sm btn-primary" title="<?php e('sharing.view'); ?>">
                                <span>◉</span>
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
            <h3 class="card-title"><?php e('sharing.sharing_rules'); ?></h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div>
                <h4>✓ <?php e('sharing.what_is_allowed'); ?></h4>
                <ul style="list-style: none; padding: 0;">
                    <li><?php e('sharing.view_balances'); ?></li>
                    <li><?php e('sharing.view_transactions'); ?></li>
                    <li><?php e('sharing.access_forecasts'); ?></li>
                    <li><?php e('sharing.email_notifications'); ?></li>
                </ul>
            </div>
            <div>
                <h4>✕ <?php e('sharing.what_is_prohibited'); ?></h4>
                <ul style="list-style: none; padding: 0;">
                    <li><?php e('sharing.modify_data'); ?></li>
                    <li><?php e('sharing.add_transactions'); ?></li>
                    <li><?php e('sharing.delete_elements'); ?></li>
                    <li><?php e('sharing.modify_settings'); ?></li>
                </ul>
            </div>
            <div>
                <h4>◆ <?php e('sharing.security'); ?></h4>
                <ul style="list-style: none; padding: 0;">
                    <li><?php e('sharing.readonly_access'); ?></li>
                    <li><?php e('sharing.email_invitation_required'); ?></li>
                    <li><?php e('sharing.revocable_anytime'); ?></li>
                    <li><?php e('sharing.access_traceability'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Sharing Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('sharing.recent_activity'); ?></h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
                <div>
                    <strong><?php e('sharing.invitation_sent'); ?></strong>
                    <p style="margin: 0; color: var(--gray-600);"><?php e('sharing.current_account'); ?> → marie.martin@example.com</p>
                </div>
                <span style="color: var(--gray-500); font-size: 0.875rem;"><?php e('sharing.hours_ago', ['hours' => 2]); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
                <div>
                    <strong><?php e('sharing.access_revoked'); ?></strong>
                    <p style="margin: 0; color: var(--gray-600);">Livret A → pierre.durand@example.com</p>
                </div>
                <span style="color: var(--gray-500); font-size: 0.875rem;"><?php e('sharing.days_ago', ['days' => 1]); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
                <div>
                    <strong><?php e('sharing.new_access_received'); ?></strong>
                    <p style="margin: 0; color: var(--gray-600);"><?php e('sharing.family_account'); ?> ← sophie.bernard@example.com</p>
                </div>
                <span style="color: var(--gray-500); font-size: 0.875rem;"><?php e('sharing.days_ago', ['days' => 3]); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Edit Share Modal -->
<div id="editShareModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?php e('sharing.edit_share'); ?></h3>
            <button class="modal-close" onclick="closeModal('editShareModal')">&times;</button>
        </div>
        <form method="POST" action="actions/edit_share.php">
            <input type="hidden" name="share_id" value="">
            <div class="form-group">
                <label for="edit_access_type" class="form-label"><?php e('sharing.access_type'); ?></label>
                <select id="edit_access_type" name="access_type" class="form-select" required>
                    <option value="read_only"><?php e('sharing.readonly'); ?></option>
                    <option value="read_write" disabled><?php e('sharing.readwrite_coming'); ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_share_message" class="form-label"><?php e('sharing.message_optional'); ?></label>
                <textarea id="edit_share_message" name="message" class="form-input" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editShareModal')"><?php e('common.cancel'); ?></button>
                <button type="submit" class="btn btn-primary"><?php e('sharing.update'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function revokeAccess(shareId) {
    // AJAX call to revoke access
    console.log('Revoking access for share:', shareId);
    alert('<?php e('sharing.access_revoked_success'); ?>');
}

// Auto-refresh sharing activity every 30 seconds
setInterval(function() {
    // This would fetch new sharing activity
    console.log('Checking for new sharing activity...');
}, 30000);
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
