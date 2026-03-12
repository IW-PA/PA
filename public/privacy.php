<?php
require_once __DIR__ . '/../src/config/config.php';
$page_title = 'Politique de confidentialité';
include SRC_PATH . '/includes/header.php';
?>

<div class="container" style="max-width: 900px;">
    <div class="card">
        <div class="card-header">
            <h1 style="font-size: 2rem; margin: 0;">🔒 Politique de confidentialité</h1>
            <p style="color: var(--gray-600); margin: 0.5rem 0 0 0;">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
        </div>

        <div style="line-height: 1.8; color: var(--gray-700);">
            <h2 style="color: var(--primary-color); margin-top: 2rem;">1. Introduction</h2>
            <p>
                Chez Budgie, nous prenons votre vie privée très au sérieux. Cette politique explique comment nous 
                collectons, utilisons et protégeons vos données personnelles.
            </p>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">2. Données collectées</h2>
            <p><strong>Informations de compte :</strong></p>
            <ul>
                <li>Prénom et nom</li>
                <li>Adresse email</li>
                <li>Mot de passe (stocké de manière cryptée)</li>
            </ul>
            <p><strong>Données financières :</strong></p>
            <ul>
                <li>Comptes et leurs soldes</li>
                <li>Dépenses et revenus</li>
                <li>Prévisions financières calculées</li>
            </ul>
            <p><strong>Données techniques :</strong></p>
            <ul>
                <li>Adresse IP</li>
                <li>Informations sur le navigateur (User-Agent)</li>
                <li>Dates et heures de connexion</li>
                <li>Logs d'activité</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">3. Utilisation des données</h2>
            <p>Nous utilisons vos données pour :</p>
            <ul>
                <li>Fournir et améliorer nos services</li>
                <li>Gérer votre compte et vos abonnements</li>
                <li>Effectuer des calculs de prévisions financières</li>
                <li>Assurer la sécurité et prévenir la fraude</li>
                <li>Vous contacter concernant votre compte (si nécessaire)</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">4. Protection des données</h2>
            <p>Nous mettons en œuvre des mesures de sécurité pour protéger vos données :</p>
            <ul>
                <li>Chiffrement des mots de passe avec bcrypt</li>
                <li>Protection CSRF sur tous les formulaires</li>
                <li>Limitation du taux de requêtes (rate limiting)</li>
                <li>Sessions sécurisées avec tokens</li>
                <li>Requêtes SQL préparées pour prévenir les injections</li>
                <li>Certificat SSL pour le chiffrement des communications</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">5. Partage des données</h2>
            <p>
                Nous ne vendons jamais vos données personnelles. Vos données ne sont partagées qu'avec :
            </p>
            <ul>
                <li><strong>Stripe</strong> : pour le traitement des paiements (plan Premium uniquement)</li>
                <li><strong>Utilisateurs autorisés</strong> : si vous partagez un compte explicitement</li>
                <li><strong>Autorités légales</strong> : si requis par la loi</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">6. Vos droits (RGPD)</h2>
            <p>Conformément au RGPD, vous avez le droit de :</p>
            <ul>
                <li><strong>Accéder</strong> à vos données personnelles</li>
                <li><strong>Rectifier</strong> vos données inexactes</li>
                <li><strong>Supprimer</strong> votre compte et toutes vos données</li>
                <li><strong>Exporter</strong> vos données dans un format lisible</li>
                <li><strong>Vous opposer</strong> au traitement de vos données</li>
                <li><strong>Limiter</strong> le traitement de vos données</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">7. Cookies</h2>
            <p>Nous utilisons des cookies essentiels pour :</p>
            <ul>
                <li>Maintenir votre session connectée</li>
                <li>Mémoriser vos préférences (si "Se souvenir de moi" est coché)</li>
                <li>Assurer la sécurité (tokens CSRF)</li>
            </ul>
            <p>Nous n'utilisons pas de cookies publicitaires ou de tracking tiers.</p>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">8. Conservation des données</h2>
            <p>
                Vos données sont conservées tant que votre compte est actif. Si vous supprimez votre compte, 
                toutes vos données personnelles et financières sont définitivement supprimées sous 30 jours, 
                sauf obligation légale de conservation.
            </p>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">9. Modifications</h2>
            <p>
                Nous pouvons modifier cette politique de confidentialité. Les changements importants vous 
                seront notifiés par email.
            </p>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">10. Contact</h2>
            <p>
                Pour exercer vos droits ou pour toute question concernant la confidentialité, contactez-nous :
            </p>
            <ul>
                <li>Email : <a href="mailto:privacy@budgie.com" style="color: var(--primary-color);">privacy@budgie.com</a></li>
                <li>Délégué à la protection des données : dpo@budgie.com</li>
            </ul>
        </div>

        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200); text-align: center;">
            <a href="signup.php" class="btn btn-primary">Accepter et créer un compte</a>
            <a href="login.php" class="btn btn-secondary" style="margin-left: 1rem;">Retour à la connexion</a>
        </div>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
