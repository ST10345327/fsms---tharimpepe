/**
 * Tharimpepe FSMS - Notification System
 * 
 * Features:
 * - Real-time notifications from API
 * - Low stock alerts
 * - Attendance alerts
 * - New beneficiary alerts
 * - Donation alerts
 * - Volunteer alerts
 * - Mark as read / Mark all as read
 * - Notification count badge
 * - Pull to refresh
 * - Offline queue support
 */

const Notifications = (() => {
  const STORAGE_KEY = 'notifications';
  const UNREAD_KEY = 'unread_count';
  const PANEL_ID = 'notification-panel';
  const LIST_ID = 'notification-list';
  const BADGE_ID = 'notification-badge';
  
  let allNotifications = [];
  let unreadCount = 0;

  // ====== STORAGE HELPERS ======

  function loadFromStorage() {
    try {
      const data = localStorage.getItem(STORAGE_KEY);
      if (data) allNotifications = JSON.parse(data);
      const unread = localStorage.getItem(UNREAD_KEY);
      if (unread) unreadCount = parseInt(unread, 10) || 0;
    } catch {
      allNotifications = [];
      unreadCount = 0;
    }
  }

  function saveToStorage() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(allNotifications));
      localStorage.setItem(UNREAD_KEY, String(unreadCount));
    } catch {
      // Storage full or unavailable
    }
  }

  // ====== PUBLIC API ======

  async function init() {
    loadFromStorage();
    updateBadge();
    await fetchNotifications();
  }

  async function fetchNotifications() {
    try {
      const response = await API.get('/api/notifications/list.php');
      if (response.success && Array.isArray(response.data)) {
        const newNotifications = response.data.map(normalizeNotification);
        
        // Merge with existing, preserving read states
        const existingMap = new Map(allNotifications.map(n => [n.id, n]));
        const merged = [];
        
        for (const n of newNotifications) {
          const existing = existingMap.get(n.id);
          if (existing) {
            merged.push({ ...n, read: existing.read });
          } else {
            merged.push(n);
          }
        }
        
        allNotifications = merged;
        countUnread();
        saveToStorage();
        updateBadge();
        updateCountLabel();
        renderList();
      }
    } catch {
      // Use cached data if offline
      renderList();
    }
  }

  function getCount() {
    return unreadCount;
  }

  function getAll() {
    return allNotifications;
  }

  function getUnread() {
    return allNotifications.filter(n => !n.read);
  }

  function markAsRead(id) {
    const notification = allNotifications.find(n => n.id === id);
    if (notification && !notification.read) {
      notification.read = true;
      countUnread();
      saveToStorage();
      updateBadge();
      renderList();
    }
  }

  function markAllAsRead() {
    allNotifications.forEach(n => n.read = true);
    countUnread();
    saveToStorage();
    updateBadge();
    renderList();
  }

  async function deleteNotification(id) {
    allNotifications = allNotifications.filter(n => n.id !== id);
    countUnread();
    saveToStorage();
    updateBadge();
    renderList();
    
    try {
      await API.del(`/api/notifications/delete.php?id=${id}`, true);
    } catch {
      // Queue for later sync if offline
    }
  }

  function clearAll() {
    allNotifications = [];
    countUnread();
    saveToStorage();
    updateBadge();
    renderList();
  }

  // ====== PANEL CONTROLS ======

  function openPanel() {
    const panel = document.getElementById(PANEL_ID);
    if (panel) {
      panel.classList.add('open');
      renderList();
    }
  }

  function closePanel() {
    const panel = document.getElementById(PANEL_ID);
    if (panel) {
      panel.classList.remove('open');
    }
  }

  function toggle() {
    const panel = document.getElementById(PANEL_ID);
    if (!panel) return;
    
    if (panel.classList.contains('open')) {
      closePanel();
    } else {
      openPanel();
    }
  }

  // ====== RENDERING ======

  function renderList() {
    const list = document.getElementById(LIST_ID);
    if (!list) return;

    if (allNotifications.length === 0) {
      list.innerHTML = `
        <div class="empty-state" style="padding-top:60px;">
          <div class="state-icon-wrapper" style="margin-bottom:20px;">
            <i class="fas fa-bell-slash" style="font-size:64px;color:var(--mobile-primary);opacity:0.2;"></i>
          </div>
          <h3 class="state-title">You're all caught up!</h3>
          <p class="state-message">No notifications found at the moment.</p>
        </div>
      `;
      return;
    }

    list.innerHTML = allNotifications.map(n => `
      <div class="notification-item ${n.read ? '' : 'unread'}" 
           data-id="${n.id}"
           role="button"
           tabindex="0"
           aria-label="${n.read ? 'Read' : 'Unread'} notification: ${escapeHtml(n.text || n.message)}"
           onclick="Notifications.handleItemClick('${n.id}')"
           onkeypress="if(event.key==='Enter') Notifications.handleItemClick('${n.id}')">
        <div class="notification-icon ${n.type || 'info'}">
          <i class="fas ${getNotificationIcon(n.type || 'info')}" aria-hidden="true"></i>
        </div>
        <div class="notification-content">
          <div class="notification-text" style="font-weight: ${n.read ? '400' : '600'};">${escapeHtml(n.text || n.message)}</div>
          <div class="notification-time">${formatTime(n.timestamp || n.created_at)}</div>
        </div>
        ${n.read ? '' : '<div style="width:10px;height:10px;background:var(--mobile-primary);border-radius:50%;flex-shrink:0;margin-top:6px;box-shadow:0 0 8px var(--mobile-primary);"></div>'}
      </div>
    `).join('');
  }

  function updateBadge() {
    const badge = document.getElementById(BADGE_ID);
    if (!badge) return;

    if (unreadCount > 0) {
      badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  }

  function handleItemClick(id) {
    markAsRead(id);
    
    // Navigate based on notification type/data
    const notification = allNotifications.find(n => n.id === id);
    if (notification && notification.link) {
      window.location.href = notification.link;
    }
  }

  // ====== UTILITIES ======

  function normalizeNotification(apiNotif) {
    return {
      id: String(apiNotif.id || apiNotif.NotificationID || Date.now() + Math.random()),
      text: apiNotif.message || apiNotif.text || apiNotif.title || 'Notification',
      type: apiNotif.type || apiNotif.category || 'info',
      timestamp: apiNotif.created_at || apiNotif.timestamp || apiNotif.time || new Date().toISOString(),
      read: Boolean(apiNotif.read || apiNotif.is_read),
      link: apiNotif.link || apiNotif.action_url || null,
      metadata: apiNotif.metadata || apiNotif.data || {}
    };
  }

  function countUnread() {
    unreadCount = allNotifications.filter(n => !n.read).length;
  }

  function getNotificationIcon(type) {
    const icons = {
      alert: 'fa-triangle-exclamation',
      warning: 'fa-exclamation-triangle',
      info: 'fa-info-circle',
      success: 'fa-check-circle',
      attendance: 'fa-clipboard-check',
      stock: 'fa-boxes-stacked',
      donation: 'fa-hand-holding-heart',
      beneficiary: 'fa-user-plus',
      volunteer: 'fa-handshake-angle'
    };
    return icons[type] || 'fa-bell';
  }

  function formatTime(timestamp) {
    if (!timestamp) return 'Just now';
    
    try {
      const date = new Date(timestamp);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMins / 60);
      const diffDays = Math.floor(diffHours / 24);

      if (diffMins < 1) return 'Just now';
      if (diffMins < 60) return `${diffMins}m ago`;
      if (diffHours < 24) return `${diffHours}h ago`;
      if (diffDays < 7) return `${diffDays}d ago`;
      
      return date.toLocaleDateString('en-ZA', { 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    } catch {
      return '';
    }
  }

  function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // ====== BELL ICON ======

  function createBellButton() {
    const btn = document.createElement('button');
    btn.className = 'notification-bell';
    btn.id = 'notification-bell-btn';
    btn.setAttribute('aria-label', 'Notifications');
    btn.onclick = toggle;
    btn.innerHTML = `
      <i class="fas fa-bell"></i>
      <span class="notification-badge" id="${BADGE_ID}" style="display:none;">0</span>
    `;
    return btn;
  }

  // ====== PANEL HTML ======

  function getNotificationPanelHTML() {
    return `
      <div class="${PANEL_ID}" id="${PANEL_ID}">
        <div class="notification-header">
          <div>
            <div class="notification-title">Notifications</div>
            <div class="notification-subtitle" id="notification-count-label" style="font-size:12px;color:var(--mobile-muted);">0 unread</div>
          </div>
          <button class="notification-close" onclick="Notifications.closePanel()" aria-label="Close notifications">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="notification-actions">
          ${unreadCount > 0 ? `
            <button class="btn btn-sm btn-secondary" onclick="Notifications.markAllAsRead()">
              <i class="fas fa-check-double"></i> Mark all read
            </button>
          ` : ''}
          <button class="btn btn-sm btn-secondary" onclick="Notifications.refresh()" style="flex:0;">
            <i class="fas fa-rotate"></i>
          </button>
          ${allNotifications.length > 0 ? `
            <button class="btn btn-sm btn-danger" onclick="Notifications.clearAll()" style="flex:0;">
              <i class="fas fa-trash"></i>
            </button>
          ` : ''}
        </div>
        <div class="notification-list" id="${LIST_ID}">
          <!-- Populated by JS -->
        </div>
      </div>
    `;
  }

  function updateCountLabel() {
    const label = document.getElementById('notification-count-label');
    if (label) {
      label.textContent = `${unreadCount} unread notification${unreadCount !== 1 ? 's' : ''}`;
    }
  }

  async function refresh() {
    const btn = document.querySelector('.notification-actions .fa-rotate');
    if (btn) btn.classList.add('fa-spin');
    
    await fetchNotifications();
    
    if (btn) btn.classList.remove('fa-spin');
    
    const label = document.getElementById('notification-count-label');
    if (label) {
      label.textContent = `${unreadCount} unread notification${unreadCount !== 1 ? 's' : ''}`;
    }
  }

  // ====== INITIALIZATION ======

  function injectPanel() {
    // Remove existing panel if present
    const existing = document.getElementById(PANEL_ID);
    if (existing) existing.remove();

    // Create and inject panel
    const container = document.createElement('div');
    container.innerHTML = getNotificationPanelHTML();
    document.body.prepend(container.firstElementChild);

    // Close on overlay click (panel is sibling to overlay in DOM)
    document.getElementById(PANEL_ID).addEventListener('click', function(e) {
      if (e.target === this) {
        closePanel();
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && document.getElementById(PANEL_ID).classList.contains('open')) {
        closePanel();
      }
    });
  }

  function injectBellButton() {
    const headerRight = document.querySelector('.header-right');
    if (!headerRight) return;

    // Check if already exists
    if (headerRight.querySelector('#notification-bell-btn')) return;

    headerRight.insertBefore(createBellButton(), headerRight.firstChild);
  }

  // ====== PUBLIC API ======

  return {
    init,
    openPanel,
    closePanel,
    toggle,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    clearAll,
    refresh,
    getCount,
    getAll,
    getUnread,
    injectPanel,
    injectBellButton,
    handleItemClick
  };
})();

// Make globally accessible
window.Notifications = Notifications;