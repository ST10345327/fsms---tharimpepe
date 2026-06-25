<?php
/**
 * Module: Shared Navigation Component
 * Purpose: Enterprise-style sidebar and top bar used across authenticated pages
 */

require_once __DIR__ . "/../../helpers/SessionHandler.php";
require_once __DIR__ . "/../../helpers/Rbac.php";
$currentUser = getCurrentUser();
$username = $currentUser['username'] ?? 'Guest';
$role = $currentUser['role'] ?? 'volunteer';
$username = (string)($username ?? 'Guest');
$role = (string)($role ?? 'volunteer');
$displayName = strtolower($username) === 'admin' ? 'Admin User' : $username;
$displayRole = strtolower($role) === 'admin' ? 'Administrator' : ucfirst($role);
$initials = strtolower($username) === 'admin' ? 'AD' : strtoupper(substr($username ?: 'G', 0, 1));
$currentAction = basename($_SERVER['PHP_SELF'] ?? '');

$navItems = rbacNavItemsForRole($role);
?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/fsms-ui.css">

<aside class="fsms-sidebar" aria-label="Primary navigation">
    <a class="fsms-brand" href="../controllers/DashboardController.php?action=overview">
           <img class="fsms-brand-logo" src="/assets/images/generate_raster.php?name=tharimpepe-logo&w=172&h=48&dpr=1"
               srcset="/assets/images/generate_raster.php?name=tharimpepe-logo&w=172&h=48&dpr=1 1x, /assets/images/generate_raster.php?name=tharimpepe-logo&w=344&h=96&dpr=2 2x"
               sizes="(max-width:640px) 58px, (max-width:992px) 120px, 172px"
               alt="Tharimpepe" loading="lazy" width="172" height="48">
        <span class="fsms-brand-title">Feeding Scheme Management</span>
    </a>

    <nav class="fsms-nav">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = ($currentAction !== '' && strpos($currentAction, $item['match'] ?? '') !== false) ? 'active' : ''; ?>
            <a class="fsms-nav-link <?php echo $isActive; ?>"
               href="<?php echo htmlspecialchars($item['href']); ?>">
                <i class="fas <?php echo htmlspecialchars($item['icon']); ?>" aria-hidden="true"></i>
                <span class="fsms-nav-text"><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>

        <div class="fsms-nav-bottom">
            <a class="fsms-nav-link" href="../../index.php?action=logout">
                <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>
                <span class="fsms-nav-text">Logout</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Offcanvas nav for small screens -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="fsmsOffcanvas" aria-labelledby="fsmsOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="fsmsOffcanvasLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="fsms-nav offcanvas-nav">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = ($currentAction !== '' && strpos($currentAction, $item['match'] ?? '') !== false) ? 'active' : ''; ?>
                <a class="fsms-nav-link <?php echo $isActive; ?>"
                   href="<?php echo htmlspecialchars($item['href']); ?>">
                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?>" aria-hidden="true"></i>
                    <span class="fsms-nav-text"><?php echo htmlspecialchars($item['label']); ?></span>
                </a>
            <?php endforeach; ?>

            <div class="fsms-nav-bottom">
                <a class="fsms-nav-link" href="../../index.php?action=logout">
                    <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>
                    <span class="fsms-nav-text">Logout</span>
                </a>
            </div>
        </nav>
    </div>
</div>

<header class="fsms-topbar">
    <button class="fsms-hamburger" type="button" data-bs-toggle="offcanvas" data-bs-target="#fsmsOffcanvas" aria-controls="fsmsOffcanvas" aria-label="Open menu">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>
    <div>
        <h1 class="fsms-page-title"><?php echo htmlspecialchars($pageTitle ?? 'Admin Portal'); ?></h1>
        <div class="fsms-page-subtitle">Tharimpepe Feeding Scheme</div>
    </div>
    <?php
        require_once __DIR__ . "/../../helpers/NotificationHelper.php";
        $notif = new NotificationHelper($currentUser['user_id'] ?? 0, $role);
        $notifications = $notif->getNotifications();
        $totalCount = array_sum(array_column($notifications, 'count'));
        $totalCount = $totalCount ?: count($notifications);
    ?>
    <div class="fsms-top-actions">
        <?php if (rbacCan('messages', $role)): ?>
        <div class="dropdown d-inline-block">
            <a class="fsms-icon-button dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="View notifications">
                <i class="fas fa-bell" aria-hidden="true"></i>
                <?php if ($totalCount > 0): ?>
                <span class="fsms-alert-dot" aria-hidden="true"><?php echo $totalCount > 9 ? '9+' : $totalCount; ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow" style="width:320px;border-radius:10px;padding:0;margin-top:10px;">
                <div style="padding:14px 16px;border-bottom:1px solid #e5e7eb;font-weight:700;font-size:15px;color:#1f2a44;">
                    Notifications
                    <?php if ($totalCount > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $totalCount; ?></span>
                    <?php endif; ?>
                </div>
                <div style="max-height:360px;overflow-y:auto;">
                <?php if (empty($notifications)): ?>
                    <div style="padding:30px 16px;text-align:center;color:#9ca3af;">
                        <i class="fas fa-check-circle fs-3 mb-2 d-block"></i>
                        <span>All caught up!</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                    <a href="<?php echo htmlspecialchars($n['link']); ?>" class="dropdown-item" style="padding:12px 16px;border-bottom:1px solid #f3f4f6;white-space:normal;display:flex;align-items:flex-start;gap:12px;">
                        <i class="<?php echo htmlspecialchars($n['icon']); ?> text-<?php echo htmlspecialchars($n['type']); ?>" style="font-size:18px;margin-top:2px;"></i>
                        <span style="font-size:14px;color:#374151;line-height:1.4;"><?php echo htmlspecialchars($n['text']); ?></span>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
                <a href="../controllers/MessageController.php?action=inbox" style="display:block;padding:12px 16px;text-align:center;border-top:1px solid #e5e7eb;font-size:14px;font-weight:600;color:#1b3a5c;text-decoration:none;border-radius:0 0 10px 10px;">
                    <i class="fas fa-envelope me-1"></i> View All Messages
                </a>
            </div>
        </div>
        <?php endif; ?>
        <div class="fsms-user-chip">
            <span class="fsms-avatar" aria-hidden="true"><?php echo htmlspecialchars($initials); ?></span>
            <span class="fsms-user-meta">
                <span class="fsms-user-name"><?php echo htmlspecialchars($displayName); ?></span>
                <span class="fsms-user-role"><?php echo htmlspecialchars($displayRole); ?></span>
            </span>
        </div>
    </div>
</header>
