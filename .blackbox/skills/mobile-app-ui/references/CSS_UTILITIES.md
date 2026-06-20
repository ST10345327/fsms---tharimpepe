# CSS Utilities Reference

Mobile-first CSS variables, utilities, and patterns for responsive design.

## CSS Custom Properties

```css
:root {
  --mobile-bg: #0b1220;           /* Page background */
  --mobile-surface: #0f172a;        /* Surface elements */
  --mobile-card: #0b1326;         /* Card background */
  --mobile-text: #e5e7eb;          /* Primary text */
  --mobile-muted: #9ca3af;         /* Secondary/muted text */
  --mobile-primary: #0b5ed7;       /* Primary brand color */
  --mobile-border: rgba(255,255,255,.10);
  --mobile-nav-height: 74px;       /* Bottom nav height */
}
```

## Breakpoints

```css
/* Base mobile styles (no media query) */

@media (min-width: 768px) {
  /* Tablet/desktop enhancements */
  .mobile-content { max-width: 680px; margin: 0 auto; }
}
```

## Safe Area Helpers

```css
.mobile-safe {
  padding-bottom: calc(var(--mobile-nav-height) + env(safe-area-inset-bottom));
  padding-top: calc(env(safe-area-inset-top));
}
```

## Dark Mode Support

```css
@media (prefers-color-scheme: light) {
  :root {
    --mobile-bg: #f4f7ff;
    --mobile-text: #0b1220;
    --mobile-border: rgba(0,0,0,.08);
  }
}
```

## Grid Patterns

### KPI Grid (2-column)
```css
.kpi-grid {
  display: grid;
  gap: 12px;
  margin-bottom: 16px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}
```

### Responsive Multi-column
```css
.dashboard-mobile-grid {
  display: grid;
  gap: 12px;
}
@media (min-width: 768px) {
  .dashboard-mobile-grid { grid-template-columns: 2fr 1fr; }
}
```

## Touch Target Utilities

Minimum sizes enforced:
- Buttons: 44x44px
- Nav items: min-height 74px
- Clickable icons: 44x44px container

## Animation Utilities

```css
/* From mobile-animations.css */
.drawer-enter { transform: translateX(-100%); }
.drawer-enter-active { transform: translateX(0); transition: transform .18s ease; }
```