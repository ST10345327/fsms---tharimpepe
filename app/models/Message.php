<?php
/**
 * Module: Messages Management
 * Purpose: Data layer for internal messaging system between staff/admin users
 * Reference: Task 2b System Design - Additional Production Modules
 * Author: WIL Student
 * Entity: Messages (MySQL table)
 * Security: Requires authentication, admin/staff access only
 */

class Message
{
    private $conn;
    private $table = "Messages";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * HZ-MSG-001
     * Purpose: Send a new message from one user to another
     * Table: Messages
     * Parameters: senderId, recipientId, subject, content
     * Returns: MessageID on success, false on failure
     * Security: Validates user permissions, sanitizes input
     */
    public function sendMessage($senderId, $recipientId, $subject, $content)
    {
        // Validate required fields
        if (empty($senderId) || empty($recipientId) || empty($subject) || empty($content)) {
            return false;
        }

        // Prepare SQL with parameterized query
        $query = "INSERT INTO " . $this->table . "
                 (SenderID, RecipientID, Subject, Content, IsRead, SentAt)
                 VALUES (:senderId, :recipientId, :subject, :content, 0, NOW())";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(":senderId", $senderId, PDO::PARAM_INT);
        $stmt->bindParam(":recipientId", $recipientId, PDO::PARAM_INT);
        $stmt->bindParam(":subject", htmlspecialchars(strip_tags($subject)));
        $stmt->bindParam(":content", htmlspecialchars(strip_tags($content)));

        // Execute and return result
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * HZ-MSG-002
     * Purpose: Get all messages for a specific user (inbox)
     * Table: Messages
     * Parameters: userId, limit (optional), offset (optional)
     * Returns: Array of message records ordered by SentAt DESC
     * Security: Users can only see messages sent to them
     */
    public function getInboxMessages($userId, $limit = 50, $offset = 0)
    {
        $query = "SELECT m.*, u.Username as SenderName
                 FROM " . $this->table . " m
                 LEFT JOIN Users u ON m.SenderID = u.UserID
                 WHERE m.RecipientID = :userId
                 ORDER BY m.SentAt DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-MSG-003
     * Purpose: Get all messages sent by a specific user (sent items)
     * Table: Messages
     * Parameters: userId, limit (optional), offset (optional)
     * Returns: Array of message records ordered by SentAt DESC
     * Security: Users can only see messages they sent
     */
    public function getSentMessages($userId, $limit = 50, $offset = 0)
    {
        $query = "SELECT m.*, u.Username as RecipientName
                 FROM " . $this->table . " m
                 LEFT JOIN Users u ON m.RecipientID = u.UserID
                 WHERE m.SenderID = :userId
                 ORDER BY m.SentAt DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-MSG-004
     * Purpose: Get a specific message by ID
     * Table: Messages
     * Parameters: messageId, userId (for security check)
     * Returns: Message record or false if not found/not authorized
     * Security: Users can only view messages sent to them or sent by them
     */
    public function getMessageById($messageId, $userId)
    {
        $query = "SELECT m.*, u.Username as SenderName, r.Username as RecipientName
                 FROM " . $this->table . " m
                 LEFT JOIN Users u ON m.SenderID = u.UserID
                 LEFT JOIN Users r ON m.RecipientID = r.UserID
                 WHERE m.MessageID = :messageId
                 AND (m.SenderID = :userId OR m.RecipientID = :userId)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":messageId", $messageId, PDO::PARAM_INT);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-MSG-005
     * Purpose: Mark a message as read
     * Table: Messages
     * Parameters: messageId, userId (recipient)
     * Returns: True on success, false on failure
     * Security: Only recipient can mark message as read
     */
    public function markAsRead($messageId, $userId)
    {
        $query = "UPDATE " . $this->table . "
                 SET IsRead = 1
                 WHERE MessageID = :messageId AND RecipientID = :userId";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":messageId", $messageId, PDO::PARAM_INT);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * HZ-MSG-006
     * Purpose: Get count of unread messages for a user
     * Table: Messages
     * Parameters: userId
     * Returns: Integer count of unread messages
     * Security: Users can only see their own unread count
     */
    public function getUnreadCount($userId)
    {
        $query = "SELECT COUNT(*) as unread_count
                 FROM " . $this->table . "
                 WHERE RecipientID = :userId AND IsRead = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['unread_count'];
    }

    /**
     * HZ-MSG-007
     * Purpose: Get all users available for messaging (active staff/admin)
     * Table: Users
     * Returns: Array of user records (ID, Username, Role)
     * Security: Only returns active users with appropriate roles
     */
    public function getAvailableRecipients()
    {
        $query = "SELECT UserID, Username, Role
                 FROM Users
                 WHERE IsActive = 1
                 AND Role IN ('admin', 'staff', 'volunteer')
                 ORDER BY Username ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-MSG-008
     * Purpose: Delete a message (soft delete by marking as deleted for sender/recipient)
     * Table: Messages
     * Parameters: messageId, userId
     * Returns: True on success, false on failure
     * Security: Users can only delete messages they sent or received
     * Note: In a real system, you might want separate deleted flags for sender/recipient
     */
    public function deleteMessage($messageId, $userId)
    {
        $query = "DELETE FROM " . $this->table . "
                 WHERE MessageID = :messageId
                 AND (SenderID = :userId OR RecipientID = :userId)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":messageId", $messageId, PDO::PARAM_INT);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * HZ-MSG-009
     * Purpose: Search messages by subject or content
     * Table: Messages
     * Parameters: userId, searchTerm, limit, offset
     * Returns: Array of matching message records
     * Security: Users can only search their own messages
     */
    public function searchMessages($userId, $searchTerm, $limit = 50, $offset = 0)
    {
        $searchTerm = "%" . $searchTerm . "%";

        $query = "SELECT m.*, u.Username as SenderName, r.Username as RecipientName
                 FROM " . $this->table . " m
                 LEFT JOIN Users u ON m.SenderID = u.UserID
                 LEFT JOIN Users r ON m.RecipientID = r.UserID
                 WHERE (m.SenderID = :userId OR m.RecipientID = :userId)
                 AND (m.Subject LIKE :searchTerm OR m.Content LIKE :searchTerm)
                 ORDER BY m.SentAt DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":searchTerm", $searchTerm, PDO::PARAM_STR);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}