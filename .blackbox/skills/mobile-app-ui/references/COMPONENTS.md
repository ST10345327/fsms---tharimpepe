# Mobile UI Components Library

Copy-paste components for building mobile views. All components follow WCAG 2.1 AA accessibility standards with 44px minimum touch targets.

## Navigation Components

### Bottom Navigation Item
```html
<a class="mobile-nav-item active" href="/view" aria-label="Home">
  <i class="fas fa-house" aria-hidden="true"></i>
  <span>Home</span>
</a>
```

### Drawer Menu Link
```html
<a class="mobile-drawer-link" href="/path">
  <i class="fas fa-icon" aria-hidden="true"></i>
  <span>Menu Item</span>
</a>
```

### Hamburger Toggle Button
```html
<button class="mobile-hamburger" type="button" aria-label="Open menu" data-mobile-drawer-open>
  <i class="fa-solid fa-bars" aria-hidden="true"></i>
</button>
```

## Card Components

### KPI Card
```html
<div class="kpi-card">
  <div>
    <div class="kpi-label">Total Beneficiaries</div>
    <div class="kpi-value">342</div>
    <div class="kpi-meta">+12 this month</div>
  </div>
  <span class="kpi-icon blue"><i class="fas fa-users" aria-hidden="true"></i></span>
</div>
```

Icon colors: `.blue`, `.green`, `.purple`, `.orange`

### Content Card
```html
<div class="proto-card">
  <h2>Section Title</h2>
  <!-- Card content -->
</div>
```

## Form Components

### Mobile Form Group
```html
<div class="mobile-form-group">
  <label for="inputId" class="mobile-label">Label Text</label>
  <input type="text" id="inputId" class="mobile-input" placeholder="Placeholder">
</div>
```

### Action Button
```html
<a href="/path" class="quick-action navy">
  <i class="fas fa-plus" aria-hidden="true"></i>
  <span>Add Item</span>
</a>
```

Button color variants: `.navy`, `.green`, `.orange`, `.purple`

## Progress & Status

### Progress Bar
```html
<div class="progress" role="progressbar" aria-label="Stock level" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
  <div class="progress-bar bg-success" style="width: 45%"></div>
</div>
```

### Activity Item
```html
<div class="activity-item">
  <span class="activity-dot" aria-hidden="true"></span>
  <div>
    <div class="activity-title">Action performed</div>
    <div class="activity-by">by User Name</div>
  </div>
  <div class="activity-time">10 min ago</div>
</div>
```

## Layout Patterns

### Grid Layouts
```html
<!-- 2-column KPI grid -->
<section class="kpi-grid" aria-label="Metrics">
  <div class="kpi-card">...</div>
  <div class="kpi-card">...</div>
</section>

<!-- Single column cards -->
<section class="dashboard-mobile-grid">
  <div class="proto-card">...</div>
</section>
```

### Search Section
```html
<div class="search-section">
  <form method="GET" action="/controller">
    <input type="text" name="q" class="form-control" placeholder="Search..." required>
    <button type="submit" class="btn btn-outline-primary">
      <i class="fas fa-search"></i>
    </button>
  </form>
</div>
```

## Accessibility Patterns

- Always include `aria-label` on interactive elements
- Use semantic HTML (`<main>`, `<section>`, `<nav>`)
- Ensure contrast ratio >= 4.5:1 for text
- Provide `role="progressbar"` for progress bars