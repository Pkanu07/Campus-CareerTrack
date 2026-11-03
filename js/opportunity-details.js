/**
 * Campus CareerTrack - Opportunity Details Controller
 * Renders complete specifications, eligibility, responsibilities,
 * and application actions for a single selected opportunity.
 */

const OpportunityDetailsController = {
  currentOpp: null,

  init() {
    const params = new URLSearchParams(window.location.search);
    const oppId = params.get('id') || 'opp-1';
    
    this.currentOpp = Storage.getOpportunityById(oppId);

    if (!this.currentOpp) {
      const all = Storage.getOpportunities();
      this.currentOpp = all[0] || null;
    }

    if (!this.currentOpp) {
      document.getElementById('detailsContainer').innerHTML = `
        <div class="empty-state">
          <div class="empty-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <h3>Opportunity Not Found</h3>
          <p>The requested opportunity does not exist or has been removed.</p>
          <a href="opportunities.html" class="btn btn-primary">Browse All Opportunities</a>
        </div>
      `;
      return;
    }

    this.render();
    this.bindEvents();
  },

  render() {
    const opp = this.currentOpp;
    const isSaved = Storage.isBookmarked(opp.id);
    const application = Storage.getApplicationByOppId(opp.id);
    const daysLeft = App.getDaysRemaining(opp.deadline);

    // Update document title
    document.title = `${opp.title} at ${opp.company} | Campus CareerTrack`;

    const bannerContainer = document.getElementById('detailsBanner');
    const contentContainer = document.getElementById('detailsContent');
    const sidebarContainer = document.getElementById('detailsSidebar');

    if (bannerContainer) {
      bannerContainer.innerHTML = `
        <div class="details-banner-left">
          <div class="company-logo-badge avatar-lg" style="background-color: ${opp.logoColor}; border-radius: var(--radius-lg);">
            ${opp.logoText}
          </div>
          <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
              <span class="badge badge-primary">${App.escapeHTML(opp.type)}</span>
              <span class="badge badge-secondary">${App.escapeHTML(opp.category)}</span>
              <span class="badge badge-info"><i class="fa-solid fa-building"></i> ${App.escapeHTML(opp.department)}</span>
            </div>
            <h1 style="font-size: 1.85rem; margin-bottom: 0.35rem;">${App.escapeHTML(opp.title)}</h1>
            <p style="font-size: 1.05rem; color: var(--text-muted);">
              <strong>${App.escapeHTML(opp.company)}</strong> &bull; 
              <i class="fa-solid fa-location-dot"></i> ${App.escapeHTML(opp.location)} (${App.escapeHTML(opp.workMode)})
            </p>
          </div>
        </div>
        <div>
          <button type="button" class="btn btn-outline-secondary" id="detailBookmarkBtn">
            <i class="${isSaved ? 'fa-solid' : 'fa-regular'} fa-bookmark" style="${isSaved ? 'color: #ef4444;' : ''}"></i>
            <span>${isSaved ? 'Saved' : 'Save Role'}</span>
          </button>
        </div>
      `;
    }

    if (contentContainer) {
      contentContainer.innerHTML = `
        ${application ? `
          <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            <i class="fa-solid fa-circle-check" style="font-size: 1.35rem;"></i>
            <div>
              <strong>You have applied for this role!</strong><br />
              Submitted on ${App.formatDate(application.appliedDate)} &bull; Current Status: 
              <span class="status-pill status-${application.status.toLowerCase().replace(/\s+/g, '-')}">${application.status}</span>
              <a href="applications.html" style="margin-left: 0.75rem; text-decoration: underline; font-weight: 600;">View in Tracker</a>
            </div>
          </div>
        ` : ''}

        <section class="rich-content-section">
          <h3><i class="fa-solid fa-align-left text-primary"></i> Opportunity Overview</h3>
          <p style="font-size: 1rem; line-height: 1.7; color: var(--text-main); margin-bottom: 1.25rem;">
            ${App.escapeHTML(opp.fullDesc)}
          </p>
        </section>

        <section class="rich-content-section">
          <h3><i class="fa-solid fa-list-check text-primary"></i> Key Responsibilities</h3>
          <ul class="responsibilities-list">
            ${opp.responsibilities.map(r => `<li>${App.escapeHTML(r)}</li>`).join('')}
          </ul>
        </section>

        <section class="rich-content-section">
          <h3><i class="fa-solid fa-code text-primary"></i> Required Skills & Core Tech</h3>
          <div class="opp-skills-list" style="margin-bottom: 0;">
            ${opp.skills.map(s => `<span class="tag-badge" style="font-size: 0.9rem; padding: 0.4rem 0.85rem;"><i class="fa-solid fa-check"></i> ${App.escapeHTML(s)}</span>`).join('')}
          </div>
        </section>

        <section class="rich-content-section">
          <h3><i class="fa-solid fa-diagram-project text-primary"></i> Selection & Interview Workflow</h3>
          <ol class="interview-rounds-list">
            ${opp.interviewRounds.map((round, idx) => `
              <li>
                <span class="round-number">${idx + 1}</span>
                <span>${App.escapeHTML(round)}</span>
              </li>
            `).join('')}
          </ol>
        </section>
      `;
    }

    if (sidebarContainer) {
      sidebarContainer.innerHTML = `
        <div class="details-sidebar-card">
          <h4 style="margin-bottom: 1rem; font-size: 1.15rem;">Opportunity Summary</h4>

          <div class="detail-spec-item">
            <div class="detail-spec-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
            <div class="detail-spec-text">
              <strong>${App.escapeHTML(opp.stipend)}</strong>
              <small>Est. Annual CTC: ${App.escapeHTML(opp.salaryAnnual)}</small>
            </div>
          </div>

          <div class="detail-spec-item">
            <div class="detail-spec-icon"><i class="fa-regular fa-clock"></i></div>
            <div class="detail-spec-text">
              <strong>${App.formatDate(opp.deadline)}</strong>
              <small>${daysLeft > 0 ? `${daysLeft} days remaining` : 'Application Closed'}</small>
            </div>
          </div>

          <div class="detail-spec-item">
            <div class="detail-spec-icon"><i class="fa-solid fa-graduation-cap"></i></div>
            <div class="detail-spec-text">
              <strong>Min. CGPA: ${opp.minCGPA}</strong>
              <small>Max Backlogs: ${opp.maxBacklogs}</small>
            </div>
          </div>

          <div class="detail-spec-item">
            <div class="detail-spec-icon"><i class="fa-solid fa-users"></i></div>
            <div class="detail-spec-text">
              <strong>Batch: ${opp.allowedBatches.join(', ')}</strong>
              <small>${opp.allowedBranches.join(', ')}</small>
            </div>
          </div>

          <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
            ${application ? `
              <button type="button" class="btn btn-secondary btn-lg" style="width: 100%;" disabled>
                <i class="fa-solid fa-check"></i> Already Applied
              </button>
            ` : `
              <button type="button" class="btn btn-primary btn-lg" id="detailApplyBtn" style="width: 100%;">
                <i class="fa-solid fa-paper-plane"></i> Apply Now
              </button>
            `}

            <a href="eligibility.html?id=${opp.id}" class="btn btn-outline-primary" style="width: 100%; text-align: center;">
              <i class="fa-solid fa-wand-magic-sparkles"></i> Check My Eligibility
            </a>
          </div>

          <div style="margin-top: 1.25rem; font-size: 0.775rem; color: var(--text-subtle); text-align: center;">
            <i class="fa-solid fa-shield-halved"></i> Verified Campus Recruitment Partner
          </div>
        </div>
      `;
    }
  },

  bindEvents() {
    const opp = this.currentOpp;

    // Apply button
    const applyBtn = document.getElementById('detailApplyBtn');
    if (applyBtn) {
      applyBtn.onclick = () => {
        App.openApplyModal(opp.id, () => {
          this.render();
          this.bindEvents();
        });
      };
    }

    // Bookmark button
    const bookmarkBtn = document.getElementById('detailBookmarkBtn');
    if (bookmarkBtn) {
      bookmarkBtn.onclick = () => {
        const nowSaved = Storage.toggleBookmark(opp.id);
        const icon = bookmarkBtn.querySelector('i');
        const text = bookmarkBtn.querySelector('span');

        if (nowSaved) {
          icon.className = 'fa-solid fa-bookmark';
          icon.style.color = '#ef4444';
          text.textContent = 'Saved';
          App.showToast("Opportunity bookmarked.", "success");
        } else {
          icon.className = 'fa-regular fa-bookmark';
          icon.style.color = '';
          text.textContent = 'Save Role';
          App.showToast("Removed from bookmarks.", "info");
        }
      };
    }
  }
};
