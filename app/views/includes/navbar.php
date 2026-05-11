<?php
/**
 * Module: Shared Navigation Component
 * Purpose: Enterprise-style sidebar and top bar used across authenticated pages
 */

require_once __DIR__ . "/../../helpers/SessionHandler.php";
$currentUser = getCurrentUser();
$username = $currentUser['username'] ?? 'Guest';
$role = $currentUser['role'] ?? 'volunteer';
$displayName = strtolower($username) === 'admin' ? 'Admin User' : $username;
$displayRole = strtolower($role) === 'admin' ? 'Administrator' : ucfirst($role);
$initials = strtolower($username) === 'admin' ? 'AD' : strtoupper(substr($username, 0, 1));
$currentAction = basename($_SERVER['PHP_SELF'] ?? '');

$navItems = [
    ['label' => 'Dashboard', 'icon' => 'fa-house', 'href' => '../controllers/DashboardController.php?action=overview', 'match' => 'DashboardController.php'],
    ['label' => 'Beneficiaries', 'icon' => 'fa-users', 'href' => '../controllers/BeneficiaryController.php?action=list', 'match' => 'BeneficiaryController.php'],
    ['label' => 'Attendance', 'icon' => 'fa-clipboard-check', 'href' => '../controllers/AttendanceController.php?action=list', 'match' => 'AttendanceController.php'],
    ['label' => 'Food Stock', 'icon' => 'fa-boxes-stacked', 'href' => '../controllers/FoodStockController.php?action=list', 'match' => 'FoodStockController.php'],
    ['label' => 'Volunteers', 'icon' => 'fa-user-check', 'href' => '../controllers/VolunteerController.php?action=list', 'match' => 'VolunteerController.php'],
    ['label' => 'Donations', 'icon' => 'fa-hand-holding-dollar', 'href' => '../controllers/DonationController.php?action=list', 'match' => 'DonationController.php'],
    ['label' => 'Reports', 'icon' => 'fa-file-lines', 'href' => '../controllers/ReportsController.php?action=dashboard', 'match' => 'ReportsController.php'],
    ['label' => 'Users', 'icon' => 'fa-shield-halved', 'href' => '../controllers/UserController.php?action=list', 'match' => 'UserController.php'],
];
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/public/assets/css/fsms-ui.css">

<aside class="fsms-sidebar" aria-label="Primary navigation">
    <a class="fsms-brand" href="../controllers/DashboardController.php?action=overview">
        <img class="fsms-brand-logo" src="/public/assets/images/tharimpepe-logo.png" alt="Tharimpepe">
        <span class="fsms-brand-title">Feeding Scheme Management</span>
    </a>

    <nav class="fsms-nav">
        <?php foreach ($navItems as $item): ?>
            <a class="fsms-nav-link <?php echo $currentAction === $item['match'] ? 'active' : ''; ?>"
               href="<?php echo htmlspecialchars($item['href']); ?>">
                <i class="fas <?php echo htmlspecialchars($item['icon']); ?>" aria-hidden="true"></i>
                <span class="fsms-nav-text"><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>

        <div class="fsms-nav-bottom">
            <a class="fsms-nav-link" href="../controllers/AuthController.php?action=logout">
                <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>
                <span class="fsms-nav-text">Logout</span>
            </a>
        </div>
    </nav>
</aside>

<header class="fsms-topbar">
    <div>
        <h1 class="fsms-page-title"><?php echo htmlspecialchars($pageTitle ?? 'Admin Portal'); ?></h1>
        <div class="fsms-page-subtitle">Tharimpepe Feeding Scheme</div>
    </div>
    <div class="fsms-top-actions">
        <a class="fsms-icon-button" href="../controllers/MessageController.php?action=inbox" aria-label="View notifications and messages">
            <i class="fas fa-bell" aria-hidden="true"></i>
            <span class="fsms-alert-dot" aria-hidden="true"></span>
        </a>
        <div class="fsms-user-chip">
            <span class="fsms-avatar" aria-hidden="true"><?php echo htmlspecialchars($initials); ?></span>
            <span class="fsms-user-meta">
                <span class="fsms-user-name"><?php echo htmlspecialchars($displayName); ?></span>
                <span class="fsms-user-role"><?php echo htmlspecialchars($displayRole); ?></span>
            </span>
        </div>
    </div>
</header>
