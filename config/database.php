<?php
/**
 * Module: User Authentication & Database Connection
 * Purpose: Establish secure PDO connections to the local MySQL database
 * Reference: Task 2b System Design - Database Layer
 * Author: WIL Student
 *
 * This project uses MySQL only (XAMPP environment). SQLite is not supported.
 */

class Database
{
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $conn;

    public function __construct()
    {
        // Default to XAMPP MySQL defaults for local development
        // Use localhost so Windows/XAMPP MariaDB can resolve via the local loopback
        // path that is actually listening in this environment.
        $this->host = getenv('DB_HOST') ?: "127.0.0.1";
        $this->port = (int)(getenv('DB_PORT') ?: 3306);
        $this->db_name = getenv('DB_NAME') ?: "fsms";
        $this->username = getenv('DB_USERNAME') ?: "root";
        // XAMPP default: empty password for root user on local dev
        $this->password = getenv('DB_PASSWORD');
        if ($this->password === false) {
            $this->password = "";
        }
        $this->charset = getenv('DB_CHARSET') ?: "utf8mb4";
    }

    /**
     * HZ-DB-001
     * Purpose: Establish PDO connection to MySQL database
     * Returns: PDO connection object or null on failure
     */
    public function connect()
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        try {
            $this->conn = $this->createConnection($this->db_name);
        } catch (PDOException $e) {
            // Return null on failure so API endpoints can fall back to demo users
            error_log("Database::connect - " . $e->getMessage());
            return null;
        }

        return $this->conn;
    }


    /**
     * Compatibility alias used by several controllers.
     */
    public function getConnection()
    {
        return $this->connect();
    }

    private function createConnection($databaseName = null)
    {
        $dsn = "mysql:host={$this->host};port={$this->port}";
        if (!empty($databaseName)) {
            $dsn .= ";dbname={$databaseName}";
        }
        $dsn .= ";charset={$this->charset}";

        $connection = new PDO($dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            // Critical: prevent long hangs during connect/query
            PDO::ATTR_TIMEOUT => 5,
        ]);


        return $connection;
    }
}

/**
 * Compatibility helper used across older controllers and models.
 */
function getConnection()
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $database = new Database();
    $connection = $database->getConnection();

    return $connection;
}
?>
