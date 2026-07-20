<?php
// Entry point redirect for WAMP/Apache
session_start();

// Compute base path for this project (preserve subfolder like /PA)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '';
}

// If user is logged in, redirect to Dashboard, otherwise to Landing Page
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $targetUrl = ($basePath !== '' ? $basePath : '') . '/public/index.php';
} else {
    $targetUrl = ($basePath !== '' ? $basePath : '') . '/public/landing.php';
}

header('Location: ' . $targetUrl);
exit();
