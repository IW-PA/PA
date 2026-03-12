<?php
// Header component for Budgie
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Budgie</title>
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
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php if (!in_array($current_page, ['login', 'signup'])): ?>
    <div class="main-layout">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="nav-brand">
                <h1>🐦 Budgie</h1>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">
                        <i>🏠</i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="accounts.php" class="nav-link <?php echo $current_page === 'accounts' ? 'active' : ''; ?>">
                        <i>💳</i> Comptes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="expenses.php" class="nav-link <?php echo $current_page === 'expenses' ? 'active' : ''; ?>">
                        <i>💸</i> Dépenses
                    </a>
                </li>
                <li class="nav-item">
                    <a href="incomes.php" class="nav-link <?php echo $current_page === 'incomes' ? 'active' : ''; ?>">
                        <i>💰</i> Revenus
                    </a>
                </li>
                <li class="nav-item">
                    <a href="forecasts.php" class="nav-link <?php echo $current_page === 'forecasts' ? 'active' : ''; ?>">
                        <i>📊</i> Prévisions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="sharing.php" class="nav-link <?php echo $current_page === 'sharing' ? 'active' : ''; ?>">
                        <i>🤝</i> Partage
                    </a>
                </li>
                <li class="nav-item">
                    <a href="subscriptions.php" class="nav-link <?php echo $current_page === 'subscriptions' ? 'active' : ''; ?>">
                        <i>⭐</i> Abonnements
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                        <i>👤</i> Profil
                    </a>
                </li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="admin.php" class="nav-link <?php echo $current_page === 'admin' ? 'active' : ''; ?>">
                        <i>⚙️</i> Administration
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i>🚪</i> Déconnexion
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div>
                    <button id="mobileMenuBtn" class="btn btn-secondary d-none" style="margin-right: 1rem;">☰</button>
                    <h2><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h2>
                </div>
                <div class="user-menu">
                    <div class="user-info">
                        <span>👤</span>
                        <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Utilisateur'; ?></span>
                    </div>
                </div>
            </header>
    <?php endif; ?>
