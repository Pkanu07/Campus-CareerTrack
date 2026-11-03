/**
 * Campus CareerTrack - Eligibility Checker Engine
 * Evaluates student academic and skill profiles against specific opportunity requirements.
 * Provides instant feedback, criteria checklists, skill-gap analysis, and direct application CTA.
 */

const EligibilityController = {
  selectedOpp: null,

  init() {
    this.populateOpportunitiesDropdown();
    this.checkUrlParams();
    this.prefillStudentInputs();
    this.setupListeners();
    this.evaluateEligibility();
  },

  populateOpportunitiesDropdown() {
    const select = document.getElementById('eligibilityOppSelect');
    if (!select) return;

    const list = Storage.getOpportunities();
    select.innerHTML = list.map(opp => `
      <option value="${opp.id}">${opp.company} &ndash; ${opp.title} (Min CGPA: ${opp.minCGPA})</option>
    `).join('');
  },

  checkUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const oppId = params.get('id');
    const select = document.getElementById('eligibilityOppSelect');
    if (oppId && select) {
      select.value = oppId;
    }
  },

  prefillStudentInputs() {
    const profile = Storage.getProfile();

    const cgpaInput = document.getElementById('evalCGPA');
    const gradYearInput = document.getElementById('evalGradYear');
    const backlogsInput = document.getElementById('evalBacklogs');
    const branchInput = document.getElementById('evalBranch');
    const skillsInput = document.getElementById('evalSkills');

    if (cgpaInput) cgpaInput.value = profile.cgpa || 8.0;
    if (gradYearInput) gradYearInput.value = profile.gradYear || '2026';
    if (backlogsInput) backlogsInput.value = profile.activeBacklogs ?? 0;
    if (branchInput) branchInput.value = profile.department || 'Computer Science and Engineering';
    if (skillsInput) skillsInput.value = (profile.skills || []).join(', ');
  },

  setupListeners() {
    const oppSelect = document.getElementById('eligibilityOppSelect');
    if (oppSelect) {
      oppSelect.addEventListener('change', () => {
        this.evaluateEligibility();
      });
    }

    const evalForm = document.getElementById('eligibilityEvalForm');
    if (evalForm) {
      evalForm.addEventListener('submit', (e) => {
        e.preventDefault();
        this.evaluateEligibility();
      });
    }

    const resetBtn = document.getElementById('resetToProfileBtn');
    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        this.prefillStudentInputs();
        this.evaluateEligibility();
        App.showToast("Values restored from current student profile.", "info");
      });
    }
  },

  evaluateEligibility() {
    const oppSelect = document.getElementById('eligibilityOppSelect');
    const oppId = oppSelect?.value;
    const opp = Storage.getOpportunityById(oppId);
    if (!opp) return;

    this.selectedOpp = opp;

    // Student parameters
    const studentCGPA = parseFloat(document.getElementById('evalCGPA')?.value || '0');
    const studentGradYear = document.getElementById('evalGradYear')?.value.trim() || '2026';
    const studentBacklogs = parseInt(document.getElementById('evalBacklogs')?.value || '0', 10);
    const studentBranch = document.getElementById('evalBranch')?.value.trim() || '';
    const studentSkillsRaw = document.getElementById('evalSkills')?.value || '';
    const studentSkills = studentSkillsRaw
      .split(',')
      .map(s => s.trim().toLowerCase())
      .filter(Boolean);

    // Evaluation checks
    const cgpaPassed = studentCGPA >= opp.minCGPA;
    const backlogsPassed = studentBacklogs <= opp.maxBacklogs;
    const batchPassed = opp.allowedBatches.includes(studentGradYear);
    
    // Branch check
    const isAllBranches = opp.allowedBranches.some(b => b.toLowerCase().includes('all'));
    const branchPassed = isAllBranches || opp.allowedBranches.some(b => 
      studentBranch.toLowerCase().includes(b.toLowerCase()) || b.toLowerCase().includes(studentBranch.toLowerCase())
    );

    // Skills check
    const matchedSkills = [];
    const missingSkills = [];

    opp.skills.forEach(oppSkill => {
      const lower = oppSkill.toLowerCase();
      const hasSkill = studentSkills.some(s => s === lower || lower.includes(s) || s.includes(lower));
      if (hasSkill) {
        matchedSkills.push(oppSkill);
      } else {
        missingSkills.push(oppSkill);
      }
    });

    const isAcademicallyEligible = cgpaPassed && backlogsPassed && batchPassed && branchPassed;
    const allCriteriaPassed = isAcademicallyEligible && (missingSkills.length <= 1);

    // Update Company Benchmark Card
    const benchmarkContainer = document.getElementById('companyBenchmarkBox');
    if (benchmarkContainer) {
      benchmarkContainer.innerHTML = `
        <div class="criteria-row">
          <span>Minimum Required CGPA:</span>
          <strong>${opp.minCGPA}</strong>
        </div>
        <div class="criteria-row">
          <span>Maximum Active Backlogs:</span>
          <strong>${opp.maxBacklogs}</strong>
        </div>
        <div class="criteria-row">
          <span>Eligible Graduation Batches:</span>
          <strong>${opp.allowedBatches.join(', ')}</strong>
        </div>
        <div class="criteria-row">
          <span>Eligible Disciplines:</span>
          <strong>${opp.allowedBranches.join(', ')}</strong>
        </div>
        <div class="criteria-row" style="align-items: flex-start;">
          <span>Target Core Skills:</span>
          <div style="text-align: right; max-width: 60%;">
            ${opp.skills.map(s => `<span class="skill-tag">${App.escapeHTML(s)}</span> `).join('')}
          </div>
        </div>
      `;
    }

    // Render Results Card
    const resultsContainer = document.getElementById('eligibilityResultBox');
    if (!resultsContainer) return;

    let heroClass = '';
    let heroIcon = '';
    let heroTitle = '';
    let heroSubtitle = '';

    if (isAcademicallyEligible && missingSkills.length === 0) {
      heroClass = 'eligible';
      heroIcon = '<i class="fa-solid fa-circle-check"></i>';
      heroTitle = 'Fully Eligible to Apply!';
      heroSubtitle = `You meet all academic criteria and possess 100% of the required core skills for ${opp.company}.`;
    } else if (isAcademicallyEligible) {
      heroClass = 'eligible';
      heroIcon = '<i class="fa-solid fa-certificate"></i>';
      heroTitle = 'Eligible with Skill Recommendations';
      heroSubtitle = `Academic requirements met. Brush up on recommended skills to maximize interview success.`;
    } else {
      heroClass = 'not-eligible';
      heroIcon = '<i class="fa-solid fa-circle-xmark"></i>';
      heroTitle = 'Does Not Meet Stated Criteria';
      heroSubtitle = `One or more mandatory eligibility requirements are currently not satisfied for this role.`;
    }

    const hasApplied = Storage.hasApplied(opp.id);

    resultsContainer.innerHTML = `
      <div class="result-badge-hero ${heroClass}">
        <div class="result-hero-icon">${heroIcon}</div>
        <div class="result-hero-title">${heroTitle}</div>
        <p style="font-size: 0.9rem; margin: 0; color: inherit; opacity: 0.95;">${heroSubtitle}</p>
      </div>

      <h4 style="font-size: 1rem; margin-bottom: 0.75rem;"><i class="fa-solid fa-list-check"></i> Criteria Verification Breakdown</h4>
      <ul class="result-checklist">
        <li class="${cgpaPassed ? 'passed' : 'failed'}">
          <i class="fa-solid ${cgpaPassed ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
          <div>
            <strong>CGPA Requirement (${studentCGPA} / Min ${opp.minCGPA}):</strong>
            <span>${cgpaPassed ? 'Satisfied' : `Short by ${(opp.minCGPA - studentCGPA).toFixed(2)} CGPA points`}</span>
          </div>
        </li>
        <li class="${backlogsPassed ? 'passed' : 'failed'}">
          <i class="fa-solid ${backlogsPassed ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
          <div>
            <strong>Active Backlogs (${studentBacklogs} / Max ${opp.maxBacklogs}):</strong>
            <span>${backlogsPassed ? 'Satisfied' : `Exceeds permitted maximum limit`}</span>
          </div>
        </li>
        <li class="${batchPassed ? 'passed' : 'failed'}">
          <i class="fa-solid ${batchPassed ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
          <div>
            <strong>Graduation Batch (${studentGradYear}):</strong>
            <span>${batchPassed ? 'Satisfied' : `Target batches: ${opp.allowedBatches.join(', ')}`}</span>
          </div>
        </li>
        <li class="${branchPassed ? 'passed' : 'failed'}">
          <i class="fa-solid ${branchPassed ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
          <div>
            <strong>Discipline / Branch (${studentBranch}):</strong>
            <span>${branchPassed ? 'Eligible Department' : 'Not in preferred list'}</span>
          </div>
        </li>
      </ul>

      <div class="skills-gap-box">
        <h5><i class="fa-solid fa-wand-magic-sparkles text-primary"></i> Skills Assessment</h5>
        <div style="margin-bottom: 0.75rem;">
          <small style="color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Matched Skills (${matchedSkills.length}/${opp.skills.length}):</small>
          ${matchedSkills.length > 0
            ? matchedSkills.map(s => `<span class="badge badge-success" style="margin: 2px;"><i class="fa-solid fa-check"></i> ${App.escapeHTML(s)}</span>`).join('')
            : '<span style="font-size: 0.8rem; color: var(--text-subtle);">None matched yet</span>'
          }
        </div>
        ${missingSkills.length > 0 ? `
          <div>
            <small style="color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Recommended to Learn / Brush Up:</small>
            ${missingSkills.map(s => `<span class="badge badge-warning" style="margin: 2px;"><i class="fa-solid fa-lightbulb"></i> ${App.escapeHTML(s)}</span>`).join('')}
          </div>
        ` : ''}
      </div>

      <div style="margin-top: 1.5rem; display: flex; gap: 0.85rem; flex-wrap: wrap;">
        ${isAcademicallyEligible ? `
          ${hasApplied ? `
            <a href="applications.html" class="btn btn-secondary" style="flex: 1;">
              <i class="fa-solid fa-check"></i> View in Application Tracker
            </a>
          ` : `
            <button type="button" class="btn btn-primary" id="eligibilityApplyBtn" style="flex: 1;">
              <i class="fa-solid fa-paper-plane"></i> Proceed to Apply
            </button>
          `}
        ` : `
          <a href="opportunities.html" class="btn btn-outline-primary" style="flex: 1;">
            <i class="fa-solid fa-magnifying-glass"></i> Find Alternative Opportunities
          </a>
        `}
      </div>
    `;

    const applyBtn = document.getElementById('eligibilityApplyBtn');
    if (applyBtn) {
      applyBtn.onclick = () => {
        App.openApplyModal(opp.id, () => {
          this.evaluateEligibility();
        });
      };
    }
  }
};
