<?php
/**
 * CareerPulse - Candidate Dashboard
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth('candidate');

// Fetch candidate applications
$applications = dbFetchAll("
    SELECT a.*, j.title as job_title, j.location as job_location, j.job_type, ep.company_name, c.name as category_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN categories c ON j.category_id = c.id
    LEFT JOIN employer_profiles ep ON ep.user_id = j.employer_id
    WHERE a.candidate_id = ?
    ORDER BY a.applied_at DESC
", [$user['id']]);

// Metrics
$totalApplied = count($applications);
$inReview = 0;
$interviews = 0;
$accepted = 0;

foreach ($applications as $app) {
    if (in_array($app['status'], ['pending', 'under_review'], true)) {
        $inReview++;
    } elseif (in_array($app['status'], ['shortlisted', 'interview'], true)) {
        $interviews++;
    } elseif ($app['status'] === 'accepted') {
        $accepted++;
    }
}

$pageTitle = 'Candidate Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-slate-200">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600">Candidate Portal</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-0.5">
                Hello, <?= e($user['name']) ?>! 👋
            </h1>
            <p class="text-sm text-slate-500 mt-1">Here is a real-time overview of your submitted job applications</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= baseUrl('jobs.php') ?>" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i data-lucide="search" class="w-4 h-4"></i>
                Browse Open Roles
            </a>
            <a href="<?= baseUrl('candidate/profile.php') ?>" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                <i data-lucide="user" class="w-4 h-4"></i>
                Edit Profile
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 my-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="send" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $totalApplied ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Applied</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $inReview ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Under Review</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="calendar" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $interviews ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Interviews</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $accepted ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Offers / Accepted</span>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-base">Your Active &amp; Past Applications</h2>
            <span class="text-xs text-slate-500"><?= $totalApplied ?> Total</span>
        </div>

        <?php if (empty($applications)): ?>
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="file-question" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-base">No applications submitted yet</h3>
                <p class="text-xs text-slate-500 mt-1 mb-4">Start exploring exciting opportunities and apply with your resume.</p>
                <a href="<?= baseUrl('jobs.php') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-xs font-semibold rounded-lg shadow-sm">
                    Browse All Jobs
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200/60 font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Company &amp; Role</th>
                            <th class="px-6 py-3.5">Applied Date</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Recruiter Notes</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($applications as $app): ?>
                            <?php $badge = getStatusBadge($app['status']); ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">
                                        <a href="<?= baseUrl('job_details.php?id=' . $app['job_id']) ?>" class="hover:text-brand-600">
                                            <?= e($app['job_title']) ?>
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        <?= e($app['company_name'] ?? 'Company') ?> &bull; <?= e($app['job_location']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    <?= date('M j, Y', strtotime($app['applied_at'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border <?= $badge['bg'] ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $badge['dot'] ?>"></span>
                                        <?= $badge['label'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-xs">
                                    <?= !empty($app['employer_notes']) ? e($app['employer_notes']) : '<span class="text-slate-400 italic">No notes yet</span>' ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= baseUrl('job_details.php?id=' . $app['job_id']) ?>" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                                        View Job
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
