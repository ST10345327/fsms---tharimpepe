<?php
// Slide-out drawer for mobile navigation.
?>
<aside class="mobile-drawer" aria-hidden="true" data-mobile-drawer>
  <div class="mobile-drawer-scrim" data-mobile-drawer-close></div>
  <div class="mobile-drawer-panel" role="dialog" aria-modal="true" aria-label="Menu">
    <div class="mobile-drawer-header">
      <div class="mobile-drawer-brand">Menu</div>
      <button class="mobile-drawer-close" type="button" aria-label="Close menu" data-mobile-drawer-close>
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </div>

    <div class="mobile-drawer-section">
      <a class="mobile-drawer-link" href="/views/dashboard/dashboard-mobile.php">
        <i class="fa-solid fa-house" aria-hidden="true"></i>
        <span>Dashboard</span>
      </a>

      <a class="mobile-drawer-link" href="/views/beneficiaries/list-mobile.php">
        <i class="fa-solid fa-users" aria-hidden="true"></i>
        <span>Beneficiaries</span>
      </a>

      <a class="mobile-drawer-link" href="/views/attendance/list-mobile.php">
        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
        <span>Attendance</span>
      </a>

      <a class="mobile-drawer-link" href="/views/food_stock/list-mobile.php">
        <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
        <span>Food Stock</span>
      </a>

      <a class="mobile-drawer-link" href="/views/donations/list-mobile.php">
        <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
        <span>Donations</span>
      </a>

      <a class="mobile-drawer-link" href="/views/schedules/list-mobile.php">
        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
        <span>Schedules</span>
      </a>

      <a class="mobile-drawer-link" href="/views/reports/list-mobile.php">
        <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
        <span>Reports</span>
      </a>
    </div>

    <div class="mobile-drawer-section">
      <a class="mobile-drawer-link mobile-drawer-logout" href="/controllers/AuthController.php?action=logout">
        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
        <span>Logout</span>
      </a>
    </div>
  </div>
</aside>

