<?php
// Diagnostics for deployment path issues
header('Content-Type: text/plain; charset=utf-8');
$paths = [
    __DIR__ . '/admin_login.php',
    __DIR__ . '/run_migration.php',
    __DIR__ . '/index.php',
    __DIR__ . '/../database/run_migration.php',
    __DIR__ . '/../database/migrations/add_admin_role.sql'
];

echo "PHP script realpath: " . realpath(__FILE__) . "\n\n";

foreach ($paths as $p) {
    echo "Checking: $p\n";
    echo "Exists: " . (file_exists($p) ? 'YES' : 'NO') . "\n";
    if (file_exists($p)) echo "Realpath: " . realpath($p) . "\n";
    echo "---\n";
}

// List public directory
$publicDir = __DIR__;
if (is_dir($publicDir)) {
    echo "\nFiles in public directory:\n";
    $files = scandir($publicDir);
    foreach ($files as $f) {
        echo $f . "\n";
    }
}

// Show Apache vhost hint
echo "\nIf this shows files exist but you still get 404, check Apache DocumentRoot and virtual hosts.\n";
echo "Common WAMP web root: C:\\wamp64\\www\\ (ensure your project is placed there, e.g. C:\\wamp64\\www\\PA).\n";

echo "\nYou can remove this file after debugging.\n";
?>