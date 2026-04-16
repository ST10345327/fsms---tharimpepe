<?php
/**
 * Module: User Authentication & Database Connection
 * Purpose: Establishes secure PDO connection to MySQL database
 * Reference: Task 2b System Design - Database Layer
 * Author: WIL Student
 */

class Database
{
    // HZ-USER-000
    // Purpose: Database connection configuration
    // Database: MySQL 8.0 via XAMPP
    
    private $host = "localhost";
    private $db_name = "fsms_database";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * HZ-DB-001
     * Purpose: Establish PDO connection to MySQL database
     * Table: All entities
     * Returns: PDO connection object or null on failure
     */
    public function connect()
    {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
