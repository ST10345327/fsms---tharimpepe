---
name: mobile-app-ui
description: Builds responsive mobile UI for PHP web apps with touch-optimized navigation, bottom tabs, slide drawer menu, and WCAG-compliant components
---

# Mobile App UI Implementation Guide

Complete workflow for converting PHP web views to mobile-optimized versions with native-like navigation and responsive design.

## When to Use This Skill

- Converting existing desktop PHP views to mobile
- Adding touch-friendly navigation patterns
- Implementing responsive layouts for small screens
- Creating WCAG 2.1 AA accessible mobile interfaces
- Building bottom tab navigation with slide drawer menu

## Implementation Phases

### Phase 1: Mobile Navigation Components

1. **Bottom Tab Navigation** (`app/views/includes/mobile-nav.php`)
   - Uses CSS Grid for 4-column layout
   - Active state controlled via `$activeNav` variable
   - Minimum 44px touch targets for accessibility
   - Safe area support for notched devices

2. **Slide Drawer Menu** (`app/views/includes/mobile-drawer.php`)
   - Hidden by default, triggered by hamburger button
   - Uses `data-open` attribute for state management
   - Closes on ESC key or scrim click
   - 82vw width with 340px max-width

3. **JavaScript for Drawer** (`public/assets/js/mobile-nav.js`)
   - Opens/closes drawer without page reload
   - ESC key support for accessibility
   - No external dependencies (vanilla JS)

### Phase 2: Layout Wrapper Templates

1. **Layout Start** (`app/views/includes/mobile-layout-start.php`)
   - Sets viewport with `viewport-fit=cover`
   - Includes all mobile CSS files
   - Renders drawer and nav components
   - Provides sticky header with hamburger/profile buttons

2. **Layout End** (`app/views/includes/mobile-layout-end.php`)
   - Closes main content wrapper
   - Includes mobile JS files

3. **CSS Files** (`public/assets/css/`)
   - `mobile-base.css` - Base variables, header, nav, drawer styles
   - `mobile-responsive.css` - Media queries and light mode support
   - `mobile-animations.css` - Smooth transitions

### Phase 3: Convert Existing Views

Pattern for converting any desktop view to mobile:

```php
<?php
require_once __DIR__ . '/../../helpers/SessionHandler.php';
requireLogin();

$pageTitle = 'View Name';
$pageSubtitle = 'Optional subtitle';
$activeNav = 'dashboard'; // Match one of: dashboard, beneficiaries, attendance, stock

// Fetch data from controllers
$user = getCurrentUser();
?>

<?php include __DIR__ . '/../includes/mobile-layout-start.php'; ?>

<!-- Mobile content with proper spacing -->
<section class="mobile-section">
  <h2>Content Title</h2>
  <!-- Add mobile-optimized components here -->
</section>

<?php include __DIR__ . '/../includes/mobile-layout-end.php'; ?>
```

### Phase 4: Testing & Debugging

1. **Local Testing**
   - Run PHP server: `php -S localhost:8000`
   - Access via: `http://localhost:8000/views/dashboard/dashboard-mobile.php`

2. **Chrome DevTools Mobile Simulation**
   - Open DevTools (F12) → Toggle Device Toolbar (Ctrl+Shift+M)
   - Test breakpoints: 375px (iPhone), 768px (tablet)
   - Verify touch target sizes (44px minimum)

3. **Remote Device Testing**
   - Enable USB debugging on Android
   - Connect via Chrome: `chrome://inspect`
   - Test on actual devices for real-world performance

### Phase 5: Responsive Design Patterns

CSS breakpoints used:
- Mobile-first (no media query base)
- `@media (min-width: 768px)` - Tablet/desktop enhancements

Dark mode support:
```css
@media (prefers-color-scheme: dark) {
  :root {
    --mobile-bg: #0b1220;
    --mobile-text: #e5e7eb;
  }
}
```

## Available Navigation Routes

The bottom nav references these mobile views (create them following the pattern):
- `/views/dashboard/dashboard-mobile.php` - Dashboard home
- `/views/beneficiaries/list-mobile.php` - Beneficiary list
- `/views/attendance/list-mobile.php` - Attendance tracking
- `/views/food_stock/list-mobile.php` - Food stock management

The drawer menu includes additional routes:
- `/views/donations/list-mobile.php` - Donations
- `/views/schedules/list-mobile.php` - Schedules
- `/views/reports/list-mobile.php` - Reports

## Mobile UI Components

### KPI Cards (2-column responsive grid)
```html
<section class="kpi-grid" aria-label="Key metrics">
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Label</div>
      <div class="kpi-value">Value</div>
      <div class="kpi-meta">Meta info</div>
    </div>
    <span class="kpi-icon blue"><i class="fas fa-icon"></i></span>
  </div>
</section>
```

### Quick Action Buttons
```html
<a href="/path" class="quick-action navy">
  <i class="fas fa-plus"></i>
  <span>Action Text</span>
</a>
```

Colors: `navy`, `green`, `orange`, `purple`

### Progress Bars
```html
<div class="progress" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
  <div class="progress-bar bg-success" style="width: 50%"></div>
</div>
```

## Create a New Mobile View

Steps:
1. Copy existing `dashboard-mobile.php` as template
2. Update `$pageTitle`, `$pageSubtitle`, `$activeNav`
3. Replace content section with your module's data
4. Use semantic HTML with proper ARIA labels
5. Add any necessary inline styles (consider moving to CSS file)
6. Test at 375px width minimum