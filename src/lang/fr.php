<?php
// Traductions françaises pour Budgie

return [
    // Navigation
    'nav' => [
        'dashboard' => 'Dashboard',
        'accounts' => 'Comptes',
        'expenses' => 'Dépenses',
        'incomes' => 'Revenus',
        'forecasts' => 'Prévisions',
        'sharing' => 'Partage',
        'subscriptions' => 'Abonnements',
        'profile' => 'Profil',
        'administration' => 'Administration',
        'logout' => 'Déconnexion',
    ],

    // Dashboard
    'dashboard' => [
        'title' => 'Dashboard',
        'welcome' => 'Bienvenue',
        'overview' => 'Voici un aperçu de votre situation financière.',
        'total_balance' => 'Solde Total',
        'monthly_expenses' => 'Dépenses Mensuelles',
        'monthly_income' => 'Revenus Mensuels',
        'monthly_savings' => 'Épargne Mensuelle',
        'financial_evolution' => 'Évolution des Finances',
        'account_distribution' => 'Répartition des Comptes',
        'quick_actions' => 'Actions Rapides',
        'recent_activity' => 'Activité Récente',
        'manage_accounts' => 'Gérer les Comptes',
        'add_expense' => 'Ajouter une Dépense',
        'add_income' => 'Ajouter un Revenu',
        'view_forecasts' => 'Voir les Prévisions',
        'type' => 'Type',
        'account' => 'Compte',
        'expense' => 'Dépense',
        'income' => 'Revenu',
        'current_account' => 'Compte Courant',
        'groceries' => 'Courses alimentaires',
        'salary' => 'Salaire',
        'gas' => 'Essence',
        'restaurant' => 'Restaurant',
    ],

    // Comptes
    'accounts' => [
        'title' => 'Comptes',
        'add_account' => 'Ajouter un Compte',
        'edit_account' => 'Modifier le Compte',
        'delete_account' => 'Supprimer le Compte',
        'account_name' => 'Nom du Compte',
        'account_type' => 'Type de Compte',
        'balance' => 'Solde',
        'actions' => 'Actions',
        'types' => [
            'bank' => 'Banque',
            'cash' => 'Espèces',
            'credit' => 'Carte de Crédit',
            'savings' => 'Épargne',
            'investment' => 'Investissement',
        ],
    ],

    // Dépenses
    'expenses' => [
        'title' => 'Dépenses',
        'add_expense' => 'Ajouter une Dépense',
        'edit_expense' => 'Modifier la Dépense',
        'delete_expense' => 'Supprimer la Dépense',
        'expense_name' => 'Nom de la Dépense',
        'description' => 'Description',
        'amount' => 'Montant',
        'frequency' => 'Fréquence',
        'start_date' => 'Date de Début',
        'end_date' => 'Date de Fin',
        'account' => 'Compte',
        'select_account' => 'Sélectionner un compte',
        'select_frequency' => 'Sélectionner une fréquence',
        'frequencies' => [
            'one_time' => 'Unique',
            'daily' => 'Quotidien',
            'weekly' => 'Hebdomadaire',
            'monthly' => 'Mensuel',
            'yearly' => 'Annuel',
        ],
    ],

    // Revenus
    'incomes' => [
        'title' => 'Revenus',
        'add_income' => 'Ajouter un Revenu',
        'edit_income' => 'Modifier le Revenu',
        'delete_income' => 'Supprimer le Revenu',
        'income_name' => 'Nom du Revenu',
        'description' => 'Description',
        'amount' => 'Montant',
        'frequency' => 'Fréquence',
        'start_date' => 'Date de Début',
        'end_date' => 'Date de Fin',
        'account' => 'Compte',
        'delete_confirm' => 'Êtes-vous sûr de vouloir supprimer ce revenu ?',
    ],

    // Prévisions
    'forecasts' => [
        'title' => 'Prévisions',
        'period' => 'Période',
        'next_3_months' => '3 Prochains Mois',
        'next_6_months' => '6 Prochains Mois',
        'next_year' => 'Année Prochaine',
        'projected_balance' => 'Solde Projeté',
        'projected_expenses' => 'Dépenses Projetées',
        'projected_income' => 'Revenus Projetés',
    ],

    // Partage
    'sharing' => [
        'title' => 'Partage',
        'share_account' => 'Partager un Compte',
        'account_to_share' => 'Compte à partager',
        'select_account' => 'Sélectionner un compte',
        'person_email' => 'Email de la personne',
        'invitation_message' => 'Message d\'invitation (optionnel)',
        'message_placeholder' => 'Bonjour, je vous invite à consulter mon compte...',
        'send_invitation' => 'Envoyer l\'Invitation',

        // Tables
        'accounts_i_share' => 'Comptes que je Partage',
        'accounts_shared_with_me' => 'Comptes Partagés avec Moi',
        'account' => 'Compte',
        'shared_with' => 'Partagé avec',
        'owner' => 'Propriétaire',
        'access_type' => 'Type d\'Accès',
        'share_date' => 'Date de Partage',
        'revoke' => 'Révoquer',
        'revoke_confirm' => 'Êtes-vous sûr de vouloir révoquer l\'accès ?',
        'view' => 'Consulter',

        // Règles de Partage
        'sharing_rules' => 'Règles de Partage',
        'what_is_allowed' => 'Ce qui est autorisé',
        'what_is_prohibited' => 'Ce qui est interdit',
        'security' => 'Sécurité',

        // Autorisé
        'view_balances' => 'Consultation des soldes',
        'view_transactions' => 'Visualisation des transactions',
        'access_forecasts' => 'Accès aux prévisions',
        'email_notifications' => 'Notifications par email',

        // Interdit
        'modify_data' => 'Modification des données',
        'add_transactions' => 'Ajout de transactions',
        'delete_elements' => 'Suppression d\'éléments',
        'modify_settings' => 'Modification des paramètres',

        // Sécurité
        'readonly_access' => 'Accès en lecture seule uniquement',
        'email_invitation_required' => 'Invitation par email obligatoire',
        'revocable_anytime' => 'Accès révocable à tout moment',
        'access_traceability' => 'Traçabilité des accès',

        // Activité Récente
        'recent_activity' => 'Activité Récente',
        'invitation_sent' => 'Invitation envoyée',
        'access_revoked' => 'Accès révoqué',
        'new_access_received' => 'Nouvel accès reçu',
        'current_account' => 'Compte Courant',
        'family_account' => 'Compte Famille',
        'hours_ago' => 'Il y a :hours heures',
        'days_ago' => 'Il y a :days jour(s)',

        // Édition Modal
        'edit_share' => 'Modifier le Partage',
        'readonly' => 'Lecture seule',
        'readwrite_coming' => 'Lecture/Écriture (bientôt disponible)',
        'message_optional' => 'Message (optionnel)',
        'update' => 'Mettre à jour',
        'access_revoked_success' => 'Accès révoqué avec succès',
    ],

    // Abonnements
    'subscriptions' => [
        'title' => 'Abonnements',
        'current_subscription' => 'Abonnement Actuel',
        'current_plan' => 'Plan Actuel',
        'monthly_price' => 'Prix Mensuel',
        'accounts_allowed' => 'Comptes Autorisés',
        'expenses_per_account' => 'Dépenses par Compte',
        'next_billing' => 'Prochaine facturation',
        'choose_plan' => 'Choisir un Plan',
        'plan_free' => 'Gratuit',
        'plan_gratuit' => 'Gratuit',
        'plan_premium' => 'Premium',
        'per_month' => 'par mois',
        'button_current' => 'Plan Actuel',
        'button_upgrade' => 'Passer au Premium',
        'already_using' => 'Vous utilisez déjà ce plan',

        // Fonctionnalités
        'feature_free_0' => '2 comptes maximum',
        'feature_free_1' => '7 dépenses par compte',
        'feature_free_2' => '2 revenus par compte',
        'feature_free_3' => 'Prévisions basiques',
        'feature_free_4' => 'Support email',
        'feature_premium_0' => 'Comptes illimités',
        'feature_premium_1' => 'Dépenses illimitées',
        'feature_premium_2' => 'Revenus illimités',
        'feature_premium_3' => 'Prévisions avancées',
        'feature_premium_4' => 'Partage de comptes',
        'feature_premium_5' => 'Support prioritaire',
        'feature_premium_6' => 'Exports Excel/PDF',
        'feature_premium_7' => 'API access',

        // Comparaison des Fonctionnalités
        'feature_comparison' => 'Comparaison des Fonctionnalités',
        'feature' => 'Fonctionnalité',
        'number_of_accounts' => 'Nombre de comptes',
        'incomes_per_account' => 'Revenus par compte',
        'forecasts' => 'Prévisions',
        'account_sharing' => 'Partage de comptes',
        'exports' => 'Exports',
        'support' => 'Support',
        'api_access' => 'API Access',
        'unlimited' => 'Illimité',
        'basic' => 'Basiques',
        'advanced' => 'Avancées',
        'email' => 'Email',
        'priority' => 'Prioritaire',

        // Facturation
        'billing_history' => 'Historique des Factures',
        'status' => 'Statut',
        'paid' => 'Payé',
        'download' => 'Télécharger',
        'month_january' => 'Janvier',
        'month_december' => 'Décembre',

        // Méthode de Paiement
        'payment_method' => 'Méthode de Paiement',
        'expires' => 'Expire le',
        'update_payment_method' => 'Mettre à jour la Méthode de Paiement',
        'card_number' => 'Numéro de carte',
        'expiry_date' => 'Date d\'expiration',
        'cvv' => 'CVV',
        'cardholder_name' => 'Nom du titulaire',
        'update' => 'Mettre à jour',

        // FAQ
        'faq' => 'Questions Fréquentes',
        'faq_change_plan_q' => 'Puis-je changer de plan à tout moment ?',
        'faq_change_plan_a' => 'Oui, vous pouvez passer du plan Gratuit au Premium ou vice versa à tout moment. Les changements prennent effet immédiatement.',
        'faq_exceed_limits_q' => 'Que se passe-t-il si je dépasse les limites du plan Gratuit ?',
        'faq_exceed_limits_a' => 'Vous recevrez une notification vous invitant à passer au plan Premium pour continuer à utiliser toutes les fonctionnalités.',
        'faq_cancel_q' => 'Puis-je annuler mon abonnement ?',
        'faq_cancel_a' => 'Oui, vous pouvez annuler votre abonnement à tout moment. Vous conserverez l\'accès Premium jusqu\'à la fin de la période de facturation.',
        'faq_hidden_fees_q' => 'Y a-t-il des frais cachés ?',
        'faq_hidden_fees_a' => 'Non, le prix affiché est le prix final. Aucun frais caché ou supplémentaire.',

        // Confirmation
        'confirm_upgrade' => 'Êtes-vous sûr de vouloir passer au plan ',
    ],

    // Profil
    'profile' => [
        'title' => 'Profil',
        'personal_info' => 'Informations Personnelles',
        'first_name' => 'Prénom',
        'last_name' => 'Nom',
        'name' => 'Nom',
        'email' => 'Email',
        'password' => 'Mot de Passe',
        'change_password' => 'Changer le Mot de Passe',
        'current_password' => 'Mot de Passe Actuel',
        'new_password' => 'Nouveau Mot de Passe',
        'confirm_password' => 'Confirmer le Mot de Passe',
        'save_changes' => 'Enregistrer les Modifications',

        // Abonnement
        'manage_subscription' => 'Gérer l\'abonnement',
        'upgrade_to_premium' => 'Passez au plan Premium',
        'unlock_features' => 'Débloquez toutes les fonctionnalités avec un abonnement illimité !',
        'view_plans' => 'Voir les Plans',

        // Statistiques
        'account_stats' => 'Statistiques du Compte',
        'accounts_created' => 'Comptes Créés',
        'active_expenses' => 'Dépenses Actives',
        'active_incomes' => 'Revenus Actifs',
        'last_login' => 'Dernière Connexion',
        'first_login' => 'Première connexion',
        'at' => 'à',

        // Activité & Sécurité
        'activity_security' => 'Activité & Sécurité',
        'recent_activity' => 'Activité Récente',
        'ip_address' => 'Adresse IP',
        'active_sessions' => 'Sessions Actives',
        'manage_sessions_desc' => 'Gérez les sessions actives sur vos différents appareils.',
        'view_sessions' => 'Voir les Sessions',
        'sessions_alert' => 'Vous pouvez voir toutes vos sessions actives et les révoquer si nécessaire.',

        // Zone de Danger
        'danger_zone' => 'Zone de Danger',
        'delete_account' => 'Supprimer le Compte',
        'delete_warning' => 'Cette action est irréversible. Toutes vos données seront définitivement supprimées.',
        'delete_confirm' => 'Êtes-vous ABSOLUMENT sûr de vouloir supprimer votre compte ? Cette action est irréversible !',
        'coming_soon' => 'Fonctionnalité à venir',
    ],

    // Authentification
    'auth' => [
        'login' => 'Connexion',
        'signup' => 'Inscription',
        'logout' => 'Déconnexion',
        'forgot_password' => 'Mot de passe oublié ?',
        'reset_password' => 'Réinitialiser le Mot de Passe',
        'remember_me' => 'Se souvenir de moi',
        'no_account' => 'Pas encore de compte ?',
        'already_account' => 'Vous avez déjà un compte ?',
        'email' => 'Email',
        'password' => 'Mot de Passe',
        'confirm_password' => 'Confirmer le Mot de Passe',
        'name' => 'Nom',
    ],

    // Commun
    'common' => [
        'save' => 'Enregistrer',
        'cancel' => 'Annuler',
        'delete' => 'Supprimer',
        'edit' => 'Modifier',
        'add' => 'Ajouter',
        'close' => 'Fermer',
        'confirm' => 'Confirmer',
        'yes' => 'Oui',
        'no' => 'Non',
        'search' => 'Rechercher',
        'filter' => 'Filtrer',
        'actions' => 'Actions',
        'date' => 'Date',
        'amount' => 'Montant',
        'description' => 'Description',
        'name' => 'Nom',
        'status' => 'Statut',
        'active' => 'Actif',
        'inactive' => 'Inactif',
        'loading' => 'Chargement...',
        'user' => 'Utilisateur',
    ],

    // Messages
    'messages' => [
        'success' => 'Opération réussie !',
        'error' => 'Une erreur est survenue.',
        'delete_confirm' => 'Êtes-vous sûr de vouloir supprimer cet élément ?',
        'no_data' => 'Aucune donnée disponible.',
        'limit_reached' => 'Vous avez atteint la limite de votre abonnement gratuit. Passez au Premium pour créer plus d\'éléments.',
    ],

    // Validation
    'validation' => [
        'required' => 'Ce champ est requis.',
        'email' => 'Veuillez entrer une adresse email valide.',
        'min_length' => 'Doit contenir au moins :min caractères.',
        'max_length' => 'Ne doit pas dépasser :max caractères.',
        'match' => 'Les mots de passe ne correspondent pas.',
        'invalid' => 'Valeur invalide.',
        'greater_than' => 'Doit être supérieur à :value.',
    ],
];
