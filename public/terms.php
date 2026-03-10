<?php
require_once __DIR__ . '/../src/config/config.php';
$page_title = 'Conditions d\'utilisation';
include SRC_PATH . '/includes/header.php';
?>

<div class="container" style="max-width: 900px;">
    <div class="card">
        <div class="card-header">
            <h1 style="font-size: 2rem; margin: 0;">📜 Conditions d'utilisation</h1>
            <p style="color: var(--gray-600); margin: 0.5rem 0 0 0;">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
        </div>

        <div style="line-height: 1.8; color: var(--gray-700);">
            <h2 style="color: var(--primary-color); margin-top: 2rem;">1. Acceptation des conditions</h2>
            <p>
                En accédant et en utilisant Budgie, vous acceptez d'être lié par ces conditions d'utilisation. 
                Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre service.
            </p>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">2. Description du service</h2>
            <p>
                Budgie est une application de gestion financière personnelle qui vous permet de :
            </p>
            <ul>
                <li>Créer et gérer des comptes financiers</li>
                <li>Suivre vos dépenses et revenus</li>
                <li>Effectuer des prévisions financières</li>
                <li>Partager des comptes avec d'autres utilisateurs (plan Premium)</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">3. Compte utilisateur</h2>
            <p>
                Pour utiliser Budgie, vous devez créer un compte. Vous êtes responsable de :
            </p>
            <ul>
                <li>Maintenir la confidentialité de votre mot de passe</li>
                <li>Toutes les activités effectuées sous votre compte</li>
                <li>Fournir des informations exactes et à jour</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">4. Plans d'abonnement</h2>
            <p><strong>Plan Gratuit :</strong></p>
            <ul>
                <li>2 comptes maximum</li>
                <li>7 dépenses par compte</li>
                <li>2 revenus par compte</li>
            </ul>
            <p><strong>Plan Premium (9,99€/mois) :</strong></p>
            <ul>
                <li>Comptes, dépenses et revenus illimités</li>
                <li>Fonctionnalités de partage</li>
                <li>Support prioritaire</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">5. Utilisation acceptable</h2>
            <p>Vous acceptez de ne pas :</p>
            <ul>
                <li>Utiliser le service à des fins illégales</li>
                <li>Tenter d'accéder aux comptes d'autres utilisateurs</li>
                <li>Perturber le fonctionnement du service</li>
                <li>Utiliser des robots ou des scripts automatisés</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">6. Propriété intellectuelle</h2>
            <p>
                Budgie et tout son contenu (design, code, logos) sont la propriété de leurs créateurs 
                et sont protégés par les lois sur la propriété intellectuelle.
            </p>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">7. Limitation de responsabilité</h2>
            <p>
                Budgie est fourni "tel quel" sans garantie. Nous ne sommes pas responsables des pertes 
                financières résultant de l'utilisation de notre service. Les prévisions sont indicatives 
                et ne constituent pas des conseils financiers.
            </p>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">8. Résiliation</h2>
            <p>
                Nous nous réservons le droit de suspendre ou de résilier votre compte en cas de violation 
                de ces conditions. Vous pouvez supprimer votre compte à tout moment depuis votre profil.
            </p>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">9. Modifications</h2>
            <p>
                Nous nous réservons le droit de modifier ces conditions à tout moment. Les modifications 
                seront effectives dès leur publication sur cette page.
            </p>

            <h2 style="color: var(--primary-color); margin-top: 2rem;">10. Contact</h2>
            <p>
                Pour toute question concernant ces conditions, contactez-nous à : 
                <a href="mailto:support@budgie.com" style="color: var(--primary-color);">support@budgie.com</a>
            </p>
        </div>

        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200); text-align: center;">
            <a href="signup.php" class="btn btn-primary">Accepter et créer un compte</a>
            <a href="login.php" class="btn btn-secondary" style="margin-left: 1rem;">Retour à la connexion</a>
        </div>
    </div>
</div>

<?php include SRC_PATH . '/includes/footer.php'; ?>
