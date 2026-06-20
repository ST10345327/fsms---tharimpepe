/**
 * Shared JavaScript for Tharimpepe FSMS Mobile App
 * 
 * Provides:
 * - Unified header/hamburger UI
 * - Auth state management via localStorage (Persistent across app restarts)
 * - Session restoration via token validation
 * - Role-based UI rendering
 * - API-powered data loading
 */

// ====== AUTH HELPERS ======

/**
 * Get current user from localStorage (persistent storage)
 * Falls back to sessionStorage for backward compatibility
 */
function getCurrentUser() {
  // Try localStorage first (persistent across app restarts)
  const data = localStorage.getItem('user');
  if (data) return JSON.parse(data);
  
  // Fallback to sessionStorage for backward compatibility
  const sessionData = sessionStorage.getItem('user');
  if (sessionData) {
    const user = JSON.parse(sessionData);
    // Migrate to localStorage
    localStorage.setItem('user', JSON.stringify(user));
    sessionStorage.removeItem('user');
    return user;
  }
  return null;
}

/**
 * Get initials from name/username
 */
function getInitials(name) {
  if (!name) return '?';
  return name.charAt(0).toUpperCase();
}

/**
 * Get role display name
 */
function getRoleDisplay(role) {
  const roles = {
    admin: 'Administrator',
    volunteer: 'Volunteer',
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

// ====== UI BUILDERS ======

/**
 * Build hamburger menu HTML
 */
function buildHamburgerMenu(currentPage) {
  const user = getCurrentUser();
  const username = user ? (user.username || user.fullname || 'User') : 'User';
  const role = user ? user.role : 'guest';
  const initials = getInitials(username);

  const pages = [
    { id: 'dashboard', icon: 'fa-house', label: 'Dashboard', href: 'dashboard.html' },
    { id: 'beneficiaries', icon: 'fa-users', label: 'Beneficiaries', href: 'beneficiaries.html' },
    { id: 'attendance', icon: 'fa-clipboard-check', label: 'Attendance', href: 'attendance.html' },
    { id: 'stock', icon: 'fa-boxes-stacked', label: 'Stock & Donations', href: 'stock.html' },
    { id: 'volunteers', icon: 'fa-handshake-angle', label: 'Volunteers', href: 'volunteers.html' },
    { id: 'reports', icon: 'fa-file-lines', label: 'Reports', href: 'reports.html' }
  ];

  const navItems = pages.map(p => `
    <a class="hamburger-nav-item${p.id === currentPage ? ' active' : ''}" href="${p.href}">
      <i class="fas ${p.icon}"></i>
      <span>${p.label}</span>
    </a>
  `).join('');

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
        <nav class="hamburger-nav">
          ${navItems}
        </nav>
        <div class="hamburger-menu-footer">
          <div class="hamburger-user-info">
            <div class="hamburger-user-avatar">${initials}</div>
            <div>
              <div class="hamburger-user-name">${username}</div>
              <div class="hamburger-user-role">${getRoleDisplay(role)}</div>
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
 * Build header HTML
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
          <div class="header-subtitle">Tharimpepe FSMS</div>
        </div>
      </div>
      <div class="header-right">
        <span class="user-info-badge" id="user-info-badge"></span>
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
  // Best-effort server-side token revocation.
  // We ignore failures because the local session MUST be cleared regardless.
  try {
    if (typeof API !== 'undefined' && API.isAuthenticated()) {
      await API.logout();
      return;
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
          if (key.includes('_pct') || key.includes('_rate')) {
            el.textContent = kpis[key] + '%';
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
          <td><span class="badge ${(b.Status === 'Active' || b.status === 'active') ? 'badge-green' : 'badge-amber'}">${b.Status || b.status}</span></td>
        </tr>
      `).join('');
    }
  } catch (err) {
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
             data-id="${b.BeneficiaryID || b.id}" 
             onclick="toggleAttendance(this, ${b.BeneficiaryID || b.id})">
          <div class="tile-avatar" style="background:${getAvatarColor(b.FullName || b.name)};">${getInitials(b.FullName || b.name)}</div>
          <div class="tile-name">${b.FullName || b.name}</div>
          <div class="tile-status">${b.status || 'Pending'}</div>
        </div>
      `).join('');
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
        const barColor = pct > 50 ? '#1D9E75' : pct > 25 ? '#F5B041' : '#dc2626';
        const textColor = pct > 50 ? '#5DCAA5' : pct > 25 ? '#F5B041' : '#fca5a5';
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
  }

  // Update user badge
  const badge = document.getElementById('user-info-badge');
  if (badge && user) {
    badge.textContent = (user.username || user.fullname || 'User') + ' (' + (user.role || 'User') + ')';
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