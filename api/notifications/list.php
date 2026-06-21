<?php
/**
 * Notifications List API Endpoint
 * Returns a list of notifications for the current user
 *
 * Endpoint: GET /api/notifications/list.php
 * Auth: Bearer token required
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }

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

    $notifications = [];

    // 1. Get messages from the Messages table
    $stmt = $db->prepare("
        SELECT MessageID, Subject as type, Content as message, SentAt as timestamp, IsRead as is_read
        FROM Messages
        WHERE RecipientID = :uid OR RecipientID IS NULL
        ORDER BY SentAt DESC
        LIMIT 20
    ");
    $stmt->execute([':uid' => $user['user_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as $m) {
        $notifications[] = [
            'id' => 'msg_' . $m['MessageID'],
            'message' => $m['message'],
            'type' => strtolower($m['type'] ?: 'info'),
            'timestamp' => $m['timestamp'],
            'read' => (bool)$m['is_read'],
            'link' => null
        ];
    }

    // 2. Add some "Dynamic Notifications" based on system state (simulating real alerts)

    // Low Stock
    $stmt = $db->query("SELECT ItemName, Quantity FROM foodstock WHERE Quantity < 10 LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            'id' => 'stock_low_' . md5($row['ItemName']),
            'message' => "Low Stock Alert: " . $row['ItemName'] . " is down to " . $row['Quantity'] . " units.",
            'type' => 'stock',
            'timestamp' => date('c'),
            'read' => false,
            'link' => 'stock.html'
        ];
    }

    // Sort by timestamp descending
    usort($notifications, function($a, $b) {
        return strcmp($b['timestamp'], $a['timestamp']);
    });

    echo json_encode(['success' => true, 'data' => $notifications]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Notifications list error: " . $e->getMessage(), 'ERROR');
}
