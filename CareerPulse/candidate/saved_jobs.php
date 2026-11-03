<?php
/**
 * CareerPulse - Saved / Bookmarked Jobs
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth('candidate');

// Handle Unsave
if (isPost() && isset($_POST['unsave_id'])) {
    if (validateCsrf($_POST['csrf_token'] ?? '')) {
        $saveId = (int)$_POST['unsave_id'];
        dbExecute("DELETE FROM saved_jobs WHERE id = ? AND user_id = ?", [$saveId, $user['id']]);
        setFlash('info', 'Job removed from your saved list.');
        header('Location: ' . baseUrl('candidate/saved_jobs.php'));
        exit;
    }
}

// Fetch saved jobs
$savedJobs = dbFetchAll("
    SELECT s.id as save_id, s.saved_at, j.*, c.name as category_name, ep.company_name
    FROM saved_jobs s
    JOIN jobs j ON s.job_id = j.id
    JOIN categories c ON j.category_id = c.id
    LEFT JOIN employer_profiles ep ON ep.user_id = j.employer_id
    WHERE s.user_id = ?
    ORDER BY s.saved_at DESC
", [$user['id']]);

$pageTitle = 'Saved Jobs';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between pb-6 border-b border-slate-200 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Saved Bookmarks</h1>
            <p class="text-sm text-slate-500 mt-1">Review jobs you have pinned for later application</p>
        </div>
        <a href="<?= baseUrl('jobs.php') ?>" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow transition">
            Explore More Jobs
        </a>
    </div>

    <?php if (empty($savedJobs)): ?>
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="bookmark" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-base">You haven't bookmarked any jobs yet</h3>
            <p class="text-xs text-slate-500 mt-1 mb-5">Click the bookmark icon on any job posting to save it here.</p>
            <a href="<?= baseUrl('jobs.php') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-xs font-semibold rounded-lg shadow-sm">
                Browse Jobs Now
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($savedJobs as $job): ?>
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-xs font-semibold text-slate-500"><?= e($job['company_name'] ?? 'Company') ?></span>
                                <h3 class="font-bold text-slate-900 text-lg hover:text-brand-600">
                                    <a href="<?= baseUrl('job_details.php?id=' . $job['id']) ?>"><?= e($job['title']) ?></a>
                                </h3>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="unsave_id" value="<?= $job['save_id'] ?>">
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Remove bookmark">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-medium">
                                <?= e($job['location']) ?>
                            </span>
                            <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 font-semibold">
                                <?= formatSalary($job['salary_min'], $job['salary_max'], $job['salary_currency']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Saved <?= timeAgo($job['saved_at']) ?></span>
                        <a href="<?= baseUrl('job_details.php?id=' . $job['id']) ?>" class="px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition">
                            View &amp; Apply &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
