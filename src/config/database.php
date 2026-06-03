<?php
// Database configuration for Budgie

class Database {
    private string $host;
    private string $port;
    private string $db_name;
    private string $username;
    private string $password;
    private static ?PDO $conn = null; // Static singleton connection

    public function __construct() {
        $this->host     = defined('DB_HOST') ? DB_HOST : ($_ENV['DB_HOST'] ?? '127.0.0.1');
        $this->port     = defined('DB_PORT') ? DB_PORT : ($_ENV['DB_PORT'] ?? '3306');
        $this->db_name  = defined('DB_NAME') ? DB_NAME : ($_ENV['DB_NAME'] ?? 'budgie_db');
        $this->username = defined('DB_USER') ? DB_USER : ($_ENV['DB_USER'] ?? 'root');
        $this->password = defined('DB_PASS') ? DB_PASS : ($_ENV['DB_PASS'] ?? '');
    }

    public function getConnection(): PDO {
        if (self::$conn !== null) {
            return self::$conn;
        }

        try {
            self::$conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }

        return self::$conn;
    }

    public static function resetConnection(): void {
        self::$conn = null;
    }
}

/**
 * Returns the singleton PDO connection.
 */
function getDB(): PDO {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db->getConnection();
}

/**
 * Execute a prepared statement and return the PDOStatement.
 */
function executeQuery(string $sql, array $params = []): PDOStatement {
    try {
        $pdo  = getDB();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution error: " . $e->getMessage() . " | SQL: " . $sql);
        throw new Exception("Database query failed: " . $e->getMessage());
    }
}

/**
 * Fetch a single row, or false if not found.
 */
function fetchOne(string $sql, array $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Fetch all rows.
 */
function fetchAll(string $sql, array $params = []): array {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Insert a record and return the new auto-increment ID (or 0 on failure).
 */
function insertRecord(string $table, array $data): int {
    $columns      = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));

    $sql  = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    // Use the SAME PDO connection for lastInsertId()
    $pdo  = getDB();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);

    return (int) $pdo->lastInsertId();
}

/**
 * Update records and return the number of affected rows.
 */
function updateRecord(string $table, array $data, string $where, array $whereParams = []): int {
    $setClauses = [];
    foreach ($data as $key => $value) {
        $setClauses[] = "{$key} = :{$key}";
    }
    $setClause = implode(', ', $setClauses);

    $sql    = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $params = array_merge($data, $whereParams);

    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

/**
 * Delete records (hard delete) and return the number of affected rows.
 */
function deleteRecord(string $table, string $where, array $params = []): int {
    $sql  = "DELETE FROM {$table} WHERE {$where}";
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}
?>
