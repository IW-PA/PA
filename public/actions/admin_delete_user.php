<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/middleware/AdminGuard.php';
require_once __DIR__ . '/../../src/services/AdminService.php';

AdminGuard::requireAdmin();
AdminGuard::requireCsrfToken();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin.php');
}

$userId = (int) ($_POST['user_id'] ?? 0);
if ($userId <= 0) {
    setFlashMessage('error', 'Utilisateur invalide.');
    redirect('admin.php');
}

if (AdminService::deleteUser($userId)) {
    setFlashMessage('success', 'Utilisateur supprimé.');
} else {
    setFlashMessage('error', 'Suppression impossible (vous ne pouvez pas supprimer votre propre compte).');
}

redirect('admin.php');
