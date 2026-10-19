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
        $query = "SELECT UserID, Username, Email, PasswordHash, Role, CreatedAt, Status 
                  FROM " . $this->table . " 
                  WHERE Username = :username 
                  AND Status IN ('active', 'pending') 
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
        $query = "SELECT UserID, Username, Email, Role, CreatedAt, Status 
                  FROM " . $this->table . " 
                  WHERE Email = :email 
                  AND Status IN ('active', 'pending')
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
    public function register($username, $email, $password, $role = 'volunteer', $fullName = null, $phone = null, $status = 'pending')
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

        $this->validatePasswordStrength($password);

        // Hash password using PHP password_hash (bcrypt)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Prepare and execute INSERT query
        $query = "INSERT INTO " . $this->table . " 
                  (Username, Email, PasswordHash, FullName, Phone, Role, Status) 
                  VALUES (:username, :email, :password_hash, :full_name, :phone, :role, :status)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password_hash", $hashedPassword);
        $stmt->bindParam(":full_name", $fullName);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":status", $status);

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
        $query = "SELECT UserID, Username, Email, FullName, Phone, Role, CreatedAt, Status, UpdatedAt 
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

    public function getUserAuthById($userId)
    {
        $query = "SELECT UserID, Username, Email, PasswordHash, FullName, Phone, Role, CreatedAt, Status, UpdatedAt
                  FROM " . $this->table . " WHERE UserID = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    public function updateDetails($userId, $email, $fullName, $phone, $role = null, $status = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        $fields = ["Email = :email", "FullName = :full_name", "Phone = :phone"];
        $params = [':email' => $email, ':full_name' => $fullName, ':phone' => $phone, ':user_id' => $userId];
        if ($role !== null) {
            $fields[] = "Role = :role";
            $params[':role'] = $role;
        }
        if ($status !== null) {
            $fields[] = "Status = :status";
            $params[':status'] = $status;
        }

        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE UserID = :user_id");
        return $stmt->execute($params);
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
                  SET Status = 'inactive' 
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
        $this->validatePasswordStrength($newPassword);

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $ownsTransaction = !$this->conn->inTransaction();
        if ($ownsTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $query = "UPDATE " . $this->table . "
                      SET PasswordHash = :password_hash
                      WHERE UserID = :user_id";
            $stmt = $this->conn->prepare($query);
            $updated = $stmt->execute([
                ':password_hash' => $hashedPassword,
                ':user_id' => $userId,
            ]);

            if (!$updated) {
                if ($ownsTransaction) $this->conn->rollBack();
                return false;
            }

            // Password changes invalidate every previously issued API bearer token.
            $revoke = $this->conn->prepare(
                'UPDATE ApiTokens SET RevokedAt = UTC_TIMESTAMP() WHERE UserID = :user_id AND RevokedAt IS NULL'
            );
            $revoke->execute([':user_id' => $userId]);

            if ($ownsTransaction) {
                $this->conn->commit();
            }
            return true;
        } catch (Throwable $e) {
            if ($ownsTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    private function validatePasswordStrength($password)
    {
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            throw new Exception('Password must be at least 8 characters and include an uppercase letter and a number');
        }
    }
}
