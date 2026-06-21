<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';

$results = [
    'database' => getDBConnection() ? 'ok' : 'fail',
    'tables' => [],
    'issues' => []
];

$requiredTables = [
    'users', 'beneficiaries', 'attendance', 'donations', 'volunteers',
    'foodstock', 'activitylog', 'authtokens', 'Messages'
];

$optionalTables = [
    'blogposts', 'gallery', 'announcements', 'outreachprograms',
    'chatbotfaq', 'paymenttransactions', 'auditlogs', 'reportschedules'
];

$db = getDBConnection();
if ($db) {
    foreach (array_merge($requiredTables, $optionalTables) as $table) {
        try {
            $stmt = $db->query("SELECT 1 FROM {$table} LIMIT 1");
            $results['tables'][$table] = 'exists';
        } catch (Exception $e) {
            $results['tables'][$table] = 'missing';
            if (in_array($table, $requiredTables, true)) {
                $results['issues'][] = "Required table missing: {$table}";
            }
        }
    }
}

echo json_encode($results, JSON_PRETTY_PRINT) . PHP_EOL;
