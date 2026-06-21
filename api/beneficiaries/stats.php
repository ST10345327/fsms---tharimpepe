<?php
/**
 * Beneficiary Statistics API Endpoint
 * Returns statistical counts by status for dashboard/widgets
 * 
 * Endpoint: GET /api/beneficiaries/stats.php
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": {
 *     "total": 148,
 *     "active": 120,
 *     "inactive": 15,
 *     "suspended": 13,
 *     "new_this_month": 12,
 *     "by_category": {"Orphan": 50, "Vulnerable": 98},
 *     "by_gender": {"Male": 60, "Female": 85, "Other": 3},
 *     "by_age_group": {"0-5": 30, "6-12": 70, "13-17": 48}
 *   }
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

    $stats = [
        'total' => 0,
        'active' => 0,
        'inactive' => 0,
        'suspended' => 0,
        'new_this_month' => 0,
        'by_category' => new stdClass(),
        'by_gender' => new stdClass(),
        'by_age_group' => new stdClass()
    ];

    if ($db !== null) {
        // Total count
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM beneficiaries");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total'] = (int)$row['cnt'];

        // By status (case-insensitive matching)
        $stmt = $db->query("
            SELECT LOWER(Status) as status, COUNT(*) as cnt
            FROM beneficiaries
            GROUP BY LOWER(Status)
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = strtolower($row['status'] ?? '');
            $count = (int)$row['cnt'];
            if ($status === 'active') $stats['active'] = $count;
            elseif ($status === 'inactive') $stats['inactive'] = $count;
            elseif ($status === 'suspended') $stats['suspended'] = $count;
        }

        // New this month
        $stmt = $db->query("
            SELECT COUNT(*) as cnt
            FROM beneficiaries
            WHERE MONTH(COALESCE(CreatedAt, RegistrationDate, CURDATE())) = MONTH(CURDATE())
              AND YEAR(COALESCE(CreatedAt, RegistrationDate, CURDATE())) = YEAR(CURDATE())
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['new_this_month'] = (int)$row['cnt'];

        // By category (if column exists)
        try {
            $stmt = $db->query("
                SELECT LOWER(Category) as category, COUNT(*) as cnt
                FROM beneficiaries
                GROUP BY LOWER(Category)
            ");
            $byCat = new stdClass();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $byCat->{$row['category']} = (int)$row['cnt'];
            }
            $stats['by_category'] = $byCat;
        } catch (Exception $e) { /* Category column may not exist */ }

        // By gender (if column exists)
        try {
            $stmt = $db->query("
                SELECT LOWER(Gender) as gender, COUNT(*) as cnt
                FROM beneficiaries
                WHERE Gender IS NOT NULL AND Gender != ''
                GROUP BY LOWER(Gender)
            ");
            $byGender = new stdClass();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $byGender->{$row['gender']} = (int)$row['cnt'];
            }
            $stats['by_gender'] = $byGender;
        } catch (Exception $e) { /* Gender column may not exist */ }

        // By age group (if DateOfBirth exists)
        try {
            $stmt = $db->query("
                SELECT 
                    CASE
                        WHEN TIMESTAMPDIFF(YEAR, DateOfBirth, CURDATE()) <= 5 THEN '0-5'
                        WHEN TIMESTAMPDIFF(YEAR, DateOfBirth, CURDATE()) <= 12 THEN '6-12'
                        WHEN TIMESTAMPDIFF(YEAR, DateOfBirth, CURDATE()) <= 17 THEN '13-17'
                        ELSE '18+'
                    END as age_group,
                    COUNT(*) as cnt
                FROM beneficiaries
                WHERE DateOfBirth IS NOT NULL
                GROUP BY age_group
            ");
            $byAge = new stdClass();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $byAge->{$row['age_group']} = (int)$row['cnt'];
            }
            $stats['by_age_group'] = $byAge;
        } catch (Exception $e) { /* DateOfBirth column may not exist */ }
    }

    logMessage("Beneficiary stats fetched by user '{$user['username']}'", 'INFO');
    echo json_encode(['success' => true, 'data' => $stats]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Beneficiary stats error: " . $e->getMessage(), 'ERROR');
}