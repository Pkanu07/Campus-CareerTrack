<?php
/**
 * CareerPulse - Global Header Component
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = currentUser();
$flash = getFlash();
$activeDriver = getActiveDriver();
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 text-slate-900 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | CareerPulse' : 'CareerPulse — Modern Job Board & Career Platform' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        .glass-nav {
            backdrop-filter: blur(12px);
            background-color: rgba(255, 255, 255, 0.92);
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-slate-50 font-sans selection:bg-brand-500 selection:text-white">

<!-- Top Announcement & System Status Bar -->
<header class="sticky top-0 z-50 glass-nav border-b border-slate-200/80 transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <!-- Brand Logo -->
            <div class="flex items-center gap-8">
                <a href="<?= baseUrl('index.php') ?>" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 via-brand-700 to-indigo-600 bg-clip-text text-transparent">CareerPulse</span>
                        <span class="hidden sm:inline-block ml-1 text-xs px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider <?= $activeDriver === 'mysql' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' ?>">
                            <?= strtoupper($activeDriver) ?>
                        </span>
                    </div>
                </a>

                <!-- Primary Nav Links -->
                <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-slate-600">
                    <a href="<?= baseUrl('index.php') ?>" class="px-3.5 py-2 rounded-lg hover:text-brand-600 hover:bg-slate-100/80 transition <?= $currentPage === 'index.php' ? 'text-brand-600 font-semibold bg-brand-50' : '' ?>">
                        Home
                    </a>
                    <a href="<?= baseUrl('jobs.php') ?>" class="px-3.5 py-2 rounded-lg hover:text-brand-600 hover:bg-slate-100/80 transition <?= $currentPage === 'jobs.php' ? 'text-brand-600 font-semibold bg-brand-50' : '' ?>">
                        Browse Jobs
                    </a>
                    
                    <?php if ($user && $user['role'] === 'candidate'): ?>
                        <a href="<?= baseUrl('candidate/dashboard.php') ?>" class="px-3.5 py-2 rounded-lg hover:text-brand-600 hover:bg-slate-100/80 transition <?= $currentPage === 'dashboard.php' ? 'text-brand-600 font-semibold bg-brand-50' : '' ?>">
                            My Applications
                        </a>
                        <a href="<?= baseUrl('candidate/saved_jobs.php') ?>" class="px-3.5 py-2 rounded-lg hover:text-brand-600 hover:bg-slate-100/80 transition">
                            Saved Jobs
                        </a>
                    <?php elseif ($user && $user['role'] === 'employer'): ?>
                        <a href="<?= baseUrl('employer/dashboard.php') ?>" class="px-3.5 py-2 rounded-lg hover:text-brand-600 hover:bg-slate-100/80 transition <?= $currentPage === 'dashboard.php' ? 'text-brand-600 font-semibold bg-brand-50' : '' ?>">
                            Recruiter Dashboard
                        </a>
                        <a href="<?= baseUrl('employer/manage_jobs.php') ?>" class="px-3.5 py-2 rounded-lg hover:text-brand-600 hover:bg-slate-100/80 transition">
                            Manage Listings
                        </a>
                    <?php elseif ($user && $user['role'] === 'admin'): ?>
                        <a href="<?= baseUrl('admin/dashboard.php') ?>" class="px-3.5 py-2 rounded-lg hover:text-brand-600 hover:bg-slate-100/80 transition">
                            Admin Center
                        </a>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- Right Nav / User Profile -->
            <div class="flex items-center gap-3">
                <?php if ($user): ?>
                    <!-- Quick Post Job button for Employers -->
                    <?php if ($user['role'] === 'employer'): ?>
                        <a href="<?= baseUrl('employer/post_job.php') ?>" class="hidden sm:inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold px-3.5 py-2 rounded-lg shadow-sm transition">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            Post a Job
                        </a>
                    <?php endif; ?>

                    <!-- User Pill Dropdown Trigger -->
                    <div class="relative group">
                        <button class="flex items-center gap-2.5 p-1.5 pl-2.5 rounded-full bg-slate-100 hover:bg-slate-200/80 transition text-slate-700 font-medium text-sm border border-slate-200">
                            <span class="w-7 h-7 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </span>
                            <span class="hidden sm:inline max-w-[120px] truncate"><?= e($user['name']) ?></span>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full <?= $user['role'] === 'employer' ? 'bg-purple-100 text-purple-700' : ($user['role'] === 'admin' ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700') ?>">
                                <?= e($user['role']) ?>
                            </span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs text-slate-400 font-medium">Signed in as</p>
                                <p class="text-sm font-semibold text-slate-900 truncate"><?= e($user['email']) ?></p>
                            </div>

                            <?php if ($user['role'] === 'candidate'): ?>
                                <a href="<?= baseUrl('candidate/dashboard.php') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-400"></i>
                                    Candidate Dashboard
                                </a>
                                <a href="<?= baseUrl('candidate/profile.php') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition">
                                    <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                                    My Profile & Resume
                                </a>
                                <a href="<?= baseUrl('candidate/saved_jobs.php') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition">
                                    <i data-lucide="bookmark" class="w-4 h-4 text-slate-400"></i>
                                    Saved Jobs
                                </a>
                            <?php elseif ($user['role'] === 'employer'): ?>
                                <a href="<?= baseUrl('employer/dashboard.php') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-400"></i>
                                    Employer Dashboard
                                </a>
                                <a href="<?= baseUrl('employer/manage_jobs.php') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition">
                                    <i data-lucide="briefcase" class="w-4 h-4 text-slate-400"></i>
                                    Job Postings
                                </a>
                                <a href="<?= baseUrl('employer/company_profile.php') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition">
                                    <i data-lucide="building" class="w-4 h-4 text-slate-400"></i>
                                    Company Profile
                                </a>
                            <?php elseif ($user['role'] === 'admin'): ?>
                                <a href="<?= baseUrl('admin/dashboard.php') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-slate-400"></i>
                                    Admin Center
                                </a>
                            <?php endif; ?>

                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="<?= baseUrl('auth/logout.php') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 transition">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                Sign Out
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Guest Links -->
                    <a href="<?= baseUrl('auth/login.php') ?>" class="text-sm font-semibold text-slate-700 hover:text-brand-600 px-3 py-2 transition">
                        Sign In
                    </a>
                    <a href="<?= baseUrl('auth/register.php') ?>" class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm shadow-brand-500/20 transition">
                        Get Started
                    </a>
                <?php endif; ?>

                <!-- Mobile menu button -->
                <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Toggle Menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Container -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-slate-200 py-3 space-y-1">
            <a href="<?= baseUrl('index.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Home</a>
            <a href="<?= baseUrl('jobs.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Browse Jobs</a>
            <?php if ($user): ?>
                <?php if ($user['role'] === 'candidate'): ?>
                    <a href="<?= baseUrl('candidate/dashboard.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">My Applications</a>
                    <a href="<?= baseUrl('candidate/profile.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">My Profile</a>
                <?php elseif ($user['role'] === 'employer'): ?>
                    <a href="<?= baseUrl('employer/dashboard.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Employer Dashboard</a>
                    <a href="<?= baseUrl('employer/post_job.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Post a Job</a>
                <?php elseif ($user['role'] === 'admin'): ?>
                    <a href="<?= baseUrl('admin/dashboard.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Admin Center</a>
                <?php endif; ?>
                <a href="<?= baseUrl('auth/logout.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-rose-600 hover:bg-rose-50">Sign Out</a>
            <?php else: ?>
                <a href="<?= baseUrl('auth/login.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Sign In</a>
                <a href="<?= baseUrl('auth/register.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-brand-600 font-semibold hover:bg-brand-50">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Flash Alerts Notification Banner -->
<?php if ($flash): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="flex items-center justify-between p-4 rounded-xl border shadow-sm text-sm font-medium transition <?= match ($flash['type']) {
            'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'error' => 'bg-rose-50 border-rose-200 text-rose-800',
            'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
            default => 'bg-blue-50 border-blue-200 text-blue-800',
        } ?>" role="alert">
            <div class="flex items-center gap-3">
                <i data-lucide="<?= match ($flash['type']) {
                    'success' => 'check-circle-2',
                    'error' => 'alert-circle',
                    'warning' => 'alert-triangle',
                    default => 'info',
                } ?>" class="w-5 h-5 flex-shrink-0"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content Area -->
<main class="flex-grow">
