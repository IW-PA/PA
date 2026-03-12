<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Subscriptions';
include SRC_PATH . '/includes/header.php';

// Simulate current subscription
$current_subscription = [
    'plan' => 'Gratuit',
    'accounts_limit' => 2,
    'expenses_limit' => 7,
    'incomes_limit' => 2,
    'next_billing' => null,
    'price' => 0
];

$subscription_plans = [
    [
        'name' => 'Gratuit',
        'price' => 0,
        'period' => 'Toujours',
        'features' => [
            '2 comptes maximum',
            '7 dépenses par compte',
            '2 revenus par compte',
            'Prévisions basiques',
            'Support email'
        ],
        'current' => true,
        'button_text' => 'Plan Actuel',
        'button_class' => 'btn-secondary'
    ],
    [
        'name' => 'Premium',
        'price' => 9.99,
        'period' => 'par mois',
        'features' => [
            'Comptes illimités',
            'Dépenses illimitées',
            'Revenus illimités',
            'Prévisions avancées',
            'Partage de comptes',
            'Support prioritaire',
            'Exports Excel/PDF',
            'API access'
        ],
        'current' => false,
        'button_text' => 'Passer au Premium',
        'button_class' => 'btn-primary'
    ]
];
?>

<div class="container">
    <!-- Current Subscription -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('subscriptions.current_subscription'); ?></h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div class="stat-card">
                <div class="stat-value"><?php echo t('subscriptions.plan_' . strtolower($current_subscription['plan'])); ?></div>
                <div class="stat-label"><?php e('subscriptions.current_plan'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€<?php echo number_format($current_subscription['price'], 2); ?></div>
                <div class="stat-label"><?php e('subscriptions.monthly_price'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $current_subscription['accounts_limit']; ?></div>
                <div class="stat-label"><?php e('subscriptions.accounts_allowed'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $current_subscription['expenses_limit']; ?></div>
                <div class="stat-label"><?php e('subscriptions.expenses_per_account'); ?></div>
            </div>
        </div>

        <?php if ($current_subscription['next_billing']): ?>
        <div style="margin-top: 1.5rem; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
            <p style="margin: 0; color: var(--gray-600);">
                <strong><?php e('subscriptions.next_billing'); ?>:</strong> <?php echo $current_subscription['next_billing']; ?>
            </p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Subscription Plans -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('subscriptions.choose_plan'); ?></h3>
        </div>
        <div class="plan-grid">
            <?php foreach ($subscription_plans as $plan): ?>
            <div class="plan-card <?php echo $plan['current'] ? '' : 'featured'; ?>">
                <div class="plan-name"><?php echo t('subscriptions.plan_' . strtolower($plan['name'])); ?></div>
                <div class="plan-price">
                    €<?php echo number_format($plan['price'], 2); ?>
                    <?php if ($plan['price'] > 0): ?>
                    <span style="font-size: 1rem; color: var(--gray-500);"><?php e('subscriptions.per_month'); ?></span>
                    <?php endif; ?>
                </div>
                <ul class="plan-features">
                    <?php foreach ($plan['features'] as $key => $feature): ?>
                    <li><?php echo t('subscriptions.feature_' . ($plan['name'] === 'Gratuit' ? 'free' : 'premium') . '_' . $key); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button class="btn <?php echo $plan['button_class']; ?> btn-lg" 
                        onclick="<?php echo $plan['current'] ? 'alert(\'' . t('subscriptions.already_using') . '\')' : 'subscribeToPlan(\'' . $plan['name'] . '\')'; ?>"
                        <?php echo $plan['current'] ? 'disabled' : ''; ?>>
                    <?php echo t('subscriptions.button_' . ($plan['current'] ? 'current' : 'upgrade')); ?>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Feature Comparison -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('subscriptions.feature_comparison'); ?></h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php e('subscriptions.feature'); ?></th>
                        <th class="text-center"><?php e('subscriptions.plan_free'); ?></th>
                        <th class="text-center"><?php e('subscriptions.plan_premium'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php e('subscriptions.number_of_accounts'); ?></strong></td>
                        <td class="text-center">2</td>
                        <td class="text-center text-success"><?php e('subscriptions.unlimited'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php e('subscriptions.expenses_per_account'); ?></strong></td>
                        <td class="text-center">7</td>
                        <td class="text-center text-success"><?php e('subscriptions.unlimited'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php e('subscriptions.incomes_per_account'); ?></strong></td>
                        <td class="text-center">2</td>
                        <td class="text-center text-success"><?php e('subscriptions.unlimited'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php e('subscriptions.forecasts'); ?></strong></td>
                        <td class="text-center">✓ <?php e('subscriptions.basic'); ?></td>
                        <td class="text-center text-success">✓ <?php e('subscriptions.advanced'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php e('subscriptions.account_sharing'); ?></strong></td>
                        <td class="text-center">✕</td>
                        <td class="text-center text-success">✓</td>
                    </tr>
                    <tr>
                        <td><strong><?php e('subscriptions.exports'); ?></strong></td>
                        <td class="text-center">✕</td>
                        <td class="text-center text-success">✓ Excel/PDF</td>
                    </tr>
                    <tr>
                        <td><strong><?php e('subscriptions.support'); ?></strong></td>
                        <td class="text-center">◉ <?php e('subscriptions.email'); ?></td>
                        <td class="text-center text-success">★ <?php e('subscriptions.priority'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php e('subscriptions.api_access'); ?></strong></td>
                        <td class="text-center">✕</td>
                        <td class="text-center text-success">✓</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Billing History -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('subscriptions.billing_history'); ?></h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php e('common.date'); ?></th>
                        <th><?php e('common.description'); ?></th>
                        <th><?php e('common.amount'); ?></th>
                        <th><?php e('subscriptions.status'); ?></th>
                        <th><?php e('common.actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>15/01/2025</td>
                        <td><?php e('subscriptions.plan_premium'); ?> - <?php e('subscriptions.month_january'); ?> 2025</td>
                        <td>€9.99</td>
                        <td><span class="text-success"><?php e('subscriptions.paid'); ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="downloadInvoice('2025-01-15')" title="<?php e('subscriptions.download'); ?>">
                                <span>▼</span>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>15/12/2024</td>
                        <td><?php e('subscriptions.plan_premium'); ?> - <?php e('subscriptions.month_december'); ?> 2024</td>
                        <td>€9.99</td>
                        <td><span class="text-success"><?php e('subscriptions.paid'); ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="downloadInvoice('2024-12-15')" title="<?php e('subscriptions.download'); ?>">
                                <span>▼</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Method -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php e('subscriptions.payment_method'); ?></h3>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
            <div>
                <strong>▬ **** **** **** 4242</strong>
                <p style="margin: 0; color: var(--gray-600);"><?php e('subscriptions.expires'); ?> 12/26</p>
            </div>
            <button class="btn btn-secondary" onclick="openModal('updatePaymentModal')" title="<?php e('common.edit'); ?>">
                <span>✎</span>
            </button>
        </div>
    </div>

    <!-- FAQ -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">◉ <?php e('subscriptions.faq'); ?></h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div>
                <h4><?php e('subscriptions.faq_change_plan_q'); ?></h4>
                <p style="color: var(--gray-600);"><?php e('subscriptions.faq_change_plan_a'); ?></p>
            </div>
            <div>
                <h4><?php e('subscriptions.faq_exceed_limits_q'); ?></h4>
                <p style="color: var(--gray-600);"><?php e('subscriptions.faq_exceed_limits_a'); ?></p>
            </div>
            <div>
                <h4><?php e('subscriptions.faq_cancel_q'); ?></h4>
                <p style="color: var(--gray-600);"><?php e('subscriptions.faq_cancel_a'); ?></p>
            </div>
            <div>
                <h4><?php e('subscriptions.faq_hidden_fees_q'); ?></h4>
                <p style="color: var(--gray-600);"><?php e('subscriptions.faq_hidden_fees_a'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Update Payment Modal -->
<div id="updatePaymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?php e('subscriptions.update_payment_method'); ?></h3>
            <button class="modal-close" onclick="closeModal('updatePaymentModal')">&times;</button>
        </div>
        <form method="POST" action="actions/update_payment.php">
            <div class="form-group">
                <label for="card_number" class="form-label"><?php e('subscriptions.card_number'); ?></label>
                <input type="text" id="card_number" name="card_number" class="form-input" placeholder="1234 5678 9012 3456" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="expiry_date" class="form-label"><?php e('subscriptions.expiry_date'); ?></label>
                    <input type="text" id="expiry_date" name="expiry_date" class="form-input" placeholder="MM/YY" required>
                </div>
                <div class="form-group">
                    <label for="cvv" class="form-label"><?php e('subscriptions.cvv'); ?></label>
                    <input type="text" id="cvv" name="cvv" class="form-input" placeholder="123" required>
                </div>
            </div>
            <div class="form-group">
                <label for="cardholder_name" class="form-label"><?php e('subscriptions.cardholder_name'); ?></label>
                <input type="text" id="cardholder_name" name="cardholder_name" class="form-input" required>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('updatePaymentModal')"><?php e('common.cancel'); ?></button>
                <button type="submit" class="btn btn-primary"><?php e('subscriptions.update'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function subscribeToPlan(planName) {
    if (confirm('<?php e('subscriptions.confirm_upgrade'); ?>' + planName + ' ?')) {
        // This would integrate with Stripe
        console.log('Subscribing to plan:', planName);
        alert('Redirection vers le paiement...');
    }
}

function downloadInvoice(date) {
    console.log('Downloading invoice for:', date);
    alert('Téléchargement de la facture...');
}
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
