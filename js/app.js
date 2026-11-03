/**
 * Campus CareerTrack - Core Application Framework
 * Handles layout coordination, theme switching, toast alerts, modals,
 * and unified user actions across all pages.
 */

const App = {
  init() {
    this.applyTheme(Storage.getTheme());
    this.updateTopbarUser();
    this.updateNotificationBadge();
    this.initMobileMenu();
    this.initGlobalSearch();
    this.highlightCurrentNavLink();
  },

  // ----------------------------------------------------
  // THEME MANAGEMENT
  // ----------------------------------------------------
  applyTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    } else {
      document.documentElement.removeAttribute('data-theme');
    }
    
    // Update theme toggle buttons if present on page
    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    toggleBtns.forEach(btn => {
      if (theme === 'dark') {
        btn.innerHTML = `<i class="fa-solid fa-sun"></i><span class="theme-text">Light Mode</span>`;
        btn.setAttribute('title', 'Switch to Light Mode');
      } else {
        btn.innerHTML = `<i class="fa-solid fa-moon"></i><span class="theme-text">Dark Mode</span>`;
        btn.setAttribute('title', 'Switch to Dark Mode');
      }
    });
  },

  toggleTheme() {
    const current = Storage.getTheme();
    const next = current === 'dark' ? 'light' : 'dark';
    Storage.setTheme(next);
    this.applyTheme(next);
    this.showToast(`Switched to ${next === 'dark' ? 'Dark' : 'Light'} theme`, 'info');
  },

  // ----------------------------------------------------
  // USER PROFILE IN UI
  // ----------------------------------------------------
  updateTopbarUser() {
    const profile = Storage.getProfile();
    const nameEls = document.querySelectorAll('.user-name-display');
    const roleEls = document.querySelectorAll('.user-role-display');
    const avatarEls = document.querySelectorAll('.user-avatar-display');

    const initials = profile.fullName
      ? profile.fullName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
      : 'ST';

    nameEls.forEach(el => el.textContent = profile.fullName || 'Student');
    roleEls.forEach(el => el.textContent = `${profile.degree || 'B.E.'} ${profile.department ? profile.department.split(' ')[0] : 'CSE'}`);
    avatarEls.forEach(el => el.textContent = initials);
  },

  updateNotificationBadge() {
    const count = Storage.getUnreadNotificationsCount();
    const badges = document.querySelectorAll('.notification-badge');
    badges.forEach(badge => {
      if (count > 0) {
        badge.textContent = count > 9 ? '9+' : count;
        badge.style.display = 'inline-flex';
      } else {
        badge.style.display = 'none';
      }
    });
  },

  // ----------------------------------------------------
  // NAVIGATION & MOBILE SIDEBAR
  // ----------------------------------------------------
  initMobileMenu() {
    const toggleBtn = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleBtn && sidebar) {
      toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('active');
      });
    }

    if (overlay && sidebar) {
      overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
      });
    }

    // Collapse sidebar button on desktop
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    if (collapseBtn && sidebar) {
      collapseBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');
      });
    }
  },

  highlightCurrentNavLink() {
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-item, .navbar-nav .nav-link');

    navLinks.forEach(link => {
      const href = link.getAttribute('href');
      if (href && href === currentPath) {
        link.classList.add('active');
      } else if (currentPath === '' && href === 'index.html') {
        link.classList.add('active');
      }
    });
  },

  initGlobalSearch() {
    const searchInputs = document.querySelectorAll('.global-search-input');
    searchInputs.forEach(input => {
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && input.value.trim()) {
          const query = encodeURIComponent(input.value.trim());
          window.location.href = `opportunities.html?search=${query}`;
        }
      });
    });
  },

  // ----------------------------------------------------
  // TOAST NOTIFICATION COMPONENT
  // ----------------------------------------------------
  showToast(message, type = 'success', duration = 3500) {
    let container = document.getElementById('cct-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'cct-toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    let iconClass = 'fa-circle-check';
    if (type === 'error') iconClass = 'fa-circle-xmark';
    if (type === 'info') iconClass = 'fa-circle-info';
    if (type === 'warning') iconClass = 'fa-triangle-exclamation';

    toast.innerHTML = `
      <div class="toast-icon"><i class="fa-solid ${iconClass}"></i></div>
      <div class="toast-body">${this.escapeHTML(message)}</div>
      <button class="toast-close" aria-label="Close notification">&times;</button>
      <div class="toast-progress"></div>
    `;

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
      toast.classList.add('show');
    });

    const closeToast = () => {
      toast.classList.remove('show');
      setTimeout(() => {
        if (toast.parentElement) toast.remove();
      }, 300);
    };

    toast.querySelector('.toast-close').addEventListener('click', closeToast);

    // Auto dismiss
    setTimeout(closeToast, duration);
  },

  // ----------------------------------------------------
  // CONFIRMATION MODAL SYSTEM
  // ----------------------------------------------------
  showModal(options) {
    const {
      title = "Confirm Action",
      message = "Are you sure you want to proceed?",
      confirmText = "Confirm",
      cancelText = "Cancel",
      variant = "primary", // primary, danger, warning
      onConfirm = () => {},
      onCancel = () => {}
    } = options;

    let modal = document.getElementById('cct-global-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'cct-global-modal';
      modal.className = 'modal-backdrop';
      document.body.appendChild(modal);
    }

    const btnClass = variant === 'danger' ? 'btn-danger' : variant === 'warning' ? 'btn-warning' : 'btn-primary';

    modal.innerHTML = `
      <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-header">
          <h3 id="modalTitle" class="modal-title">${this.escapeHTML(title)}</h3>
          <button type="button" class="modal-close-btn" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
          <p>${this.escapeHTML(message)}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary modal-cancel-btn">${this.escapeHTML(cancelText)}</button>
          <button type="button" class="btn ${btnClass} modal-confirm-btn">${this.escapeHTML(confirmText)}</button>
        </div>
      </div>
    `;

    modal.classList.add('active');

    const closeModal = () => {
      modal.classList.remove('active');
    };

    const confirmBtn = modal.querySelector('.modal-confirm-btn');
    const cancelBtn = modal.querySelector('.modal-cancel-btn');
    const closeBtn = modal.querySelector('.modal-close-btn');

    confirmBtn.onclick = () => {
      closeModal();
      onConfirm();
    };

    cancelBtn.onclick = () => {
      closeModal();
      onCancel();
    };

    closeBtn.onclick = () => {
      closeModal();
      onCancel();
    };

    modal.onclick = (e) => {
      if (e.target === modal) {
        closeModal();
        onCancel();
      }
    };
  },

  // ----------------------------------------------------
  // APPLICATION SUBMISSION MODAL
  // ----------------------------------------------------
  openApplyModal(oppId, onApplicationSuccess = null) {
    const opp = Storage.getOpportunityById(oppId);
    if (!opp) {
      this.showToast("Opportunity details not found.", "error");
      return;
    }

    if (Storage.hasApplied(oppId)) {
      this.showToast(`You have already submitted an application to ${opp.company} for this role.`, "warning");
      return;
    }

    const profile = Storage.getProfile();

    let modal = document.getElementById('cct-apply-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'cct-apply-modal';
      modal.className = 'modal-backdrop';
      document.body.appendChild(modal);
    }

    modal.innerHTML = `
      <div class="modal-dialog modal-lg" role="dialog" aria-modal="true">
        <div class="modal-header">
          <div>
            <span class="badge badge-primary">${this.escapeHTML(opp.type)}</span>
            <h3 class="modal-title" style="margin-top: 4px;">Apply to ${this.escapeHTML(opp.company)}</h3>
            <p class="modal-subtitle">${this.escapeHTML(opp.title)} &bull; ${this.escapeHTML(opp.location)}</p>
          </div>
          <button type="button" class="modal-close-btn" aria-label="Close modal">&times;</button>
        </div>
        <form id="applySubmissionForm">
          <div class="modal-body">
            <div class="alert alert-info">
              <i class="fa-solid fa-circle-info"></i>
              <div>
                <strong>Application Eligibility:</strong> Min CGPA: ${opp.minCGPA} | Max Backlogs: ${opp.maxBacklogs} | Batch: ${opp.allowedBatches.join(', ')}
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label for="applyFullName" class="form-label">Full Name <span class="required">*</span></label>
                <input type="text" id="applyFullName" class="form-control" value="${this.escapeHTML(profile.fullName || '')}" required />
              </div>
              <div class="form-group">
                <label for="applyEmail" class="form-label">College Email Address <span class="required">*</span></label>
                <input type="email" id="applyEmail" class="form-control" value="${this.escapeHTML(profile.email || '')}" required />
              </div>
              <div class="form-group">
                <label for="applyPhone" class="form-label">Contact Phone Number <span class="required">*</span></label>
                <input type="tel" id="applyPhone" class="form-control" value="${this.escapeHTML(profile.phone || '+91 ')}" required />
              </div>
              <div class="form-group">
                <label for="applyCGPA" class="form-label">Current CGPA</label>
                <input type="text" id="applyCGPA" class="form-control" value="${profile.cgpa || ''}" readonly />
              </div>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
              <label class="form-label">Attached Resume File</label>
              <div class="resume-badge-box">
                <i class="fa-solid fa-file-pdf"></i>
                <div class="resume-file-info">
                  <strong>${this.escapeHTML(profile.resumeFilename || 'Student_Resume.pdf')}</strong>
                  <small>Selected from Student Profile (Local client-side reference)</small>
                </div>
                <a href="profile.html" class="btn btn-sm btn-outline-secondary">Change in Profile</a>
              </div>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
              <label for="applyNotes" class="form-label">Candidate Statement / Note to Hiring Team (Optional)</label>
              <textarea id="applyNotes" class="form-control" rows="3" placeholder="Briefly highlight your relevant projects, core strengths, and motivation for this role..."></textarea>
            </div>

            <div class="form-check" style="margin-top: 1rem;">
              <input type="checkbox" id="applyConsent" class="form-check-input" required />
              <label for="applyConsent" class="form-check-label">
                I certify that the academic information (CGPA: ${profile.cgpa}, Backlogs: ${profile.activeBacklogs}) is true and I meet the stated eligibility criteria.
              </label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-cancel-btn">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitApplyBtn">
              <i class="fa-solid fa-paper-plane"></i> Submit Application
            </button>
          </div>
        </form>
      </div>
    `;

    modal.classList.add('active');

    const closeModal = () => modal.classList.remove('active');
    modal.querySelector('.modal-close-btn').onclick = closeModal;
    modal.querySelector('.modal-cancel-btn').onclick = closeModal;

    const form = modal.querySelector('#applySubmissionForm');
    form.onsubmit = (e) => {
      e.preventDefault();

      const fullName = modal.querySelector('#applyFullName').value.trim();
      const email = modal.querySelector('#applyEmail').value.trim();
      const phone = modal.querySelector('#applyPhone').value.trim();
      const notes = modal.querySelector('#applyNotes').value.trim();

      if (!fullName || !email || !phone) {
        App.showToast("Please fill in all required fields.", "error");
        return;
      }

      // Check simple email pattern
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        App.showToast("Please provide a valid email address.", "error");
        return;
      }

      const result = Storage.addApplication({
        oppId: opp.id,
        company: opp.company,
        logoText: opp.logoText,
        logoColor: opp.logoColor,
        role: opp.title,
        applicantName: fullName,
        applicantEmail: email,
        applicantPhone: phone,
        resumeFilename: profile.resumeFilename || 'Resume.pdf',
        notes: notes || "Applied directly via Campus CareerTrack portal."
      });

      if (result.success) {
        closeModal();
        App.showToast(`Application successfully submitted for ${opp.company} - ${opp.title}!`, "success");
        App.updateNotificationBadge();
        if (typeof onApplicationSuccess === 'function') {
          onApplicationSuccess(result.application);
        }
      } else {
        App.showToast(result.message, "warning");
      }
    };
  },

  // ----------------------------------------------------
  // UTILITY HELPERS
  // ----------------------------------------------------
  escapeHTML(str) {
    if (typeof str !== 'string') return str ?? '';
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  },

  formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    try {
      const parts = dateStr.split('-');
      if (parts.length === 3) {
        const d = new Date(parts[0], parts[1] - 1, parts[2]);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      }
      return dateStr;
    } catch {
      return dateStr;
    }
  },

  getDaysRemaining(deadlineStr) {
    if (!deadlineStr) return 0;
    try {
      const deadline = new Date(deadlineStr);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      deadline.setHours(0, 0, 0, 0);
      const diffTime = deadline - today;
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    } catch {
      return 0;
    }
  }
};

// Initialize common app logic on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
  App.init();
});
