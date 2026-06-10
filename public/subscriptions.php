<?php
require_once __DIR__ . '/../src/config/config.php';
require_once SRC_PATH . '/services/SubscriptionService.php';
require_once SRC_PATH . '/services/ActivityLogger.php';
requireLogin();

// Handle the return from Stripe Checkout (verify the session before granting access).
if (($_GET['checkout'] ?? '') === 'success' && !empty($_GET['session_id']) && STRIPE_SECRET_KEY !== '') {
    try {
        $stripe  = new StripeClient();
        $session = $stripe->retrieveCheckoutSession($_GET['session_id']);
        $belongsToUser = (string) ($session['metadata']['user_id'] ?? '') === (string) $_SESSION['user_id'];

        if ($belongsToUser && ($session['payment_status'] ?? '') === 'paid') {
            SubscriptionService::activatePremium(
                (int) $_SESSION['user_id'],
                $session['subscription'] ?? null,
                null
            );
            SubscriptionService::recordPayment(
                (int) $_SESSION['user_id'],
                isset($session['amount_total']) ? $session['amount_total'] / 100 : PREMIUM_PRICE,
                $session['currency'] ?? 'eur',
                'succeeded',
                $session['id'] ?? null
            );
            ActivityLogger::log((int) $_SESSION['user_id'], 'subscription.activated', 'user', (int) $_SESSION['user_id']);
            setFlashMessage('success', 'Bienvenue dans Budgie Premium ! Votre abonnement est actif.');
        } else {
            setFlashMessage('error', 'Le paiement n\'a pas pu être confirmé.');
        }
    } catch (Exception $e) {
        error_log('Checkout verification error: ' . $e->getMessage());
        setFlashMessage('error', 'Impossible de vérifier le paiement.');
    }
    redirect('subscriptions.php');
} elseif (($_GET['checkout'] ?? '') === 'cancel') {
    setFlashMessage('error', 'Paiement annulé. Aucun montant n\'a été débité.');
    redirect('subscriptions.php');
}

$page_title = 'Abonnements';
include SRC_PATH . '/includes/header.php';

// Get user's current subscription from database
$user_info = fetchOne(
    "SELECT subscription_type, subscription_start_date, subscription_end_date FROM users WHERE id = ?",
    [$_SESSION['user_id']]
);

$isPremium    = $user_info['subscription_type'] === 'premium';
$paymentReady = STRIPE_SECRET_KEY !== '' && STRIPE_PRICE_ID !== '';

$current_subscription = [
    'plan' => ucfirst($user_info['subscription_type']),
    'accounts_limit' => $user_info['subscription_type'] === 'premium' ? 'Illimité' : FREE_ACCOUNTS_LIMIT,
    'expenses_limit' => $user_info['subscription_type'] === 'premium' ? 'Illimité' : FREE_EXPENSES_LIMIT,
    'incomes_limit' => $user_info['subscription_type'] === 'premium' ? 'Illimité' : FREE_INCOMES_LIMIT,
    'next_billing' => $user_info['subscription_end_date'] ? formatDate($user_info['subscription_end_date']) : null,
    'price' => $user_info['subscription_type'] === 'premium' ? 9.99 : 0
];

// Fetch payment history
$payments = fetchAll(
    "SELECT * FROM subscription_payments WHERE user_id = ? ORDER BY payment_date DESC",
    [$_SESSION['user_id']]
);

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
        'current' => !$isPremium,
        'plan_key' => 'free',
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
        'current' => $isPremium,
        'plan_key' => 'premium',
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
                <?php if ($plan['current']): ?>
                    <button class="btn btn-secondary btn-lg" disabled>Plan Actuel</button>
                <?php elseif ($plan['plan_key'] === 'premium'): ?>
                    <?php if ($paymentReady): ?>
                    <form method="POST" action="actions/create_checkout_session.php" style="margin:0;">
                        <?php echo CSRFProtection::getTokenField(); ?>
                        <button type="submit" class="btn btn-primary btn-lg"><?php echo $plan['button_text']; ?></button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-primary btn-lg" disabled title="Paiement non configuré">Bientôt disponible</button>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>Inclus</button>
                <?php endif; ?>
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
                    <?php if (empty($payments)): ?>
                    <tr><td colspan="5" class="text-center">Aucun historique de paiement.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo formatDate($payment['payment_date']); ?></td>
                            <td>Abonnement Premium - <?php echo date('F Y', strtotime($payment['payment_date'])); ?></td>
                            <td><?php echo formatCurrency($payment['amount']); ?></td>
                            <td>
                                <span class="<?php echo $payment['status'] === 'succeeded' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $payment['status'] === 'succeeded' ? 'Payé' : ucfirst($payment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="downloadInvoice('<?php echo $payment['id']; ?>')">
                                    <span>📄</span> Télécharger
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($isPremium): ?>
    <!-- Subscription management (Stripe Customer Portal) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gérer mon Abonnement</h3>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; padding: 1rem; background: var(--gray-100); border-radius: var(--border-radius);">
            <div>
                <strong>💳 Abonnement Premium actif</strong>
                <p style="margin: 0; color: var(--gray-600);">Mettez à jour votre moyen de paiement, consultez vos factures ou résiliez via le portail sécurisé Stripe.</p>
            </div>
            <form method="POST" action="actions/customer_portal.php" style="margin:0;">
                <?php echo CSRFProtection::getTokenField(); ?>
                <button type="submit" class="btn btn-primary">
                    <span>⚙️</span> Gérer / Résilier
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

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

<script>
function downloadInvoice(date) {
    // Invoices are available from the Stripe Customer Portal ("Gérer / Résilier").
    alert('Vos factures sont disponibles dans le portail « Gérer mon Abonnement ».');
}
</script>

<?php include SRC_PATH . '/includes/footer.php'; ?>
