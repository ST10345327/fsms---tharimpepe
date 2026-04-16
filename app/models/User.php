<?php
/**
 * Module: User Management & Authentication
 * Purpose: Data layer for user CRUD operations and authentication
 * Reference: Task 2b System Design Section 4.1 - User Entity
 * Author: WIL Student
 * Entity: Users (MySQL table)
 */

class User
{
    private $conn;
    private $table = "Users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * HZ-USER-001
     * Purpose: Authenticate user by verifying username and password
     * Table: Users
     * Returns: User array on success, false on failure
     * Security: Uses password_verify for secure password comparison
     */
    public function authenticate($username, $password)
    {
        $user = $this->findByUsername($username);

        if (!$user) {
            return false;
        }

        // Verify password using PHP password_verify
        if (password_verify($password, $user["PasswordHash"])) {
            return $user;
        }

        return false;
    }

    /**
     * HZ-USER-002
     * Purpose: Find user by username from Users table
     * Table: Users
     * Returns: User record (array) or false if not found
     * Security: Uses parameterized query to prevent SQL injection
     */
    public function findByUsername($username)
    {
        $query = "SELECT UserID, Username, Email, PasswordHash, Role, CreatedAt, IsActive 
                  FROM " . $this->table . " 
                  WHERE Username = :username 
                  AND IsActive = TRUE 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * HZ-USER-003
     * Purpose: Find user by email address
     * Table: Users
     * Returns: User record (array) or false if not found
     * Security: Uses parameterized query to prevent SQL injection
     */
    public function findByEmail($email)
    {
        $query = "SELECT UserID, Username, Email, Role, CreatedAt 
                  FROM " . $this->table . " 
                  WHERE Email = :email 
                  AND IsActive = TRUE 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * HZ-USER-004
     * Purpose: Register new user account
     * Table: Users
     * Returns: UserID on success, false on failure
     * Security: Hashes password using PHP password_hash, validates input
     * Validation: Checks for duplicate username/email, validates email format
     */
    public function register($username, $email, $password, $role = 'volunteer')
    {
        // Validation: Check if username already exists
        if ($this->findByUsername($username)) {
            throw new Exception("Username already exists");
        }

        // Validation: Check if email already exists
        if ($this->findByEmail($email)) {
            throw new Exception("Email already exists");
        }

        // Validation: Email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validation: Password strength (minimum 6 characters)
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters");
        }

        // Hash password using PHP password_hash (bcrypt)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Prepare and execute INSERT query
        $query = "INSERT INTO " . $this->table . " 
                  (Username, Email, PasswordHash, Role) 
                  VALUES (:username, :email, :password_hash, :role)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password_hash", $hashedPassword);
        $stmt->bindParam(":role", $role);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * HZ-USER-005
     * Purpose: Get user by UserID
     * Table: Users
     * Returns: User record (array) or false if not found
     * Security: Uses parameterized query
     */
    public function getUserById($userId)
    {
        $query = "SELECT UserID, Username, Email, Role, CreatedAt, IsActive 
                  FROM " . $this->table . " 
                  WHERE UserID = :user_id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * HZ-USER-006
     * Purpose: Deactivate user account (soft delete)
     * Table: Users
     * Returns: true on success, false on failure
     * Security: Uses parameterized query, does not hard-delete data
     */
    public function deactivateUser($userId)
    {
        $query = "UPDATE " . $this->table . " 
                  SET IsActive = FALSE 
                  WHERE UserID = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);

        return $stmt->execute();
    }

    /**
     * HZ-USER-007
     * Purpose: Update user password
     * Table: Users
     * Returns: true on success, false on failure
     * Security: Hashes new password with bcrypt
     */
    public function changePassword($userId, $newPassword)
    {
        // Validation: Password strength
        if (strlen($newPassword) < 6) {
            throw new Exception("Password must be at least 6 characters");
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $query = "UPDATE " . $this->table . " 
                  SET PasswordHash = :password_hash 
                  WHERE UserID = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password_hash", $hashedPassword);
        $stmt->bindParam(":user_id", $userId);

        return $stmt->execute();
    }
}