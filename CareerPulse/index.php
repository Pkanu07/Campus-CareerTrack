<?php
/**
 * CareerPulse - Landing Page
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch key platform statistics
$totalJobs = (int)dbFetchValue("SELECT COUNT(*) FROM jobs WHERE status = 'active'");
$totalCompanies = (int)dbFetchValue("SELECT COUNT(DISTINCT employer_id) FROM jobs WHERE status = 'active'");
$totalApplications = (int)dbFetchValue("SELECT COUNT(*) FROM applications");

// Fetch active categories with job count
$categories = dbFetchAll("
    SELECT c.*, COUNT(j.id) as job_count
    FROM categories c
    LEFT JOIN jobs j ON j.category_id = c.id AND j.status = 'active'
    GROUP BY c.id
    ORDER BY c.name ASC
");

// Fetch featured & recent jobs
$featuredJobs = dbFetchAll("
    SELECT j.*, c.name as category_name, ep.company_name, ep.logo_url
    FROM jobs j
    JOIN categories c ON j.category_id = c.id
    LEFT JOIN employer_profiles ep ON ep.user_id = j.employer_id
    WHERE j.status = 'active'
    ORDER BY j.is_featured DESC, j.created_at DESC
    LIMIT 6
");

$pageTitle = 'Find Your Dream Career';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-slate-50 pt-16 pb-20 md:pt-24 md:pb-28 border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100 text-brand-800 text-xs font-semibold uppercase tracking-wider mb-6 border border-brand-200/80 shadow-sm">
            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-brand-600"></i>
            The Next-Generation Tech & Professional Career Portal
        </div>

        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-slate-900 tracking-tight max-w-4xl mx-auto leading-tight">
            Discover roles where your talent meets <span class="bg-gradient-to-r from-brand-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">real impact.</span>
        </h1>

        <p class="mt-5 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
            Search verified engineering, design, AI, and leadership opportunities. Apply directly with your resume and track review milestones in real time.
        </p>

        <!-- Search Bar Form -->
        <div class="mt-8 max-w-3xl mx-auto bg-white p-2 sm:p-2.5 rounded-2xl shadow-xl border border-slate-200/80">
            <form action="<?= baseUrl('jobs.php') ?>" method="GET" class="flex flex-col md:flex-row items-center gap-2">
                <div class="flex-1 flex items-center gap-2.5 px-3 py-2 w-full">
                    <i data-lucide="search" class="w-5 h-5 text-slate-400"></i>
                    <input type="text" name="q" placeholder="Job title, keywords, or skills..." class="w-full text-sm bg-transparent outline-none text-slate-800 placeholder-slate-400 font-medium">
                </div>

                <div class="hidden md:block w-px h-8 bg-slate-200"></div>

                <div class="flex-1 flex items-center gap-2.5 px-3 py-2 w-full">
                    <i data-lucide="folder-kanban" class="w-5 h-5 text-slate-400"></i>
                    <select name="category" class="w-full text-sm bg-transparent outline-none text-slate-700 font-medium cursor-pointer">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="w-full md:w-auto px-7 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-brand-500/20 transition flex items-center justify-center gap-2">
                    <span>Search</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <!-- Popular Search Tags -->
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2 text-xs text-slate-500 font-medium">
            <span>Popular searches:</span>
            <a href="<?= baseUrl('jobs.php?q=PHP') ?>" class="px-2.5 py-1 bg-white hover:bg-slate-100 rounded-md border border-slate-200 text-slate-700 transition">PHP</a>
            <a href="<?= baseUrl('jobs.php?q=React') ?>" class="px-2.5 py-1 bg-white hover:bg-slate-100 rounded-md border border-slate-200 text-slate-700 transition">React</a>
            <a href="<?= baseUrl('jobs.php?is_remote=1') ?>" class="px-2.5 py-1 bg-white hover:bg-slate-100 rounded-md border border-slate-200 text-slate-700 transition">Remote Only</a>
            <a href="<?= baseUrl('jobs.php?q=AI') ?>" class="px-2.5 py-1 bg-white hover:bg-slate-100 rounded-md border border-slate-200 text-slate-700 transition">Data &amp; AI</a>
            <a href="<?= baseUrl('jobs.php?experience_level=entry') ?>" class="px-2.5 py-1 bg-white hover:bg-slate-100 rounded-md border border-slate-200 text-slate-700 transition">Internships / Entry</a>
        </div>

        <!-- Live Platform Stats -->
        <div class="mt-14 pt-8 border-t border-slate-200/60 grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-4xl mx-auto">
            <div>
                <div class="text-3xl font-extrabold text-slate-900"><?= number_format($totalJobs) ?>+</div>
                <div class="text-xs uppercase tracking-wider font-semibold text-slate-500 mt-1">Verified Jobs</div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-slate-900"><?= number_format($totalCompanies) ?></div>
                <div class="text-xs uppercase tracking-wider font-semibold text-slate-500 mt-1">Hiring Companies</div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-slate-900"><?= number_format($totalApplications + 120) ?>+</div>
                <div class="text-xs uppercase tracking-wider font-semibold text-slate-500 mt-1">Applications Processed</div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-brand-600">98%</div>
                <div class="text-xs uppercase tracking-wider font-semibold text-slate-500 mt-1">Recruiter Response Rate</div>
            </div>
        </div>

    </div>
</section>

<!-- Popular Categories Section -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600">Industry Sectors</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Explore by Category</h2>
        </div>
        <a href="<?= baseUrl('jobs.php') ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition">
            View All Openings &rarr;
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($categories as $cat): ?>
            <a href="<?= baseUrl('jobs.php?category=' . urlencode($cat['slug'])) ?>" class="group p-5 bg-white rounded-2xl border border-slate-200 hover:border-brand-500/50 hover:shadow-lg transition-all flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                    <i data-lucide="<?= e($cat['icon'] ?: 'briefcase') ?>" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 group-hover:text-brand-600 transition text-base"><?= e($cat['name']) ?></h3>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed"><?= e($cat['description']) ?></p>
                    <span class="inline-block mt-3 text-xs font-semibold text-slate-400 group-hover:text-brand-600">
                        <?= (int)$cat['job_count'] ?> active <?= $cat['job_count'] == 1 ? 'opening' : 'openings' ?> &rarr;
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Job Listings Section -->
<section class="py-16 bg-slate-100/60 border-y border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600">Top Opportunities</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Featured Job Openings</h2>
            </div>
            <a href="<?= baseUrl('jobs.php') ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition">
                Browse all <?= $totalJobs ?> jobs &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($featuredJobs as $job): ?>
                <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <!-- Header row -->
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 text-brand-700 font-extrabold flex items-center justify-center text-lg border border-slate-200">
                                    <?= strtoupper(substr($job['company_name'] ?? 'CP', 0, 2)) ?>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700"><?= e($job['company_name'] ?? 'Confidential Employer') ?></h4>
                                    <a href="<?= baseUrl('job_details.php?id=' . $job['id']) ?>" class="font-bold text-slate-900 text-lg hover:text-brand-600 transition line-clamp-1">
                                        <?= e($job['title']) ?>
                                    </a>
                                </div>
                            </div>
                            <?php if ($job['is_featured']): ?>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                                    Featured
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Description excerpt -->
                        <p class="text-xs text-slate-500 mt-3.5 line-clamp-2 leading-relaxed">
                            <?= e($job['description']) ?>
                        </p>

                        <!-- Tags -->
                        <div class="mt-4 flex flex-wrap gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                <?= e($job['location']) ?>
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-medium flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-indigo-400"></i>
                                <?= ucfirst(e($job['job_type'])) ?>
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold flex items-center gap-1">
                                <i data-lucide="badge-dollar-sign" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <?= formatSalary($job['salary_min'], $job['salary_max'], $job['salary_currency']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Footer row -->
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <span>Posted <?= timeAgo($job['created_at']) ?></span>
                        <a href="<?= baseUrl('job_details.php?id=' . $job['id']) ?>" class="inline-flex items-center gap-1.5 font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                            View Position &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-gradient-to-r from-brand-700 via-indigo-700 to-slate-900 rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <h2 class="text-3xl font-extrabold tracking-tight">Are you hiring for high-impact roles?</h2>
            <p class="mt-3 text-slate-200 text-sm sm:text-base leading-relaxed">
                Connect directly with thousands of qualified software engineers, designers, data specialists, and operational leaders. Post listings in minutes and manage applicant pipelines with ease.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= baseUrl('auth/register.php?role=employer') ?>" class="px-6 py-3 bg-white text-brand-700 hover:bg-slate-100 font-bold text-sm rounded-xl shadow-md transition">
                    Post a Job Today
                </a>
                <a href="<?= baseUrl('auth/login.php') ?>" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold text-sm rounded-xl backdrop-blur transition border border-white/20">
                    Employer Sign In
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
