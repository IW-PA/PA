<?php
// Header component for Budgie
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr" data-theme="">
<head>
    <script>
        // Apply saved theme immediately to prevent flash of unstyled content
        (function() {
            var t = localStorage.getItem('budgie-theme');
            if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Budgie — Ton partenaire financier personnel. Suivez vos dépenses, revenus et faites des prévisions sans connexion bancaire.">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Budgie</title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐦</text></svg>">
    <?php
    // Compute base href pointing to the public folder (use /{project}/public/)
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $publicPos = strpos($script, '/public');
    if ($publicPos !== false) {
        $basePath = substr($script, 0, $publicPos);
    } else {
        $parts = explode('/', trim($script, '/'));
        $basePath = isset($parts[0]) && $parts[0] !== '' ? '/' . $parts[0] : '';
    }
    if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
        $basePath = '';
    }
    $baseHref = $basePath . '/public/';
    ?>
    <base href="<?php echo htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php if (!in_array($current_page, ['login', 'signup', 'forgot_password', 'reset_password', 'landing', 'verify_email', 'verify_notice'])): ?>
    <div class="main-layout">
        <!-- Sidebar -->
        <nav class="sidebar" id="appSidebar">
            <div class="nav-brand">
                <h1>🐦 Budgie</h1>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="accounts.php" class="nav-link <?php echo $current_page === 'accounts' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        Comptes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="expenses.php" class="nav-link <?php echo $current_page === 'expenses' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                        Dépenses
                    </a>
                </li>
                <li class="nav-item">
                    <a href="incomes.php" class="nav-link <?php echo $current_page === 'incomes' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                        Revenus
                    </a>
                </li>
                <li class="nav-item">
                    <a href="exceptions.php" class="nav-link <?php echo $current_page === 'exceptions' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Exceptions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="forecasts.php" class="nav-link <?php echo $current_page === 'forecasts' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                        Prévisions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="sharing.php" class="nav-link <?php echo $current_page === 'sharing' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        Partage
                    </a>
                </li>
                <li class="nav-item">
                    <a href="subscriptions.php" class="nav-link <?php echo $current_page === 'subscriptions' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Abonnements
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Profil
                    </a>
                </li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="admin.php" class="nav-link <?php echo $current_page === 'admin' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Administration
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item" style="margin-top: auto;">
                    <a href="logout.php" class="nav-link" style="color: var(--danger-color);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Déconnexion
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div style="display:flex;align-items:center;gap:.75rem">
                    <button id="mobileMenuBtn" class="btn btn-secondary mobile-menu-btn" style="margin-right: 0.5rem;" aria-label="Menu">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <h2><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h2>
                </div>
                <div class="user-menu">
                    <button id="darkModeToggle" class="dark-mode-toggle" aria-label="Basculer le mode sombre" title="Mode sombre">
                        <svg id="dmIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        <span id="dmText">Thème</span>
                    </button>
                    <div class="user-info">
                        <span><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Utilisateur'; ?></span>
                    </div>
                </div>
            </header>
            <?php
            // Display global flash messages
            $gFlashError   = getFlashMessage('error');
            $gFlashSuccess = getFlashMessage('success');
            if ($gFlashError): ?>
            <div class="alert alert-danger" style="margin: 0 0 1rem 0;"><?php echo $gFlashError; ?></div>
            <?php endif;
            if ($gFlashSuccess): ?>
            <div class="alert alert-success" style="margin: 0 0 1rem 0;"><?php echo htmlspecialchars($gFlashSuccess); ?></div>
            <?php endif; ?>
    <?php else: // auth pages (login/signup/verify/...) — still show flash feedback ?>
        <?php
        $gFlashError   = getFlashMessage('error');
        $gFlashSuccess = getFlashMessage('success');
        if ($gFlashError || $gFlashSuccess): ?>
        <div style="max-width:400px; margin:1.5rem auto 0; padding:0 1rem;">
            <?php if ($gFlashError): ?><div class="alert alert-danger"><?php echo $gFlashError; ?></div><?php endif; ?>
            <?php if ($gFlashSuccess): ?><div class="alert alert-success"><?php echo htmlspecialchars($gFlashSuccess); ?></div><?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
