<?php
/**
 * CareerPulse - User Sign In Page
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    $u = currentUser();
    $target = match ($u['role']) {
        'employer' => 'employer/dashboard.php',
        'admin' => 'admin/dashboard.php',
        default => 'candidate/dashboard.php',
    };
    header('Location: ' . baseUrl($target));
    exit;
}

$error = null;
$redirect = $_GET['redirect'] ?? '';

// Handle form submission
if (isPost()) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    // Quick demo login buttons pass 'demo_user'
    if (isset($_POST['demo_user'])) {
        $email = match ($_POST['demo_user']) {
            'candidate' => 'alex.morgan@email.com',
            'employer' => 'recruiter@techcorp.com',
            'admin' => 'admin@careerpulse.com',
            default => '',
        };
        $password = 'Password123!';
    }

    if (!isset($_POST['demo_user']) && !validateCsrf($csrf)) {
        $error = 'Invalid security session token. Please try again.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            setFlash('success', 'Welcome back, ' . $result['user']['name'] . '!');
            if (!empty($redirect) && str_starts_with($redirect, '/')) {
                header('Location: ' . $redirect);
            } else {
                $target = match ($result['user']['role']) {
                    'employer' => 'employer/dashboard.php',
                    'admin' => 'admin/dashboard.php',
                    default => 'candidate/dashboard.php',
                };
                header('Location: ' . baseUrl($target));
            }
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Sign In';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-2xl shadow-xl border border-slate-200/80">
        
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 mb-3">
                <i data-lucide="log-in" class="w-6 h-6"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Welcome Back</h2>
            <p class="text-sm text-slate-500 mt-1">Sign in to manage your jobs, applications, and profile</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Quick 1-Click Demo Buttons -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/70">
            <p class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i>
                Instant 1-Click Demo Logins:
            </p>
            <form method="POST" class="grid grid-cols-3 gap-2">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <button type="submit" name="demo_user" value="candidate" class="px-2.5 py-1.5 bg-white hover:bg-brand-50 border border-slate-200 hover:border-brand-300 rounded-lg text-xs font-semibold text-slate-700 hover:text-brand-600 transition shadow-sm text-center">
                    Candidate
                </button>
                <button type="submit" name="demo_user" value="employer" class="px-2.5 py-1.5 bg-white hover:bg-brand-50 border border-slate-200 hover:border-brand-300 rounded-lg text-xs font-semibold text-slate-700 hover:text-brand-600 transition shadow-sm text-center">
                    Recruiter
                </button>
                <button type="submit" name="demo_user" value="admin" class="px-2.5 py-1.5 bg-white hover:bg-brand-50 border border-slate-200 hover:border-brand-300 rounded-lg text-xs font-semibold text-slate-700 hover:text-brand-600 transition shadow-sm text-center">
                    Admin
                </button>
            </form>
        </div>

        <!-- Standard Login Form -->
        <form class="space-y-4" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input id="email" name="email" type="email" autocomplete="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@company.com" class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition outline-none">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••" class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition outline-none">
                </div>
            </div>

            <button type="submit" class="w-full mt-2 py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-brand-500/20 transition flex items-center justify-center gap-2">
                <span>Sign In</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <div class="text-center text-sm text-slate-500 pt-2 border-t border-slate-100">
            Don't have an account yet? 
            <a href="<?= baseUrl('auth/register.php') ?>" class="font-semibold text-brand-600 hover:underline">Create an account</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
