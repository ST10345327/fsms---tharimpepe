<?php
/**
 * Module: Activity Logging
 * Purpose: Track user actions for audit trail
 * Reference: HZ-LOG-001 to HZ-LOG-005
 * Author: WIL Student
 */

require_once __DIR__ . "/../../config/database.php";

class ActivityLog {
    // HZ-LOG-001: Log user activity
    public static function log($userId, $action, $entityName, $entityId, $details = '') {
        $conn = getConnection();
        
        try {
            $query = "INSERT INTO ActivityLog (UserID, Action, AffectedEntityName, AffectedEntityID, Details, Timestamp) 
                      VALUES (:user_id, :action, :entity_name, :entity_id, :details, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':entity_name', $entityName);
            $stmt->bindParam(':entity_id', $entityId);
            $stmt->bindParam(':details', $details);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("ActivityLog::log - " . $e->getMessage());
            return false;
        }
    }

    // HZ-LOG-002: Get activity logs by date range
    public static function getLogsByDateRange($fromDate, $toDate, $limit = 100) {
        $conn = getConnection();
        
        try {
            $query = "SELECT al.*, u.Username FROM ActivityLog al 
                      LEFT JOIN Users u ON al.UserID = u.UserID 
                      WHERE al.Timestamp BETWEEN :from_date AND :to_date 
                      ORDER BY al.Timestamp DESC LIMIT :limit";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':from_date', $fromDate);
            $stmt->bindParam(':to_date', $toDate);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ActivityLog::getLogsByDateRange - " . $e->getMessage());
            return [];
        }
    }

    // HZ-LOG-003: Get activity logs by user
    public static function getLogsByUser($userId, $limit = 50) {
        $conn = getConnection();
        
        try {
            $query = "SELECT * FROM ActivityLog WHERE UserID = :user_id ORDER BY Timestamp DESC LIMIT :limit";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ActivityLog::getLogsByUser - " . $e->getMessage());
            return [];
        }
    }

    // HZ-LOG-004: Get activity logs by action
    public static function getLogsByAction($action, $limit = 100) {
        $conn = getConnection();
        
        try {
            $query = "SELECT al.*, u.Username FROM ActivityLog al 
                      LEFT JOIN Users u ON al.UserID = u.UserID 
                      WHERE al.Action = :action 
                      ORDER BY al.Timestamp DESC LIMIT :limit";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ActivityLog::getLogsByAction - " . $e->getMessage());
            return [];
        }
    }

    // HZ-LOG-005: Clear old logs (retention policy)
    public static function clearOldLogs($daysToKeep = 90) {
        $conn = getConnection();
        
        try {
            $query = "DELETE FROM ActivityLog WHERE Timestamp < DATE_SUB(NOW(), INTERVAL :days DAY)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':days', $daysToKeep, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("ActivityLog::clearOldLogs - " . $e->getMessage());
            return false;
        }
    }
}
?>
