<?php
/**
 * CareerPulse - Manage Job Postings
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth('employer');

// Handle status toggle or deletion
if (isPost()) {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security token expired.');
    } else {
        $action = $_POST['action'] ?? '';
        $targetJobId = (int)($_POST['job_id'] ?? 0);

        if ($action === 'delete') {
            dbExecute("DELETE FROM jobs WHERE id = ? AND employer_id = ?", [$targetJobId, $user['id']]);
            setFlash('success', 'Job posting deleted successfully.');
        } elseif (in_array($action, ['active', 'paused', 'closed'], true)) {
            dbExecute("UPDATE jobs SET status = ? WHERE id = ? AND employer_id = ?", [$action, $targetJobId, $user['id']]);
            setFlash('info', 'Job status updated to ' . ucfirst($action) . '.');
        }
        header('Location: ' . baseUrl('employer/manage_jobs.php'));
        exit;
    }
}

$statusFilter = trim($_GET['status'] ?? 'all');
$sql = "
    SELECT j.*, c.name as category_name, COUNT(a.id) as applicant_count
    FROM jobs j
    JOIN categories c ON j.category_id = c.id
    LEFT JOIN applications a ON a.job_id = j.id
    WHERE j.employer_id = ?
";
$params = [$user['id']];

if (in_array($statusFilter, ['active', 'paused', 'closed'], true)) {
    $sql .= " AND j.status = ?";
    $params[] = $statusFilter;
}

$sql .= " GROUP BY j.id ORDER BY j.created_at DESC";
$jobs = dbFetchAll($sql, $params);

$pageTitle = 'Manage Job Postings';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manage Your Job Postings</h1>
            <p class="text-sm text-slate-500 mt-1">Activate, pause, edit, and track applicant flow for all your open roles</p>
        </div>
        <a href="<?= baseUrl('employer/post_job.php') ?>" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Post New Opening
        </a>
    </div>

    <!-- Status Filter Tabs -->
    <div class="flex items-center gap-2 my-6">
        <a href="<?= baseUrl('employer/manage_jobs.php?status=all') ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition <?= $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
            All Listings
        </a>
        <a href="<?= baseUrl('employer/manage_jobs.php?status=active') ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition <?= $statusFilter === 'active' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
            Active
        </a>
        <a href="<?= baseUrl('employer/manage_jobs.php?status=paused') ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition <?= $statusFilter === 'paused' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
            Paused
        </a>
        <a href="<?= baseUrl('employer/manage_jobs.php?status=closed') ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition <?= $statusFilter === 'closed' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
            Closed
        </a>
    </div>

    <!-- Listings Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <?php if (empty($jobs)): ?>
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="briefcase" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-base">No listings found</h3>
                <p class="text-xs text-slate-500 mt-1 mb-4">You have no listings under this filter.</p>
                <a href="<?= baseUrl('employer/post_job.php') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-xs font-semibold rounded-lg shadow-sm">
                    Post a Job Opening
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200/60 font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Position &amp; Category</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Candidates</th>
                            <th class="px-6 py-3.5">Deadline</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($jobs as $job): ?>
                            <?php $badge = getStatusBadge($job['status']); ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">
                                        <a href="<?= baseUrl('job_details.php?id=' . $job['id']) ?>" class="hover:text-brand-600">
                                            <?= e($job['title']) ?>
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        <?= e($job['category_name']) ?> &bull; <?= e($job['location']) ?> &bull; <?= ucfirst(e($job['job_type'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <!-- Status quick switcher form -->
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <select name="action" onchange="this.form.submit()" class="text-xs font-semibold px-2.5 py-1 rounded-full border <?= $badge['bg'] ?> outline-none cursor-pointer">
                                            <option value="active" <?= $job['status'] === 'active' ? 'selected' : '' ?>>● Active</option>
                                            <option value="paused" <?= $job['status'] === 'paused' ? 'selected' : '' ?>>● Paused</option>
                                            <option value="closed" <?= $job['status'] === 'closed' ? 'selected' : '' ?>>● Closed</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="<?= baseUrl('employer/applicants.php?job_id=' . $job['id']) ?>" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 text-slate-800 hover:text-brand-600 font-bold text-xs transition">
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                        <span><?= (int)$job['applicant_count'] ?> Applicants</span>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    <?= $job['deadline'] ? date('M j, Y', strtotime($job['deadline'])) : 'Open' ?>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="<?= baseUrl('employer/post_job.php?id=' . $job['id']) ?>" class="px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-brand-600 bg-slate-50 hover:bg-slate-100 rounded-lg transition">
                                        Edit
                                    </a>
                                    <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to permanently delete this job listing?');">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            Delete
                                        </button>
                                    </form>
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
