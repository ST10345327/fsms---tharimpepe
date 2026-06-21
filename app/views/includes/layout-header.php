<?php
/**
 * Layout Header - Clean unified layout
 * All authenticated pages include this at the top
 */
require_once __DIR__ . "/../../helpers/SessionHandler.php";
$currentUser = getCurrentUser();
$username = isset($currentUser['username']) ? $currentUser['username'] : 'Guest';
$role = isset($currentUser['role']) ? $currentUser['role'] : 'volunteer';
$displayName = strtolower($username) === 'admin' ? 'Admin User' : $username;
$displayRole = strtolower($role) === 'admin' ? 'Administrator' : ucfirst($role);
$initials = strtolower($username) === 'admin' ? 'AD' : strtoupper(substr($username, 0, 1));
$currentUri = $_SERVER['REQUEST_URI'] ?? '';

$navItems = [
    ['label' => 'Dashboard', 'icon' => 'fa-house', 'href' => '/controllers/DashboardController.php?action=overview', 'match' => 'DashboardController'],
    ['label' => 'Beneficiaries', 'icon' => 'fa-users', 'href' => '/controllers/BeneficiaryController.php?action=list', 'match' => 'BeneficiaryController'],
    ['label' => 'Attendance', 'icon' => 'fa-clipboard-check', 'href' => '/controllers/AttendanceController.php?action=list', 'match' => 'AttendanceController'],
    ['label' => 'Food Stock', 'icon' => 'fa-boxes-stacked', 'href' => '/controllers/FoodStockController.php?action=list', 'match' => 'FoodStockController'],
    ['label' => 'Volunteers', 'icon' => 'fa-user-check', 'href' => '/controllers/VolunteerScheduleController.php?action=list', 'match' => 'VolunteerScheduleController'],
    ['label' => 'Donations', 'icon' => 'fa-hand-holding-dollar', 'href' => '/controllers/DonationController.php?action=list', 'match' => 'DonationController'],
    ['label' => 'Reports', 'icon' => 'fa-file-lines', 'href' => '/controllers/ReportsController.php?action=dashboard', 'match' => 'ReportsController'],
    ['label' => 'Users', 'icon' => 'fa-shield-halved', 'href' => '/controllers/UserController.php?action=list', 'match' => 'UserController'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'FSMS'); ?> · Tharimpepe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <script src="/assets/js/fsms-app.js" defer></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="fsms-sidebar-overlay" id="fsms-sidebar-overlay" aria-hidden="true"></div>
    <aside class="fsms-sidebar" id="fsms-sidebar" aria-label="Sidebar navigation">
        <a class="fsms-brand" href="../controllers/DashboardController.php?action=overview">
            <span class="fsms-brand-icon">T</span>
            <span class="fsms-brand-title">Tharimpepe</span>
            <span class="fsms-brand-subtitle">Feeding Scheme</span>
        </a>

        <nav class="fsms-nav">
            <?php foreach ($navItems as $item):
                $isActive = strpos($currentUri, $item['match']) !== false;
            ?>
                <a class="fsms-nav-link <?php echo $isActive ? 'active' : ''; ?>"
                   href="<?php echo htmlspecialchars($item['href']); ?>">
                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                    <span><?php echo htmlspecialchars($item['label']); ?></span>
                </a>
            <?php endforeach; ?>

            <div class="fsms-nav-divider"></div>

            <a class="fsms-nav-link" href="../controllers/AuthController.php?action=logout">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Notifications offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="notificationsOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold">Notifications</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="p-4 text-center text-muted">
                <i class="fas fa-bell-slash fs-1 mb-3 opacity-25"></i>
                <p>No notifications</p>
            </div>
        </div>
    </div>

    <!-- Main area -->
    <div class="fsms-main">
        <!-- Top bar -->
        <header class="fsms-topbar">
            <div class="fsms-topbar-left">
                <button type="button" class="fsms-sidebar-toggle" id="fsms-sidebar-toggle" aria-label="Open navigation menu" aria-expanded="false" aria-controls="fsms-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="fsms-topbar-titles">
                    <h1 class="fsms-page-title"><?php echo htmlspecialchars($pageTitle ?? 'Admin Portal'); ?></h1>
                    <?php if (!empty($pageSubtitle)): ?>
                        <p class="fsms-page-subtitle"><?php echo htmlspecialchars($pageSubtitle); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="fsms-topbar-right">
                <button id="fsms-dark-toggle" type="button" class="fsms-topbar-btn" aria-label="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="fsms-topbar-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#notificationsOffcanvas" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                </button>
                <div class="fsms-user-badge">
                    <span class="fsms-avatar"><?php echo htmlspecialchars($initials); ?></span>
                    <div>
                        <div class="fsms-user-name"><?php echo htmlspecialchars($displayName); ?></div>
                        <div class="fsms-user-role"><?php echo htmlspecialchars($displayRole); ?></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <div class="fsms-content">