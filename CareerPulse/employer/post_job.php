<?php
/**
 * CareerPulse - Create / Edit Job Posting
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth('employer');

$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEditing = $jobId > 0;
$job = null;

if ($isEditing) {
    $job = dbFetchOne("SELECT * FROM jobs WHERE id = ? AND employer_id = ?", [$jobId, $user['id']]);
    if (!$job) {
        setFlash('error', 'Job posting not found or unauthorized.');
        header('Location: ' . baseUrl('employer/manage_jobs.php'));
        exit;
    }
}

$categories = dbFetchAll("SELECT * FROM categories ORDER BY name ASC");
$error = null;

if (isPost()) {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security session token. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $jobType = trim($_POST['job_type'] ?? 'full-time');
        $expLevel = trim($_POST['experience_level'] ?? 'mid');
        $location = trim($_POST['location'] ?? '');
        $isRemote = isset($_POST['is_remote']) ? 1 : 0;
        $salaryMin = !empty($_POST['salary_min']) ? (int)$_POST['salary_min'] : null;
        $salaryMax = !empty($_POST['salary_max']) ? (int)$_POST['salary_max'] : null;
        $currency = trim($_POST['salary_currency'] ?? 'USD');
        $description = trim($_POST['description'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $benefits = trim($_POST['benefits'] ?? '');
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        $status = in_array($_POST['status'] ?? '', ['active', 'paused', 'closed'], true) ? $_POST['status'] : 'active';
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

        if (strlen($title) < 4) {
            $error = 'Job title must be at least 4 characters long.';
        } elseif ($categoryId <= 0) {
            $error = 'Please select a valid job category.';
        } elseif (empty($location)) {
            $error = 'Please specify the job location or select Remote.';
        } elseif (strlen($description) < 20) {
            $error = 'Job description must be at least 20 characters.';
        } else {
            try {
                if ($isEditing) {
                    dbExecute("
                        UPDATE jobs SET
                            category_id = :category_id,
                            title = :title,
                            slug = :slug,
                            job_type = :job_type,
                            experience_level = :experience_level,
                            location = :location,
                            is_remote = :is_remote,
                            salary_min = :salary_min,
                            salary_max = :salary_max,
                            salary_currency = :salary_currency,
                            description = :description,
                            requirements = :requirements,
                            benefits = :benefits,
                            status = :status,
                            deadline = :deadline
                        WHERE id = :id AND employer_id = :employer_id
                    ", [
                        'category_id' => $categoryId,
                        'title' => $title,
                        'slug' => $slug,
                        'job_type' => $jobType,
                        'experience_level' => $expLevel,
                        'location' => $location,
                        'is_remote' => $isRemote,
                        'salary_min' => $salaryMin,
                        'salary_max' => $salaryMax,
                        'salary_currency' => $currency,
                        'description' => $description,
                        'requirements' => $requirements,
                        'benefits' => $benefits,
                        'status' => $status,
                        'deadline' => $deadline,
                        'id' => $jobId,
                        'employer_id' => $user['id'],
                    ]);

                    setFlash('success', 'Job posting updated successfully!');
                } else {
                    $newId = dbInsert("
                        INSERT INTO jobs (
                            employer_id, category_id, title, slug, job_type, experience_level,
                            location, is_remote, salary_min, salary_max, salary_currency,
                            description, requirements, benefits, status, deadline
                        ) VALUES (
                            :employer_id, :category_id, :title, :slug, :job_type, :experience_level,
                            :location, :is_remote, :salary_min, :salary_max, :salary_currency,
                            :description, :requirements, :benefits, :status, :deadline
                        )
                    ", [
                        'employer_id' => $user['id'],
                        'category_id' => $categoryId,
                        'title' => $title,
                        'slug' => $slug,
                        'job_type' => $jobType,
                        'experience_level' => $expLevel,
                        'location' => $location,
                        'is_remote' => $isRemote,
                        'salary_min' => $salaryMin,
                        'salary_max' => $salaryMax,
                        'salary_currency' => $currency,
                        'description' => $description,
                        'requirements' => $requirements,
                        'benefits' => $benefits,
                        'status' => $status,
                        'deadline' => $deadline,
                    ]);

                    setFlash('success', 'Your new job opening was published successfully!');
                }

                header('Location: ' . baseUrl('employer/manage_jobs.php'));
                exit;
            } catch (Throwable $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = $isEditing ? 'Edit Job Posting' : 'Post a New Job';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="flex items-center justify-between pb-6 border-b border-slate-200 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                <?= $isEditing ? 'Edit Position' : 'Create a New Job Posting' ?>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Specify role details, qualifications, compensation, and requirements</p>
        </div>
        <a href="<?= baseUrl('employer/manage_jobs.php') ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            Cancel &amp; Return
        </a>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6 bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <!-- Job Title -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Job Title *</label>
            <input type="text" name="title" required value="<?= e($_POST['title'] ?? $job['title'] ?? '') ?>" placeholder="e.g. Senior Frontend Engineer (React/TypeScript)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
        </div>

        <!-- Category & Job Type & Experience -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Category *</label>
                <select name="category_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? $job['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Employment Type</label>
                <?php $currentType = $_POST['job_type'] ?? $job['job_type'] ?? 'full-time'; ?>
                <select name="job_type" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                    <option value="full-time" <?= $currentType === 'full-time' ? 'selected' : '' ?>>Full-time</option>
                    <option value="part-time" <?= $currentType === 'part-time' ? 'selected' : '' ?>>Part-time</option>
                    <option value="contract" <?= $currentType === 'contract' ? 'selected' : '' ?>>Contract</option>
                    <option value="internship" <?= $currentType === 'internship' ? 'selected' : '' ?>>Internship</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Experience Level</label>
                <?php $currentExp = $_POST['experience_level'] ?? $job['experience_level'] ?? 'mid'; ?>
                <select name="experience_level" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                    <option value="entry" <?= $currentExp === 'entry' ? 'selected' : '' ?>>Entry Level</option>
                    <option value="mid" <?= $currentExp === 'mid' ? 'selected' : '' ?>>Mid Level</option>
                    <option value="senior" <?= $currentExp === 'senior' ? 'selected' : '' ?>>Senior</option>
                    <option value="lead" <?= $currentExp === 'lead' ? 'selected' : '' ?>>Lead / Principal</option>
                    <option value="executive" <?= $currentExp === 'executive' ? 'selected' : '' ?>>Executive</option>
                </select>
            </div>
        </div>

        <!-- Location & Remote -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-center">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Location *</label>
                <input type="text" name="location" required value="<?= e($_POST['location'] ?? $job['location'] ?? '') ?>" placeholder="e.g. San Francisco, CA or Remote" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>

            <div class="pt-5">
                <label class="inline-flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-800">
                    <?php $remoteChecked = isset($_POST['is_remote']) ? true : (!empty($job['is_remote'])); ?>
                    <input type="checkbox" name="is_remote" value="1" <?= $remoteChecked ? 'checked' : '' ?> class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                    <span>This is a 100% Remote / Work from Home role</span>
                </label>
            </div>
        </div>

        <!-- Salary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Minimum Salary (Annual)</label>
                <input type="number" name="salary_min" step="1000" value="<?= e($_POST['salary_min'] ?? $job['salary_min'] ?? '') ?>" placeholder="e.g. 90000" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Maximum Salary (Annual)</label>
                <input type="number" name="salary_max" step="1000" value="<?= e($_POST['salary_max'] ?? $job['salary_max'] ?? '') ?>" placeholder="e.g. 130000" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Currency</label>
                <?php $curr = $_POST['salary_currency'] ?? $job['salary_currency'] ?? 'USD'; ?>
                <select name="salary_currency" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                    <option value="USD" <?= $curr === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                    <option value="EUR" <?= $curr === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                    <option value="GBP" <?= $curr === 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
                    <option value="INR" <?= $curr === 'INR' ? 'selected' : '' ?>>INR (₹)</option>
                </select>
            </div>
        </div>

        <!-- Job Description -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Job Description *</label>
            <textarea name="description" rows="5" required placeholder="Describe the role responsibilities, mission, and day-to-day work..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"><?= e($_POST['description'] ?? $job['description'] ?? '') ?></textarea>
        </div>

        <!-- Requirements -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Requirements &amp; Qualifications</label>
            <textarea name="requirements" rows="4" placeholder="• 3+ years experience with...&#10;• Strong understanding of...&#10;• Experience with..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"><?= e($_POST['requirements'] ?? $job['requirements'] ?? '') ?></textarea>
        </div>

        <!-- Benefits -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Perks &amp; Benefits</label>
            <textarea name="benefits" rows="3" placeholder="• Health insurance&#10;• Unlimited PTO&#10;• Equity / 401(k) matching" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"><?= e($_POST['benefits'] ?? $job['benefits'] ?? '') ?></textarea>
        </div>

        <!-- Deadline & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-4 border-t border-slate-100">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Application Deadline</label>
                <input type="date" name="deadline" value="<?= e($_POST['deadline'] ?? $job['deadline'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Posting Status</label>
                <?php $st = $_POST['status'] ?? $job['status'] ?? 'active'; ?>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                    <option value="active" <?= $st === 'active' ? 'selected' : '' ?>>Active (Visible on job board)</option>
                    <option value="paused" <?= $st === 'paused' ? 'selected' : '' ?>>Paused (Hidden temporarily)</option>
                    <option value="closed" <?= $st === 'closed' ? 'selected' : '' ?>>Closed (Applications ended)</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
            <a href="<?= baseUrl('employer/manage_jobs.php') ?>" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-7 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <?= $isEditing ? 'Update Job Posting' : 'Publish Job Posting' ?>
            </button>
        </div>
    </form>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
