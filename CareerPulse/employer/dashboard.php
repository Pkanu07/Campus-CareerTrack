<?php
/**
 * CareerPulse - Employer / Recruiter Dashboard
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth('employer');

// Fetch employer metrics
$totalPostings = (int)dbFetchValue("SELECT COUNT(*) FROM jobs WHERE employer_id = ?", [$user['id']]);
$activePostings = (int)dbFetchValue("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = 'active'", [$user['id']]);

$totalApplicants = (int)dbFetchValue("
    SELECT COUNT(a.id)
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ?
", [$user['id']]);

$shortlistedCount = (int)dbFetchValue("
    SELECT COUNT(a.id)
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ? AND a.status IN ('shortlisted', 'interview', 'accepted')
", [$user['id']]);

// Fetch recent postings
$recentJobs = dbFetchAll("
    SELECT j.*, c.name as category_name, COUNT(a.id) as applicant_count
    FROM jobs j
    JOIN categories c ON j.category_id = c.id
    LEFT JOIN applications a ON a.job_id = j.id
    WHERE j.employer_id = ?
    GROUP BY j.id
    ORDER BY j.created_at DESC
    LIMIT 5
", [$user['id']]);

// Fetch recent applicants
$recentApplicants = dbFetchAll("
    SELECT a.*, u.name as candidate_name, u.email as candidate_email, j.title as job_title, cp.headline
    FROM applications a
    JOIN users u ON a.candidate_id = u.id
    JOIN jobs j ON a.job_id = j.id
    LEFT JOIN candidate_profiles cp ON cp.user_id = u.id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
", [$user['id']]);

$pageTitle = 'Employer Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Top Welcome Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-slate-200">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-purple-600">Recruiter Center</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-0.5">
                <?= e($user['employer_profile']['company_name'] ?? $user['name']) ?>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Review active talent pipelines, job applications, and candidate shortlists</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= baseUrl('employer/post_job.php') ?>" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Post New Opening
            </a>
            <a href="<?= baseUrl('employer/applicants.php') ?>" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                <i data-lucide="users" class="w-4 h-4"></i>
                Review All Candidates
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 my-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="briefcase" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $activePostings ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Active Listings</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $totalApplicants ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Applicants</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="award" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $shortlistedCount ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Shortlisted / Offers</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-check" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $totalPostings ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Postings Created</span>
            </div>
        </div>
    </div>

    <!-- Active Openings Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-base">Your Job Postings</h2>
            <a href="<?= baseUrl('employer/manage_jobs.php') ?>" class="text-xs font-semibold text-brand-600 hover:underline">Manage All &rarr;</a>
        </div>

        <?php if (empty($recentJobs)): ?>
            <div class="p-8 text-center">
                <p class="text-sm text-slate-500 mb-3">You haven't posted any jobs yet.</p>
                <a href="<?= baseUrl('employer/post_job.php') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-xs font-semibold rounded-lg shadow-sm">
                    Post Your First Opening
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200/60 font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Title &amp; Category</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Applicants</th>
                            <th class="px-6 py-3.5">Created</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($recentJobs as $j): ?>
                            <?php $badge = getStatusBadge($j['status']); ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">
                                        <a href="<?= baseUrl('job_details.php?id=' . $j['id']) ?>" class="hover:text-brand-600">
                                            <?= e($j['title']) ?>
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        <?= e($j['category_name']) ?> &bull; <?= e($j['location']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border <?= $badge['bg'] ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $badge['dot'] ?>"></span>
                                        <?= $badge['label'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="<?= baseUrl('employer/applicants.php?job_id=' . $j['id']) ?>" class="inline-flex items-center gap-1.5 font-bold text-slate-800 hover:text-brand-600">
                                        <span class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-xs">
                                            <?= (int)$j['applicant_count'] ?>
                                        </span>
                                        <span class="text-xs font-semibold underline">View Pipeline</span>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    <?= date('M j, Y', strtotime($j['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="<?= baseUrl('employer/post_job.php?id=' . $j['id']) ?>" class="text-xs font-semibold text-slate-600 hover:text-brand-600">
                                        Edit
                                    </a>
                                    <a href="<?= baseUrl('employer/applicants.php?job_id=' . $j['id']) ?>" class="text-xs font-semibold text-brand-600 hover:underline">
                                        Candidates
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Candidate Applications -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-base">Latest Candidate Applications</h2>
            <a href="<?= baseUrl('employer/applicants.php') ?>" class="text-xs font-semibold text-brand-600 hover:underline">All Candidates &rarr;</a>
        </div>

        <?php if (empty($recentApplicants)): ?>
            <div class="p-8 text-center text-sm text-slate-500">
                No candidate applications received yet.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200/60 font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Candidate</th>
                            <th class="px-6 py-3.5">Applied For</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Applied Date</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($recentApplicants as $cand): ?>
                            <?php $badge = getStatusBadge($cand['status']); ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900"><?= e($cand['candidate_name']) ?></div>
                                    <div class="text-xs text-slate-500 mt-0.5 truncate max-w-xs"><?= e($cand['headline'] ?? $cand['candidate_email']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                    <?= e($cand['job_title']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border <?= $badge['bg'] ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $badge['dot'] ?>"></span>
                                        <?= $badge['label'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    <?= timeAgo($cand['applied_at']) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= baseUrl('employer/applicants.php?job_id=' . $cand['job_id']) ?>" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-600 hover:text-white text-brand-700 rounded-lg text-xs font-semibold transition">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
