<?php
/**
 * Ensure Messages table exists for internal messaging and notifications.
 */

require_once __DIR__ . '/../config/database.php';

function ensureMessagesTable(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS Messages (
        MessageID INT AUTO_INCREMENT PRIMARY KEY,
        SenderID INT NOT NULL,
        RecipientID INT DEFAULT NULL,
        Subject VARCHAR(200),
        Content TEXT NOT NULL,
        IsRead BOOLEAN DEFAULT FALSE,
        ReadAt DATETIME DEFAULT NULL,
        ParentMessageID INT DEFAULT NULL,
        SentAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (SenderID) REFERENCES Users(UserID) ON DELETE CASCADE,
        FOREIGN KEY (RecipientID) REFERENCES Users(UserID) ON DELETE SET NULL,
        FOREIGN KEY (ParentMessageID) REFERENCES Messages(MessageID) ON DELETE SET NULL,
        INDEX idx_message_sender (SenderID),
        INDEX idx_message_recipient (RecipientID),
        INDEX idx_message_read (IsRead),
        INDEX idx_message_subject (Subject)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

try {
    $db = new Database();
    $pdo = $db->connect();
    if (!$pdo) {
        throw new Exception('Failed to obtain PDO connection');
    }
    ensureMessagesTable($pdo);
    echo "Messages table migration completed successfully.\n";
} catch (Exception $e) {
    error_log('Messages migration error: ' . $e->getMessage());
    echo "Messages migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
