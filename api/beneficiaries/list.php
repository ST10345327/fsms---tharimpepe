<?php
/**
 * Beneficiaries List API Endpoint
 * Returns paginated list of beneficiaries
 * 
 * Endpoint: GET /api/beneficiaries/list.php
 * Auth: Bearer token required
 * Query: ?search=xxx&status=active&page=1&limit=50
 * Output: { "success": true, "data": [...], "total": N }
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

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(FirstName LIKE :search OR LastName LIKE :search2 OR CONCAT(FirstName, ' ', LastName) LIKE :search3)";
        $params[':search'] = "%{$search}%";
        $params[':search2'] = "%{$search}%";
        $params[':search3'] = "%{$search}%";
    }

    if ($status !== '') {
        $where[] = "Status = :status";
        $params[':status'] = $status;
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM beneficiaries {$whereClause}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get records
    $stmt = $db->prepare(
        "SELECT BeneficiaryID, FirstName, LastName, 
                CONCAT(FirstName, ' ', LastName) AS FullName,
                Age, Gender, Phone, Email, Address,
                RegistrationDate, Status, 
                CASE 
                    WHEN Age < 18 THEN 'Child' 
                    WHEN Age >= 60 THEN 'Elderly' 
                    ELSE 'Adult' 
                END AS Category,
                Notes, CreatedAt 
         FROM beneficiaries
         {$whereClause} 
         ORDER BY LastName ASC, FirstName ASC 
         LIMIT :limit OFFSET :offset"
    );

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $beneficiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $beneficiaries,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Beneficiaries list error: " . $e->getMessage(), 'ERROR');
}