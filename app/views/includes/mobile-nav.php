<?php
// Bottom navigation (mobile)
// Active state handled by setting $activeNav in each mobile view.
$activeNav = isset($activeNav) ? $activeNav : 'dashboard';
?>
<nav class="mobile-bottom-nav" role="navigation" aria-label="Primary">
  <a class="mobile-nav-item <?php echo $activeNav === 'dashboard' ? 'active' : ''; ?>" href="/views/dashboard/dashboard-mobile.php" aria-label="Dashboard">
    <i class="fa-solid fa-house" aria-hidden="true"></i>
    <span>Home</span>
  </a>

  <a class="mobile-nav-item <?php echo $activeNav === 'beneficiaries' ? 'active' : ''; ?>" href="/views/beneficiaries/list-mobile.php" aria-label="Beneficiaries">
    <i class="fa-solid fa-users" aria-hidden="true"></i>
    <span>Beneficiaries</span>
  </a>

  <a class="mobile-nav-item <?php echo $activeNav === 'attendance' ? 'active' : ''; ?>" href="/views/attendance/list-mobile.php" aria-label="Attendance">
    <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
    <span>Attendance</span>
  </a>

  <a class="mobile-nav-item <?php echo $activeNav === 'stock' ? 'active' : ''; ?>" href="/views/food_stock/list-mobile.php" aria-label="Food Stock">
    <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
    <span>Stock</span>
  </a>
</nav>

