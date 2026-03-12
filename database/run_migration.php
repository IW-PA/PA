<?php
/**
 * Simple Migration Runner for WAMP
 * Run this file in your browser: http://localhost/PA/database/run_migration.php
 */

require_once __DIR__ . '/../src/config/config.php';

// Security: Only allow in development mode
if (APP_ENV !== 'development') {
    die('Migration runner is only available in development mode.');
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Budgie - Migration Runner</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #00ff00;
            padding: 2rem;
            line-height: 1.6;
        }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #00aaff; }
        pre {
            background: #0a0a0a;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🐦 Budgie Migration Runner</h1>
";

$migrationFile = __DIR__ . '/migrations/add_admin_role.sql';

if (!file_exists($migrationFile)) {
    echo "<p class='error'>❌ Migration file not found: {$migrationFile}</p>";
    echo "</body></html>";
    exit;
}

echo "<p class='info'>📄 Reading migration file...</p>";

$sql = file_get_contents($migrationFile);

if (!$sql) {
    echo "<p class='error'>❌ Failed to read migration file</p>";
    echo "</body></html>";
    exit;
}

echo "<p class='info'>🔧 Running migration...</p>";
echo "<pre>";

try {
    // Get database connection
    global $pdo;
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        // Skip comments
        if (empty($statement) || substr(trim($statement), 0, 2) === '--') {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // Show first 100 chars of statement
            $preview = substr($statement, 0, 100);
            if (strlen($statement) > 100) {
                $preview .= '...';
            }
            echo "<span class='success'>✓</span> " . htmlspecialchars($preview) . "\n\n";
            
        } catch (PDOException $e) {
            $errorCount++;
            
            // Check if error is "already exists" - not critical
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate column') !== false ||
                strpos($e->getMessage(), 'Duplicate key') !== false) {
                echo "<span class='info'>ℹ</span> Already exists (skipped): " . htmlspecialchars(substr($statement, 0, 100)) . "\n\n";
            } else {
                echo "<span class='error'>✗</span> Error: " . htmlspecialchars($e->getMessage()) . "\n";
                echo "   Statement: " . htmlspecialchars(substr($statement, 0, 100)) . "\n\n";
            }
        }
    }
    
    echo "</pre>";
    echo "<h2 class='success'>✅ Migration Complete!</h2>";
    echo "<p class='info'>Executed: {$successCount} statements</p>";
    
    if ($errorCount > 0) {
        echo "<p class='error'>Errors: {$errorCount} (may be non-critical)</p>";
    }
    
    // Verify admin user
    echo "<h3>🔍 Verification:</h3>";
    echo "<pre>";
    
    $adminCheck = $pdo->query("SELECT email, role, status FROM users WHERE role = 'admin' LIMIT 5");
    $admins = $adminCheck->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($admins) > 0) {
        echo "<span class='success'>✓</span> Admin users found:\n\n";
        foreach ($admins as $admin) {
            echo "  Email: " . htmlspecialchars($admin['email']) . "\n";
            echo "  Role: " . htmlspecialchars($admin['role']) . "\n";
            echo "  Status: " . htmlspecialchars($admin['status']) . "\n";
            echo "  ---\n";
        }
    } else {
        echo "<span class='info'>ℹ</span> No admin users found. Creating default admin...\n\n";
        
        // Create default admin
        $stmt = $pdo->prepare(
            "INSERT INTO users (first_name, last_name, email, password_hash, role, subscription_type, status, created_at) 
             VALUES (?, ?, ?, ?, 'admin', 'premium', 'active', NOW())
             ON DUPLICATE KEY UPDATE role = 'admin'"
        );
        
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt->execute(['Admin', 'User', 'admin@budgie.com', $password]);
        
        echo "<span class='success'>✓</span> Default admin created!\n";
        echo "  Email: admin@budgie.com\n";
        echo "  Password: admin123\n";
    }
    
    echo "</pre>";
    
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Login with admin credentials: <a href='../public/login.php'>Login Page</a></li>";
    echo "<li>Access admin panel: <a href='../public/admin.php'>Admin Panel</a></li>";
    echo "<li>Change the default admin password!</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "</pre>";
    echo "<h2 class='error'>❌ Migration Failed</h2>";
    echo "<pre class='error'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Check your database connection and try again.</p>";
}

echo "</body></html>";
?>
