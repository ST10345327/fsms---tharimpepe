<?php
/**
 * Beneficiary Advanced Filter API
 * Supports filtering by age range, gender, area, status, and date range
 * 
 * Endpoint: GET /api/beneficiaries/filter.php
 * Auth: Bearer token required
 * Params: min_age, max_age, gender, status, area, registered_from, registered_to
 * Output: {
 *   "success": true,
 *   "data": [...],
 *   "meta": {"count": 25, "timestamp": "2026-01-15T10:30:00Z"}
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

    // Build dynamic query with filters
    $where = [];
    $params = [];

    // Age range filter (requires DateOfBirth column)
    $minAge = isset($_GET['min_age']) ? (int)$_GET['min_age'] : null;
    $maxAge = isset($_GET['max_age']) ? (int)$_GET['max_age'] : null;

    // Status filter
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    if ($status) {
        $where[] = 'LOWER(Status) = LOWER(:status)';
        $params[':status'] = $status;
    }

    // Gender filter (if column exists)
    $gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
    if ($gender) {
        $hasGender = false;
        try {
            $stmt = $db->query("SHOW COLUMNS FROM beneficiaries LIKE 'Gender'");
            $hasGender = (bool)$stmt->fetchColumn();
        } catch (Exception $e) { /* ignore */ }

        if ($hasGender) {
            $where[] = 'LOWER(Gender) = LOWER(:gender)';
            $params[':gender'] = $gender;
        }
    }

    // Area/Location filter
    $area = isset($_GET['area']) ? trim($_GET['area']) : '';
    if ($area) {
        $hasArea = false;
        try {
            $stmt = $db->query("SHOW COLUMNS FROM beneficiaries LIKE 'Area'");
            $hasArea = (bool)$stmt->fetchColumn();
        } catch (Exception $e) { /* ignore */ }

        if ($hasArea) {
            $where[] = 'LOWER(Area) LIKE LOWER(:area)';
            $params[':area'] = '%' . $area . '%';
        }
    }

    // Date range filter
    $regFrom = isset($_GET['registered_from']) ? trim($_GET['registered_from']) : '';
    $regTo = isset($_GET['registered_to']) ? trim($_GET['registered_to']) : '';

    $dateColumn = 'RegistrationDate';
    try {
        $stmt = $db->query("SHOW COLUMNS FROM beneficiaries LIKE 'RegistrationDate'");
        if (!$stmt->fetchColumn()) {
            $dateColumn = 'CreatedAt';
        }
    } catch (Exception $e) { /* ignore */ }

    if ($regFrom) {
        $where[] = "DATE($dateColumn) >= :reg_from";
        $params[':reg_from'] = $regFrom;
    }
    if ($regTo) {
        $where[] = "DATE($dateColumn) <= :reg_to";
        $params[':reg_to'] = $regTo;
    }

    // Pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 50;
    $offset = ($page - 1) * $limit;

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $orderBy = "ORDER BY $dateColumn DESC";

    $results = [];
    $total = 0;

    if ($db !== null) {
        // Count total
        $countSql = "SELECT COUNT(*) as cnt FROM beneficiaries $whereClause";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

        // Fetch page
        $sql = "SELECT * FROM beneficiaries $whereClause $orderBy LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);

        // Bind filter params
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        // Bind pagination
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apply age range filter in PHP (since DateOfBirth may vary)
        if ($minAge !== null || $maxAge !== null) {
            $filtered = [];
            foreach ($results as $row) {
                $dob = $row['DateOfBirth'] ?? null;
                if (!$dob) continue;

                $age = calculateAgeSimple($dob);
                if ($minAge !== null && $age < $minAge) continue;
                if ($maxAge !== null && $age > $maxAge) continue;

                $filtered[] = $row;
            }
            $results = $filtered;
        }
    }

    logMessage("Beneficiaries filtered by user '{$user['username']}' - " . count($results) . " results", 'INFO');
    echo json_encode([
        'success' => true,
        'data' => $results,
        'meta' => [
            'count' => count($results),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'timestamp' => date('c')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Beneficiary filter error: " . $e->getMessage(), 'ERROR');
}

function calculateAgeSimple($dob) {
    try {
        $birth = new DateTime($dob);
        $now = new DateTime();
        return $now->diff($birth)->y;
    } catch (Exception $e) {
        return 0;
    }
}