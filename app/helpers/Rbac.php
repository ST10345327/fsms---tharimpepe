<?php
/**
 * Centralized role-based access control for Tharimpepe FSMS.
 * Web controllers, views, and API endpoints should use these helpers.
 */

require_once __DIR__ . '/SessionHandler.php';

/** @return string[] */
function rbacAllRoles()
{
    return ['admin', 'staff', 'volunteer', 'donor'];
}

/**
 * Permission key => allowed roles.
 *
 * @return array<string, string[]>
 */
function rbacPermissions()
{
    return [
        // Dashboards
        'dashboard.operational' => ['admin', 'staff', 'volunteer'],
        'dashboard.donor'       => ['donor'],

        // Account (all authenticated roles)
        'profile'               => ['admin', 'staff', 'volunteer', 'donor'],
        'change_password'       => ['admin', 'staff', 'volunteer', 'donor'],

        // User administration
        'users'                 => ['admin'],

        // Operational modules
        'beneficiaries'         => ['admin', 'staff'],
        'attendance'            => ['admin', 'staff', 'volunteer'],
        'food_stock'            => ['admin', 'staff'],
        'volunteers'            => ['admin', 'staff'],
        'schedules'             => ['admin', 'staff', 'volunteer'],
        'donations.manage'      => ['admin', 'staff'],
        'donations.own'         => ['donor'],
        'reports'               => ['admin', 'staff'],
        'messages'              => ['admin', 'staff', 'volunteer'],
        'audit'                 => ['admin', 'staff'],

        // Destructive actions
        'donations.delete'      => ['admin'],
        'food_stock.delete'     => ['admin'],
        'beneficiaries.delete'   => ['admin'],

        // API permission groups
        'api.beneficiaries'     => ['admin', 'staff'],
        'api.attendance.read'   => ['admin', 'staff', 'volunteer'],
        'api.attendance.write'  => ['admin', 'staff', 'volunteer'],
        'api.attendance.export' => ['admin', 'staff'],
        'api.stock'             => ['admin', 'staff'],
        'api.volunteers.manage' => ['admin', 'staff'],
        'api.volunteers.self'   => ['admin', 'staff', 'volunteer'],
        'api.donations.manage'  => ['admin', 'staff'],
        'api.donations.own'     => ['admin', 'staff', 'donor'],
        'api.reports'           => ['admin', 'staff'],
        'api.dashboard.ops'     => ['admin', 'staff', 'volunteer'],
        'api.dashboard.donor'   => ['donor'],
        'api.users.admin'       => ['admin'],
        'api.users.self'        => ['admin', 'staff', 'volunteer', 'donor'],
        'api.audit'             => ['admin', 'staff'],
        'api.activity'          => ['admin', 'staff'],
        'api.meal_sessions.read'=> ['admin', 'staff', 'volunteer'],
        'api.meal_sessions.write'=> ['admin', 'staff'],
        'api.notifications'     => ['admin', 'staff', 'volunteer'],
    ];
}

/**
 * Sidebar / navigation items with permission keys.
 *
 * @return array<int, array<string, string>>
 */
function rbacNavItems()
{
    return [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-house',
            'href' => '/controllers/DashboardController.php?action=overview',
            'match' => 'DashboardController',
            'permission' => 'dashboard.operational',
        ],
        [
            'label' => 'My Dashboard',
            'icon' => 'fa-house',
            'href' => '/controllers/DonorController.php?action=dashboard',
            'match' => 'DonorController',
            'permission' => 'dashboard.donor',
        ],
        [
            'label' => 'My Donations',
            'icon' => 'fa-hand-holding-dollar',
            'href' => '/controllers/DonorController.php?action=history',
            'match' => 'DonorController',
            'permission' => 'donations.own',
        ],
        [
            'label' => 'Beneficiaries',
            'icon' => 'fa-users',
            'href' => '/controllers/BeneficiaryController.php?action=list',
            'match' => 'BeneficiaryController',
            'permission' => 'beneficiaries',
        ],
        [
            'label' => 'Attendance',
            'icon' => 'fa-clipboard-check',
            'href' => '/controllers/AttendanceController.php?action=list',
            'match' => 'AttendanceController',
            'permission' => 'attendance',
        ],
        [
            'label' => 'Food Stock',
            'icon' => 'fa-boxes-stacked',
            'href' => '/controllers/FoodStockController.php?action=list',
            'match' => 'FoodStockController',
            'permission' => 'food_stock',
        ],
        [
            'label' => 'Volunteers',
            'icon' => 'fa-user-check',
            'href' => '/controllers/VolunteerScheduleController.php?action=list',
            'match' => 'VolunteerScheduleController',
            'permission' => 'schedules',
        ],
        [
            'label' => 'Donations',
            'icon' => 'fa-hand-holding-dollar',
            'href' => '/controllers/DonationController.php?action=list',
            'match' => 'DonationController',
            'permission' => 'donations.manage',
        ],
        [
            'label' => 'Reports',
            'icon' => 'fa-file-lines',
            'href' => '/controllers/ReportsController.php?action=dashboard',
            'match' => 'ReportsController',
            'permission' => 'reports',
        ],
        [
            'label' => 'Users',
            'icon' => 'fa-shield-halved',
            'href' => '/controllers/UserController.php?action=list',
            'match' => 'UserController',
            'permission' => 'users',
        ],
        [
            'label' => 'My Profile',
            'icon' => 'fa-user-circle',
            'href' => '/controllers/ProfileController.php?action=profile',
            'match' => 'ProfileController',
            'permission' => 'profile',
        ],
    ];
}

function rbacCurrentRole()
{
    $user = getCurrentUser();
    return $user ? strtolower((string)$user['role']) : '';
}

function rbacRolesFor($permission)
{
    $permissions = rbacPermissions();
    return $permissions[$permission] ?? [];
}

function rbacCan($permission, $role = null)
{
    $role = $role ?? rbacCurrentRole();
    if ($role === '') {
        return false;
    }
    return in_array($role, rbacRolesFor($permission), true);
}

function rbacNavItemsForRole($role = null)
{
    $role = $role ?? rbacCurrentRole();
    $items = [];
    $seen = [];

    foreach (rbacNavItems() as $item) {
        $key = $item['label'] . '|' . $item['href'];
        if (isset($seen[$key])) {
            continue;
        }
        if (!rbacCan($item['permission'], $role)) {
            continue;
        }
        $seen[$key] = true;
        $items[] = $item;
    }

    return $items;
}

function rbacDefaultDashboardPath($role = null)
{
    $role = $role ?? rbacCurrentRole();
    if ($role === 'donor') {
        return '/controllers/DonorController.php?action=dashboard';
    }
    return '/views/dashboard.php';
}

function rbacDenyWeb($message = 'Access denied')
{
    $_SESSION['error'] = $message;
    header('Location: ' . rbacDefaultDashboardPath());
    exit;
}

function rbacRequirePermission($permission)
{
    requireLogin();
    if (!rbacCan($permission)) {
        rbacDenyWeb('You do not have permission to access this page.');
    }
}

function rbacRequireAnyPermission(array $permissions)
{
    requireLogin();
    foreach ($permissions as $permission) {
        if (rbacCan($permission)) {
            return;
        }
    }
    rbacDenyWeb('You do not have permission to access this page.');
}

/**
 * Guard API endpoints using AuthMiddleware.
 *
 * @return array Authenticated user
 */
function rbacApiRequire($permission, AuthMiddleware $auth)
{
    return $auth->requireRole(rbacRolesFor($permission));
}
