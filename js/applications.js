/**
 * Campus CareerTrack - Applications Tracker & Demo Controller
 * Manages submitted applications, status filters, application withdrawal modal,
 * and the Review 01 Demo State Switcher for evaluator demonstrations.
 */

const ApplicationsController = {
  activeStatusFilter: 'all',
  searchQuery: '',

  init() {
    this.render();
    this.setupListeners();
    this.populateDemoManager();
  },

  setupListeners() {
    // Status Filter Tabs
    const tabs = document.querySelectorAll('.status-tab-btn');
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        this.activeStatusFilter = tab.getAttribute('data-status');
        this.renderTable();
      });
    });

    // Search query
    const searchInput = document.getElementById('applicationSearchInput');
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        this.searchQuery = e.target.value.toLowerCase().trim();
        this.renderTable();
      });
    }

    // Demo status switcher form
    const demoForm = document.getElementById('demoStatusSwitcherForm');
    if (demoForm) {
      demoForm.addEventListener('submit', (e) => {
        e.preventDefault();
        this.handleDemoStatusUpdate();
      });
    }
  },

  getFilteredApplications() {
    let list = Storage.getApplications();

    if (this.activeStatusFilter !== 'all') {
      list = list.filter(app => app.status === this.activeStatusFilter);
    }

    if (this.searchQuery) {
      list = list.filter(app =>
        app.company.toLowerCase().includes(this.searchQuery) ||
        app.role.toLowerCase().includes(this.searchQuery) ||
        app.status.toLowerCase().includes(this.searchQuery)
      );
    }

    return list;
  },

  updateTabCounts() {
    const all = Storage.getApplications();
    const countMap = {
      all: all.length,
      'Applied': all.filter(a => a.status === 'Applied').length,
      'Shortlisted': all.filter(a => a.status === 'Shortlisted').length,
      'Interview Scheduled': all.filter(a => a.status === 'Interview Scheduled').length,
      'Selected': all.filter(a => a.status === 'Selected').length,
      'Rejected': all.filter(a => a.status === 'Rejected').length
    };

    const countEls = document.querySelectorAll('[data-tab-count]');
    countEls.forEach(el => {
      const statusKey = el.getAttribute('data-tab-count');
      el.textContent = countMap[statusKey] ?? 0;
    });
  },

  render() {
    this.updateTabCounts();
    this.renderTable();
  },

  renderTable() {
    const container = document.getElementById('applicationsTableBody');
    const emptyState = document.getElementById('applicationsEmptyState');
    if (!container) return;

    const list = this.getFilteredApplications();

    if (list.length === 0) {
      container.innerHTML = '';
      if (emptyState) emptyState.style.display = 'block';
      return;
    }

    if (emptyState) emptyState.style.display = 'none';

    container.innerHTML = list.map(app => {
      const statusClass = 'status-' + app.status.toLowerCase().replace(/\s+/g, '-');
      return `
        <tr data-app-id="${app.id}">
          <td>
            <div class="table-company-cell">
              <div class="company-logo-badge" style="background-color: ${app.logoColor};">
                ${app.logoText}
              </div>
              <div class="company-cell-info">
                <strong>${App.escapeHTML(app.role)}</strong>
                <small>${App.escapeHTML(app.company)}</small>
              </div>
            </div>
          </td>
          <td>
            <strong>${App.formatDate(app.appliedDate)}</strong>
            <small style="display: block; color: var(--text-subtle);">Ref: #${app.id}</small>
          </td>
          <td>
            <span class="status-pill ${statusClass}">${App.escapeHTML(app.status)}</span>
            ${app.interviewDate && app.interviewDate !== 'Pending Scheduling' ? `
              <div style="font-size: 0.775rem; color: var(--warning-text); margin-top: 0.35rem; display: flex; align-items: center; gap: 0.3rem;">
                <i class="fa-regular fa-clock"></i> ${App.escapeHTML(app.interviewDate)}
              </div>
            ` : ''}
          </td>
          <td style="max-width: 250px;">
            <p style="font-size: 0.825rem; color: var(--text-muted); margin: 0; line-height: 1.4;">
              ${App.escapeHTML(app.notes || 'No remarks available.')}
            </p>
          </td>
          <td style="text-align: right; white-space: nowrap;">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
              <a href="opportunity-details.html?id=${app.oppId}" class="btn btn-sm btn-outline-secondary" title="View Opportunity Details">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Details
              </a>
              <button type="button" class="btn btn-sm btn-outline-secondary withdraw-app-btn" data-id="${app.id}" title="Withdraw Application">
                <i class="fa-solid fa-trash-can text-danger"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    this.bindActionButtons();
  },

  bindActionButtons() {
    const withdrawBtns = document.querySelectorAll('.withdraw-app-btn');
    withdrawBtns.forEach(btn => {
      btn.onclick = () => {
        const id = btn.getAttribute('data-id');
        const app = Storage.getApplicationById(id);
        if (!app) return;

        App.showModal({
          title: "Withdraw Application?",
          message: `Are you sure you want to withdraw your application for ${app.role} at ${app.company}? This action will remove your application record from the tracking system.`,
          confirmText: "Yes, Withdraw Application",
          cancelText: "Keep Application",
          variant: "danger",
          onConfirm: () => {
            Storage.removeApplication(id);
            App.showToast(`Application for ${app.company} has been withdrawn.`, "info");
            this.render();
            this.populateDemoManager();
            App.updateNotificationBadge();
          }
        });
      };
    });
  },

  populateDemoManager() {
    const select = document.getElementById('demoApplicationSelect');
    if (!select) return;

    const list = Storage.getApplications();
    if (list.length === 0) {
      select.innerHTML = '<option value="">No active applications available</option>';
      select.disabled = true;
      const submitBtn = document.getElementById('demoUpdateBtn');
      if (submitBtn) submitBtn.disabled = true;
      return;
    }

    select.disabled = false;
    const submitBtn = document.getElementById('demoUpdateBtn');
    if (submitBtn) submitBtn.disabled = false;

    select.innerHTML = list.map(app => `
      <option value="${app.id}">
        ${app.company} - ${app.role} (Current: ${app.status})
      </option>
    `).join('');
  },

  handleDemoStatusUpdate() {
    const select = document.getElementById('demoApplicationSelect');
    const statusSelect = document.getElementById('demoNewStatusSelect');
    const notesInput = document.getElementById('demoStatusNotes');

    const appId = select?.value;
    const newStatus = statusSelect?.value;
    const notes = notesInput?.value.trim() || null;

    if (!appId || !newStatus) {
      App.showToast("Please select an application and target status.", "warning");
      return;
    }

    const result = Storage.updateApplicationStatus(appId, newStatus, notes);
    if (result.success) {
      App.showToast(`Status updated to '${newStatus}' for ${result.application.company}!`, "success");
      if (notesInput) notesInput.value = '';
      this.render();
      this.populateDemoManager();
      App.updateNotificationBadge();
    } else {
      App.showToast(result.message, "error");
    }
  }
};
