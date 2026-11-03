<?php
/**
 * CareerPulse - Global Footer Component
 */

declare(strict_types=1);
?>
</main>

<!-- Footer -->
<footer class="bg-slate-900 text-slate-300 border-t border-slate-800 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            
            <!-- Col 1: Brand & Tagline -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xl font-bold text-white tracking-tight">CareerPulse</span>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Connecting top tech and professional talent with high-growth companies, venture-backed startups, and industry leaders worldwide.
                </p>
                <div class="flex items-center gap-3 text-slate-400">
                    <span class="text-xs bg-slate-800 text-slate-300 px-2.5 py-1 rounded-md border border-slate-700">
                        ⚡ Powered by PHP &amp; MySQL
                    </span>
                </div>
            </div>

            <!-- Col 2: For Candidates -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-200 mb-4">For Candidates</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?= baseUrl('jobs.php') ?>" class="hover:text-white transition">Explore Job Listings</a></li>
                    <li><a href="<?= baseUrl('jobs.php?is_remote=1') ?>" class="hover:text-white transition">Remote Tech Jobs</a></li>
                    <li><a href="<?= baseUrl('candidate/dashboard.php') ?>" class="hover:text-white transition">Track Applications</a></li>
                    <li><a href="<?= baseUrl('candidate/profile.php') ?>" class="hover:text-white transition">Build Career Profile</a></li>
                </ul>
            </div>

            <!-- Col 3: For Employers -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-200 mb-4">For Employers</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?= baseUrl('employer/post_job.php') ?>" class="hover:text-white transition">Post a Job Opening</a></li>
                    <li><a href="<?= baseUrl('employer/manage_jobs.php') ?>" class="hover:text-white transition">Review Pipeline</a></li>
                    <li><a href="<?= baseUrl('employer/dashboard.php') ?>" class="hover:text-white transition">Hiring Analytics</a></li>
                    <li><a href="<?= baseUrl('employer/company_profile.php') ?>" class="hover:text-white transition">Company Branding</a></li>
                </ul>
            </div>

            <!-- Col 4: Quick Demo Accounts -->
            <div class="bg-slate-800/60 p-4 rounded-xl border border-slate-700/60">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-400 mb-2 flex items-center gap-1.5">
                    <i data-lucide="key" class="w-3.5 h-3.5"></i>
                    Demo Logins (Pass: Password123!)
                </h3>
                <ul class="space-y-1.5 text-xs text-slate-300 font-mono">
                    <li><strong class="text-white">Admin:</strong> admin@careerpulse.com</li>
                    <li><strong class="text-white">Recruiter:</strong> recruiter@techcorp.com</li>
                    <li><strong class="text-white">Candidate:</strong> alex.morgan@email.com</li>
                </ul>
                <div class="mt-3 pt-3 border-t border-slate-700 text-[11px] text-slate-400">
                    One-click login available on the <a href="<?= baseUrl('auth/login.php') ?>" class="text-brand-400 hover:underline">Sign In page</a>.
                </div>
            </div>

        </div>

        <div class="mt-12 pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
            <p>&copy; <?= date('Y') ?> CareerPulse Inc. All rights reserved.</p>
            <div class="flex items-center gap-6">
                <a href="<?= baseUrl('database/setup.php') ?>" class="text-slate-400 hover:text-white transition">Re-run Database Seeder</a>
                <a href="<?= baseUrl('jobs.php') ?>" class="text-slate-400 hover:text-white transition">All Roles</a>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Mobile menu toggle
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
</script>
</body>
</html>
