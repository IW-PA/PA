<?php
/**
 * Rich, RANDOMIZED seed data for Budgie. Creates verified accounts, each with a
 * random number of bank accounts, incomes and expenses (random labels, amounts,
 * frequencies and dates). The data is different on every run.
 *
 * Run inside the app container:
 *   docker exec budgie_app php /var/www/html/scripts/seed.php --count=10
 *   docker exec budgie_app php /var/www/html/scripts/seed.php --clean --count=10
 *
 *   --clean       TRUNCATE every table first (wipe, then seed)
 *   --count=N     number of regular users to create (default 8)
 */

require __DIR__ . '/../src/config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$opts  = getopt('', ['count::', 'clean']);
$count = isset($opts['count']) ? max(1, (int) $opts['count']) : 8;
$clean = array_key_exists('clean', $opts);

$pdo  = getDB();
$now  = date('Y-m-d H:i:s');
$hash = password_hash('Password123!', PASSWORD_DEFAULT);

$firstNames    = ['Lucas','Emma','Hugo','Léa','Louis','Chloé','Gabriel','Manon','Jules','Camille','Nathan','Sarah','Ethan','Inès','Tom','Jade','Raphaël','Louise','Arthur','Alice','Noah','Zoé','Adam','Lina'];
$lastNames     = ['Martin','Bernard','Dubois','Thomas','Robert','Petit','Durand','Leroy','Moreau','Simon','Laurent','Lefebvre','Michel','Garcia','David','Bertrand','Roux','Vincent','Fournier','Girard'];
$accountTypes  = ['Compte Courant','Livret A','Livret Jeune','PEL','Compte Épargne','Compte Joint','LDDS','Assurance Vie'];
$expenseLabels = ['Loyer','Courses','Électricité','Gaz','Internet','Forfait mobile','Netflix','Spotify','Essence','Assurance auto','Assurance habitation','Restaurant','Transports','Salle de sport','Mutuelle','Impôts','Crédit conso','Abonnement presse','Pharmacie'];
$incomeLabels  = ['Salaire','Prime','Freelance','Allocation CAF','Bourse','Remboursement','Dividendes','Loyer perçu','Vente en ligne'];
$expenseFreqs  = ['mensuel','mensuel','mensuel','ponctuel','annuel','trimestriel','bimensuel'];
$incomeFreqs   = ['mensuel','mensuel','ponctuel','annuel'];

$pick   = fn(array $a) => $a[array_rand($a)];
$money  = fn($min, $max) => round(mt_rand((int)($min * 100), (int)($max * 100)) / 100, 2);
$aDate  = fn($daysBack = 365) => date('Y-m-d', time() - mt_rand(0, $daysBack) * 86400);

if ($clean) {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $pdo->exec("TRUNCATE TABLE `$t`");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "🧹 Cleaned " . count($tables) . " tables.\n";
}

$stats = ['users' => 0, 'accounts' => 0, 'incomes' => 0, 'expenses' => 0];

// A verified admin so you can log in and browse everyone's data.
$adminId = (int) insertRecord('users', [
    'first_name' => 'Admin', 'last_name' => 'Budgie', 'email' => 'admin@budgie.test',
    'password_hash' => $hash, 'subscription_type' => 'premium', 'role' => 'admin',
    'status' => 'active', 'email_verified_at' => $now,
]);
insertRecord('accounts', ['user_id' => $adminId, 'name' => 'Compte Courant', 'description' => 'Compte admin', 'balance' => $money(1000, 8000), 'interest_rate' => 0, 'tax_rate' => 0]);
$stats['users']++; $stats['accounts']++;

for ($u = 1; $u <= $count; $u++) {
    $fn = $pick($firstNames); $ln = $pick($lastNames);
    $email = strtolower(str_replace(['é','è','ë','ê'], 'e', $fn . '.' . $ln . $u)) . '@budgie.test';

    $uid = (int) insertRecord('users', [
        'first_name' => $fn, 'last_name' => $ln, 'email' => $email,
        'password_hash' => $hash, 'subscription_type' => $pick(['free', 'free', 'free', 'premium']),
        'role' => 'user', 'status' => $pick(['active', 'active', 'active', 'inactive']),
        'email_verified_at' => $now,
    ]);
    $stats['users']++;

    $accountIds = [];
    $nAccounts = mt_rand(1, 3);
    for ($a = 0; $a < $nAccounts; $a++) {
        $accountIds[] = (int) insertRecord('accounts', [
            'user_id' => $uid, 'name' => $pick($accountTypes), 'description' => '',
            'balance' => $money(50, 15000),
            'interest_rate' => $pick([0, 0, 0.5, 1, 2, 3]), 'tax_rate' => $pick([0, 0, 17.2, 30]),
        ]);
        $stats['accounts']++;
    }

    $nInc = mt_rand(1, 3);
    for ($k = 0; $k < $nInc; $k++) {
        insertRecord('incomes', [
            'user_id' => $uid, 'account_id' => $pick($accountIds), 'name' => $pick($incomeLabels),
            'amount' => $money(300, 4500), 'frequency' => $pick($incomeFreqs), 'start_date' => $aDate(),
        ]);
        $stats['incomes']++;
    }

    $nExp = mt_rand(3, 8);
    for ($k = 0; $k < $nExp; $k++) {
        insertRecord('expenses', [
            'user_id' => $uid, 'account_id' => $pick($accountIds), 'name' => $pick($expenseLabels),
            'amount' => $money(10, 1500), 'frequency' => $pick($expenseFreqs), 'start_date' => $aDate(),
        ]);
        $stats['expenses']++;
    }
}

echo "🌱 Seeded: 1 admin + {$count} verified users — "
   . "{$stats['accounts']} accounts, {$stats['incomes']} incomes, {$stats['expenses']} expenses.\n";
echo "🔑 Login: admin@budgie.test (+ the generated users) — password: Password123! — all email-verified.\n";
