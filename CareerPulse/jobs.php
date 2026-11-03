<?php
/**
 * CareerPulse - Job Directory & Search
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

// Filter parameters
$search = trim($_GET['q'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$jobType = trim($_GET['job_type'] ?? '');
$experience = trim($_GET['experience_level'] ?? '');
$isRemote = isset($_GET['is_remote']) && $_GET['is_remote'] === '1';
$sort = trim($_GET['sort'] ?? 'newest');

// Fetch all categories for filter sidebar
$categories = dbFetchAll("SELECT * FROM categories ORDER BY name ASC");

// Build dynamic SQL query
$sql = "
    SELECT j.*, c.name as category_name, c.slug as category_slug, ep.company_name, ep.location as company_location
    FROM jobs j
    JOIN categories c ON j.category_id = c.id
    LEFT JOIN employer_profiles ep ON ep.user_id = j.employer_id
    WHERE j.status = 'active'
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR j.requirements LIKE ? OR ep.company_name LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if (!empty($categorySlug)) {
    $sql .= " AND c.slug = ?";
    $params[] = $categorySlug;
}

if (!empty($jobType)) {
    $sql .= " AND j.job_type = ?";
    $params[] = $jobType;
}

if (!empty($experience)) {
    $sql .= " AND j.experience_level = ?";
    $params[] = $experience;
}

if ($isRemote) {
    $sql .= " AND (j.is_remote = 1 OR j.location LIKE '%remote%')";
}

// Order by
$sql .= match ($sort) {
    'salary' => " ORDER BY j.salary_max DESC, j.created_at DESC",
    'oldest' => " ORDER BY j.created_at ASC",
    default => " ORDER BY j.is_featured DESC, j.created_at DESC",
};

$jobs = dbFetchAll($sql, $params);
$totalFound = count($jobs);

$pageTitle = 'Explore Job Openings';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-slate-100/70 border-b border-slate-200 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Explore Job Openings</h1>
        <p class="text-sm text-slate-500 mt-1">Discover verified roles matching your skills and experience level</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Filter Sidebar -->
        <aside class="lg:col-span-1 space-y-6">
            <form action="<?= baseUrl('jobs.php') ?>" method="GET" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4 text-brand-600"></i>
                        Filters
                    </span>
                    <a href="<?= baseUrl('jobs.php') ?>" class="text-xs text-brand-600 hover:underline">Reset</a>
                </div>

                <!-- Keyword search -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Keywords</label>
                    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Title or skill..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Category</label>
                    <select name="category" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c['slug']) ?>" <?= $categorySlug === $c['slug'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Job Type -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Employment Type</label>
                    <select name="job_type" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                        <option value="">All Types</option>
                        <option value="full-time" <?= $jobType === 'full-time' ? 'selected' : '' ?>>Full-time</option>
                        <option value="part-time" <?= $jobType === 'part-time' ? 'selected' : '' ?>>Part-time</option>
                        <option value="contract" <?= $jobType === 'contract' ? 'selected' : '' ?>>Contract</option>
                        <option value="internship" <?= $jobType === 'internship' ? 'selected' : '' ?>>Internship</option>
                    </select>
                </div>

                <!-- Experience Level -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Experience Level</label>
                    <select name="experience_level" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                        <option value="">Any Level</option>
                        <option value="entry" <?= $experience === 'entry' ? 'selected' : '' ?>>Entry Level</option>
                        <option value="mid" <?= $experience === 'mid' ? 'selected' : '' ?>>Mid Level</option>
                        <option value="senior" <?= $experience === 'senior' ? 'selected' : '' ?>>Senior Level</option>
                        <option value="lead" <?= $experience === 'lead' ? 'selected' : '' ?>>Lead / Principal</option>
                    </select>
                </div>

                <!-- Remote Only Toggle -->
                <div class="pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                        <input type="checkbox" name="is_remote" value="1" <?= $isRemote ? 'checked' : '' ?> class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <span>Remote Positions Only</span>
                    </label>
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sort Results By</label>
                    <select name="sort" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="salary" <?= $sort === 'salary' ? 'selected' : '' ?>>Highest Salary</option>
                        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl shadow transition">
                    Apply Filters
                </button>
            </form>
        </aside>

        <!-- Main Results Column -->
        <main class="lg:col-span-3 space-y-4">
            
            <div class="flex items-center justify-between px-1">
                <span class="text-sm font-semibold text-slate-600">
                    Showing <strong class="text-slate-900"><?= $totalFound ?></strong> <?= $totalFound === 1 ? 'position' : 'positions' ?>
                </span>
                <?php if (!empty($search) || !empty($categorySlug) || !empty($jobType) || $isRemote): ?>
                    <a href="<?= baseUrl('jobs.php') ?>" class="text-xs text-brand-600 hover:underline flex items-center gap-1">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i> Clear all filters
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($jobs)): ?>
                <!-- Empty State -->
                <div class="bg-white rounded-2xl p-12 text-center border border-slate-200">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="search-x" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">No jobs matched your criteria</h3>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto mt-1 mb-5">
                        Try relaxing your keyword filters or selecting a different category.
                    </p>
                    <a href="<?= baseUrl('jobs.php') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-xs font-semibold rounded-lg shadow-sm">
                        View All Listings
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($jobs as $job): ?>
                        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm hover:border-brand-300 hover:shadow-md transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 text-brand-700 font-extrabold flex items-center justify-center text-lg border border-slate-200 flex-shrink-0">
                                        <?= strtoupper(substr($job['company_name'] ?? 'CP', 0, 2)) ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-semibold text-slate-600"><?= e($job['company_name'] ?? 'Confidential') ?></span>
                                            <span class="text-slate-300">&bull;</span>
                                            <span class="text-xs text-brand-600 font-medium"><?= e($job['category_name']) ?></span>
                                            <?php if ($job['is_featured']): ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                                                    Featured
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?= baseUrl('job_details.php?id=' . $job['id']) ?>" class="block font-bold text-slate-900 text-lg hover:text-brand-600 transition mt-0.5">
                                            <?= e($job['title']) ?>
                                        </a>
                                        <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
                                            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-medium flex items-center gap-1">
                                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                                <?= e($job['location']) ?>
                                            </span>
                                            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-medium">
                                                <?= ucfirst(e($job['job_type'])) ?>
                                            </span>
                                            <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 font-semibold">
                                                <?= formatSalary($job['salary_min'], $job['salary_max'], $job['salary_currency']) ?>
                                            </span>
                                            <span class="text-slate-400 pl-1">
                                                <?= timeAgo($job['created_at']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
                                    <a href="<?= baseUrl('job_details.php?id=' . $job['id']) ?>" class="px-4 py-2 bg-brand-50 hover:bg-brand-600 hover:text-white text-brand-700 font-semibold text-xs rounded-xl transition flex items-center gap-1">
                                        View Details &rarr;
                                    </a>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
