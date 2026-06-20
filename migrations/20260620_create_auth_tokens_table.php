<?php
/**
 * Migration: Create AuthTokens table for API token-based authentication
 */
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo "Database connection failed\n";
    exit(1);
}

// Check if table exists
$stmt = $conn->query("SHOW TABLES LIKE 'AuthTokens'");
if ($stmt->rowCount() > 0) {
    echo "AuthTokens table already exists.\n";
    exit(0);
}

// Check if table already exists with different name
$stmt = $conn->query("SHOW TABLES LIKE 'authtokens'");
if ($stmt->rowCount() > 0) {
    echo "authtokens (lowercase) table already exists.\n";
    exit(0);
}

$sql = "CREATE TABLE IF NOT EXISTS AuthTokens (
    TokenID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    TokenHash VARCHAR(64) NOT NULL,
    RefreshTokenHash VARCHAR(64) DEFAULT NULL,
    ExpiresAt DATETIME NOT NULL,
    RefreshExpiresAt DATETIME DEFAULT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastUsedAt DATETIME DEFAULT NULL,
    RevokedAt DATETIME DEFAULT NULL,
    DeviceInfo VARCHAR(255) DEFAULT NULL,
    IPAddress VARCHAR(45) DEFAULT NULL,
    INDEX idx_token_hash (TokenHash),
    INDEX idx_user_id (UserID),
    INDEX idx_expires (ExpiresAt),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $conn->exec($sql);
    echo "AuthTokens table created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
    exit(1);
}