/**
 * Campus CareerTrack - Student Profile Controller
 * Manages profile form validation, interactive skills tag editor,
 * resume filename mock picker, and profile completeness calculation.
 */

const ProfileController = {
  currentSkills: [],

  init() {
    this.loadProfile();
    this.setupListeners();
    this.updateCompletenessMeter();
  },

  loadProfile() {
    const profile = Storage.getProfile();

    document.getElementById('profileFullName').value = profile.fullName || '';
    document.getElementById('profileEmail').value = profile.email || '';
    document.getElementById('profilePhone').value = profile.phone || '';
    document.getElementById('profileRegisterNo').value = profile.registerNo || '';
    document.getElementById('profileCollege').value = profile.college || '';
    document.getElementById('profileDegree').value = profile.degree || 'B.E.';
    document.getElementById('profileDepartment').value = profile.department || '';
    document.getElementById('profileGradYear').value = profile.gradYear || '';
    document.getElementById('profileCGPA').value = profile.cgpa || '';
    document.getElementById('profileBacklogs').value = profile.activeBacklogs ?? 0;
    document.getElementById('profilePreferredRoles').value = (profile.preferredRoles || []).join(', ');
    document.getElementById('profilePreferredLocation').value = profile.preferredLocation || '';
    document.getElementById('profileLinkedIn').value = profile.linkedin || '';
    document.getElementById('profileGitHub').value = profile.github || '';
    document.getElementById('profileBio').value = profile.bio || '';
    
    // Resume file display
    const resumeEl = document.getElementById('currentResumeFilename');
    if (resumeEl) {
      resumeEl.textContent = profile.resumeFilename || 'No resume selected';
    }

    // Skills
    this.currentSkills = [...(profile.skills || [])];
    this.renderSkillsTags();

    // Overview Header Card
    const headerName = document.getElementById('profileHeaderName');
    const headerDept = document.getElementById('profileHeaderDept');
    const headerReg = document.getElementById('profileHeaderReg');
    const headerAvatar = document.getElementById('profileHeaderAvatar');

    if (headerName) headerName.textContent = profile.fullName || 'Student';
    if (headerDept) headerDept.textContent = `${profile.degree || 'B.E.'} in ${profile.department || 'CSE'}`;
    if (headerReg) headerReg.textContent = `Reg. No: ${profile.registerNo || 'N/A'}`;
    if (headerAvatar) {
      const initials = profile.fullName
        ? profile.fullName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
        : 'ST';
      headerAvatar.textContent = initials;
    }
  },

  setupListeners() {
    // Form submit
    const form = document.getElementById('studentProfileForm');
    if (form) {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        this.saveProfile();
      });
    }

    // Skills Tag Input
    const skillInput = document.getElementById('newSkillInput');
    const addSkillBtn = document.getElementById('addSkillBtn');

    if (skillInput) {
      skillInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          this.addSkillFromInput();
        }
      });
    }

    if (addSkillBtn) {
      addSkillBtn.addEventListener('click', (e) => {
        e.preventDefault();
        this.addSkillFromInput();
      });
    }

    // Mock Resume File Upload Listener
    const resumeFileInput = document.getElementById('resumeFileInput');
    if (resumeFileInput) {
      resumeFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
          const resumeEl = document.getElementById('currentResumeFilename');
          if (resumeEl) {
            resumeEl.textContent = file.name;
          }
          App.showToast(`Resume file '${file.name}' selected for profile attachment.`, "info");
          this.updateCompletenessMeter();
        }
      });
    }

    // Realtime completeness update on input changes
    const inputs = document.querySelectorAll('#studentProfileForm input, #studentProfileForm select, #studentProfileForm textarea');
    inputs.forEach(input => {
      input.addEventListener('input', () => {
        this.updateCompletenessMeter();
      });
    });
  },

  renderSkillsTags() {
    const container = document.getElementById('skillsTagsContainer');
    if (!container) return;

    container.innerHTML = this.currentSkills.map((skill, index) => `
      <span class="tag-badge">
        ${App.escapeHTML(skill)}
        <button type="button" class="remove-tag-btn" data-index="${index}" title="Remove ${skill}">&times;</button>
      </span>
    `).join('');

    container.querySelectorAll('.remove-tag-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const idx = parseInt(btn.getAttribute('data-index'), 10);
        this.currentSkills.splice(idx, 1);
        this.renderSkillsTags();
        this.updateCompletenessMeter();
      });
    });
  },

  addSkillFromInput() {
    const skillInput = document.getElementById('newSkillInput');
    if (!skillInput) return;

    const val = skillInput.value.trim();
    if (!val) return;

    if (!this.currentSkills.includes(val)) {
      this.currentSkills.push(val);
      this.renderSkillsTags();
      skillInput.value = '';
      this.updateCompletenessMeter();
    } else {
      App.showToast(`'${val}' is already in your skills list.`, "warning");
    }
  },

  calculateCompleteness() {
    const fields = [
      document.getElementById('profileFullName')?.value.trim(),
      document.getElementById('profileEmail')?.value.trim(),
      document.getElementById('profilePhone')?.value.trim(),
      document.getElementById('profileRegisterNo')?.value.trim(),
      document.getElementById('profileCollege')?.value.trim(),
      document.getElementById('profileDegree')?.value.trim(),
      document.getElementById('profileDepartment')?.value.trim(),
      document.getElementById('profileGradYear')?.value.trim(),
      document.getElementById('profileCGPA')?.value.trim(),
      document.getElementById('profilePreferredRoles')?.value.trim(),
      document.getElementById('profilePreferredLocation')?.value.trim(),
      document.getElementById('profileLinkedIn')?.value.trim(),
      document.getElementById('profileGitHub')?.value.trim(),
      document.getElementById('currentResumeFilename')?.textContent.trim() !== 'No resume selected',
      this.currentSkills.length > 0
    ];

    const filledCount = fields.filter(Boolean).length;
    return Math.round((filledCount / fields.length) * 100);
  },

  updateCompletenessMeter() {
    const percentage = this.calculateCompleteness();
    const meter = document.getElementById('completenessPercentage');
    const bar = document.getElementById('completenessProgressBar');

    if (meter) meter.textContent = `${percentage}%`;
    if (bar) bar.style.width = `${percentage}%`;
  },

  saveProfile() {
    const fullName = document.getElementById('profileFullName')?.value.trim();
    const email = document.getElementById('profileEmail')?.value.trim();
    const phone = document.getElementById('profilePhone')?.value.trim();
    const registerNo = document.getElementById('profileRegisterNo')?.value.trim();
    const college = document.getElementById('profileCollege')?.value.trim();
    const degree = document.getElementById('profileDegree')?.value.trim();
    const department = document.getElementById('profileDepartment')?.value.trim();
    const gradYear = document.getElementById('profileGradYear')?.value.trim();
    const cgpaRaw = document.getElementById('profileCGPA')?.value.trim();
    const backlogs = parseInt(document.getElementById('profileBacklogs')?.value || '0', 10);
    const preferredRolesRaw = document.getElementById('profilePreferredRoles')?.value.trim();
    const preferredLocation = document.getElementById('profilePreferredLocation')?.value.trim();
    const linkedin = document.getElementById('profileLinkedIn')?.value.trim();
    const github = document.getElementById('profileGitHub')?.value.trim();
    const bio = document.getElementById('profileBio')?.value.trim();
    const resumeFilename = document.getElementById('currentResumeFilename')?.textContent.trim();

    // Validation
    if (!fullName || !email || !phone || !registerNo) {
      App.showToast("Please complete all required identity and contact fields.", "error");
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      App.showToast("Please enter a valid email address.", "error");
      return;
    }

    const cgpa = parseFloat(cgpaRaw);
    if (isNaN(cgpa) || cgpa < 0 || cgpa > 10) {
      App.showToast("CGPA must be a valid number between 0.00 and 10.00.", "error");
      return;
    }

    if (backlogs < 0) {
      App.showToast("Active backlogs cannot be negative.", "error");
      return;
    }

    const preferredRoles = preferredRolesRaw
      ? preferredRolesRaw.split(',').map(r => r.trim()).filter(Boolean)
      : [];

    const updatedProfile = {
      fullName,
      email,
      phone,
      registerNo,
      college,
      degree,
      department,
      gradYear,
      cgpa,
      activeBacklogs: backlogs,
      skills: this.currentSkills,
      preferredRoles,
      preferredLocation,
      linkedin,
      github,
      bio,
      resumeFilename: resumeFilename !== 'No resume selected' ? resumeFilename : 'Student_Resume.pdf'
    };

    Storage.saveProfile(updatedProfile);
    App.updateTopbarUser();
    this.loadProfile();
    this.updateCompletenessMeter();

    App.showToast("Student profile successfully saved to local storage!", "success");
  }
};
