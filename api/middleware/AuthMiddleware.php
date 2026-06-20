<?php
/**
 * Module: API Authentication Middleware
 * Purpose: Validate Bearer tokens for protected API endpoints
 * Reference: Task 2b System Design Section 4.1 - Authentication Flow
 * 
 * Usage: require_once __DIR__ . '/../middleware/AuthMiddleware.php';
 *        $auth = new AuthMiddleware($db);
 *        $currentUser = $auth->requireAuth(); // exits with 401 if invalid
 *        $currentUser = $auth->optionalAuth(); // returns null if no token
 */

class AuthMiddleware
{
    private $db;

    /**
     * @param PDO|null $db Database connection
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Require valid authentication - exits with 401 if invalid
     * 
     * @return array User data from token
     */
    public function requireAuth()
    {
        $user = $this->validateToken();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
            exit();
        }

        return $user;
    }

    /**
     * Require a specific role - exits with 403 if unauthorized
     * 
     * @param string|array $roles Required role(s)
     * @return array User data from token
     */
    public function requireRole($roles)
    {
        $user = $this->requireAuth();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!in_array($user['role'], $roles)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient permissions'
            ]);
            exit();
        }

        return $user;
    }

    /**
     * Optional authentication - returns user data or null
     * 
     * @return array|null User data or null if no valid token
     */
    public function optionalAuth()
    {
        return $this->validateToken();
    }

    /**
     * Validate Bearer token from Authorization header
     * 
     * @return array|null User data or null if invalid
     */
    private function validateToken()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] 
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
            ?? '';

        if (empty($authHeader) || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return null;
        }

        $token = $matches[1];
        $tokenHash = hash('sha256', $token);

        if ($this->db === null) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT t.UserID, u.Username, u.Email, u.Role, u.FullName, t.ExpiresAt 
                 FROM AuthTokens t 
                 JOIN Users u ON t.UserID = u.UserID 
                 WHERE t.TokenHash = :token_hash 
                 AND t.RevokedAt IS NULL 
                 AND t.ExpiresAt > NOW() 
                 AND u.Status = 'active'
                 LIMIT 1"
            );
            $stmt->execute([':token_hash' => $tokenHash]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tokenData) {
                return null;
            }

            // Update last used timestamp
            $updateStmt = $this->db->prepare(
                "UPDATE AuthTokens SET LastUsedAt = NOW() WHERE TokenHash = :token_hash"
            );
            $updateStmt->execute([':token_hash' => $tokenHash]);

            return [
                'user_id' => (int)$tokenData['UserID'],
                'username' => $tokenData['Username'],
                'email' => $tokenData['Email'],
                'fullname' => $tokenData['FullName'] ?? '',
                'role' => $tokenData['Role']
            ];

        } catch (Exception $e) {
            logMessage("Auth middleware error: " . $e->getMessage(), 'ERROR');
            return null;
        }
    }

    /**
     * Revoke a specific token (for logout)
     * 
     * @param string $token The raw token to revoke
     * @return bool Success status
     */
    public function revokeToken($token)
    {
        if ($this->db === null) {
            return false;
        }

        try {
            $tokenHash = hash('sha256', $token);
            $stmt = $this->db->prepare(
                "UPDATE AuthTokens SET RevokedAt = NOW() WHERE TokenHash = :token_hash"
            );
            $stmt->execute([':token_hash' => $tokenHash]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logMessage("Token revocation error: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Revoke all tokens for a user (force logout all sessions)
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function revokeAllUserTokens($userId)
    {
        if ($this->db === null) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE AuthTokens SET RevokedAt = NOW() 
                 WHERE UserID = :user_id AND RevokedAt IS NULL"
            );
            $stmt->execute([':user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            logMessage("Token revocation error: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
}