<?php
require_once __DIR__ . '/../src/config/config.php';
requireLogin();

// Handle language change
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (setLanguage($lang)) {
        // Redirect back to previous page or dashboard
        $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';

        // If referer is the change_language page itself, redirect to dashboard
        if (strpos($redirect, 'change_language.php') !== false) {
            redirect('index.php');
        }

        // Get just the filename from the full URL
        $parts = parse_url($redirect);
        $path = basename($parts['path'] ?? 'index.php');

        redirect($path);
    }
}

// If accessed directly, redirect to dashboard
redirect('index.php');
