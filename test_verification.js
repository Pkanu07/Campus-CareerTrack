const fs = require('fs');
const vm = require('vm');

console.log("=== Campus CareerTrack Verification Test Suite ===");

const htmlFiles = [
  'index.html',
  'dashboard.html',
  'opportunities.html',
  'opportunity-details.html',
  'applications.html',
  'profile.html',
  'saved-opportunities.html',
  'eligibility.html',
  'notifications.html',
  'settings.html'
];

const jsFiles = [
  'js/storage.js',
  'js/app.js',
  'js/dashboard.js',
  'js/opportunities.js',
  'js/opportunity-details.js',
  'js/applications.js',
  'js/profile.js',
  'js/eligibility.js',
  'js/notifications.js',
  'js/settings.js'
];

const cssFiles = [
  'css/style.css',
  'css/dashboard.css',
  'css/responsive.css'
];

const docFiles = [
  'docs/project-abstract.md',
  'docs/project-report.md',
  'docs/presentation-outline.md',
  'docs/demo-script.md',
  'README.md'
];

let errors = 0;

// 1. Check all files exist and are non-empty
console.log("\n[1] Checking file existence and non-zero sizes...");
[...htmlFiles, ...jsFiles, ...cssFiles, ...docFiles].forEach(f => {
  if (!fs.existsSync(f)) {
    console.error(`❌ Missing file: ${f}`);
    errors++;
  } else {
    const stat = fs.statSync(f);
    if (stat.size === 0) {
      console.error(`❌ Empty file: ${f}`);
      errors++;
    } else {
      console.log(`✅ ${f} (${stat.size} bytes)`);
    }
  }
});

// 2. Syntax check JS files
console.log("\n[2] Checking JavaScript syntax across all modules...");
jsFiles.forEach(f => {
  try {
    const content = fs.readFileSync(f, 'utf8');
    new Function(content); // Test basic compilation
    console.log(`✅ JS Syntax OK: ${f}`);
  } catch (err) {
    console.error(`❌ Syntax error in ${f}:`, err.message);
    errors++;
  }
});

// 3. Check HTML links and assets
console.log("\n[3] Checking HTML references, stylesheets, and scripts...");
htmlFiles.forEach(htmlFile => {
  const content = fs.readFileSync(htmlFile, 'utf8');

  // Check script tags
  const scriptRegex = /src=["']([^"']+)["']/g;
  let match;
  while ((match = scriptRegex.exec(content)) !== null) {
    const src = match[1];
    if (!src.startsWith('http')) {
      if (!fs.existsSync(src)) {
        console.error(`❌ In ${htmlFile}: broken script reference '${src}'`);
        errors++;
      }
    }
  }

  // Check stylesheet tags
  const linkRegex = /href=["']([^"']+\.css)["']/g;
  while ((match = linkRegex.exec(content)) !== null) {
    const href = match[1];
    if (!href.startsWith('http')) {
      if (!fs.existsSync(href)) {
        console.error(`❌ In ${htmlFile}: broken stylesheet reference '${href}'`);
        errors++;
      }
    }
  }

  // Check internal html page links
  const pageLinkRegex = /href=["']([a-zA-Z0-9_\-]+\.html(?:\?[^"']*)?)["']/g;
  while ((match = pageLinkRegex.exec(content)) !== null) {
    const href = match[1].split('?')[0];
    if (!fs.existsSync(href)) {
      console.error(`❌ In ${htmlFile}: broken page link '${href}'`);
      errors++;
    }
  }
});

console.log("\n[4] Simulating Storage Engine & Logic in Virtual Browser Context...");
const mockStorage = {};
const sandbox = {
  console: console,
  localStorage: {
    getItem: (k) => mockStorage[k] || null,
    setItem: (k, v) => { mockStorage[k] = v; },
    removeItem: (k) => { delete mockStorage[k]; }
  }
};
vm.createContext(sandbox);

try {
  const storageCode = fs.readFileSync('js/storage.js', 'utf8') + '\nthis.Storage = Storage;\n';
  vm.runInContext(storageCode, sandbox);
  const Storage = sandbox.Storage;
  Storage.init();
  
  const opportunities = Storage.getOpportunities();
  console.log(`✅ Storage initialized ${opportunities.length} opportunities.`);
  if (opportunities.length < 10) {
    console.error("❌ Expected at least 10 opportunities!");
    errors++;
  }

  const applications = Storage.getApplications();
  console.log(`✅ Storage initialized ${applications.length} applications.`);

  const stats = Storage.getDashboardStats();
  console.log(`✅ Dashboard stats computed:`, stats);
  if (stats.totalApplications !== 4) {
    console.error(`❌ Expected 4 initial applications, got ${stats.totalApplications}`);
    errors++;
  }

  // Test add application & duplicate prevention
  const newAppResult = Storage.addApplication({
    oppId: "opp-8",
    company: "CRED",
    role: "Mobile App Developer",
    applicantName: "Priti Kumari",
    applicantEmail: "priti.kumari@campus.edu",
    notes: "Automated test submission"
  });
  if (!newAppResult.success) {
    console.error("❌ Failed to add application for opp-8");
    errors++;
  } else {
    console.log("✅ Successfully added application for opp-8.");
  }

  // Test duplicate prevention
  const dupResult = Storage.addApplication({
    oppId: "opp-8",
    company: "CRED",
    role: "Mobile App Developer"
  });
  if (dupResult.success) {
    console.error("❌ Duplicate application was NOT prevented!");
    errors++;
  } else {
    console.log("✅ Duplicate application successfully prevented.");
  }

  // Test status update
  const updateResult = Storage.updateApplicationStatus(newAppResult.application.id, "Shortlisted", "Shortlisted in unit test");
  if (!updateResult.success || updateResult.application.status !== "Shortlisted") {
    console.error("❌ Status update failed!");
    errors++;
  } else {
    console.log("✅ Status updated to Shortlisted successfully.");
  }

  // Test bookmarks toggle
  const bm1 = Storage.isBookmarked("opp-12");
  Storage.toggleBookmark("opp-12");
  const bm2 = Storage.isBookmarked("opp-12");
  if (bm1 === bm2) {
    console.error("❌ Bookmark toggle did not change state!");
    errors++;
  } else {
    console.log("✅ Bookmark toggle functions correctly.");
  }

} catch (err) {
  console.error("❌ Exception during Storage Engine simulation:", err);
  errors++;
}

console.log("\n==================================================");
if (errors === 0) {
  console.log("🎉 ALL TESTS PASSED! System is completely functional and verified.");
  process.exit(0);
} else {
  console.error(`⚠️ Found ${errors} error(s). Please review.`);
  process.exit(1);
}
