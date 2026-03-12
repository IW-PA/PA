<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/middleware/AdminGuard.php';
require_once __DIR__ . '/../../src/services/AdminService.php';

// Require admin access
AdminGuard::requireAdmin();

// Verify CSRF token
AdminGuard::requireCsrfToken();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin.php');
}

$userId = (int) ($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    setFlashMessage('error', 'Invalid user ID');
    redirect('admin.php');
}

if (AdminService::demoteToUser($userId)) {
    setFlashMessage('success', 'Admin demoted to user successfully');
} else {
    setFlashMessage('error', 'Failed to demote admin. This might be the last admin.');
}

redirect('admin.php');
