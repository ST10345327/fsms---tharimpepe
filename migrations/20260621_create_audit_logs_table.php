<?php
/**
 * Migration: Create Audit Logs Table
 * Purpose: Security audit trail for authentication and sensitive actions
 */

require_once __DIR__ . '/../app/helpers/bootstrap.php';

try {
    $db = getDBConnection();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS AuditLogs (
        AuditLogID INT AUTO_INCREMENT PRIMARY KEY,
        
        -- Actor information
        ActorUserID INT NULL,
        ActorUsername VARCHAR(150) NULL,
        ActorRole VARCHAR(50) NULL,
        ActorIPAddress VARCHAR(45) NULL,
        ActorUserAgent TEXT NULL,
        
        -- Action context
        ActionType VARCHAR(50) NOT NULL,
        ActionCategory VARCHAR(50) NOT NULL,
        ResourceType VARCHAR(50) NULL,
        ResourceID INT NULL,
        
        -- Request details
        RequestMethod VARCHAR(10) NULL,
        RequestEndpoint VARCHAR(255) NULL,
        RequestQuery TEXT NULL,
        RequestBodyHash CHAR(64) NULL,
        
        -- Response details
        ResponseStatus SMALLINT NULL,
        ResponseMessage VARCHAR(255) NULL,
        
        -- Outcome
        IsSuccess BOOLEAN DEFAULT TRUE,
        FailureReason VARCHAR(255) NULL,
        
        -- Severity
        Severity ENUM('info', 'warning', 'critical', 'error') DEFAULT 'info',
        
        -- Metadata
        Metadata JSON NULL,
        
        -- Audit fields
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (ActorUserID),
        INDEX idx_action_type (ActionType),
        INDEX idx_category (ActionCategory),
        INDEX idx_severity (Severity),
        INDEX idx_created_at (CreatedAt),
        INDEX idx_resource (ResourceType, ResourceID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql);
    
    echo json_encode([
        'success' => true,
        'message' => 'AuditLogs table created successfully',
        'table' => 'AuditLogs'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create audit table: ' . $e->getMessage()
    ]);
}