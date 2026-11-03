<?php
/**
 * CareerPulse - Candidate Profile & Resume Management
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth('candidate');
$profile = dbFetchOne("SELECT * FROM candidate_profiles WHERE user_id = ?", [$user['id']]);

$error = null;

if (isPost()) {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security session token. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $headline = trim($_POST['headline'] ?? '');
        $expYears = (int)($_POST['experience_years'] ?? 0);
        $education = trim($_POST['education'] ?? '');
        $skills = trim($_POST['skills'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $portfolio = trim($_POST['portfolio_url'] ?? '');
        $linkedin = trim($_POST['linkedin_url'] ?? '');
        $github = trim($_POST['github_url'] ?? '');

        // Handle resume file upload if provided
        $resumeFilename = $profile['resume_url'] ?? null;
        if (!empty($_FILES['resume']['name'])) {
            $upload = uploadResume($_FILES['resume']);
            if ($upload['success']) {
                $resumeFilename = $upload['filename'];
            } else {
                $error = $upload['error'];
            }
        }

        if (!$error) {
            try {
                // Update users table
                dbExecute("UPDATE users SET name = ?, phone = ? WHERE id = ?", [$name, $phone, $user['id']]);

                // Update or Insert candidate_profiles
                if ($profile) {
                    dbExecute("
                        UPDATE candidate_profiles
                        SET headline = ?, experience_years = ?, education = ?, skills = ?, bio = ?, resume_url = ?, portfolio_url = ?, linkedin_url = ?, github_url = ?
                        WHERE user_id = ?
                    ", [$headline, $expYears, $education, $skills, $bio, $resumeFilename, $portfolio, $linkedin, $github, $user['id']]);
                } else {
                    dbInsert("
                        INSERT INTO candidate_profiles (user_id, headline, experience_years, education, skills, bio, resume_url, portfolio_url, linkedin_url, github_url)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ", [$user['id'], $headline, $expYears, $education, $skills, $bio, $resumeFilename, $portfolio, $linkedin, $github]);
                }

                setFlash('success', 'Your candidate profile was successfully updated!');
                header('Location: ' . baseUrl('candidate/profile.php'));
                exit;
            } catch (Throwable $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Refresh profile data
$user = currentUser();
$profile = dbFetchOne("SELECT * FROM candidate_profiles WHERE user_id = ?", [$user['id']]);

$pageTitle = 'Edit Candidate Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="pb-6 border-b border-slate-200 mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Your Career Profile</h1>
        <p class="text-sm text-slate-500 mt-1">Keep your profile and resume updated for prospective employers</p>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6 bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <!-- Basic info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" required value="<?= e($user['name']) ?>" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email Address</label>
                <input type="email" disabled value="<?= e($user['email']) ?>" class="w-full px-3 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Professional Headline</label>
                <input type="text" name="headline" value="<?= e($profile['headline'] ?? '') ?>" placeholder="e.g. Senior Full-Stack Engineer | React &amp; PHP" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number</label>
                <input type="text" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Years of Experience</label>
                <input type="number" name="experience_years" min="0" max="50" value="<?= (int)($profile['experience_years'] ?? 0) ?>" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Highest Education Degree</label>
                <input type="text" name="education" value="<?= e($profile['education'] ?? '') ?>" placeholder="e.g. B.S. in Computer Science" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Key Skills (Comma Separated)</label>
            <input type="text" name="skills" value="<?= e($profile['skills'] ?? '') ?>" placeholder="e.g. PHP, JavaScript, React, MySQL, Tailwind CSS, Docker" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Professional Bio &amp; Summary</label>
            <textarea name="bio" rows="4" placeholder="Briefly describe your background, career achievements, and interests..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"><?= e($profile['bio'] ?? '') ?></textarea>
        </div>

        <!-- Social Links -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-4 border-t border-slate-100">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Portfolio URL</label>
                <input type="url" name="portfolio_url" value="<?= e($profile['portfolio_url'] ?? '') ?>" placeholder="https://..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">LinkedIn Profile</label>
                <input type="url" name="linkedin_url" value="<?= e($profile['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/in/..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">GitHub / Code</label>
                <input type="url" name="github_url" value="<?= e($profile['github_url'] ?? '') ?>" placeholder="https://github.com/..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
            </div>
        </div>

        <!-- Resume Upload -->
        <div class="pt-4 border-t border-slate-100">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Default Resume / CV (PDF or DOCX)</label>
            <?php if (!empty($profile['resume_url'])): ?>
                <div class="mb-3 flex items-center gap-2 text-xs text-slate-600 bg-slate-100 p-2.5 rounded-xl border border-slate-200">
                    <i data-lucide="file-check" class="w-4 h-4 text-emerald-600"></i>
                    <span>Current file on record: <strong><?= e($profile['resume_url']) ?></strong></span>
                </div>
            <?php endif; ?>
            <input type="file" name="resume" accept=".pdf,.doc,.docx" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer border border-slate-200 rounded-xl bg-slate-50">
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="<?= baseUrl('candidate/dashboard.php') ?>" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Back to Dashboard
            </a>
            <button type="submit" class="px-7 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                Save Profile
            </button>
        </div>
    </form>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
