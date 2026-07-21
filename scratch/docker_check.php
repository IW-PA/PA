<?php
require_once '/var/www/html/src/config/config.php';
echo 'APP_ENV=' . APP_ENV . PHP_EOL;
echo 'DB_HOST=' . DB_HOST . PHP_EOL;
echo 'DB_NAME=' . DB_NAME . PHP_EOL;
echo 'DB_USER=' . DB_USER . PHP_EOL;
try {
    getDB();
    echo 'DB OK' . PHP_EOL;

    // Check if email_verification_tokens table exists
    $tables = fetchAll("SHOW TABLES LIKE 'email_verification_tokens'");
    echo 'email_verification_tokens table exists: ' . (count($tables) > 0 ? 'YES' : 'NO') . PHP_EOL;

    $allTables = fetchAll("SHOW TABLES");
    echo 'All tables: ' . PHP_EOL;
    foreach ($allTables as $row) {
        echo '  - ' . implode('', $row) . PHP_EOL;
    }
} catch (Exception $ex) {
    echo 'ERROR: ' . $ex->getMessage() . PHP_EOL;
}
