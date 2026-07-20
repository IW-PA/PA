<?php
// Database dumper - DELETE AFTER USE
session_start();

echo "<pre style='font-family:monospace;padding:20px;'>\n";
echo "=== BUDGIE DB DUMP ===\n\n";

try {
    require_once __DIR__ . '/../src/config/config.php';
    $db = getDB();
    
    echo "Current Session User ID: " . ($_SESSION['user_id'] ?? 'NONE') . "\n";
    echo "Current Session User Name: " . ($_SESSION['user_name'] ?? 'NONE') . "\n";
    echo "Current Session Email: " . ($_SESSION['user_email'] ?? 'NONE') . "\n\n";

    // 1. Users
    echo "--- Users ---\n";
    $users = $db->query("SELECT id, first_name, last_name, email, subscription_type, status FROM users")->fetchAll();
    foreach ($users as $u) {
        echo "ID: {$u['id']} | Name: {$u['first_name']} {$u['last_name']} | Email: {$u['email']} | Sub: {$u['subscription_type']} | Status: {$u['status']}\n";
    }

    // 2. Accounts
    echo "\n--- Accounts ---\n";
    $accounts = $db->query("SELECT id, user_id, name, balance, interest_rate, tax_rate, deleted_at FROM accounts")->fetchAll();
    foreach ($accounts as $a) {
        $del = $a['deleted_at'] ? "DELETED at {$a['deleted_at']}" : "ACTIVE";
        echo "ID: {$a['id']} | User ID: {$a['user_id']} | Name: {$a['name']} | Bal: {$a['balance']} | Int: {$a['interest_rate']}% | Tax: {$a['tax_rate']}% | Status: $del\n";
    }

    // 3. Expenses
    echo "\n--- Expenses ---\n";
    $expenses = $db->query("SELECT id, user_id, account_id, name, amount, frequency, start_date, end_date, deleted_at FROM expenses")->fetchAll();
    foreach ($expenses as $e) {
        $del = $e['deleted_at'] ? "DELETED at {$e['deleted_at']}" : "ACTIVE";
        echo "ID: {$e['id']} | User ID: {$e['user_id']} | Acc: {$e['account_id']} | Name: {$e['name']} | Amt: {$e['amount']} | Freq: {$e['frequency']} | Start: {$e['start_date']} | Status: $del\n";
    }

    // 4. Incomes
    echo "\n--- Incomes ---\n";
    $incomes = $db->query("SELECT id, user_id, account_id, name, amount, frequency, start_date, end_date, deleted_at FROM incomes")->fetchAll();
    foreach ($incomes as $i) {
        $del = $i['deleted_at'] ? "DELETED at {$i['deleted_at']}" : "ACTIVE";
        echo "ID: {$i['id']} | User ID: {$i['user_id']} | Acc: {$i['account_id']} | Name: {$i['name']} | Amt: {$i['amount']} | Freq: {$i['frequency']} | Start: {$i['start_date']} | Status: $del\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>\n";
?>
