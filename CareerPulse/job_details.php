<?php
/**
 * CareerPulse - Job Detail & Application View
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$jobId = (int)($_GET['id'] ?? 0);
if ($jobId <= 0) {
    header('Location: ' . baseUrl('jobs.php'));
    exit;
}

// Fetch job with category & employer profile
$job = dbFetchOne("
    SELECT j.*, c.name as category_name, ep.company_name, ep.website as company_website, ep.industry, ep.company_size, ep.location as company_headquarters, ep.about as company_about, ep.logo_url
    FROM jobs j
    JOIN categories c ON j.category_id = c.id
    LEFT JOIN employer_profiles ep ON ep.user_id = j.employer_id
    WHERE j.id = ?
", [$jobId]);

if (!$job) {
    setFlash('error', 'Job posting not found or has been closed.');
    header('Location: ' . baseUrl('jobs.php'));
    exit;
}

$currentUser = currentUser();
$hasApplied = null;
$isSaved = false;

if ($currentUser && $currentUser['role'] === 'candidate') {
    $hasApplied = dbFetchOne("SELECT * FROM applications WHERE job_id = ? AND candidate_id = ?", [$jobId, $currentUser['id']]);
    $savedRecord = dbFetchOne("SELECT id FROM saved_jobs WHERE job_id = ? AND user_id = ?", [$jobId, $currentUser['id']]);
    $isSaved = !empty($savedRecord);
}

// Handle Bookmark Toggle
if (isPost() && isset($_POST['toggle_save']) && $currentUser) {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security token expired. Please try again.');
    } else {
        if ($isSaved) {
            dbExecute("DELETE FROM saved_jobs WHERE job_id = ? AND user_id = ?", [$jobId, $currentUser['id']]);
            setFlash('info', 'Job removed from your saved bookmarks.');
            $isSaved = false;
        } else {
            dbInsert("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)", [$currentUser['id'], $jobId]);
            setFlash('success', 'Job saved to your bookmarks.');
            $isSaved = true;
        }
    }
}

$pageTitle = $job['title'] . ' at ' . ($job['company_name'] ?? 'Company');
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-6">
        <a href="<?= baseUrl('index.php') ?>" class="hover:text-brand-600">Home</a>
        <span>/</span>
        <a href="<?= baseUrl('jobs.php') ?>" class="hover:text-brand-600">Jobs</a>
        <span>/</span>
        <span class="text-slate-800 font-semibold truncate"><?= e($job['title']) ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Job Details Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Hero Card -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-600 to-indigo-600 text-white font-extrabold flex items-center justify-center text-2xl shadow-md shadow-brand-500/20 flex-shrink-0">
                            <?= strtoupper(substr($job['company_name'] ?? 'CP', 0, 2)) ?>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-brand-600"><?= e($job['category_name']) ?></span>
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-0.5">
                                <?= e($job['title']) ?>
                            </h1>
                            <p class="text-sm font-semibold text-slate-600 mt-1"><?= e($job['company_name'] ?? 'Confidential') ?></p>
                        </div>
                    </div>

                    <!-- Bookmark Button -->
                    <?php if ($currentUser && $currentUser['role'] === 'candidate'): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="toggle_save" value="1">
                            <button type="submit" class="p-2.5 rounded-xl border <?= $isSaved ? 'bg-amber-50 border-amber-300 text-amber-600' : 'bg-slate-50 border-slate-200 text-slate-400 hover:text-slate-700' ?> transition shadow-sm" title="<?= $isSaved ? 'Unsave job' : 'Save job' ?>">
                                <i data-lucide="bookmark" class="w-5 h-5 <?= $isSaved ? 'fill-amber-500 text-amber-500' : '' ?>"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Attributes Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-slate-100">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Location</span>
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-1 mt-0.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                            <?= e($job['location']) ?>
                        </span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Job Type</span>
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-1 mt-0.5">
                            <i data-lucide="briefcase" class="w-3.5 h-3.5 text-slate-400"></i>
                            <?= ucfirst(e($job['job_type'])) ?>
                        </span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Experience</span>
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-1 mt-0.5">
                            <i data-lucide="trending-up" class="w-3.5 h-3.5 text-slate-400"></i>
                            <?= ucfirst(e($job['experience_level'])) ?> Level
                        </span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Salary Range</span>
                        <span class="text-sm font-bold text-emerald-700 flex items-center gap-1 mt-0.5">
                            <?= formatSalary($job['salary_min'], $job['salary_max'], $job['salary_currency']) ?>
                        </span>
                    </div>
                </div>

                <!-- Application Status Alert (if already applied) -->
                <?php if ($hasApplied): ?>
                    <?php $b = getStatusBadge($hasApplied['status']); ?>
                    <div class="mt-6 p-4 rounded-xl border <?= $b['bg'] ?> flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full <?= $b['dot'] ?>"></span>
                            <span class="font-bold text-sm">You applied on <?= date('M j, Y', strtotime($hasApplied['applied_at'])) ?>: <span class="uppercase tracking-wider text-xs ml-1"><?= $b['label'] ?></span></span>
                        </div>
                        <a href="<?= baseUrl('candidate/dashboard.php') ?>" class="text-xs font-bold underline hover:opacity-80">
                            Track in Dashboard &rarr;
                        </a>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Description Body -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 mb-3 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-brand-600"></i>
                        About the Position
                    </h2>
                    <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                        <?= e($job['description']) ?>
                    </div>
                </div>

                <?php if (!empty($job['requirements'])): ?>
                    <div class="pt-6 border-t border-slate-100">
                        <h2 class="text-lg font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="check-square" class="w-5 h-5 text-emerald-600"></i>
                            Key Requirements &amp; Qualifications
                        </h2>
                        <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            <?= e($job['requirements']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($job['benefits'])): ?>
                    <div class="pt-6 border-t border-slate-100">
                        <h2 class="text-lg font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="gift" class="w-5 h-5 text-purple-600"></i>
                            Compensation &amp; Benefits
                        </h2>
                        <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            <?= e($job['benefits']) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Right: Apply Sidebar & Company Info -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Quick Apply Box -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-sm sticky top-24">
                <h3 class="text-base font-bold text-slate-900 mb-2">Ready to apply?</h3>
                <p class="text-xs text-slate-500 mb-5 leading-relaxed">Submit your application directly to the hiring managers at <?= e($job['company_name'] ?? 'the company') ?>.</p>

                <?php if ($hasApplied): ?>
                    <button disabled class="w-full py-3 px-4 bg-slate-100 text-slate-400 font-semibold text-sm rounded-xl cursor-not-allowed text-center">
                        Application Submitted
                    </button>
                <?php elseif (!$currentUser): ?>
                    <a href="<?= baseUrl('auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'])) ?>" class="block w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl text-center shadow-md shadow-brand-500/20 transition">
                        Sign In to Apply
                    </a>
                <?php elseif ($currentUser['role'] !== 'candidate'): ?>
                    <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs">
                        You are signed in as an <strong><?= e($currentUser['role']) ?></strong>. Sign in as a candidate to submit applications.
                    </div>
                <?php else: ?>
                    <button onclick="document.getElementById('applyModal').classList.remove('hidden')" class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl text-center shadow-md shadow-brand-500/20 transition flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Apply for this Job
                    </button>
                <?php endif; ?>

                <div class="mt-5 pt-4 border-t border-slate-100 space-y-2.5 text-xs text-slate-500">
                    <div class="flex items-center justify-between">
                        <span>Application Deadline:</span>
                        <strong class="text-slate-800"><?= $job['deadline'] ? date('M j, Y', strtotime($job['deadline'])) : 'Open until filled' ?></strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Posted Date:</span>
                        <strong class="text-slate-800"><?= date('M j, Y', strtotime($job['created_at'])) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Company Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Company Overview</h3>
                <h4 class="text-lg font-bold text-slate-900"><?= e($job['company_name'] ?? 'Company') ?></h4>
                
                <?php if (!empty($job['company_about'])): ?>
                    <p class="text-xs text-slate-600 leading-relaxed"><?= e($job['company_about']) ?></p>
                <?php endif; ?>

                <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                    <?php if (!empty($job['industry'])): ?>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Industry:</span>
                            <span class="font-semibold text-slate-800"><?= e($job['industry']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($job['company_size'])): ?>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Company Size:</span>
                            <span class="font-semibold text-slate-800"><?= e($job['company_size']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($job['company_headquarters'])): ?>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Headquarters:</span>
                            <span class="font-semibold text-slate-800"><?= e($job['company_headquarters']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($job['company_website'])): ?>
                        <div class="pt-2">
                            <a href="<?= e($job['company_website']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-brand-600 hover:underline font-semibold">
                                Visit Website <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Application Modal -->
<?php if ($currentUser && $currentUser['role'] === 'candidate' && !$hasApplied): ?>
<div id="applyModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in duration-150">
        
        <button onclick="document.getElementById('applyModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 p-1">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <h3 class="text-xl font-bold text-slate-900 tracking-tight">Apply for <?= e($job['title']) ?></h3>
        <p class="text-xs text-slate-500 mt-1"><?= e($job['company_name'] ?? 'Company') ?></p>

        <form action="<?= baseUrl('apply.php') ?>" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Resume / CV (PDF or DOCX, max 5MB)</label>
                <input type="file" name="resume" required accept=".pdf,.doc,.docx" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer border border-slate-200 rounded-xl bg-slate-50">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Cover Letter or Note to Recruiter (Optional)</label>
                <textarea name="cover_note" rows="4" placeholder="Briefly describe why you are a great fit for this role..." class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:bg-white outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="document.getElementById('applyModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition">
                    Submit Application
                </button>
            </div>
        </form>

    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
