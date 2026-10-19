<?php
/**
 * Module: User Authentication & Database Connection
 * Purpose: Establish secure PDO connections to the local MySQL database
 * Reference: Task 2b System Design - Database Layer
 * Author: WIL Student
 */

class Database
{
    private $host;
    private $port;
    private $db_name;
    private $fallback_databases;
    private $username;
    private $password;
    private $charset;
    private $conn;
    
    /**
     * HZ-DB-CACHE-001
     * Static cache for resolved database name to avoid repeated probe queries
     * This dramatically reduces connection overhead when multiple Database
     * instances are created in a single request.
     * 
     * @var string|null Cached database name, null if not yet resolved
     */
    private static $resolvedDatabaseName = null;

    public function __construct()
    {
        // Default to XAMPP MySQL defaults for local development
        $this->host = getenv('DB_HOST') ?: "127.0.0.1";
        $this->port = (int)(getenv('DB_PORT') ?: 3306);
        $this->db_name = getenv('DB_NAME') ?: "fsms";
        $fallback = getenv('DB_FALLBACKS') ?: "fsms,fsms_database,fsms_db";
        $this->fallback_databases = array_values(array_filter(array_map('trim', explode(',', $fallback))));
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
            $this->conn = $this->createConnection($this->resolveDatabaseName());
        } catch (PDOException $e) {
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

    private function resolveDatabaseName()
    {
        // HZ-DB-CACHE-002: Return cached name if already resolved in this process
        if (self::$resolvedDatabaseName !== null) {
            return self::$resolvedDatabaseName;
        }
        
        $probe = $this->createConnection(null);
        $candidates = array_values(array_unique(array_merge([$this->db_name], $this->fallback_databases)));

        foreach ($candidates as $databaseName) {
            $stmt = $probe->prepare(
                "SELECT SCHEMA_NAME
                 FROM INFORMATION_SCHEMA.SCHEMATA
                 WHERE SCHEMA_NAME = :database_name
                 LIMIT 1"
            );
            $stmt->execute([':database_name' => $databaseName]);

            if ($stmt->fetchColumn()) {
                // Cache the resolved name for subsequent Database instances
                self::$resolvedDatabaseName = $databaseName;
                return $databaseName;
            }
        }

        // Fallback to configured name and cache it
        self::$resolvedDatabaseName = $this->db_name;
        return $this->db_name;
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
