<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/middleware/AdminGuard.php';
require_once __DIR__ . '/../../src/services/AdminService.php';

AdminGuard::requireAdmin();

$users = AdminService::getAllUsers();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="budgie-utilisateurs-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens accents correctly
fputcsv($out, ['ID', 'Prénom', 'Nom', 'Email', 'Rôle', 'Statut', 'Abonnement', 'Comptes', 'Dépenses', 'Revenus', 'Créé le', 'Dernière connexion']);
foreach ($users as $u) {
    fputcsv($out, [
        $u['id'], $u['first_name'], $u['last_name'], $u['email'], $u['role'], $u['status'],
        $u['subscription_type'], $u['accounts_count'], $u['expenses_count'], $u['incomes_count'],
        $u['created_at'], $u['last_login'],
    ]);
}
fclose($out);
exit;
