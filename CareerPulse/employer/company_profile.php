<?php
/**
 * CareerPulse - Company Profile Management
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth('employer');
$profile = dbFetchOne("SELECT * FROM employer_profiles WHERE user_id = ?", [$user['id']]);

$error = null;

if (isPost()) {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security session token. Please try again.';
    } else {
        $companyName = trim($_POST['company_name'] ?? '');
        $industry = trim($_POST['industry'] ?? '');
        $companySize = trim($_POST['company_size'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $about = trim($_POST['about'] ?? '');
        $contactPhone = trim($_POST['phone'] ?? '');

        if (empty($companyName)) {
            $error = 'Company name cannot be blank.';
        } else {
            try {
                dbExecute("UPDATE users SET phone = ? WHERE id = ?", [$contactPhone, $user['id']]);

                if ($profile) {
                    dbExecute("
                        UPDATE employer_profiles
                        SET company_name = ?, industry = ?, company_size = ?, website = ?, location = ?, about = ?
                        WHERE user_id = ?
                    ", [$companyName, $industry, $companySize, $website, $location, $about, $user['id']]);
                } else {
                    dbInsert("
                        INSERT INTO employer_profiles (user_id, company_name, industry, company_size, website, location, about)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ", [$user['id'], $companyName, $industry, $companySize, $website, $location, $about]);
                }

                setFlash('success', 'Company profile details updated successfully!');
                header('Location: ' . baseUrl('employer/company_profile.php'));
                exit;
            } catch (Throwable $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$user = currentUser();
$profile = dbFetchOne("SELECT * FROM employer_profiles WHERE user_id = ?", [$user['id']]);

$pageTitle = 'Company Profile & Branding';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="pb-6 border-b border-slate-200 mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Company Profile &amp; Branding</h1>
        <p class="text-sm text-slate-500 mt-1">This information is shown to prospective applicants across all your job postings</p>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6 bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Company / Organization Name *</label>
                <input type="text" name="company_name" required value="<?= e($profile['company_name'] ?? $user['name']) ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Industry Sector</label>
                <input type="text" name="industry" value="<?= e($profile['industry'] ?? '') ?>" placeholder="e.g. Enterprise Cloud Software, Fintech, AI" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Company Size</label>
                <?php $size = $profile['company_size'] ?? ''; ?>
                <select name="company_size" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                    <option value="">Select size</option>
                    <option value="1-10 employees" <?= $size === '1-10 employees' ? 'selected' : '' ?>>1-10 employees</option>
                    <option value="11-50 employees" <?= $size === '11-50 employees' ? 'selected' : '' ?>>11-50 employees</option>
                    <option value="50-200 employees" <?= $size === '50-200 employees' ? 'selected' : '' ?>>50-200 employees</option>
                    <option value="200-500 employees" <?= $size === '200-500 employees' ? 'selected' : '' ?>>200-500 employees</option>
                    <option value="500-1000 employees" <?= $size === '500-1000 employees' ? 'selected' : '' ?>>500-1000 employees</option>
                    <option value="1000+ employees" <?= $size === '1000+ employees' ? 'selected' : '' ?>>1000+ employees</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Official Website</label>
                <input type="url" name="website" value="<?= e($profile['website'] ?? '') ?>" placeholder="https://example.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Headquarters / Location</label>
                <input type="text" name="location" value="<?= e($profile['location'] ?? '') ?>" placeholder="e.g. San Francisco, CA" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Company Overview &amp; Culture</label>
            <textarea name="about" rows="4" placeholder="Tell potential hires about your team, tech stack, company vision, and work culture..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"><?= e($profile['about'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Phone</label>
            <input type="text" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
            <a href="<?= baseUrl('employer/dashboard.php') ?>" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-7 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                Save Profile
            </button>
        </div>
    </form>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
