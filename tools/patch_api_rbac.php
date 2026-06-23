<?php
/**
 * One-off RBAC patcher for API endpoints.
 */
$root = dirname(__DIR__);
$map = [
    'api/beneficiaries/create.php' => 'api.beneficiaries',
    'api/beneficiaries/update.php' => 'api.beneficiaries',
    'api/beneficiaries/list.php' => 'api.beneficiaries',
    'api/beneficiaries/get.php' => 'api.beneficiaries',
    'api/beneficiaries/detail.php' => 'api.beneficiaries',
    'api/beneficiaries/filter.php' => 'api.beneficiaries',
    'api/beneficiaries/stats.php' => 'api.beneficiaries',
    'api/beneficiaries/delete.php' => 'api.beneficiaries',
    'api/attendance/save.php' => 'api.attendance.write',
    'api/attendance/bulk-mark.php' => 'api.attendance.write',
    'api/attendance/analytics.php' => 'api.attendance.read',
    'api/attendance/export.php' => 'api.attendance.export',
    'api/attendance/history.php' => 'api.attendance.read',
    'api/attendance/recent.php' => 'api.attendance.read',
    'api/attendance/sessions.php' => 'api.attendance.read',
    'api/attendance/stats.php' => 'api.attendance.read',
    'api/attendance/today.php' => 'api.attendance.read',
    'api/stock/add.php' => 'api.stock',
    'api/stock/update.php' => 'api.stock',
    'api/stock/list.php' => 'api.stock',
    'api/stock/stats.php' => 'api.stock',
    'api/stock/alerts.php' => 'api.stock',
    'api/stock/history.php' => 'api.stock',
    'api/stock/low-stock.php' => 'api.stock',
    'api/stock/movements.php' => 'api.stock',
    'api/stock/distribute.php' => 'api.stock',
    'api/volunteers/register.php' => 'api.volunteers.manage',
    'api/volunteers/assign-shift.php' => 'api.volunteers.manage',
    'api/volunteers/list.php' => 'api.volunteers.manage',
    'api/volunteers/stats.php' => 'api.volunteers.manage',
    'api/volunteers/schedule.php' => 'api.volunteers.self',
    'api/volunteers/status.php' => 'api.volunteers.self',
    'api/volunteers/attendance.php' => 'api.volunteers.self',
    'api/donations/cash.php' => 'api.donations.manage',
    'api/donations/record.php' => 'api.donations.manage',
    'api/donations/list.php' => 'api.donations.manage',
    'api/donations/history.php' => 'api.donations.own',
    'api/donations/receipt.php' => 'api.donations.own',
    'api/reports/generate.php' => 'api.reports',
    'api/reports/export.php' => 'api.reports',
    'api/reports/download.php' => 'api.reports',
    'api/reports/history.php' => 'api.reports',
    'api/reports/schedule.php' => 'api.reports',
    'api/reports/summary.php' => 'api.reports',
    'api/dashboard/summary.php' => 'api.dashboard.ops',
    'api/dashboard/activity.php' => 'api.dashboard.ops',
    'api/dashboard/alerts.php' => 'api.dashboard.ops',
    'api/dashboard/inventory-summary.php' => 'api.dashboard.ops',
    'api/dashboard/donations-summary.php' => 'api.dashboard.donor',
    'api/users/create.php' => 'api.users.admin',
    'api/users/update.php' => 'api.users.admin',
    'api/users/list.php' => 'api.users.admin',
    'api/users/change-password.php' => 'api.users.self',
    'api/audit/logs.php' => 'api.audit',
    'api/audit/stats.php' => 'api.audit',
    'api/activity/list.php' => 'api.activity',
    'api/meal-sessions/list.php' => 'api.meal_sessions.read',
    'api/meal-sessions/create.php' => 'api.meal_sessions.write',
    'api/meal-sessions/close.php' => 'api.meal_sessions.write',
    'api/notifications/list.php' => 'api.notifications',
    'api/notifications/delete.php' => 'api.notifications',
];

foreach ($map as $relativePath => $permission) {
    $file = $root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($file)) {
        echo "SKIP missing: {$relativePath}\n";
        continue;
    }

    $content = file_get_contents($file);
    $original = $content;

    if (strpos($content, "require_once __DIR__ . '/../../app/helpers/Rbac.php';") === false) {
        $content = str_replace(
            "require_once __DIR__ . '/../middleware/AuthMiddleware.php';",
            "require_once __DIR__ . '/../middleware/AuthMiddleware.php';\nrequire_once __DIR__ . '/../../app/helpers/Rbac.php';",
            $content
        );
    }

    $patterns = [
        '/\$auth->requireRole\(\[[^\]]+\]\);/',
        '/\$user = \$auth->requireRole\(\[[^\]]+\]\);/',
        '/\$auth->requireAuth\(\);/',
        '/\$user = \$auth->requireAuth\(\);/',
        '/\$currentUser = \$auth->requireAuth\(\);/',
    ];

    $replacementUser = "\$user = rbacApiRequire('{$permission}', \$auth);";
    $replacementAuth = "rbacApiRequire('{$permission}', \$auth);";

    if (preg_match('/\$currentUser = \$auth->requireAuth\(\);/', $content)) {
        $content = preg_replace('/\$currentUser = \$auth->requireAuth\(\);/', str_replace('$user', '$currentUser', $replacementUser), $content, 1);
    } elseif (preg_match('/\$user = \$auth->requireRole\(\[[^\]]+\]\);/', $content)) {
        $content = preg_replace('/\$user = \$auth->requireRole\(\[[^\]]+\]\);/', $replacementUser, $content, 1);
    } elseif (preg_match('/\$auth->requireRole\(\[[^\]]+\]\);/', $content)) {
        $content = preg_replace('/\$auth->requireRole\(\[[^\]]+\]\);/', $replacementAuth, $content, 1);
    } elseif (preg_match('/\$user = \$auth->requireAuth\(\);/', $content)) {
        $content = preg_replace('/\$user = \$auth->requireAuth\(\);/', $replacementUser, $content, 1);
    } elseif (preg_match('/\$auth->requireAuth\(\);/', $content)) {
        $content = preg_replace('/\$auth->requireAuth\(\);/', $replacementAuth, $content, 1);
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "UPDATED: {$relativePath} => {$permission}\n";
    } else {
        echo "UNCHANGED: {$relativePath}\n";
    }
}

echo "Done.\n";
