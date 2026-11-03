<?php
/**
 * CareerPulse - Platform Administration Center
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$adminUser = requireAuth('admin');

// Handle Admin Actions (Delete user, Toggle featured, Delete job, Add category)
if (isPost()) {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security token expired.');
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete_user') {
            $targetUserId = (int)$_POST['user_id'];
            if ($targetUserId !== $adminUser['id']) {
                dbExecute("DELETE FROM users WHERE id = ?", [$targetUserId]);
                setFlash('success', 'User account permanently deleted.');
            } else {
                setFlash('error', 'You cannot delete your own administrative account.');
            }
        } elseif ($action === 'toggle_feature') {
            $targetJobId = (int)$_POST['job_id'];
            dbExecute("UPDATE jobs SET is_featured = 1 - is_featured WHERE id = ?", [$targetJobId]);
            setFlash('info', 'Job featured status updated.');
        } elseif ($action === 'delete_job') {
            $targetJobId = (int)$_POST['job_id'];
            dbExecute("DELETE FROM jobs WHERE id = ?", [$targetJobId]);
            setFlash('success', 'Job posting deleted.');
        } elseif ($action === 'add_category') {
            $catName = trim($_POST['cat_name'] ?? '');
            $catIcon = trim($_POST['cat_icon'] ?? 'briefcase');
            $catDesc = trim($_POST['cat_desc'] ?? '');
            $catSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $catName), '-'));

            if (!empty($catName)) {
                try {
                    dbInsert("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)", [$catName, $catSlug, $catIcon, $catDesc]);
                    setFlash('success', 'New category added.');
                } catch (Throwable $e) {
                    setFlash('error', 'Category already exists or invalid.');
                }
            }
        }
        header('Location: ' . baseUrl('admin/dashboard.php'));
        exit;
    }
}

// System stats
$totalCandidates = (int)dbFetchValue("SELECT COUNT(*) FROM users WHERE role = 'candidate'");
$totalEmployers = (int)dbFetchValue("SELECT COUNT(*) FROM users WHERE role = 'employer'");
$totalJobs = (int)dbFetchValue("SELECT COUNT(*) FROM jobs");
$totalApps = (int)dbFetchValue("SELECT COUNT(*) FROM applications");

// All users
$users = dbFetchAll("SELECT id, name, email, role, phone, created_at FROM users ORDER BY created_at DESC");

// All jobs
$jobs = dbFetchAll("
    SELECT j.*, ep.company_name, c.name as category_name, COUNT(a.id) as app_count
    FROM jobs j
    LEFT JOIN employer_profiles ep ON ep.user_id = j.employer_id
    LEFT JOIN categories c ON c.id = j.category_id
    LEFT JOIN applications a ON a.job_id = j.id
    GROUP BY j.id
    ORDER BY j.created_at DESC
    LIMIT 20
");

// Categories
$categories = dbFetchAll("
    SELECT c.*, COUNT(j.id) as job_count
    FROM categories c
    LEFT JOIN jobs j ON j.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
");

$pageTitle = 'Administration Console';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-slate-200">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Platform Control</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-0.5">
                Admin Console
            </h1>
            <p class="text-sm text-slate-500 mt-1">Platform moderation, system metrics, users, job catalog, and categories</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200">
                DB Engine: <strong class="text-slate-900 uppercase"><?= getActiveDriver() ?></strong>
            </span>
            <a href="<?= baseUrl('database/setup.php') ?>" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition">
                Reset / Reseed DB
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 my-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $totalCandidates ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Candidates</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="building" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $totalEmployers ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Employers</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="briefcase" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $totalJobs ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Postings</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="file-check" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-2xl font-extrabold text-slate-900"><?= $totalApps ?></span>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Applications</span>
            </div>
        </div>
    </div>

    <!-- Job Moderation Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-base">All Job Postings (Moderation)</h2>
            <span class="text-xs text-slate-500"><?= count($jobs) ?> Listings Shown</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200 font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Position &amp; Company</th>
                        <th class="px-6 py-3.5">Category</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Featured</th>
                        <th class="px-6 py-3.5">Applicants</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($jobs as $j): ?>
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-3.5">
                                <a href="<?= baseUrl('job_details.php?id=' . $j['id']) ?>" class="font-bold text-slate-900 hover:text-brand-600">
                                    <?= e($j['title']) ?>
                                </a>
                                <div class="text-xs text-slate-500 mt-0.5"><?= e($j['company_name'] ?? 'Company') ?> &bull; <?= e($j['location']) ?></div>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600"><?= e($j['category_name']) ?></td>
                            <td class="px-6 py-3.5">
                                <?php $b = getStatusBadge($j['status']); ?>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border <?= $b['bg'] ?>">
                                    <?= $b['label'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="toggle_feature">
                                    <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                                    <button type="submit" class="text-xs font-semibold px-2 py-1 rounded-md border <?= $j['is_featured'] ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-slate-100 text-slate-500 border-slate-200' ?>">
                                        <?= $j['is_featured'] ? '★ Featured' : '☆ Standard' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-3.5 text-xs font-bold text-slate-700"><?= (int)$j['app_count'] ?></td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <form method="POST" class="inline-block" onsubmit="return confirm('Delete this job listing?');">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="delete_job">
                                    <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                                    <button type="submit" class="text-xs text-rose-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Management & Categories in 2 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- User Accounts Table (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-900 text-base">Registered Users</h2>
                <span class="text-xs text-slate-500"><?= count($users) ?> Total Accounts</span>
            </div>
            <div class="overflow-x-auto max-h-[480px]">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200 font-semibold sticky top-0">
                        <tr>
                            <th class="px-6 py-3">Name &amp; Email</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Registered</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-3">
                                    <div class="font-bold text-slate-900"><?= e($u['name']) ?></div>
                                    <div class="text-xs text-slate-400"><?= e($u['email']) ?></div>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $u['role'] === 'admin' ? 'bg-rose-100 text-rose-700' : ($u['role'] === 'employer' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') ?>">
                                        <?= e($u['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-500">
                                    <?= date('M j, Y', strtotime($u['created_at'])) ?>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <?php if ($u['id'] !== $adminUser['id']): ?>
                                        <form method="POST" class="inline-block" onsubmit="return confirm('Delete this user?');">
                                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="text-xs text-rose-600 hover:underline">Remove</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic">Self</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Categories Box (1 col) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div>
                <h3 class="font-bold text-slate-900 text-base mb-1">Categories</h3>
                <p class="text-xs text-slate-500">Manage directory classification tags</p>
            </div>

            <!-- Category list -->
            <div class="space-y-2 max-h-56 overflow-y-auto">
                <?php foreach ($categories as $cat): ?>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 text-xs border border-slate-100">
                        <div class="flex items-center gap-2">
                            <i data-lucide="<?= e($cat['icon'] ?: 'briefcase') ?>" class="w-4 h-4 text-brand-600"></i>
                            <span class="font-bold text-slate-800"><?= e($cat['name']) ?></span>
                        </div>
                        <span class="px-2 py-0.5 bg-white rounded-md border border-slate-200 text-slate-600 font-semibold">
                            <?= (int)$cat['job_count'] ?> jobs
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Add new category form -->
            <form method="POST" class="pt-4 border-t border-slate-100 space-y-3">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="action" value="add_category">

                <span class="block text-xs font-bold uppercase tracking-wider text-slate-700">+ Add New Category</span>
                
                <input type="text" name="cat_name" required placeholder="Category name (e.g. Cybersecurity)" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                <input type="text" name="cat_icon" placeholder="Lucide icon name (e.g. shield)" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                <input type="text" name="cat_desc" placeholder="Brief description" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">

                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition">
                    Create Category
                </button>
            </form>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
