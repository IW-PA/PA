<?php
require_once __DIR__ . '/../src/config/config.php';

// If already logged in as admin, redirect
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    redirect('admin.php');
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Budgie</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-layout">
    <div class="auth-container">
        <div class="text-center mb-4">
            <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">Budgie — Admin</h1>
            <p class="text-muted">Administrator login</p>
        </div>

        <?php if ($msg = getFlashMessage('error')): ?>
            <div style="background:#ffe6e6;padding:1rem;border-radius:8px;margin-bottom:1rem;color:#a33;">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="auth/admin_login_process.php">
            <?php echo CSRFProtection::getTokenField(); ?>
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <div></div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Sign in as Admin</button>
        </form>
    </div>
</div>

</body>
</html>
