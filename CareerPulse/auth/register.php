<?php
/**
 * CareerPulse - Registration Page
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . baseUrl('index.php'));
    exit;
}

$error = null;
$role = $_GET['role'] ?? 'candidate';
if (!in_array($role, ['candidate', 'employer'], true)) {
    $role = 'candidate';
}

if (isPost()) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['password_confirmation'] ?? '';
    $selectedRole = $_POST['role'] ?? 'candidate';
    $companyName = $_POST['company_name'] ?? null;
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validateCsrf($csrf)) {
        $error = 'Invalid security session token. Please try again.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif ($selectedRole === 'employer' && empty(trim($companyName ?? ''))) {
        $error = 'Please enter your company or organization name.';
    } else {
        $result = registerUser($name, $email, $password, $selectedRole, $companyName);
        if ($result['success']) {
            setFlash('success', 'Account created successfully! Welcome to CareerPulse.');
            $dest = ($selectedRole === 'employer') ? 'employer/dashboard.php' : 'candidate/dashboard.php';
            header('Location: ' . baseUrl($dest));
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6 bg-white p-8 sm:p-10 rounded-2xl shadow-xl border border-slate-200/80">
        
        <div class="text-center">
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Create your account</h2>
            <p class="text-sm text-slate-500 mt-1">Join CareerPulse to apply or recruit top industry talent</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Role Selector Switch -->
        <div class="grid grid-cols-2 gap-3 p-1 bg-slate-100 rounded-xl">
            <button type="button" id="roleBtnCandidate" onclick="setRole('candidate')" class="py-2.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 bg-white text-slate-900 shadow-sm border border-slate-200/60">
                <i data-lucide="user" class="w-4 h-4"></i>
                Job Seeker
            </button>
            <button type="button" id="roleBtnEmployer" onclick="setRole('employer')" class="py-2.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 text-slate-500 hover:text-slate-900">
                <i data-lucide="building" class="w-4 h-4"></i>
                Employer / Recruiter
            </button>
        </div>

        <form class="space-y-4" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="role" id="roleInput" value="<?= e($role) ?>">

            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Full Name</label>
                <input id="name" name="name" type="text" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Alex Morgan" class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition outline-none">
            </div>

            <!-- Employer Only: Company Name -->
            <div id="companyField" class="<?= $role === 'employer' ? '' : 'hidden' ?>">
                <label for="company_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Company / Organization Name</label>
                <input id="company_name" name="company_name" type="text" value="<?= e($_POST['company_name'] ?? '') ?>" placeholder="e.g. TechCorp Solutions" class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition outline-none">
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Work or Personal Email</label>
                <input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@email.com" class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" required placeholder="Min 6 chars" class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition outline-none">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Confirm</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Repeat password" class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition outline-none">
                </div>
            </div>

            <button type="submit" class="w-full mt-2 py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-brand-500/20 transition flex items-center justify-center gap-2">
                <span>Create Account</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <div class="text-center text-sm text-slate-500 pt-2 border-t border-slate-100">
            Already registered? 
            <a href="<?= baseUrl('auth/login.php') ?>" class="font-semibold text-brand-600 hover:underline">Sign in instead</a>
        </div>
    </div>
</div>

<script>
function setRole(role) {
    const input = document.getElementById('roleInput');
    const btnCand = document.getElementById('roleBtnCandidate');
    const btnEmp = document.getElementById('roleBtnEmployer');
    const compField = document.getElementById('companyField');

    input.value = role;

    if (role === 'employer') {
        btnEmp.className = 'py-2.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 bg-white text-slate-900 shadow-sm border border-slate-200/60';
        btnCand.className = 'py-2.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 text-slate-500 hover:text-slate-900';
        compField.classList.remove('hidden');
    } else {
        btnCand.className = 'py-2.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 bg-white text-slate-900 shadow-sm border border-slate-200/60';
        btnEmp.className = 'py-2.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 text-slate-500 hover:text-slate-900';
        compField.classList.add('hidden');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
