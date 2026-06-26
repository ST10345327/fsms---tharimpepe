<?php
require_once __DIR__ . '/config.php';

$endpoint = isset($_GET['endpoint']) ? trim($_GET['endpoint'], '/') : '';

if (empty($endpoint)) {
    apiJsonResponse(true, 'Tharimpepe FSMS API v1.0', [
        'endpoints' => [
            'POST /api/login',
            'POST /api/register',
            'POST /api/logout',
            'GET /api/beneficiaries',
            'GET /api/beneficiaries/{id}',
            'POST /api/beneficiaries',
            'PUT /api/beneficiaries/{id}',
            'DELETE /api/beneficiaries/{id}',
            'GET /api/attendance',
            'POST /api/attendance',
            'GET /api/stock',
            'POST /api/stock',
            'PUT /api/stock/{id}',
            'DELETE /api/stock/{id}',
            'GET /api/donations',
            'POST /api/donations',
            'GET /api/volunteers',
            'POST /api/volunteers',
            'GET /api/dashboard',
            'GET /api/reports',
            'GET /api/users',
            'GET /api/mealsessions',
        ]
    ]);
}

$parts = explode('/', $endpoint);
$resource = $parts[0] ?? '';
$id = $parts[1] ?? null;
$subresource = $parts[2] ?? null;

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($resource) {
        case 'login':
            require __DIR__ . '/auth.php';
            handleLogin();
            break;
        case 'register':
            require __DIR__ . '/auth.php';
            handleRegister();
            break;
        case 'logout':
            require __DIR__ . '/auth.php';
            handleLogout();
            break;
        case 'beneficiaries':
            require __DIR__ . '/beneficiaries.php';
            handleBeneficiaries($method, $id);
            break;
        case 'attendance':
            require __DIR__ . '/attendance.php';
            handleAttendance($method, $id, $subresource);
            break;
        case 'stock':
            require __DIR__ . '/stock.php';
            handleStock($method, $id);
            break;
        case 'donations':
            require __DIR__ . '/donations.php';
            handleDonations($method, $id);
            break;
        case 'volunteers':
            require __DIR__ . '/volunteers.php';
            handleVolunteers($method, $id);
            break;
        case 'dashboard':
            require __DIR__ . '/dashboard.php';
            handleDashboard();
            break;
        case 'reports':
            require __DIR__ . '/reports.php';
            handleReports();
            break;
        case 'users':
            require __DIR__ . '/users.php';
            handleUsers($method, $id);
            break;
        case 'mealsessions':
            require __DIR__ . '/mealsessions.php';
            handleMealSessions($method, $id);
            break;
        default:
            apiJsonResponse(false, 'Endpoint not found: /api/' . $resource, null, 404);
    }
} catch (Exception $e) {
    apiJsonResponse(false, 'Server error: ' . $e->getMessage(), null, 500);
}
