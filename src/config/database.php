<?php
// Database configuration for Budgie

class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Load environment variables or use defaults
        $this->host = defined('DB_HOST') ? DB_HOST : ($_ENV['DB_HOST'] ?? '127.0.0.1');
        $this->port = defined('DB_PORT') ? DB_PORT : ($_ENV['DB_PORT'] ?? '3306');
        $this->db_name = defined('DB_NAME') ? DB_NAME : ($_ENV['DB_NAME'] ?? 'budgie_db');
        $this->username = defined('DB_USER') ? DB_USER : ($_ENV['DB_USER'] ?? 'root');
        $this->password = defined('DB_PASS') ? DB_PASS : ($_ENV['DB_PASS'] ?? '');
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }
}

// Global database instance
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db->getConnection();
}

// Helper function for prepared statements
function executeQuery($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution error: " . $e->getMessage());
        throw new Exception("Database query failed");
    }
}

// Helper function for fetching single row
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

// Helper function for fetching multiple rows
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

// Helper function for insert operations
function insertRecord($table, $data) {
    $columns = implode(',', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $stmt = executeQuery($sql, $data);
    
    return getDB()->lastInsertId();
}

// Helper function for update operations
function updateRecord($table, $data, $where, $whereParams = []) {
    $setClause = [];
    foreach ($data as $key => $value) {
        $setClause[] = "{$key} = :{$key}";
    }
    $setClause = implode(', ', $setClause);
    
    $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $params = array_merge($data, $whereParams);
    
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

// Helper function for delete operations
function deleteRecord($table, $where, $params = []) {
    $sql = "DELETE FROM {$table} WHERE {$where}";
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}
?>
