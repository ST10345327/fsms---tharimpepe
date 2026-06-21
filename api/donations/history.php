<?php
/**
 * Donation History API (Enhanced)
 * Returns donation history with advanced filtering
 * 
 * Endpoint: GET /api/donations/history.php?type=food|cash|other&donor_id=123&from=2026-01-01&to=2026-01-31
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": [...],
 *   "meta": {"count": 25, "total_amount": 5000, "timestamp": "2026-01-15T10:30:00Z"}
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $user = $auth->requireAuth();

    $type = isset($_GET['type']) ? strtolower($_GET['type']) : '';
    $donorId = isset($_GET['donor_id']) ? (int)$_GET['donor_id'] : 0;
    $from = isset($_GET['from']) ? $_GET['from'] : '';
    $to = isset($_GET['to']) ? $_GET['to'] : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];

    if ($type) {
        $validTypes = ['food', 'cash', 'other', 'general'];
        if (in_array($type, $validTypes, true)) {
            $where[] = "LOWER(d.DonationType) = :type";
            $params[':type'] = $type;
        }
    }

    if ($donorId) {
        $where[] = "d.UserID = :donor_id";
        $params[':donor_id'] = $donorId;
    }

    if ($from) {
        $where[] = "d.DonationDate >= :from";
        $params[':from'] = $from;
    }

    if ($to) {
        $where[] = "d.DonationDate <= :to";
        $params[':to'] = $to;
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $donations = [];
    $total = 0;
    $totalAmount = 0;

    if ($db !== null) {
        try {
            // Get total count and sum
            $countSql = "
                SELECT COUNT(*) as cnt, COALESCE(SUM(d.Amount), 0) as total_amount
                FROM Donations d
                $whereClause
            ";
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $countRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (int)$countRow['cnt'];
            $totalAmount = (float)$countRow['total_amount'];

            $sql = "
                SELECT 
                    d.DonationID,
                    d.DonationType,
                    d.Amount,
                    d.DonationDate,
                    d.Description,
                    d.DonorName,
                    d.DonorEmail,
                    d.Status,
                    d.PaymentMethod,
                    u.Username as recorded_by
                FROM Donations d
                LEFT JOIN Users u ON d.UserID = u.UserID
                $whereClause
                ORDER BY d.DonationDate DESC, d.DonationID DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $donations = [];
        }
    }

    logMessage("Donation history fetched by user '{$user['username']}' - $total records", 'INFO');
    echo json_encode([
        'success' => true,
        'data' => $donations,
        'meta' => [
            'count' => count($donations),
            'total' => $total,
            'total_amount' => $totalAmount,
            'page' => $page,
            'limit' => $limit,
            'timestamp' => date('c')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Donation history error: " . $e->getMessage(), 'ERROR');
}