/**
 * Campus CareerTrack - Opportunities Module
 * Handles searching, multi-criteria filtering, sorting, pagination,
 * bookmark toggling, and rendering for opportunities.html & saved-opportunities.html.
 */

const OpportunitiesController = {
  allOpportunities: [],
  filteredOpportunities: [],
  currentPage: 1,
  pageSize: 6,
  isSavedPage: false,
  activeSkillFilters: new Set(),

  init(isSavedOnly = false) {
    this.isSavedPage = isSavedOnly;
    this.loadData();
    this.setupListeners();
    this.populateSkillChips();
    this.checkUrlParams();
    this.applyFiltersAndRender();
  },

  loadData() {
    const all = Storage.getOpportunities();
    if (this.isSavedPage) {
      const bookmarks = Storage.getBookmarks();
      this.allOpportunities = all.filter(opp => bookmarks.includes(opp.id));
    } else {
      this.allOpportunities = all;
    }
  },

  setupListeners() {
    const searchInput = document.getElementById('searchQuery');
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        this.currentPage = 1;
        this.applyFiltersAndRender();
      });
    }

    const locationFilter = document.getElementById('filterLocation');
    if (locationFilter) {
      locationFilter.addEventListener('change', () => {
        this.currentPage = 1;
        this.applyFiltersAndRender();
      });
    }

    const modeFilter = document.getElementById('filterMode');
    if (modeFilter) {
      modeFilter.addEventListener('change', () => {
        this.currentPage = 1;
        this.applyFiltersAndRender();
      });
    }

    const typeFilter = document.getElementById('filterType');
    if (typeFilter) {
      typeFilter.addEventListener('change', () => {
        this.currentPage = 1;
        this.applyFiltersAndRender();
      });
    }

    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
      sortSelect.addEventListener('change', () => {
        this.applyFiltersAndRender();
      });
    }

    const clearBtn = document.getElementById('clearFiltersBtn');
    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        this.resetFilters();
      });
    }

    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
      loadMoreBtn.addEventListener('click', () => {
        this.currentPage++;
        this.renderCards(true);
      });
    }
  },

  checkUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const searchParam = params.get('search');
    if (searchParam) {
      const searchInput = document.getElementById('searchQuery');
      if (searchInput) searchInput.value = searchParam;
    }

    const typeParam = params.get('type');
    if (typeParam) {
      const typeFilter = document.getElementById('filterType');
      if (typeFilter) typeFilter.value = typeParam;
    }
  },

  populateSkillChips() {
    const container = document.getElementById('skillFilterChips');
    if (!container) return;

    // Collect unique skills from opportunities
    const skillCounts = {};
    this.allOpportunities.forEach(opp => {
      opp.skills.forEach(skill => {
        skillCounts[skill] = (skillCounts[skill] || 0) + 1;
      });
    });

    // Pick top 8 skills
    const popularSkills = Object.keys(skillCounts)
      .sort((a, b) => skillCounts[b] - skillCounts[a])
      .slice(0, 8);

    container.innerHTML = popularSkills.map(skill => `
      <button type="button" class="filter-chip" data-skill="${skill}">
        ${skill}
      </button>
    `).join('');

    container.querySelectorAll('.filter-chip').forEach(chip => {
      chip.addEventListener('click', () => {
        const skill = chip.getAttribute('data-skill');
        if (this.activeSkillFilters.has(skill)) {
          this.activeSkillFilters.delete(skill);
          chip.classList.remove('active');
        } else {
          this.activeSkillFilters.add(skill);
          chip.classList.add('active');
        }
        this.currentPage = 1;
        this.applyFiltersAndRender();
      });
    });
  },

  resetFilters() {
    const searchInput = document.getElementById('searchQuery');
    if (searchInput) searchInput.value = '';

    const locationFilter = document.getElementById('filterLocation');
    if (locationFilter) locationFilter.value = 'all';

    const modeFilter = document.getElementById('filterMode');
    if (modeFilter) modeFilter.value = 'all';

    const typeFilter = document.getElementById('filterType');
    if (typeFilter) typeFilter.value = 'all';

    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) sortSelect.value = 'deadline-asc';

    this.activeSkillFilters.clear();
    const chips = document.querySelectorAll('.filter-chip');
    chips.forEach(c => c.classList.remove('active'));

    this.currentPage = 1;
    this.applyFiltersAndRender();
    App.showToast("Filters reset to default view.", "info");
  },

  applyFiltersAndRender() {
    const search = (document.getElementById('searchQuery')?.value || '').toLowerCase().trim();
    const location = document.getElementById('filterLocation')?.value || 'all';
    const mode = document.getElementById('filterMode')?.value || 'all';
    const type = document.getElementById('filterType')?.value || 'all';
    const sortBy = document.getElementById('sortBy')?.value || 'deadline-asc';

    this.filteredOpportunities = this.allOpportunities.filter(opp => {
      // Search by title or company
      const matchesSearch = !search ||
        opp.title.toLowerCase().includes(search) ||
        opp.company.toLowerCase().includes(search) ||
        opp.category.toLowerCase().includes(search);

      // Location filter
      const matchesLocation = location === 'all' ||
        opp.location.toLowerCase().includes(location.toLowerCase());

      // Mode filter
      const matchesMode = mode === 'all' || opp.workMode === mode;

      // Type filter
      const matchesType = type === 'all' || opp.type === type;

      // Skills filter (must match at least one selected skill if any selected)
      let matchesSkills = true;
      if (this.activeSkillFilters.size > 0) {
        matchesSkills = Array.from(this.activeSkillFilters).some(skill =>
          opp.skills.includes(skill)
        );
      }

      return matchesSearch && matchesLocation && matchesMode && matchesType && matchesSkills;
    });

    // Sorting
    this.filteredOpportunities.sort((a, b) => {
      if (sortBy === 'deadline-asc') {
        return new Date(a.deadline) - new Date(b.deadline);
      } else if (sortBy === 'deadline-desc') {
        return new Date(b.deadline) - new Date(a.deadline);
      } else if (sortBy === 'stipend-desc') {
        return (b.stipendNumeric || 0) - (a.stipendNumeric || 0);
      } else if (sortBy === 'title-asc') {
        return a.title.localeCompare(b.title);
      }
      return 0;
    });

    // Update count labels
    const countDisplay = document.getElementById('opportunitiesCountDisplay');
    if (countDisplay) {
      countDisplay.textContent = `Showing ${this.filteredOpportunities.length} ${this.filteredOpportunities.length === 1 ? 'opportunity' : 'opportunities'}`;
    }

    this.renderCards(false);
  },

  renderCards(append = false) {
    const container = document.getElementById('opportunitiesCardsContainer');
    const emptyState = document.getElementById('opportunitiesEmptyState');
    const loadMoreContainer = document.getElementById('loadMoreContainer');

    if (!container) return;

    if (this.filteredOpportunities.length === 0) {
      container.innerHTML = '';
      if (emptyState) emptyState.style.display = 'block';
      if (loadMoreContainer) loadMoreContainer.style.display = 'none';
      return;
    }

    if (emptyState) emptyState.style.display = 'none';

    // Slice for pagination
    const totalToShow = this.currentPage * this.pageSize;
    const itemsToRender = this.filteredOpportunities.slice(0, totalToShow);

    // Render HTML
    container.innerHTML = itemsToRender.map(opp => this.createCardHTML(opp)).join('');

    // Toggle Load More button
    if (loadMoreContainer) {
      if (totalToShow >= this.filteredOpportunities.length) {
        loadMoreContainer.style.display = 'none';
      } else {
        loadMoreContainer.style.display = 'block';
      }
    }

    this.bindCardActions();
  },

  createCardHTML(opp) {
    const isSaved = Storage.isBookmarked(opp.id);
    const hasApplied = Storage.hasApplied(opp.id);
    const daysLeft = App.getDaysRemaining(opp.deadline);

    let deadlineBadge = '';
    if (daysLeft < 0) {
      deadlineBadge = `<span class="deadline-pill urgent">Closed</span>`;
    } else if (daysLeft <= 10) {
      deadlineBadge = `<span class="deadline-pill urgent"><i class="fa-solid fa-clock"></i> ${daysLeft} days left</span>`;
    } else {
      deadlineBadge = `<span class="deadline-pill safe"><i class="fa-regular fa-calendar"></i> ${daysLeft} days left</span>`;
    }

    return `
      <article class="opportunity-card" data-id="${opp.id}">
        <div class="opp-card-header">
          <div class="opp-company-meta">
            <div class="company-logo-badge" style="background-color: ${opp.logoColor};">
              ${opp.logoText}
            </div>
            <div>
              <span class="opp-company-name">${App.escapeHTML(opp.company)}</span>
              <div class="opp-location"><i class="fa-solid fa-location-dot"></i> ${App.escapeHTML(opp.location)} &bull; ${App.escapeHTML(opp.workMode)}</div>
            </div>
          </div>
          <button type="button" class="opp-bookmark-btn ${isSaved ? 'saved' : ''}" data-id="${opp.id}" title="${isSaved ? 'Remove bookmark' : 'Bookmark opportunity'}">
            <i class="${isSaved ? 'fa-solid' : 'fa-regular'} fa-bookmark"></i>
          </button>
        </div>

        <h3 class="opp-title">
          <a href="opportunity-details.html?id=${opp.id}">${App.escapeHTML(opp.title)}</a>
        </h3>

        <div class="opp-badges-row">
          <span class="badge badge-primary">${App.escapeHTML(opp.type)}</span>
          <span class="badge badge-secondary">${App.escapeHTML(opp.category)}</span>
          ${deadlineBadge}
        </div>

        <p class="opp-desc">${App.escapeHTML(opp.shortDesc)}</p>

        <div class="opp-skills-list">
          ${opp.skills.map(s => `<span class="skill-tag">${App.escapeHTML(s)}</span>`).join('')}
        </div>

        <div class="opp-meta-footer">
          <div class="opp-stipend-box">
            <strong>${App.escapeHTML(opp.stipend)}</strong>
            <small>Min CGPA: ${opp.minCGPA}</small>
          </div>
          <div class="opp-card-actions">
            <a href="opportunity-details.html?id=${opp.id}" class="btn btn-sm btn-outline-secondary">Details</a>
            ${hasApplied ? `
              <span class="badge badge-success"><i class="fa-solid fa-check"></i> Applied</span>
            ` : `
              <button type="button" class="btn btn-sm btn-primary opp-apply-btn" data-id="${opp.id}">
                Apply
              </button>
            `}
          </div>
        </div>
      </article>
    `;
  },

  bindCardActions() {
    // Bookmark buttons
    const bookmarkBtns = document.querySelectorAll('.opp-bookmark-btn');
    bookmarkBtns.forEach(btn => {
      btn.onclick = (e) => {
        e.preventDefault();
        const id = btn.getAttribute('data-id');
        const nowSaved = Storage.toggleBookmark(id);
        
        btn.classList.toggle('saved', nowSaved);
        const icon = btn.querySelector('i');
        if (nowSaved) {
          icon.className = 'fa-solid fa-bookmark';
          App.showToast("Opportunity saved to bookmarks.", "success");
        } else {
          icon.className = 'fa-regular fa-bookmark';
          App.showToast("Opportunity removed from bookmarks.", "info");
        }

        // If on saved-opportunities page, reload data
        if (this.isSavedPage) {
          this.loadData();
          this.applyFiltersAndRender();
        }
      };
    });

    // Apply buttons
    const applyBtns = document.querySelectorAll('.opp-apply-btn');
    applyBtns.forEach(btn => {
      btn.onclick = (e) => {
        e.preventDefault();
        const id = btn.getAttribute('data-id');
        App.openApplyModal(id, () => {
          this.applyFiltersAndRender();
        });
      };
    });
  }
};
