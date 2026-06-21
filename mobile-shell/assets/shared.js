/**
 * Shared JavaScript for Tharimpepe FSMS Mobile App
 * 
 * Provides:
 * - Unified header/hamburger UI
 * - Auth state management via localStorage (Persistent across app restarts)
 * - Session restoration via token validation
 * - Role-based UI rendering
 * - API-powered data loading
 * - Notification system integration
 * - Feedback UI: toast, confirm dialog, skeletons, offline queue integration
 */

// ====== THEME MANAGEMENT ======

const ThemeManager = {
  key: 'app_theme',

  init() {
    const saved = localStorage.getItem(this.key) || 'system';
    this.apply(saved);
  },

  apply(theme) {
    let actualTheme = theme;
    if (theme === 'system') {
      actualTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    document.documentElement.setAttribute('data-theme', actualTheme);
    localStorage.setItem(this.key, theme);

    // Update theme-color meta tag for Android status bar
    const metaTheme = document.querySelector('meta[name="theme-color"]');
    if (metaTheme) {
      metaTheme.setAttribute('content', actualTheme === 'dark' ? '#0f1419' : '#1b3a5c');
    }

    console.log(`[Theme] Applied ${theme} mode`);
  },

  get() {
    return localStorage.getItem(this.key) || 'system';
  },

  toggle() {
    const current = this.get();
    const next = current === 'dark' ? 'light' : current === 'light' ? 'system' : 'dark';
    this.apply(next);
    return next;
  }
};

// ====== AUTH HELPERS ======

/**
 * Get current user from localStorage (persistent storage)
 * Falls back to sessionStorage for backward compatibility
 */
function getCurrentUser() {
  let user = null;
  // Try localStorage first (persistent across app restarts)
  const data = localStorage.getItem('user');
  if (data) user = JSON.parse(data);

  if (!user) {
    // Fallback to sessionStorage for backward compatibility
    const sessionData = sessionStorage.getItem('user');
    if (sessionData) {
      user = JSON.parse(sessionData);
      localStorage.setItem('user', JSON.stringify(user));
      sessionStorage.removeItem('user');
    }
  }

  if (user && user.role) {
    user.role = String(user.role).toLowerCase();
    if (user.role === 'coordinator') user.role = 'staff';
  }
  return user;
}

/**
 * Get initials from name/username
 */
function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }
  return name.charAt(0).toUpperCase();
}

/**
 * Get role display name
 */
function getRoleDisplay(role) {
  const roles = {
    admin: 'Administrator',
    volunteer: 'Volunteer',
    coordinator: 'Coordinator',
    staff: 'Staff',
    donor: 'Donor'
  };
  return roles[role] || role || 'User';
}

/**
 * Check if user has a specific role (case-insensitive)
 */
function hasRole(role) {
  const user = getCurrentUser();
  return user && user.role && user.role.toLowerCase() === role.toLowerCase();
}

/**
 * Check if user has any of the specified roles
 */
function hasAnyRole(roles) {
  const user = getCurrentUser();
  if (!user || !user.role) return false;
  const userRole = user.role.toLowerCase();
  return roles.some(r => r.toLowerCase() === userRole);
}

// ====== UI BUILDERS ======

/**
 * Build hamburger menu HTML with role-based filtering
 */
function buildHamburgerMenu(currentPage) {
  const user = getCurrentUser();
  const username = user ? (user.username || user.fullname || user.name || 'User') : 'User';
  const role = user ? user.role : 'guest';
  const initials = getInitials(username);
  const email = user ? (user.email || user.username || '') : '';

  // Role-based navigation items (aligned with website layout-header.php)
  const allPages = [
    { id: 'dashboard', icon: 'fa-house', label: 'Dashboard', href: 'dashboard.html', roles: ['admin', 'volunteer', 'coordinator', 'staff'] },
    { id: 'beneficiaries', icon: 'fa-users', label: 'Beneficiaries', href: 'beneficiaries.html', roles: ['admin', 'coordinator', 'staff'] },
    { id: 'attendance', icon: 'fa-clipboard-check', label: 'Attendance', href: 'attendance.html', roles: ['admin', 'volunteer', 'coordinator', 'staff'] },
    { id: 'stock', icon: 'fa-boxes-stacked', label: 'Food Stock', href: 'stock.html', roles: ['admin', 'coordinator', 'staff'] },
    { id: 'volunteers', icon: 'fa-user-check', label: 'Volunteers', href: 'volunteers.html', roles: ['admin', 'coordinator', 'staff'] },
    { id: 'donations', icon: 'fa-hand-holding-dollar', label: 'Donations', href: 'stock.html#donations', roles: ['admin', 'coordinator', 'staff'] },
    { id: 'reports', icon: 'fa-file-lines', label: 'Reports', href: 'reports.html', roles: ['admin', 'coordinator', 'staff'] },
    { id: 'users', icon: 'fa-shield-halved', label: 'Audit Logs', href: 'security.html', roles: ['admin'] }
  ];

  // Filter based on user role
  const pages = allPages.filter(p => p.roles.includes(role));

  const navItems = pages.map(p => `
    <a class="hamburger-nav-item${p.id === currentPage ? ' active' : ''}" href="${p.href}">
      <i class="fas ${p.icon}"></i>
      <span>${p.label}</span>
    </a>
  `).join('');

  const currentTheme = ThemeManager.get();
  const themeIcon = currentTheme === 'dark' ? 'fa-moon' : currentTheme === 'light' ? 'fa-sun' : 'fa-circle-half-stroke';
  const themeLabel = currentTheme.charAt(0).toUpperCase() + currentTheme.slice(1) + ' Mode';

  return `
    <div class="hamburger-overlay" id="hamburger-overlay">
      <div class="hamburger-menu">
        <div class="hamburger-menu-header">
          <img src="assets/tharimpepe-logo.png" alt="Tharimpepe" class="hamburger-menu-logo" onerror="this.style.display='none'">
          <div>
            <div class="hamburger-menu-title">Tharimpepe</div>
            <div class="hamburger-menu-subtitle">Feeding Scheme</div>
          </div>
          <button class="hamburger-menu-close" onclick="closeHamburger()" aria-label="Close menu">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <nav class="hamburger-nav" aria-label="Main navigation">
          ${navItems}

          <div style="border-top:1px solid rgba(255,255,255,0.08);margin:8px 16px;padding-top:8px;">
            <div style="font-size:11px;color:rgba(255,255,255,0.45);margin-bottom:8px;padding-left:2px;text-transform:uppercase;letter-spacing:0.04em;">Appearance</div>
            <button class="hamburger-nav-item" onclick="ThemeManager.toggle(); this.querySelector('i').className='fas ' + (ThemeManager.get() === 'dark' ? 'fa-moon' : ThemeManager.get() === 'light' ? 'fa-sun' : 'fa-circle-half-stroke'); this.querySelector('span').textContent = ThemeManager.get().charAt(0).toUpperCase() + ThemeManager.get().slice(1) + ' Mode';" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;">
              <i class="fas ${themeIcon}"></i>
              <span>${themeLabel}</span>
            </button>
          </div>
        </nav>
        <div class="hamburger-menu-footer">
          <div class="hamburger-user-info">
            <div class="hamburger-user-avatar">${initials}</div>
            <div>
              <div class="hamburger-user-name">${username}</div>
              <div class="hamburger-user-role">${getRoleDisplay(role)}</div>
              ${email ? `<div class="hamburger-user-email" style="font-size:11px;color:var(--mobile-muted);margin-top:2px;">${email}</div>` : ''}
            </div>
          </div>
          <button class="hamburger-logout-btn" onclick="logout()">
            <i class="fas fa-right-from-bracket"></i>
            <span>Sign Out</span>
          </button>
        </div>
      </div>
    </div>
  `;
}

/**
 * Build bottom tab navigation (primary mobile nav, aligned with website)
 */
function buildBottomNav(currentPage) {
  const user = getCurrentUser();
  const role = user ? user.role : 'guest';

  const bottomNavItems = [
    { id: 'dashboard', icon: 'fa-house', label: 'Dashboard', href: 'dashboard.html', roles: ['admin', 'volunteer', 'coordinator', 'staff'] },
    { id: 'beneficiaries', icon: 'fa-users', label: 'Beneficiaries', href: 'beneficiaries.html', roles: ['admin', 'coordinator', 'staff'] },
    { id: 'attendance', icon: 'fa-clipboard-check', label: 'Attendance', href: 'attendance.html', roles: ['admin', 'volunteer', 'coordinator', 'staff'] },
    { id: 'stock', icon: 'fa-boxes-stacked', label: 'Food Stock', href: 'stock.html', roles: ['admin', 'coordinator', 'staff'] }
  ];

  const items = bottomNavItems.filter(p => p.roles.includes(role));
  const navLinks = items.map(p => `
    <a class="mobile-nav-item${p.id === currentPage ? ' active' : ''}" href="${p.href}" aria-label="${p.label}"${p.id === currentPage ? ' aria-current="page"' : ''}>
      <i class="fas ${p.icon}" aria-hidden="true"></i>
      <span>${p.label}</span>
    </a>
  `).join('');

  return `<nav class="mobile-bottom-nav" role="navigation" aria-label="Main menu">${navLinks}</nav>`;
}

/**
 * Build header HTML (matches website topbar)
 */
function buildHeader(pageTitle) {
  return `
    <header class="mobile-header">
      <div class="header-left">
        <button class="hamburger-btn" onclick="openHamburger()" aria-label="Open menu">
          <i class="fas fa-bars"></i>
        </button>
        <img src="assets/tharimpepe-logo.png" alt="Tharimpepe" class="header-logo" onerror="this.style.display='none'">
        <div class="header-title-area">
          <div class="header-title">${pageTitle}</div>
          <div class="header-subtitle">Tharimpepe · Feeding Scheme</div>
        </div>
      </div>
    </header>
  `;
}

// ====== HAMBURGER CONTROLS ======

function openHamburger() {
  const overlay = document.getElementById('hamburger-overlay');
  if (overlay) {
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}

function closeHamburger() {
  const overlay = document.getElementById('hamburger-overlay');
  if (overlay) {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }
}

// ====== LOGOUT ======

/**
 * Logout - clear auth and redirect to login
 */
async function logout() {
  try {
    if (typeof API !== 'undefined' && API.isAuthenticated()) {
      await API.logout();
    }
  } catch {
    // Proceed to local cleanup below
  }

  // Clear localStorage (persistent storage)
  try {
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('token_expires_at');
    localStorage.removeItem('user');
    sessionStorage.removeItem('user');
  } catch {
    // no-op
  }

  window.location.href = './index.html';
}

// ====== API DATA LOADERS ======

/**
 * Load dashboard KPIs from API
 */
async function loadDashboardKPIs() {
  try {
    const data = await API.get('/api/dashboard/summary.php');
    if (data.success) {
      const kpis = data.data || data;
      // Update KPI values by data-key attribute
      Object.keys(kpis).forEach(key => {
        const el = document.querySelector(`[data-kpi="${key}"]`);
        if (el) {
          if (key.includes('_pct') || key.includes('_rate') || key.includes('_percentage')) {
            el.textContent = kpis[key] + '%';
          } else if (typeof kpis[key] === 'number') {
            el.textContent = kpis[key].toLocaleString();
          } else {
            el.textContent = kpis[key];
          }
        }
      });
    }
  } catch (err) {
    console.warn('Dashboard KPIs unavailable:', err.message);
  }
}

/**
 * Load beneficiaries list from API
 */
async function loadBeneficiaries() {
  const tbody = document.querySelector('#beneficiary-table tbody');
  if (!tbody) return;

  try {
    const data = await API.get('/api/beneficiaries/list.php');
    if (data.success && data.data) {
      tbody.innerHTML = data.data.map(b => `
        <tr>
          <td>${b.BeneficiaryID || b.id}</td>
          <td>${b.FullName || (b.FirstName + ' ' + b.LastName)}</td>
          <td>${b.Category || b.category}</td>
          <td><span class="badge ${(b.Status === 'Active' || b.status === 'active') ? 'badge-green' : (b.Status === 'Suspended' || b.status === 'suspended') ? 'badge-red' : 'badge-amber'}">${b.Status || b.status}</span></td>
        </tr>
      `).join('');
    }
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--mobile-muted);">Unable to load data</td></tr>';
    console.warn('Beneficiaries unavailable:', err.message);
  }
}

/**
 * Load attendance data from API
 */
async function loadAttendance() {
  const container = document.querySelector('#attendance-grid');
  if (!container) return;

  try {
    const data = await API.get('/api/attendance/today.php');
    if (data.success && data.data) {
      container.innerHTML = data.data.map(b => `
        <div class="beneficiary-tile ${b.status === 'Present' || b.status === 'present' ? 'present' : ''}"
             data-id="${b.BeneficiaryID || b.id}">
          <div class="tile-avatar" style="background:${getAvatarColor(b.FullName || b.name)};">${getInitials(b.FullName || b.name)}</div>
          <div class="tile-name">${b.FullName || b.name}</div>
          <div class="tile-status">${b.status === 'Present' || b.status === 'present' ? 'Present' : 'Tap to mark'}</div>
        </div>
      `).join('');

      // Add click handlers for tiles
      container.querySelectorAll('.beneficiary-tile').forEach(function(tile) {
        tile.addEventListener('click', function() {
          const isPresent = this.classList.contains('present');
          this.classList.toggle('present', !isPresent);
          this.classList.toggle('absent', isPresent);
          const st = this.querySelector('.tile-status');
          if (!isPresent) {
            st.textContent = 'Present';
            st.style.color = 'var(--success)';
          } else {
            st.textContent = 'Absent';
            st.style.color = 'var(--danger)';
          }
        });
      });
    }
  } catch (err) {
    container.innerHTML = '<div class="placeholder"><i class="fas fa-exclamation-circle"></i><p>Unable to load attendance data</p></div>';
  }
}

/**
 * Load stock items from API
 */
async function loadStock() {
  const container = document.querySelector('#stock-list');
  if (!container) return;

  try {
    const data = await API.get('/api/stock/list.php');
    if (data.success && data.data) {
      container.innerHTML = data.data.map(item => {
        const pct = item.StockLevel || item.stock_level || item.quantity_pct || 0;
        const barColor = pct > 50 ? 'var(--mobile-primary)' : pct > 25 ? 'var(--warning)' : '#dc2626';
        const textColor = pct > 50 ? 'var(--success)' : pct > 25 ? 'var(--warning)' : 'var(--danger)';
        return `
          <div class="stock-item">
            <div>
              <div class="stock-item-name">${item.ItemName || item.name || item.item_name}</div>
              <div class="stock-item-qty">${item.Quantity || item.quantity || '0'} ${item.Unit || item.unit || ''} remaining</div>
            </div>
            <div class="stock-item-pct" style="color:${textColor};">${pct}%</div>
          </div>
          <div class="stock-bar"><div class="stock-fill" style="width:${pct}%;background:${barColor};"></div></div>
        `;
      }).join('');
    }
  } catch (err) {
    container.innerHTML = '<div class="placeholder"><i class="fas fa-exclamation-circle"></i><p>Unable to load stock data</p></div>';
  }
}

/**
 * Get deterministic avatar color from name
 */
function getAvatarColor(name) {
  const colors = ['#1D9E75', '#2563ff', '#ad3df5', '#ff5b00', '#06b6d4', '#f59e0b', '#ef4444', '#8b5cf6'];
  let hash = 0;
  for (let i = 0; i < (name || '').length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length];
}

// ====== PAGE INITIALIZATION ======

/**
 * Initialize page: check auth, inject header + hamburger, update user badge
 * @param {string} pageTitle - Title to display in header
 * @param {string} currentPage - Current page identifier for nav highlighting
 * @param {object} options - Optional configuration
 * @param {boolean} options.loadData - Whether to load API data automatically
 * @param {string} options.dataType - Type of data to load ('kpi', 'beneficiaries', 'attendance', 'stock')
 */
function initPage(pageTitle, currentPage, options = {}) {
  // Check authentication
  const user = getCurrentUser();
  if (!user || (!user.username && !user.user_id)) {
    // Clean any stale token remnants before redirecting back to login
    try {
      if (typeof API !== 'undefined') {
        API.clearAuth();
      } else {
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('token_expires_at');
        localStorage.removeItem('user');
        sessionStorage.removeItem('user');
      }
    } catch {
      // no-op
    }
    window.location.href = './index.html';
    return;
  }

  // Inject Theme Manager
  ThemeManager.init();

  // Inject notification panel
  if (typeof Notifications !== 'undefined') {
    Notifications.injectPanel();
    Notifications.injectBellButton();
    Notifications.init();
  }

  // Inject hamburger overlay
  const hamburgerHTML = buildHamburgerMenu(currentPage);
  const hamburgerContainer = document.createElement('div');
  hamburgerContainer.innerHTML = hamburgerHTML;
  document.body.prepend(hamburgerContainer.firstElementChild);

  // Inject header
  const headerHTML = buildHeader(pageTitle);
  const headerContainer = document.createElement('div');
  headerContainer.innerHTML = headerHTML;
  const mobileSafe = document.querySelector('.mobile-safe');
  if (mobileSafe) {
    mobileSafe.prepend(headerContainer.firstElementChild);

    // Inject unified bottom navigation (replaces per-page static nav)
    const existingNav = mobileSafe.querySelector('.mobile-bottom-nav');
    if (existingNav) existingNav.remove();
    const navContainer = document.createElement('div');
    navContainer.innerHTML = buildBottomNav(currentPage);
    mobileSafe.appendChild(navContainer.firstElementChild);
  }

  // Close hamburger on overlay click
  document.getElementById('hamburger-overlay').addEventListener('click', function(e) {
    if (e.target === this) {
      closeHamburger();
    }
  });

  // Close hamburger on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeHamburger();
    }
  });

  // Prevent the WebView from navigating back to blank pages via browser history.
  // We maintain a single-page-like experience inside the WebView.
  try {
    history.pushState(null, '', location.href);
    window.addEventListener('popstate', function() {
      if (!document.querySelector('.hamburger-overlay.open')) {
        history.pushState(null, '', location.href);
      }
    });
  } catch {
    // no-op on environments where History API is restricted
  }

  // Auto-load data if requested
  if (options.loadData) {
    switch (options.dataType) {
      case 'kpi':
        loadDashboardKPIs();
        break;
      case 'beneficiaries':
        loadBeneficiaries();
        break;
      case 'attendance':
        loadAttendance();
        break;
      case 'stock':
        loadStock();
        break;
    }
  }

  // Token validation on page load (silent refresh if needed)
  if (typeof API !== 'undefined' && API.isAuthenticated()) {
    API.validateToken().catch(() => {
      // Token invalid - silent fail, will redirect on next page action
    });
  }
}

// ====== TOAST ======

function showToast(message, type = 'info', duration = 3500) {
  const container = document.querySelector('.toast-container');
  if (!container) return;

  const iconMap = {
    success: 'fa-check-circle',
    error: 'fa-circle-exclamation',
    warning: 'fa-triangle-exclamation',
    info: 'fa-circle-info'
  };

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <span class="toast-icon"><i class="fas ${iconMap[type] || iconMap.info}"></i></span>
    <span class="toast-message">${message}</span>
  `;

  container.appendChild(toast);

  const remove = () => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    toast.style.transition = 'opacity 0.25s, transform 0.25s';
    setTimeout(() => {
      if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 250);
  };

  const timer = setTimeout(remove, duration);
  toast.addEventListener('click', () => {
    clearTimeout(timer);
    remove();
  });

  return remove;
}

// ====== CONFIRM DIALOG ======

function showConfirmDialog({ title = 'Are you sure?', message = '', confirmText = 'Yes', cancelText = 'Cancel', type = 'warning', onConfirm, onCancel } = {}) {
  const iconClass = type === 'danger' ? 'fa-trash-can' : type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-question';
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.innerHTML = `
    <div class="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="confirm-dialog-title">
      <div class="confirm-icon ${type}"><i class="fas ${iconClass}"></i></div>
      <div class="confirm-title" id="confirm-dialog-title">${title}</div>
      <div class="confirm-message">${message}</div>
      <div class="confirm-actions">
        <button class="btn btn-secondary" id="confirm-cancel">${cancelText}</button>
        <button class="btn btn-primary" id="confirm-ok">${confirmText}</button>
      </div>
    </div>
  `;

  document.body.appendChild(overlay);
  requestAnimationFrame(() => overlay.classList.add('open'));

  const close = (result) => {
    overlay.classList.remove('open');
    setTimeout(() => {
      if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
    }, 250);
    if (result) onConfirm && onConfirm();
    else onCancel && onCancel();
  };

  overlay.querySelector('#confirm-ok').addEventListener('click', () => close(true));
  overlay.querySelector('#confirm-cancel').addEventListener('click', () => close(false));
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) close(false);
  });

  const esc = (e) => {
    if (e.key === 'Escape') {
      document.removeEventListener('keydown', esc);
      close(false);
    }
  };
  document.addEventListener('keydown', esc);
}

// ====== LOADING & SKELETONS ======

function createSkeletonKPIs() {
  return `
    <div class="kpi-grid">
      <div class="skeleton-card skeleton"></div>
      <div class="skeleton-card skeleton"></div>
      <div class="skeleton-card skeleton"></div>
      <div class="skeleton-card skeleton"></div>
    </div>
  `;
}

function createSkeletonCard() {
  return '<div class="skeleton skeleton-card" style="margin-bottom:var(--space-md);"></div>';
}

function createSkeletonList(count = 4) {
  return `
    <div class="skeleton-list">
      ${Array.from({ length: count }, () => `
        <div class="activity-item" style="border-bottom:1px solid var(--mobile-border);padding:12px 0;">
          <div class="skeleton skeleton-avatar-sm"></div>
          <div style="flex:1;">
            <div class="skeleton skeleton-line" style="width:70%;height:16px;"></div>
            <div class="skeleton skeleton-line" style="width:40%;height:12px;"></div>
          </div>
        </div>
      `).join('')}
    </div>
  `;
}

function setLoading(containerId, type = 'list') {
  const el = document.getElementById(containerId);
  if (!el) return;

  if (type === 'kpi') {
    el.innerHTML = createSkeletonKPIs();
  } else if (type === 'card') {
    el.innerHTML = createSkeletonCard();
  } else {
    el.innerHTML = createSkeletonList();
  }
}

function setEmpty(containerId, icon = 'fa-box-open', title = 'No information found', message = 'Try refreshing or checking back later.', action = null) {
  const el = document.getElementById(containerId);
  if (!el) return;

  let actionHTML = '';
  if (action) {
    actionHTML = `<button class="btn btn-primary btn-sm mt-md" onclick="${action.onclick}">${action.label}</button>`;
  }

  el.innerHTML = `
    <div class="empty-state">
      <div class="state-icon-wrapper" style="margin-bottom:20px;">
        <i class="fas ${icon}" style="font-size:64px;color:var(--mobile-primary);opacity:0.3;"></i>
      </div>
      <h3 class="state-title" style="font-size:18px;margin-bottom:10px;">${title}</h3>
      <p class="state-message" style="max-width:240px;margin:0 auto 16px;">${message}</p>
      ${actionHTML}
    </div>
  `;
}

function setError(containerId, title = 'Something went wrong', message = 'We were unable to load the data. Please check your connection.') {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.innerHTML = `
    <div class="error-state">
      <i class="fas fa-triangle-exclamation state-icon" style="color:var(--warning);"></i>
      <h3 class="state-title">${title}</h3>
      <p class="state-message">${message}</p>
      <button class="btn btn-secondary btn-sm" onclick="location.reload()"><i class="fas fa-rotate"></i> Retry</button>
    </div>
  `;
}

// ====== COMPONENT HELPERS ======

function renderRoleBadge(role) {
  if (!role) return '';
  return `<span class="role-badge ${role}">${getRoleDisplay(role)}</span>`;
}

function renderUserProfileHeader(user) {
  if (!user) return '';
  const name = user.username || user.fullname || user.name || 'User';
  const email = user.email || '';
  return `
    <div class="user-profile-header">
      <div class="user-profile-avatar">${getInitials(name)}</div>
      <div class="user-profile-info">
        <div class="user-profile-name">${name}</div>
        <div class="user-profile-email">${email}</div>
      </div>
      ${renderRoleBadge((user.role || '').toLowerCase())}
    </div>
  `;
}

function renderBeneficiaryCard(b, onClickHandler = 'showBeneficiaryDetail') {
  const id = b.BeneficiaryID || b.id;
  const name = b.FullName || ((b.FirstName || '') + ' ' + (b.LastName || '')).trim() || 'Unknown';
  const status = b.Status || b.status || 'Unknown';
  const statusClass = (status === 'Active' || status === 'active') ? 'badge-green'
    : (status === 'Suspended' || status === 'suspended') ? 'badge-red' : 'badge-amber';
  return `
    <article class="mobile-data-card" onclick="${onClickHandler}(${id})" role="button" tabindex="0" aria-label="View ${name}">
      <div class="mobile-data-card-header">
        <div class="mobile-data-card-title">${name}</div>
        <span class="badge ${statusClass}">${status}</span>
      </div>
      <div class="mobile-data-card-meta">
        <span>ID: ${id}</span>
        <span>${b.Category || b.category || '—'}</span>
      </div>
    </article>
  `;
}

function renderBeneficiaryCardList(list, container, onClickHandler = 'showBeneficiaryDetail') {
  if (!container) return;
  if (!list || list.length === 0) {
    container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><h3>No beneficiaries found</h3><p>Try adjusting your search or filters.</p></div>';
    return;
  }
  container.innerHTML = `<div class="mobile-data-list">${list.map(b => renderBeneficiaryCard(b, onClickHandler)).join('')}</div>`;
}

function renderActivityItem(item) {
  const iconMap = {
    attendance: 'fa-clipboard-check',
    beneficiary: 'fa-user-plus',
    donation: 'fa-hand-holding-heart',
    stock: 'fa-boxes-stacked',
    volunteer: 'fa-handshake-angle'
  };
  const icon = (item.icon && /^fa-/.test(item.icon)) ? item.icon : (iconMap[item.type] || 'fa-circle-info');
  return `
    <div class="activity-item">
      <div class="activity-icon ${item.type || ''}"><i class="fas ${icon}"></i></div>
      <div class="activity-text">${item.message || item.description || item.title || 'Activity'}</div>
      <div class="activity-time">${formatTimeAgo(item.timestamp || item.created_at)}</div>
    </div>
  `;
}

function renderAlertItem(item) {
  const severity = item.severity || item.type || 'info';
  const typeClass = severity === 'critical' ? 'alert-danger' : severity === 'warning' ? 'alert-warning' : severity === 'success' ? 'alert-success' : 'alert-info';
  const icon = severity === 'critical' ? 'fa-circle-exclamation' : severity === 'warning' ? 'fa-triangle-exclamation' : severity === 'success' ? 'fa-circle-check' : 'fa-circle-info';
  const displayIcon = (item.icon && /^fa-/.test(item.icon)) ? item.icon : icon;
  const actionBtn = item.action_url ? `<a class="btn btn-xs btn-secondary" href="${item.action_url}" style="margin-left:8px;">${item.action_label || 'View'}</a>` : '';
  return `
    <div class="alert-item ${typeClass}">
      <i class="fas ${displayIcon}"></i>
      <span class="alert-item-text">
        <strong>${item.title || 'Alert'}</strong>
        ${actionBtn}
      </span>
      <span class="alert-item-time">${formatTimeAgo(item.created_at || item.timestamp || item.time)}</span>
    </div>
  `;
}

// ====== OFFLINE QUEUE HELPERS ======

function enqueueOfflineRequest(request) {
  if (typeof OfflineQueue !== 'undefined') {
    return OfflineQueue.enqueue(request);
  }
  return null;
}

async function processOfflineQueue() {
  if (typeof OfflineQueue === 'undefined') return { processed: 0, failed: 0 };
  const result = await OfflineQueue.process();
  if (typeof showToast === 'function') {
    if (result.processed > 0) showToast(`${result.processed} offline change(s) synced`, 'success');
    if (result.failed > 0) showToast(`${result.failed} change(s) could not be synced yet`, 'warning');
  }
  return result;
}

function getOfflineQueueStatus() {
  if (typeof OfflineQueue === 'undefined') return { count: 0, failed: 0, lastSync: null };
  return OfflineQueue.getMeta();
}

// ====== UTILITIES ======

function formatTimeAgo(timestamp) {
  if (!timestamp) return '';
  try {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMins = Math.floor((now - date) / 60000);
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h ago`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `${diffDays}d ago`;
    return date.toLocaleDateString('en-ZA', { month: 'short', day: 'numeric' });
  } catch {
    return '';
  }
}

// ====== EXPORTS ======

if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    getCurrentUser,
    getInitials,
    getRoleDisplay,
    hasRole,
    hasAnyRole,
    logout,
    initPage,
    loadDashboardKPIs,
    loadBeneficiaries,
    loadAttendance,
    loadStock,
    getAvatarColor,
    buildHamburgerMenu,
    buildBottomNav,
    buildHeader,
    openHamburger,
    closeHamburger,
    showToast,
    showConfirmDialog,
    createSkeletonCard,
    createSkeletonList,
    setLoading,
    setEmpty,
    setError,
    renderRoleBadge,
    renderUserProfileHeader,
    renderBeneficiaryCard,
    renderBeneficiaryCardList,
    renderActivityItem,
    renderAlertItem,
    formatTimeAgo
  };
}
