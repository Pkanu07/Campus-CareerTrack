/**
 * Campus CareerTrack - Notifications Module
 * Handles categorized notifications, mark-as-read, delete, and badge sync.
 */

const NotificationsController = {
  currentTab: 'all',

  init() {
    this.render();
    this.setupListeners();
  },

  setupListeners() {
    const tabBtns = document.querySelectorAll('.notif-filter-tab');
    tabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        tabBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        this.currentTab = btn.getAttribute('data-tab');
        this.renderList();
      });
    });

    const markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) {
      markAllBtn.addEventListener('click', () => {
        Storage.markAllNotificationsRead();
        App.showToast("All notifications marked as read.", "success");
        this.render();
        App.updateNotificationBadge();
      });
    }

    const clearAllBtn = document.getElementById('clearAllNotifsBtn');
    if (clearAllBtn) {
      clearAllBtn.addEventListener('click', () => {
        App.showModal({
          title: "Clear All Notifications?",
          message: "Are you sure you want to delete all notifications from your notification log? This action cannot be undone.",
          confirmText: "Clear All",
          variant: "danger",
          onConfirm: () => {
            Storage.clearAllNotifications();
            App.showToast("Notification log cleared.", "info");
            this.render();
            App.updateNotificationBadge();
          }
        });
      });
    }
  },

  render() {
    this.updateCounters();
    this.renderList();
  },

  updateCounters() {
    const all = Storage.getNotifications();
    const unread = all.filter(n => !n.read).length;

    const countAllEl = document.getElementById('notifCountAll');
    const countUnreadEl = document.getElementById('notifCountUnread');

    if (countAllEl) countAllEl.textContent = all.length;
    if (countUnreadEl) countUnreadEl.textContent = unread;
  },

  renderList() {
    const container = document.getElementById('notificationsListContainer');
    const emptyState = document.getElementById('notificationsEmptyState');
    if (!container) return;

    let list = Storage.getNotifications();
    if (this.currentTab === 'unread') {
      list = list.filter(n => !n.read);
    }

    if (list.length === 0) {
      container.innerHTML = '';
      if (emptyState) emptyState.style.display = 'block';
      return;
    }

    if (emptyState) emptyState.style.display = 'none';

    container.innerHTML = list.map(item => {
      let iconClass = 'fa-bell';
      let typeClass = 'notif-system';

      if (item.type === 'interview') {
        iconClass = 'fa-calendar-check';
        typeClass = 'notif-interview';
      } else if (item.type === 'status') {
        iconClass = 'fa-paper-plane';
        typeClass = 'notif-status';
      } else if (item.type === 'selection') {
        iconClass = 'fa-award';
        typeClass = 'notif-selection';
      } else if (item.type === 'deadline') {
        iconClass = 'fa-clock';
        typeClass = 'notif-deadline';
      }

      const formattedTime = new Date(item.timestamp).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });

      return `
        <div class="notification-card ${item.read ? '' : 'unread'}" data-id="${item.id}">
          <div class="notification-icon-wrap ${typeClass}">
            <i class="fa-solid ${iconClass}"></i>
          </div>
          <div class="notification-body-content">
            <div class="notification-title-row">
              <strong>${App.escapeHTML(item.title)}</strong>
              <span class="notification-time">${formattedTime}</span>
            </div>
            <p class="notification-message">${App.escapeHTML(item.message)}</p>
            <div class="notification-card-actions">
              ${item.link ? `
                <a href="${item.link}" class="btn btn-sm btn-outline-primary">
                  View Relevant Page <i class="fa-solid fa-arrow-right"></i>
                </a>
              ` : ''}
              ${!item.read ? `
                <button type="button" class="btn btn-sm btn-secondary mark-single-read-btn" data-id="${item.id}">
                  <i class="fa-solid fa-check"></i> Mark as Read
                </button>
              ` : ''}
              <button type="button" class="btn btn-sm btn-outline-secondary delete-single-notif-btn" data-id="${item.id}" title="Delete notification">
                <i class="fa-solid fa-trash-can text-danger"></i>
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');

    this.bindItemActions();
  },

  bindItemActions() {
    // Mark as read buttons
    const markBtns = document.querySelectorAll('.mark-single-read-btn');
    markBtns.forEach(btn => {
      btn.onclick = () => {
        const id = btn.getAttribute('data-id');
        Storage.markNotificationRead(id);
        this.render();
        App.updateNotificationBadge();
        App.showToast("Notification marked as read.", "info");
      };
    });

    // Delete buttons
    const delBtns = document.querySelectorAll('.delete-single-notif-btn');
    delBtns.forEach(btn => {
      btn.onclick = () => {
        const id = btn.getAttribute('data-id');
        Storage.clearNotification(id);
        this.render();
        App.updateNotificationBadge();
      };
    });
  }
};
