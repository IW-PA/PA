<?php
// Entry point redirect for WAMP/Apache
// Redirects to login if not authenticated, otherwise to dashboard

session_start();

// Compute base path for this project (preserve subfolder like /PA)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
// If root, keep empty string
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '';
}

// Build URLs that include the base path so redirects work from /PA or any folder
$loginUrl = ($basePath !== '' ? $basePath : '') . '/public/login.php';
$dashboardUrl = ($basePath !== '' ? $basePath : '') . '/public/index.php';

// Check if user is logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // User is logged in, redirect to dashboard
    header('Location: ' . $dashboardUrl);
} else {
    // User is not logged in, redirect to login page
    header('Location: ' . $loginUrl);
}
exit();
