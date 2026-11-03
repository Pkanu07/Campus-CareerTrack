/**
 * Campus CareerTrack - Dashboard Controller
 * Loads dynamic statistics, recent applications, upcoming deadlines,
 * recommendations, and renders the visual status distribution chart.
 */

const DashboardController = {
  init() {
    this.renderStats();
    this.renderWelcome();
    this.renderRecentApplications();
    this.renderUpcomingDeadlines();
    this.renderRecommendedOpportunities();
    this.renderStatusChart();
  },

  renderWelcome() {
    const profile = Storage.getProfile();
    const welcomeName = document.getElementById('dashWelcomeName');
    if (welcomeName) {
      welcomeName.textContent = profile.fullName ? profile.fullName.split(' ')[0] : 'Student';
    }
  },

  renderStats() {
    const stats = Storage.getDashboardStats();

    const totalEl = document.getElementById('statTotalApplications');
    const shortlistedEl = document.getElementById('statShortlisted');
    const interviewsEl = document.getElementById('statInterviews');
    const savedEl = document.getElementById('statSaved');

    if (totalEl) totalEl.textContent = stats.totalApplications;
    if (shortlistedEl) shortlistedEl.textContent = stats.shortlistedCount;
    if (interviewsEl) interviewsEl.textContent = stats.interviewCount;
    if (savedEl) savedEl.textContent = stats.savedCount;
  },

  renderRecentApplications() {
    const container = document.getElementById('recentApplicationsTableBody');
    const emptyState = document.getElementById('recentApplicationsEmptyState');
    if (!container) return;

    const applications = Storage.getApplications().slice(0, 5); // top 5 recent

    if (applications.length === 0) {
      container.innerHTML = '';
      if (emptyState) emptyState.style.display = 'block';
      return;
    }

    if (emptyState) emptyState.style.display = 'none';

    container.innerHTML = applications.map(app => {
      const statusClass = 'status-' + app.status.toLowerCase().replace(/\s+/g, '-');
      return `
        <tr>
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
          <td>${App.formatDate(app.appliedDate)}</td>
          <td><span class="status-pill ${statusClass}">${App.escapeHTML(app.status)}</span></td>
          <td style="text-align: right;">
            <a href="applications.html" class="btn btn-sm btn-outline-secondary" title="View Tracker">
              <i class="fa-solid fa-arrow-right"></i>
            </a>
          </td>
        </tr>
      `;
    }).join('');
  },

  renderUpcomingDeadlines() {
    const container = document.getElementById('upcomingDeadlinesContainer');
    if (!container) return;

    const opportunities = Storage.getOpportunities();
    // Sort by soonest deadline
    const sorted = [...opportunities]
      .filter(opp => App.getDaysRemaining(opp.deadline) >= 0)
      .sort((a, b) => new Date(a.deadline) - new Date(b.deadline))
      .slice(0, 4);

    container.innerHTML = sorted.map(opp => {
      const days = App.getDaysRemaining(opp.deadline);
      const pillClass = days <= 5 ? 'urgent' : days <= 15 ? 'moderate' : 'safe';
      return `
        <div class="deadline-item">
          <div class="deadline-info">
            <strong><a href="opportunity-details.html?id=${opp.id}">${App.escapeHTML(opp.title)}</a></strong>
            <small>${App.escapeHTML(opp.company)} &bull; ${App.formatDate(opp.deadline)}</small>
          </div>
          <span class="deadline-pill ${pillClass}">
            <i class="fa-regular fa-clock"></i> ${days} ${days === 1 ? 'day' : 'days'}
          </span>
        </div>
      `;
    }).join('');
  },

  renderRecommendedOpportunities() {
    const container = document.getElementById('recommendedOppsContainer');
    if (!container) return;

    const profile = Storage.getProfile();
    const opportunities = Storage.getOpportunities();
    const studentSkills = (profile.skills || []).map(s => s.toLowerCase());

    // Score opportunities based on skills overlap and CGPA match
    const scored = opportunities.map(opp => {
      let matchCount = 0;
      opp.skills.forEach(s => {
        if (studentSkills.includes(s.toLowerCase())) matchCount++;
      });
      const cgpaMatch = (profile.cgpa || 8.0) >= opp.minCGPA ? 1 : 0;
      return { opp, score: matchCount * 2 + cgpaMatch };
    });

    scored.sort((a, b) => b.score - a.score);
    const topThree = scored.slice(0, 3).map(s => s.opp);

    container.innerHTML = topThree.map(opp => {
      const isSaved = Storage.isBookmarked(opp.id);
      const hasApplied = Storage.hasApplied(opp.id);
      return `
        <div class="opportunity-card" style="padding: 1.15rem; margin-bottom: 0.75rem;">
          <div class="opp-card-header" style="margin-bottom: 0.5rem;">
            <div class="opp-company-meta">
              <div class="company-logo-badge" style="background-color: ${opp.logoColor}; width: 34px; height: 34px; font-size: 0.75rem;">
                ${opp.logoText}
              </div>
              <div>
                <span class="opp-company-name" style="font-size: 0.875rem;">${App.escapeHTML(opp.company)}</span>
                <div class="opp-location" style="font-size: 0.75rem;">${App.escapeHTML(opp.location)}</div>
              </div>
            </div>
            <span class="badge badge-primary" style="font-size: 0.7rem;">${App.escapeHTML(opp.type)}</span>
          </div>
          <h4 style="font-size: 0.95rem; margin-bottom: 0.4rem;">
            <a href="opportunity-details.html?id=${opp.id}">${App.escapeHTML(opp.title)}</a>
          </h4>
          <div class="opp-meta-footer" style="padding-top: 0.5rem; margin-top: 0.5rem;">
            <strong style="color: var(--primary); font-size: 0.85rem;">${App.escapeHTML(opp.stipend)}</strong>
            <div style="display: flex; gap: 0.35rem;">
              <a href="opportunity-details.html?id=${opp.id}" class="btn btn-sm btn-outline-secondary">Details</a>
              ${hasApplied ? `
                <span class="badge badge-success"><i class="fa-solid fa-check"></i> Applied</span>
              ` : `
                <button type="button" class="btn btn-sm btn-primary dash-apply-btn" data-id="${opp.id}">Apply</button>
              `}
            </div>
          </div>
        </div>
      `;
    }).join('');

    // Bind apply buttons
    container.querySelectorAll('.dash-apply-btn').forEach(btn => {
      btn.onclick = () => {
        const id = btn.getAttribute('data-id');
        App.openApplyModal(id, () => {
          this.init();
        });
      };
    });
  },

  renderStatusChart() {
    const stats = Storage.getDashboardStats();
    const total = stats.totalApplications;

    const countApplied = stats.appliedCount;
    const countShortlisted = stats.shortlistedCount;
    const countInterview = stats.interviewCount;
    const countSelected = stats.selectedCount;
    const countRejected = stats.rejectedCount;

    // Calculate percentages
    const pctApplied = total > 0 ? (countApplied / total) * 100 : 0;
    const pctShortlisted = total > 0 ? (countShortlisted / total) * 100 : 0;
    const pctInterview = total > 0 ? (countInterview / total) * 100 : 0;
    const pctSelected = total > 0 ? (countSelected / total) * 100 : 0;
    const pctRejected = total > 0 ? (countRejected / total) * 100 : 0;

    // Circumference for SVG circle r=45 is 2 * PI * 45 = 282.74
    const C = 282.74;

    const strokeApplied = (pctApplied / 100) * C;
    const strokeShortlisted = (pctShortlisted / 100) * C;
    const strokeInterview = (pctInterview / 100) * C;
    const strokeSelected = (pctSelected / 100) * C;
    const strokeRejected = (pctRejected / 100) * C;

    const offset0 = 0;
    const offset1 = -strokeApplied;
    const offset2 = -(strokeApplied + strokeShortlisted);
    const offset3 = -(strokeApplied + strokeShortlisted + strokeInterview);
    const offset4 = -(strokeApplied + strokeShortlisted + strokeInterview + strokeSelected);

    const chartSvgContainer = document.getElementById('statusChartSvgBox');
    if (chartSvgContainer) {
      if (total === 0) {
        chartSvgContainer.innerHTML = `
          <svg viewBox="0 0 100 100" class="chart-svg">
            <circle cx="50" cy="50" r="45" fill="transparent" stroke="var(--border-color)" stroke-width="10"></circle>
          </svg>
          <div class="chart-center-label">
            <span class="chart-center-number">0</span>
            <span class="chart-center-text">Total</span>
          </div>
        `;
      } else {
        chartSvgContainer.innerHTML = `
          <svg viewBox="0 0 100 100" class="chart-svg">
            <circle cx="50" cy="50" r="45" fill="transparent" stroke="var(--border-color)" stroke-width="10"></circle>
            <!-- Applied (Blue) -->
            <circle cx="50" cy="50" r="45" fill="transparent" stroke="#3b82f6" stroke-width="10"
              stroke-dasharray="${strokeApplied} ${C}" stroke-dashoffset="${offset0}"></circle>
            <!-- Shortlisted (Indigo) -->
            <circle cx="50" cy="50" r="45" fill="transparent" stroke="#4f46e5" stroke-width="10"
              stroke-dasharray="${strokeShortlisted} ${C}" stroke-dashoffset="${offset1}"></circle>
            <!-- Interview (Amber) -->
            <circle cx="50" cy="50" r="45" fill="transparent" stroke="#f59e0b" stroke-width="10"
              stroke-dasharray="${strokeInterview} ${C}" stroke-dashoffset="${offset2}"></circle>
            <!-- Selected (Green) -->
            <circle cx="50" cy="50" r="45" fill="transparent" stroke="#10b981" stroke-width="10"
              stroke-dasharray="${strokeSelected} ${C}" stroke-dashoffset="${offset3}"></circle>
            <!-- Rejected (Red) -->
            <circle cx="50" cy="50" r="45" fill="transparent" stroke="#ef4444" stroke-width="10"
              stroke-dasharray="${strokeRejected} ${C}" stroke-dashoffset="${offset4}"></circle>
          </svg>
          <div class="chart-center-label">
            <span class="chart-center-number">${total}</span>
            <span class="chart-center-text">Active</span>
          </div>
        `;
      }
    }

    // Update legend values
    const legApplied = document.getElementById('legendCountApplied');
    const legShortlisted = document.getElementById('legendCountShortlisted');
    const legInterview = document.getElementById('legendCountInterview');
    const legSelected = document.getElementById('legendCountSelected');
    const legRejected = document.getElementById('legendCountRejected');

    if (legApplied) legApplied.textContent = countApplied;
    if (legShortlisted) legShortlisted.textContent = countShortlisted;
    if (legInterview) legInterview.textContent = countInterview;
    if (legSelected) legSelected.textContent = countSelected;
    if (legRejected) legRejected.textContent = countRejected;
  }
};
