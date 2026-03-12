<?php
// Header component for Budgie
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Budgie</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php if (!in_array($current_page, ['login', 'signup'])): ?>
    <div class="main-layout">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="nav-brand">
                <a href="index.php" style="text-decoration: none; color: inherit;">
                    <h1>🐦 Budgie</h1>
                </a>
            </div>
            <ul class="nav-menu">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <!-- Admin-only navigation -->
                    <li class="nav-item">
                        <a href="admin.php" class="nav-link <?php echo $current_page === 'admin' ? 'active' : ''; ?>">
                            <i>⚙</i> <?php e('nav.administration'); ?>
                        </a>
                    </li>
                <?php else: ?>
                    <!-- Regular user navigation -->
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">
                            <i>▦</i> <?php e('nav.dashboard'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="accounts.php" class="nav-link <?php echo $current_page === 'accounts' ? 'active' : ''; ?>">
                            <i>▬</i> <?php e('nav.accounts'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="expenses.php" class="nav-link <?php echo $current_page === 'expenses' ? 'active' : ''; ?>">
                            <i>▼</i> <?php e('nav.expenses'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="incomes.php" class="nav-link <?php echo $current_page === 'incomes' ? 'active' : ''; ?>">
                            <i>▲</i> <?php e('nav.incomes'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="forecasts.php" class="nav-link <?php echo $current_page === 'forecasts' ? 'active' : ''; ?>">
                            <i>▤</i> <?php e('nav.forecasts'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="sharing.php" class="nav-link <?php echo $current_page === 'sharing' ? 'active' : ''; ?>">
                            <i>⬡</i> <?php e('nav.sharing'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="subscriptions.php" class="nav-link <?php echo $current_page === 'subscriptions' ? 'active' : ''; ?>">
                            <i>★</i> <?php e('nav.subscriptions'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                            <i>●</i> <?php e('nav.profile'); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i>◀</i> <?php e('nav.logout'); ?>
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
                    <h2><?php echo isset($page_title) ? t('nav.' . strtolower(str_replace(' ', '_', $page_title))) : t('dashboard.title'); ?></h2>
                </div>
                <div class="user-menu">
                    <!-- Language Switcher -->
                    <div class="language-switcher">
                        <?php $currentLang = getCurrentLanguage(); ?>
                        <a href="change_language.php?lang=<?php echo $currentLang === 'fr' ? 'en' : 'fr'; ?>" 
                           class="lang-toggle"
                           title="<?php echo $currentLang === 'fr' ? 'Switch to English' : 'Passer en Français'; ?>">
                            <?php echo $currentLang === 'fr' ? '◉ EN' : '◉ FR'; ?>
                        </a>
                    </div>
                    <div class="user-info">
                        <span>●</span>
                        <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : t('common.user', ['User']); ?></span>
                    </div>
                </div>
            </header>
    <?php endif; ?>
