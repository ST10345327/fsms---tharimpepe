<?php
/**
 * Module: Audit Logger
 * Purpose: Security audit trail for authentication and sensitive actions
 * Reference: Phase 10 - Security Hardening
 * 
 * Usage:
 *   require_once __DIR__ . '/AuditLogger.php';
 *   AuditLogger::log('login_success', 'auth', 'user', $userId, [
 *       'response_status' => 200,
 *       'severity' => 'info'
 *   ]);
 */

class AuditLogger
{
    private static $db = null;
    private static $initialized = false;

    public static function init($db = null)
    {
        if ($db !== null) {
            self::$db = $db;
            self::$initialized = true;
            return;
        }

        try {
            if (function_exists('getDBConnection')) {
                self::$db = getDBConnection();
                self::$initialized = true;
            }
        } catch (Exception $e) {
            error_log("AuditLogger init failed: " . $e->getMessage());
        }
    }

    public static function log(
        $actionType,
        $actionCategory,
        $resourceType = null,
        $resourceId = null,
        $options = []
    ) {
        if (!self::$initialized || self::$db === null) {
            self::init();
        }

        if (!self::$initialized || self::$db === null) {
            return;
        }

        try {
            $actorUserId = isset($options['actor_user_id']) ? $options['actor_user_id'] : null;
            $actorUsername = isset($options['actor_username']) ? $options['actor_username'] : null;
            $actorRole = isset($options['actor_role']) ? $options['actor_role'] : null;
            
            $ipAddress = self::getClientIP();
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

            $severity = isset($options['severity']) ? $options['severity'] : 'info';
            if (!in_array($severity, ['info', 'warning', 'critical', 'error'], true)) {
                $severity = 'info';
            }

            $sql = "
                INSERT INTO AuditLogs (
                    ActorUserID,
                    ActorUsername,
                    ActorRole,
                    ActorIPAddress,
                    ActorUserAgent,
                    ActionType,
                    ActionCategory,
                    ResourceType,
                    ResourceID,
                    RequestMethod,
                    RequestEndpoint,
                    RequestQuery,
                    RequestBodyHash,
                    ResponseStatus,
                    ResponseMessage,
                    IsSuccess,
                    FailureReason,
                    Severity,
                    Metadata
                ) VALUES (
                    :user_id,
                    :username,
                    :role,
                    :ip,
                    :user_agent,
                    :action_type,
                    :action_category,
                    :resource_type,
                    :resource_id,
                    :request_method,
                    :request_endpoint,
                    :request_query,
                    :request_body_hash,
                    :response_status,
                    :response_message,
                    :is_success,
                    :failure_reason,
                    :severity,
                    :metadata
                )
            ";

            $stmt = self::$db->prepare($sql);
            $stmt->execute([
                ':user_id' => $actorUserId,
                ':username' => $actorUsername ? substr($actorUsername, 0, 150) : null,
                ':role' => $actorRole,
                ':ip' => $ipAddress ? substr($ipAddress, 0, 45) : null,
                ':user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
                ':action_type' => substr($actionType, 0, 50),
                ':action_category' => substr($actionCategory, 0, 50),
                ':resource_type' => $resourceType ? substr($resourceType, 0, 50) : null,
                ':resource_id' => $resourceId,
                ':request_method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null,
                ':request_endpoint' => self::getRequestEndpoint(),
                ':request_query' => self::getQueryString(),
                ':request_body_hash' => self::getRequestBodyHash(),
                ':response_status' => isset($options['response_status']) ? $options['response_status'] : null,
                ':response_message' => isset($options['response_message']) ? substr($options['response_message'], 0, 255) : null,
                ':is_success' => isset($options['is_success']) ? $options['is_success'] : true,
                ':failure_reason' => isset($options['failure_reason']) ? substr($options['failure_reason'], 0, 255) : null,
                ':severity' => $severity,
                ':metadata' => isset($options['metadata']) ? json_encode($options['metadata']) : null
            ]);

        } catch (Exception $e) {
            error_log("AuditLogger::log failed: " . $e->getMessage());
        }
    }

    public static function getLogs($filters = [])
    {
        if (!self::$initialized || self::$db === null) {
            self::init();
        }

        if (!self::$initialized || self::$db === null) {
            return [];
        }

        try {
            $sql = "SELECT * FROM AuditLogs WHERE 1=1";
            $params = [];

            if (isset($filters['user_id'])) {
                $sql .= " AND ActorUserID = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }

            if (isset($filters['action_category'])) {
                $sql .= " AND ActionCategory = :category";
                $params[':category'] = $filters['action_category'];
            }

            if (isset($filters['severity'])) {
                $sql .= " AND Severity = :severity";
                $params[':severity'] = $filters['severity'];
            }

            if (isset($filters['action_type'])) {
                $sql .= " AND ActionType = :action_type";
                $params[':action_type'] = $filters['action_type'];
            }

            $sql .= " ORDER BY CreatedAt DESC";
            
            $limit = isset($filters['limit']) ? (int)$filters['limit'] : 100;
            $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;

            $stmt = self::$db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("AuditLogger::getLogs failed: " . $e->getMessage());
            return [];
        }
    }

    public static function getStats()
    {
        if (!self::$initialized || self::$db === null) {
            self::init();
        }

        if (!self::$initialized || self::$db === null) {
            return [];
        }

        try {
            $stats = [];

            $stmt = self::$db->query("SELECT COUNT(*) as total FROM AuditLogs");
            $stats['total'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = self::$db->query("
                SELECT Severity, COUNT(*) as count 
                FROM AuditLogs 
                GROUP BY Severity
            ");
            $stats['by_severity'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $stmt = self::$db->query("
                SELECT ActionCategory, COUNT(*) as count 
                FROM AuditLogs 
                GROUP BY ActionCategory 
                ORDER BY count DESC 
                LIMIT 10
            ");
            $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = self::$db->query("
                SELECT COUNT(*) as failed 
                FROM AuditLogs 
                WHERE IsSuccess = 0 
                AND CreatedAt > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stats['recent_failures_24h'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['failed'];

            $stmt = self::$db->query("
                SELECT COUNT(DISTINCT ActorUserID) as active_users
                FROM AuditLogs
                WHERE CreatedAt > DATE_SUB(NOW(), INTERVAL 1 DAY)
                AND ActorUserID IS NOT NULL
            ");
            $stats['active_users_today'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['active_users'];

            return $stats;

        } catch (Exception $e) {
            error_log("AuditLogger::getStats failed: " . $e->getMessage());
            return [];
        }
    }

    private static function getClientIP()
    {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }

        return null;
    }

    private static function getRequestEndpoint()
    {
        $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null;
        $request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
        
        if ($request && strpos($request, '?') !== false) {
            $request = substr($request, 0, strpos($request, '?'));
        }
        
        return $request ?: $script;
    }

    private static function getQueryString()
    {
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null;
    }

    private static function getRequestBodyHash()
    {
        $body = file_get_contents('php://input');
        if (empty($body)) {
            return null;
        }
        return hash('sha256', $body);
    }
}