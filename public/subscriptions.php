<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();
$page_title = 'Abonnements';
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
            <h3 class="card-title">Abonnement Actuel</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div class="stat-card">
                <div class="stat-value"><?php echo $current_subscription['plan']; ?></div>
                <div class="stat-label">Plan Actuel</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€<?php echo number_format($current_subscription['price'], 2); ?></div>
                <div class="stat-label">Prix Mensuel</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $current_subscription['accounts_limit']; ?></div>
                <div class="stat-label">Comptes Autorisés</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $current_subscription['expenses_limit']; ?></div>
                <div class="stat-label">Dépenses par Compte</div>
            </div>
        </div>
        
        <?php if ($current_subscription['next_billing']): ?>
        <div style="margin-top: 1.5rem; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
            <p style="margin: 0; color: var(--gray-600);">
                <strong>Prochaine facturation :</strong> <?php echo $current_subscription['next_billing']; ?>
            </p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Subscription Plans -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Choisir un Plan</h3>
        </div>
        <div class="plan-grid">
            <?php foreach ($subscription_plans as $plan): ?>
            <div class="plan-card <?php echo $plan['current'] ? '' : 'featured'; ?>">
                <div class="plan-name"><?php echo $plan['name']; ?></div>
                <div class="plan-price">
                    €<?php echo number_format($plan['price'], 2); ?>
                    <?php if ($plan['price'] > 0): ?>
                    <span style="font-size: 1rem; color: var(--gray-500);"><?php echo $plan['period']; ?></span>
                    <?php endif; ?>
                </div>
                <ul class="plan-features">
                    <?php foreach ($plan['features'] as $feature): ?>
                    <li><?php echo $feature; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button class="btn <?php echo $plan['button_class']; ?> btn-lg" 
                        onclick="<?php echo $plan['current'] ? 'alert(\'Vous utilisez déjà ce plan\')' : 'subscribeToPlan(\'' . $plan['name'] . '\')'; ?>"
                        <?php echo $plan['current'] ? 'disabled' : ''; ?>>
                    <?php echo $plan['button_text']; ?>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Feature Comparison -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Comparaison des Fonctionnalités</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fonctionnalité</th>
                        <th class="text-center">Gratuit</th>
                        <th class="text-center">Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Nombre de comptes</strong></td>
                        <td class="text-center">2</td>
                        <td class="text-center text-success">Illimité</td>
                    </tr>
                    <tr>
                        <td><strong>Dépenses par compte</strong></td>
                        <td class="text-center">7</td>
                        <td class="text-center text-success">Illimité</td>
                    </tr>
                    <tr>
                        <td><strong>Revenus par compte</strong></td>
                        <td class="text-center">2</td>
                        <td class="text-center text-success">Illimité</td>
                    </tr>
                    <tr>
                        <td><strong>Prévisions</strong></td>
                        <td class="text-center">✅ Basiques</td>
                        <td class="text-center text-success">✅ Avancées</td>
                    </tr>
                    <tr>
                        <td><strong>Partage de comptes</strong></td>
                        <td class="text-center">❌</td>
                        <td class="text-center text-success">✅</td>
                    </tr>
                    <tr>
                        <td><strong>Exports</strong></td>
                        <td class="text-center">❌</td>
                        <td class="text-center text-success">✅ Excel/PDF</td>
                    </tr>
                    <tr>
                        <td><strong>Support</strong></td>
                        <td class="text-center">📧 Email</td>
                        <td class="text-center text-success">🚀 Prioritaire</td>
                    </tr>
                    <tr>
                        <td><strong>API Access</strong></td>
                        <td class="text-center">❌</td>
                        <td class="text-center text-success">✅</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Billing History -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historique des Factures</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>15/01/2025</td>
                        <td>Abonnement Premium - Janvier 2025</td>
                        <td>€9.99</td>
                        <td><span class="text-success">Payé</span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="downloadInvoice('2025-01-15')">
                                <span>📄</span> Télécharger
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>15/12/2024</td>
                        <td>Abonnement Premium - Décembre 2024</td>
                        <td>€9.99</td>
                        <td><span class="text-success">Payé</span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="downloadInvoice('2024-12-15')">
                                <span>📄</span> Télécharger
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
            <h3 class="card-title">Méthode de Paiement</h3>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
            <div>
                <strong>💳 **** **** **** 4242</strong>
                <p style="margin: 0; color: var(--gray-600);">Expire le 12/26</p>
            </div>
            <button class="btn btn-secondary" onclick="openModal('updatePaymentModal')">
                <span>✏️</span> Modifier
            </button>
        </div>
    </div>

    <!-- FAQ -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">❓ Questions Fréquentes</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div>
                <h4>Puis-je changer de plan à tout moment ?</h4>
                <p style="color: var(--gray-600);">Oui, vous pouvez passer du plan Gratuit au Premium ou vice versa à tout moment. Les changements prennent effet immédiatement.</p>
            </div>
            <div>
                <h4>Que se passe-t-il si je dépasse les limites du plan Gratuit ?</h4>
                <p style="color: var(--gray-600);">Vous recevrez une notification vous invitant à passer au plan Premium pour continuer à utiliser toutes les fonctionnalités.</p>
            </div>
            <div>
                <h4>Puis-je annuler mon abonnement ?</h4>
                <p style="color: var(--gray-600);">Oui, vous pouvez annuler votre abonnement à tout moment. Vous conserverez l'accès Premium jusqu'à la fin de la période de facturation.</p>
            </div>
            <div>
                <h4>Y a-t-il des frais cachés ?</h4>
                <p style="color: var(--gray-600);">Non, le prix affiché est le prix final. Aucun frais caché ou supplémentaire.</p>
            </div>
        </div>
    </div>
</div>

<!-- Update Payment Modal -->
<div id="updatePaymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Mettre à jour la Méthode de Paiement</h3>
            <button class="modal-close" onclick="closeModal('updatePaymentModal')">&times;</button>
        </div>
        <form method="POST" action="actions/update_payment.php">
            <div class="form-group">
                <label for="card_number" class="form-label">Numéro de carte</label>
                <input type="text" id="card_number" name="card_number" class="form-input" placeholder="1234 5678 9012 3456" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="expiry_date" class="form-label">Date d'expiration</label>
                    <input type="text" id="expiry_date" name="expiry_date" class="form-input" placeholder="MM/AA" required>
                </div>
                <div class="form-group">
                    <label for="cvv" class="form-label">CVV</label>
                    <input type="text" id="cvv" name="cvv" class="form-input" placeholder="123" required>
                </div>
            </div>
            <div class="form-group">
                <label for="cardholder_name" class="form-label">Nom du titulaire</label>
                <input type="text" id="cardholder_name" name="cardholder_name" class="form-input" required>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('updatePaymentModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
function subscribeToPlan(planName) {
    if (confirm('Êtes-vous sûr de vouloir passer au plan ' + planName + ' ?')) {
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
