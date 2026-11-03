/**
 * Campus CareerTrack - Settings Controller
 * Manages theme selection, demo data resets, data export, and notification toggles.
 */

const SettingsController = {
  init() {
    this.loadCurrentSettings();
    this.setupListeners();
  },

  loadCurrentSettings() {
    const settings = Storage.getSettings();
    const currentTheme = Storage.getTheme();

    // Theme options
    document.querySelectorAll('.theme-card-option').forEach(card => {
      const themeVal = card.getAttribute('data-theme-val');
      card.classList.toggle('active', themeVal === currentTheme);
    });

    // Toggles
    const emailToggle = document.getElementById('settingEmailAlerts');
    const deadlineToggle = document.getElementById('settingDeadlineAlerts');
    const interviewToggle = document.getElementById('settingInterviewAlerts');

    if (emailToggle) emailToggle.checked = !!settings.emailAlerts;
    if (deadlineToggle) deadlineToggle.checked = !!settings.deadlineReminders;
    if (interviewToggle) interviewToggle.checked = !!settings.interviewAlerts;
  },

  setupListeners() {
    // Theme options
    document.querySelectorAll('.theme-card-option').forEach(card => {
      card.addEventListener('click', () => {
        const targetTheme = card.getAttribute('data-theme-val');
        Storage.setTheme(targetTheme);
        App.applyTheme(targetTheme);
        this.loadCurrentSettings();
        App.showToast(`Applied ${targetTheme === 'dark' ? 'Dark' : 'Light'} theme.`, "success");
      });
    });

    // Setting check changes
    const toggles = [
      { id: 'settingEmailAlerts', key: 'emailAlerts' },
      { id: 'settingDeadlineAlerts', key: 'deadlineReminders' },
      { id: 'settingInterviewAlerts', key: 'interviewAlerts' }
    ];

    toggles.forEach(t => {
      const el = document.getElementById(t.id);
      if (el) {
        el.addEventListener('change', (e) => {
          const update = {};
          update[t.key] = e.target.checked;
          Storage.saveSettings(update);
          App.showToast("Preference saved locally.", "info");
        });
      }
    });

    // Reset Demo Data
    const resetBtn = document.getElementById('resetDemoDataBtn');
    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        App.showModal({
          title: "Reset Application to Default Sample Data?",
          message: "This will restore the 12 original sample opportunities, 4 demo applications, 3 bookmarks, default student profile, and notifications. Any changes made during testing will be reset.",
          confirmText: "Yes, Reset Demo Data",
          cancelText: "Cancel",
          variant: "danger",
          onConfirm: () => {
            Storage.resetToDefaultData(true);
            this.loadCurrentSettings();
            App.updateTopbarUser();
            App.updateNotificationBadge();
          }
        });
      });
    }

    // Clear All Data
    const clearBtn = document.getElementById('clearAllDataBtn');
    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        App.showModal({
          title: "Clear All Stored Application Data?",
          message: "This will completely erase all local storage records (profile, bookmarks, applications, notifications). The application will start in a clean blank state.",
          confirmText: "Clear All Data",
          cancelText: "Cancel",
          variant: "danger",
          onConfirm: () => {
            Storage.clearAllData();
            App.showToast("All local records cleared.", "warning");
            setTimeout(() => {
              window.location.reload();
            }, 600);
          }
        });
      });
    }

    // Export JSON Backup
    const exportBtn = document.getElementById('exportDataJsonBtn');
    if (exportBtn) {
      exportBtn.addEventListener('click', () => {
        const fullBackup = {
          exportTimestamp: new Date().toISOString(),
          system: "Campus CareerTrack v1.0.0",
          profile: Storage.getProfile(),
          opportunities: Storage.getOpportunities(),
          applications: Storage.getApplications(),
          bookmarks: Storage.getBookmarks(),
          notifications: Storage.getNotifications(),
          settings: Storage.getSettings()
        };

        const jsonStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(fullBackup, null, 2));
        const downloadAnchor = document.createElement('a');
        downloadAnchor.setAttribute("href", jsonStr);
        downloadAnchor.setAttribute("download", `Campus_CareerTrack_Backup_${new Date().toISOString().split('T')[0]}.json`);
        document.body.appendChild(downloadAnchor);
        downloadAnchor.click();
        downloadAnchor.remove();

        App.showToast("Complete demo state JSON backup downloaded successfully.", "success");
      });
    }
  }
};
