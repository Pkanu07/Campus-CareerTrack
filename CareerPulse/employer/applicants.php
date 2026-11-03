<?php
/**
 * CareerPulse - Candidate Pipeline & Applicant Review
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth('employer');

// Fetch employer's jobs for the job selector filter
$employerJobs = dbFetchAll("SELECT id, title FROM jobs WHERE employer_id = ? ORDER BY created_at DESC", [$user['id']]);

$selectedJobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$statusFilter = trim($_GET['status'] ?? 'all');

// Handle status & note updates from POST
if (isPost() && isset($_POST['update_application'])) {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security token expired.');
    } else {
        $appId = (int)$_POST['application_id'];
        $newStatus = trim($_POST['status'] ?? 'pending');
        $notes = trim($_POST['employer_notes'] ?? '');

        // Verify that this application belongs to a job posted by this employer
        $verify = dbFetchOne("
            SELECT a.id, u.name as cand_name, j.title as job_title
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN users u ON a.candidate_id = u.id
            WHERE a.id = ? AND j.employer_id = ?
        ", [$appId, $user['id']]);

        if ($verify) {
            dbExecute("
                UPDATE applications
                SET status = ?, employer_notes = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ", [$newStatus, $notes, $appId]);

            setFlash('success', "Updated {$verify['cand_name']}'s status to " . strtoupper($newStatus) . "!");
        } else {
            setFlash('error', 'Unauthorized or application not found.');
        }

        $redirectUrl = baseUrl('employer/applicants.php?' . http_build_query([
            'job_id' => $selectedJobId ?: null,
            'status' => $statusFilter !== 'all' ? $statusFilter : null,
        ]));
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Build query
$sql = "
    SELECT a.*, u.name as candidate_name, u.email as candidate_email, u.phone as candidate_phone,
           cp.headline, cp.experience_years, cp.education, cp.skills, cp.portfolio_url, cp.linkedin_url, cp.github_url,
           j.title as job_title, j.id as job_id
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.candidate_id = u.id
    LEFT JOIN candidate_profiles cp ON cp.user_id = u.id
    WHERE j.employer_id = ?
";
$params = [$user['id']];

if ($selectedJobId > 0) {
    $sql .= " AND j.id = ?";
    $params[] = $selectedJobId;
}

if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected'], true)) {
    $sql .= " AND a.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY a.applied_at DESC";
$applicants = dbFetchAll($sql, $params);

$pageTitle = 'Candidate Review Pipeline';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-slate-200">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-purple-600">Applicant Pipeline</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-0.5">
                Talent Review &amp; Hiring Stages
            </h1>
            <p class="text-sm text-slate-500 mt-1">Review applicant qualifications, resumes, schedule interviews, and update statuses</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= baseUrl('employer/dashboard.php') ?>" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                &larr; Dashboard
            </a>
            <a href="<?= baseUrl('employer/post_job.php') ?>" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow transition">
                + Post Job
            </a>
        </div>
    </div>

    <!-- Filter Bar: By Job & Pipeline Stage -->
    <div class="my-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Job selector -->
        <div class="flex items-center gap-3">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-600 whitespace-nowrap">Filter by Job:</label>
            <form method="GET" class="inline-block">
                <?php if ($statusFilter !== 'all'): ?>
                    <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
                <?php endif; ?>
                <select name="job_id" onchange="this.form.submit()" class="px-3 py-2 text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="0">All Posted Jobs (<?= count($employerJobs) ?>)</option>
                    <?php foreach ($employerJobs as $ej): ?>
                        <option value="<?= $ej['id'] ?>" <?= $selectedJobId === (int)$ej['id'] ? 'selected' : '' ?>>
                            <?= e($ej['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Status Pills -->
        <div class="flex items-center gap-1.5 flex-wrap text-xs font-bold">
            <?php
            $stages = [
                'all' => 'All',
                'pending' => 'Pending',
                'under_review' => 'Under Review',
                'shortlisted' => 'Shortlisted',
                'interview' => 'Interview',
                'accepted' => 'Accepted',
                'rejected' => 'Declined',
            ];
            foreach ($stages as $key => $label):
                $active = ($statusFilter === $key);
                $query = ['status' => $key];
                if ($selectedJobId > 0) $query['job_id'] = $selectedJobId;
            ?>
                <a href="<?= baseUrl('employer/applicants.php?' . http_build_query($query)) ?>" class="px-3 py-1.5 rounded-lg transition <?= $active ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- Applicant Cards / Pipeline -->
    <?php if (empty($applicants)): ?>
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-base">No candidates found in this stage</h3>
            <p class="text-xs text-slate-500 mt-1 mb-4">Try selecting "All" or choosing another job posting.</p>
            <a href="<?= baseUrl('employer/applicants.php') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-sm">
                Reset Filters
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($applicants as $app): ?>
                <?php $badge = getStatusBadge($app['status']); ?>
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 hover:shadow-md transition">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                        
                        <!-- Candidate Identity & Headline -->
                        <div class="space-y-3 flex-1">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-brand-600 to-purple-600 text-white font-extrabold flex items-center justify-center text-lg shadow-sm flex-shrink-0">
                                    <?= strtoupper(substr($app['candidate_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-bold text-slate-900 text-lg"><?= e($app['candidate_name']) ?></h3>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border <?= $badge['bg'] ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $badge['dot'] ?>"></span>
                                            <?= $badge['label'] ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-brand-600 font-semibold mt-0.5">
                                        Applied for: <a href="<?= baseUrl('job_details.php?id=' . $app['job_id']) ?>" class="hover:underline font-bold"><?= e($app['job_title']) ?></a>
                                    </p>
                                </div>
                            </div>

                            <?php if (!empty($app['headline'])): ?>
                                <p class="text-xs font-medium text-slate-700 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                                    <?= e($app['headline']) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Contact info & Portfolio Links -->
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-1">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <a href="mailto:<?= e($app['candidate_email']) ?>" class="hover:underline text-slate-700"><?= e($app['candidate_email']) ?></a>
                                </span>
                                <?php if (!empty($app['candidate_phone'])): ?>
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span class="text-slate-700"><?= e($app['candidate_phone']) ?></span>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($app['portfolio_url'])): ?>
                                    <a href="<?= e($app['portfolio_url']) ?>" target="_blank" class="flex items-center gap-1 text-brand-600 hover:underline">
                                        <i data-lucide="globe" class="w-3.5 h-3.5"></i> Portfolio
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($app['linkedin_url'])): ?>
                                    <a href="<?= e($app['linkedin_url']) ?>" target="_blank" class="flex items-center gap-1 text-blue-600 hover:underline">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i> LinkedIn
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($app['github_url'])): ?>
                                    <a href="<?= e($app['github_url']) ?>" target="_blank" class="flex items-center gap-1 text-slate-800 hover:underline">
                                        <i data-lucide="code" class="w-3.5 h-3.5"></i> GitHub
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Skills Chips -->
                            <?php if (!empty($app['skills'])): ?>
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <?php foreach (explode(',', $app['skills']) as $skill): ?>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                                            <?= e(trim($skill)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Cover Note -->
                            <?php if (!empty($app['cover_note'])): ?>
                                <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/70 text-xs text-slate-600 leading-relaxed">
                                    <strong class="block text-slate-800 uppercase tracking-wider text-[10px] mb-1">Candidate Note:</strong>
                                    "<?= e($app['cover_note']) ?>"
                                </div>
                            <?php endif; ?>

                            <!-- Resume Access Link -->
                            <div class="pt-2">
                                <a href="<?= baseUrl('uploads/resumes/' . $app['resume_path']) ?>" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-brand-600 text-white font-semibold text-xs shadow-sm transition">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                    Download / View Resume (<?= e($app['resume_path']) ?>)
                                </a>
                                <span class="text-[11px] text-slate-400 ml-3">Submitted <?= timeAgo($app['applied_at']) ?></span>
                            </div>
                        </div>

                        <!-- Status & Review Controls Box -->
                        <div class="lg:w-80 bg-slate-50 p-4 rounded-xl border border-slate-200/80 space-y-3 flex-shrink-0">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-brand-600"></i>
                                Update Hiring Stage
                            </span>

                            <form method="POST" class="space-y-3">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="update_application" value="1">
                                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Candidate Status</label>
                                    <select name="status" class="w-full px-2.5 py-1.5 text-xs font-semibold bg-white border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-brand-500">
                                        <option value="pending" <?= $app['status'] === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                                        <option value="under_review" <?= $app['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                                        <option value="shortlisted" <?= $app['status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                                        <option value="interview" <?= $app['status'] === 'interview' ? 'selected' : '' ?>>Interview Scheduled</option>
                                        <option value="accepted" <?= $app['status'] === 'accepted' ? 'selected' : '' ?>>Accepted / Offer Made</option>
                                        <option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>Declined / Rejected</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Internal Feedback / Note</label>
                                    <textarea name="employer_notes" rows="2" placeholder="e.g. Technical interview scheduled for Thursday..." class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-brand-500"><?= e($app['employer_notes'] ?? '') ?></textarea>
                                </div>

                                <button type="submit" class="w-full py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                                    Save Decision
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
