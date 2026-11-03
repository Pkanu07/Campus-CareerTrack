<?php
/**
 * CareerPulse - Database Setup & Seeder Script
 * Run from CLI: php database/setup.php
 * Or access via browser: http://localhost:8000/database/setup.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function runSetup(): array {
    $results = [];
    try {
        $db = getDB();
        $driver = getActiveDriver();
        $results[] = "Connected to database using driver: [{$driver}]";

        // Create tables based on driver
        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(150) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    role ENUM('candidate', 'employer', 'admin') DEFAULT 'candidate',
                    phone VARCHAR(30) NULL,
                    avatar VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS employer_profiles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL UNIQUE,
                    company_name VARCHAR(150) NOT NULL,
                    industry VARCHAR(100) NULL,
                    company_size VARCHAR(50) NULL,
                    website VARCHAR(255) NULL,
                    location VARCHAR(150) NULL,
                    about TEXT NULL,
                    logo_url VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS candidate_profiles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL UNIQUE,
                    headline VARCHAR(200) NULL,
                    experience_years INT DEFAULT 0,
                    education VARCHAR(255) NULL,
                    skills TEXT NULL,
                    bio TEXT NULL,
                    resume_url VARCHAR(255) NULL,
                    portfolio_url VARCHAR(255) NULL,
                    linkedin_url VARCHAR(255) NULL,
                    github_url VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    slug VARCHAR(100) NOT NULL UNIQUE,
                    icon VARCHAR(50) DEFAULT 'briefcase',
                    description VARCHAR(255) NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS jobs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employer_id INT NOT NULL,
                    category_id INT NOT NULL,
                    title VARCHAR(200) NOT NULL,
                    slug VARCHAR(220) NULL,
                    job_type ENUM('full-time', 'part-time', 'contract', 'remote', 'internship') DEFAULT 'full-time',
                    experience_level ENUM('entry', 'mid', 'senior', 'lead', 'executive') DEFAULT 'entry',
                    location VARCHAR(150) NOT NULL,
                    is_remote TINYINT(1) DEFAULT 0,
                    salary_min INT NULL,
                    salary_max INT NULL,
                    salary_currency VARCHAR(10) DEFAULT 'USD',
                    description MEDIUMTEXT NOT NULL,
                    requirements MEDIUMTEXT NULL,
                    benefits TEXT NULL,
                    status ENUM('active', 'paused', 'closed') DEFAULT 'active',
                    is_featured TINYINT(1) DEFAULT 0,
                    deadline DATE NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS applications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    job_id INT NOT NULL,
                    candidate_id INT NOT NULL,
                    resume_path VARCHAR(255) NOT NULL,
                    cover_note TEXT NULL,
                    status ENUM('pending', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected') DEFAULT 'pending',
                    employer_notes TEXT NULL,
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user_job (job_id, candidate_id),
                    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
                    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS saved_jobs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    job_id INT NOT NULL,
                    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user_saved (user_id, job_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } else {
            // SQLite schema
            $db->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    role TEXT CHECK(role IN ('candidate', 'employer', 'admin')) DEFAULT 'candidate',
                    phone TEXT NULL,
                    avatar TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS employer_profiles (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL UNIQUE,
                    company_name TEXT NOT NULL,
                    industry TEXT NULL,
                    company_size TEXT NULL,
                    website TEXT NULL,
                    location TEXT NULL,
                    about TEXT NULL,
                    logo_url TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS candidate_profiles (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL UNIQUE,
                    headline TEXT NULL,
                    experience_years INTEGER DEFAULT 0,
                    education TEXT NULL,
                    skills TEXT NULL,
                    bio TEXT NULL,
                    resume_url TEXT NULL,
                    portfolio_url TEXT NULL,
                    linkedin_url TEXT NULL,
                    github_url TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE,
                    slug TEXT NOT NULL UNIQUE,
                    icon TEXT DEFAULT 'briefcase',
                    description TEXT NULL
                );

                CREATE TABLE IF NOT EXISTS jobs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    employer_id INTEGER NOT NULL,
                    category_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    slug TEXT NULL,
                    job_type TEXT DEFAULT 'full-time',
                    experience_level TEXT DEFAULT 'entry',
                    location TEXT NOT NULL,
                    is_remote INTEGER DEFAULT 0,
                    salary_min INTEGER NULL,
                    salary_max INTEGER NULL,
                    salary_currency TEXT DEFAULT 'USD',
                    description TEXT NOT NULL,
                    requirements TEXT NULL,
                    benefits TEXT NULL,
                    status TEXT DEFAULT 'active',
                    is_featured INTEGER DEFAULT 0,
                    deadline DATE NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
                );

                CREATE TABLE IF NOT EXISTS applications (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    job_id INTEGER NOT NULL,
                    candidate_id INTEGER NOT NULL,
                    resume_path TEXT NOT NULL,
                    cover_note TEXT NULL,
                    status TEXT DEFAULT 'pending',
                    employer_notes TEXT NULL,
                    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE (job_id, candidate_id),
                    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
                    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS saved_jobs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    job_id INTEGER NOT NULL,
                    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE (user_id, job_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
                );
            ");
        }
        $results[] = "Database tables verified/created successfully.";

        // Check if users already seeded
        $userCount = (int)dbFetchValue("SELECT COUNT(*) FROM users");
        if ($userCount > 0) {
            $results[] = "Database already has {$userCount} users. Skipping seed insertion.";
            return $results;
        }

        // Seed data
        $passwordHash = password_hash('Password123!', PASSWORD_BCRYPT);

        // 1. Categories
        $categories = [
            ['Software Engineering', 'software-engineering', 'code', 'Full-stack, backend, frontend, and mobile engineering roles.'],
            ['Product & Design', 'product-design', 'palette', 'UI/UX design, product management, user research, and branding.'],
            ['Data & AI', 'data-ai', 'cpu', 'Machine learning, data science, analytics, and AI prompt engineering.'],
            ['DevOps & Cloud', 'devops-cloud', 'server', 'Cloud infrastructure, CI/CD, Kubernetes, and cybersecurity.'],
            ['Marketing & Growth', 'marketing-growth', 'trending-up', 'Performance marketing, content strategy, SEO, and sales.'],
            ['Healthcare & Science', 'healthcare-science', 'activity', 'Health informatics, biomedical tech, and clinical analysts.']
        ];

        foreach ($categories as $cat) {
            dbInsert(
                "INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)",
                $cat
            );
        }
        $results[] = "Seeded " . count($categories) . " job categories.";

        // 2. Users (Admin, Employers, Candidates)
        $users = [
            // Admin
            ['System Admin', 'admin@careerpulse.com', $passwordHash, 'admin', '+1 555-0100', null],
            // Employers
            ['Sarah Vance (TechCorp Global)', 'recruiter@techcorp.com', $passwordHash, 'employer', '+1 555-0110', null],
            ['Marcus Reed (InnovateLabs)', 'hiring@innovatelabs.io', $passwordHash, 'employer', '+1 555-0120', null],
            ['Elena Rostova (HealthFirst)', 'talent@healthfirst.org', $passwordHash, 'employer', '+1 555-0130', null],
            // Candidates
            ['Alex Morgan', 'alex.morgan@email.com', $passwordHash, 'candidate', '+1 555-0210', null],
            ['Sarah Chen', 'sarah.chen@email.com', $passwordHash, 'candidate', '+1 555-0220', null],
            ['David Kim', 'david.kim@email.com', $passwordHash, 'candidate', '+1 555-0230', null],
        ];

        $userIds = [];
        foreach ($users as $u) {
            $id = dbInsert(
                "INSERT INTO users (name, email, password, role, phone, avatar) VALUES (?, ?, ?, ?, ?, ?)",
                $u
            );
            $userIds[$u[1]] = $id;
        }
        $results[] = "Seeded " . count($users) . " system users.";

        // 3. Employer Profiles
        dbInsert(
            "INSERT INTO employer_profiles (user_id, company_name, industry, company_size, website, location, about) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $userIds['recruiter@techcorp.com'],
                'TechCorp Global',
                'Enterprise Cloud Software',
                '500-1000 employees',
                'https://techcorp.example.com',
                'San Francisco, CA / Remote',
                'TechCorp Global is a premier provider of distributed enterprise cloud platforms and developer tooling.'
            ]
        );

        dbInsert(
            "INSERT INTO employer_profiles (user_id, company_name, industry, company_size, website, location, about) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $userIds['hiring@innovatelabs.io'],
                'InnovateLabs AI',
                'Artificial Intelligence & ML',
                '50-200 employees',
                'https://innovatelabs.example.com',
                'New York, NY / Hybrid',
                'Building next-generation generative AI infrastructure and contextual agents for enterprise workflows.'
            ]
        );

        dbInsert(
            "INSERT INTO employer_profiles (user_id, company_name, industry, company_size, website, location, about) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $userIds['talent@healthfirst.org'],
                'HealthFirst Diagnostics',
                'Digital Healthcare',
                '1000+ employees',
                'https://healthfirst.example.com',
                'Boston, MA',
                'Transforming patient diagnostic workflows through secure telemedicine and clinical informatics.'
            ]
        );

        // 4. Candidate Profiles
        dbInsert(
            "INSERT INTO candidate_profiles (user_id, headline, experience_years, education, skills, bio, portfolio_url, linkedin_url, github_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userIds['alex.morgan@email.com'],
                'Senior Full-Stack Engineer | PHP, JavaScript, React, MySQL',
                5,
                'B.S. in Computer Science, State University',
                'PHP, JavaScript, React, Node.js, MySQL, PostgreSQL, Docker, Tailwind CSS, REST APIs',
                'Passionate full-stack developer with 5+ years of experience crafting high-performance, accessible web platforms.',
                'https://alexmorgan.dev',
                'https://linkedin.com/in/alexmorgan',
                'https://github.com/alexmorgan'
            ]
        );

        dbInsert(
            "INSERT INTO candidate_profiles (user_id, headline, experience_years, education, skills, bio, portfolio_url, linkedin_url, github_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userIds['sarah.chen@email.com'],
                'Lead Product & UI/UX Designer | Design Systems & Prototyping',
                4,
                'B.A. in Interactive Media & Graphic Design',
                'Figma, UI/UX Design, Design Systems, Wireframing, User Testing, HTML/CSS, Tailwind',
                'Creative product designer focused on creating intuitive, human-centered SaaS tools and fluid interactions.',
                'https://sarahchen.design',
                'https://linkedin.com/in/sarahchen',
                'https://github.com/sarahchen'
            ]
        );

        dbInsert(
            "INSERT INTO candidate_profiles (user_id, headline, experience_years, education, skills, bio, portfolio_url, linkedin_url, github_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userIds['david.kim@email.com'],
                'Machine Learning Engineer & Data Scientist | Python, PyTorch, SQL',
                3,
                'M.S. in Data Science & Applied AI',
                'Python, SQL, PyTorch, Scikit-Learn, TensorFlow, Pandas, Docker, Cloud ML',
                'Data scientist experienced in fine-tuning transformers, building predictive pipelines, and high-throughput data processing.',
                'https://davidkim.ai',
                'https://linkedin.com/in/davidkim',
                'https://github.com/davidkim'
            ]
        );

        // Fetch category map
        $catRows = dbFetchAll("SELECT id, slug FROM categories");
        $catMap = [];
        foreach ($catRows as $c) {
            $catMap[$c['slug']] = (int)$c['id'];
        }

        // 5. Realistic Job Postings
        $jobs = [
            [
                'employer_id' => $userIds['recruiter@techcorp.com'],
                'category_id' => $catMap['software-engineering'],
                'title' => 'Senior Full-Stack Web Developer (PHP & Modern JS)',
                'slug' => 'senior-full-stack-web-developer',
                'job_type' => 'full-time',
                'experience_level' => 'senior',
                'location' => 'San Francisco, CA / Remote',
                'is_remote' => 1,
                'salary_min' => 125000,
                'salary_max' => 155000,
                'salary_currency' => 'USD',
                'description' => "We are looking for an experienced Senior Full-Stack Engineer to architect, build, and maintain mission-critical web applications for our enterprise platform.\n\nYou will work across our backend PHP/MySQL services and modern reactive frontend, collaborating with designers and product managers to release clean, robust features.",
                'requirements' => "• 4+ years of production experience with PHP (8.x) and relational databases (MySQL/PostgreSQL)\n• Strong proficiency in modern JavaScript, CSS/Tailwind, and RESTful APIs\n• Experience with unit testing, git workflows, and CI/CD pipelines\n• Solid understanding of web security best practices (OWASP, CSRF, XSS prevention)",
                'benefits' => "• Competitive base salary and equity grant\n• 100% remote flexibility and $1,500 home office stipend\n• Comprehensive medical, dental, and vision coverage\n• 401(k) matching up to 5%\n• Unlimited Paid Time Off (PTO)",
                'status' => 'active',
                'is_featured' => 1,
                'deadline' => date('Y-m-d', strtotime('+30 days'))
            ],
            [
                'employer_id' => $userIds['hiring@innovatelabs.io'],
                'category_id' => $catMap['data-ai'],
                'title' => 'Machine Learning & NLP Research Engineer',
                'slug' => 'ml-nlp-research-engineer',
                'job_type' => 'full-time',
                'experience_level' => 'mid',
                'location' => 'New York, NY / Hybrid',
                'is_remote' => 0,
                'salary_min' => 135000,
                'salary_max' => 170000,
                'salary_currency' => 'USD',
                'description' => "Join InnovateLabs AI as we build state-of-the-art conversational agents and semantic search models.\n\nYou will design inference pipelines, benchmark retrieval-augmented generation (RAG) systems, and optimize model latency for production enterprise clients.",
                'requirements' => "• 3+ years experience developing with Python, PyTorch or JAX\n• Deep understanding of Transformer architectures, vector embeddings, and LLM fine-tuning\n• Strong SQL skills and database optimization techniques\n• Degree in Computer Science, Mathematics, or equivalent field",
                'benefits' => "• Cutting-edge GPU compute clusters (H100 access)\n• Catered daily lunches at our Soho, NY office\n• Generous annual learning & conference travel budget\n• Full health insurance and wellness stipends",
                'status' => 'active',
                'is_featured' => 1,
                'deadline' => date('Y-m-d', strtotime('+45 days'))
            ],
            [
                'employer_id' => $userIds['hiring@innovatelabs.io'],
                'category_id' => $catMap['product-design'],
                'title' => 'Lead Product & UI/UX Designer',
                'slug' => 'lead-product-ui-ux-designer',
                'job_type' => 'full-time',
                'experience_level' => 'lead',
                'location' => 'Remote (US/Canada)',
                'is_remote' => 1,
                'salary_min' => 115000,
                'salary_max' => 145000,
                'salary_currency' => 'USD',
                'description' => "We are looking for a visionary Lead Product Designer to own the user journey across our developer portal and SaaS dashboard.\n\nYou will create seamless interfaces that turn complex AI workflows into clean, effortless user experiences.",
                'requirements' => "• 4+ years of UI/UX design experience for SaaS or web apps\n• Mastery of Figma, auto-layout, interactive component libraries, and design tokens\n• Strong portfolio demonstrating end-to-end design thinking from discovery to delivery\n• Ability to write clean HTML/CSS or collaborate directly with frontend engineers",
                'benefits' => "• Fully remote role with flexible working hours\n• Latest M3 Max MacBook Pro + 4K dual monitor setup\n• Annual health and wellness retreat\n• 25 days paid vacation + bank holidays",
                'status' => 'active',
                'is_featured' => 1,
                'deadline' => date('Y-m-d', strtotime('+20 days'))
            ],
            [
                'employer_id' => $userIds['recruiter@techcorp.com'],
                'category_id' => $catMap['devops-cloud'],
                'title' => 'Senior Cloud DevOps & Site Reliability Engineer',
                'slug' => 'senior-cloud-devops-sre',
                'job_type' => 'full-time',
                'experience_level' => 'senior',
                'location' => 'San Francisco, CA',
                'is_remote' => 1,
                'salary_min' => 140000,
                'salary_max' => 180000,
                'salary_currency' => 'USD',
                'description' => "Help us scale high-availability infrastructure supporting over 10 million daily active requests. You will build resilient Kubernetes clusters, automate deployment pipelines, and maintain observability.",
                'requirements' => "• 5+ years supporting production cloud infrastructure (AWS/GCP)\n• Expertise in Kubernetes, Docker, Terraform, and GitHub Actions\n• Strong knowledge of monitoring tools (Prometheus, Grafana, Datadog)\n• Experience with database replication and disaster recovery planning",
                'benefits' => "• Top-of-market compensation and equity package\n• Flexible remote work culture\n• Full medical, dental, and vision insurance\n• Dedicated paid professional development time",
                'status' => 'active',
                'is_featured' => 0,
                'deadline' => date('Y-m-d', strtotime('+35 days'))
            ],
            [
                'employer_id' => $userIds['talent@healthfirst.org'],
                'category_id' => $catMap['healthcare-science'],
                'title' => 'Health Informatics Data Analyst',
                'slug' => 'health-informatics-data-analyst',
                'job_type' => 'full-time',
                'experience_level' => 'mid',
                'location' => 'Boston, MA',
                'is_remote' => 0,
                'salary_min' => 88000,
                'salary_max' => 115000,
                'salary_currency' => 'USD',
                'description' => "HealthFirst Diagnostics is seeking a Data Analyst to transform clinical and laboratory telemetry into actionable health insights and compliance reports.",
                'requirements' => "• 2+ years working with healthcare data standards (HL7, FHIR, ICD-10)\n• Strong SQL skills, data cleansing, and Tableau/PowerBI dashboard design\n• Familiarity with HIPAA compliance and clinical security protocols\n• Bachelor's degree in Health Informatics, Statistics, or related discipline",
                'benefits' => "• State pension contribution and 401(a) retirement program\n• Comprehensive healthcare plan with low copays\n• Tuition reimbursement program\n• Generous paid leave policy",
                'status' => 'active',
                'is_featured' => 0,
                'deadline' => date('Y-m-d', strtotime('+40 days'))
            ],
            [
                'employer_id' => $userIds['recruiter@techcorp.com'],
                'category_id' => $catMap['software-engineering'],
                'title' => 'Software Engineering Intern (Summer 2027)',
                'slug' => 'software-engineering-intern',
                'job_type' => 'internship',
                'experience_level' => 'entry',
                'location' => 'Remote / US',
                'is_remote' => 1,
                'salary_min' => 45000,
                'salary_max' => 60000,
                'salary_currency' => 'USD',
                'description' => "Jumpstart your engineering career! You will be paired with senior mentors to work on real customer-facing features, participate in code reviews, and learn modern software engineering in a supportive environment.",
                'requirements' => "• Currently pursuing or recent graduate in Computer Science, Software Engineering, or related field\n• Familiarity with web fundamentals (HTML, CSS, JavaScript, PHP or Python)\n• Passion for writing clean code and eagerness to learn\n• Excellent communication and teamwork skills",
                'benefits' => "• Competitive hourly pay\n• 1-on-1 mentorship from principal architects\n• Company laptop and tech gear provided\n• High possibility of full-time conversion offer",
                'status' => 'active',
                'is_featured' => 1,
                'deadline' => date('Y-m-d', strtotime('+60 days'))
            ]
        ];

        $jobIds = [];
        foreach ($jobs as $job) {
            $id = dbInsert("
                INSERT INTO jobs (employer_id, category_id, title, slug, job_type, experience_level, location, is_remote, salary_min, salary_max, salary_currency, description, requirements, benefits, status, is_featured, deadline)
                VALUES (:employer_id, :category_id, :title, :slug, :job_type, :experience_level, :location, :is_remote, :salary_min, :salary_max, :salary_currency, :description, :requirements, :benefits, :status, :is_featured, :deadline)
            ", $job);
            $jobIds[$job['title']] = $id;
        }
        $results[] = "Seeded " . count($jobs) . " realistic job listings.";

        // 6. Sample Applications
        $applications = [
            [
                'job_id' => $jobIds['Senior Full-Stack Web Developer (PHP & Modern JS)'],
                'candidate_id' => $userIds['alex.morgan@email.com'],
                'resume_path' => 'sample_alex_morgan_resume.pdf',
                'cover_note' => "Hi Hiring Team, I have 5 years of production PHP and modern web experience, building high-scale SaaS products. I would love to contribute to TechCorp Global's enterprise platforms!",
                'status' => 'shortlisted',
                'employer_notes' => 'Strong background in modern PHP 8 and component-driven frontend. Schedule technical screen.'
            ],
            [
                'job_id' => $jobIds['Software Engineering Intern (Summer 2027)'],
                'candidate_id' => $userIds['alex.morgan@email.com'],
                'resume_path' => 'sample_alex_morgan_resume.pdf',
                'cover_note' => "Exploring new mentor opportunities and excited about TechCorp's engineering standards.",
                'status' => 'interview',
                'employer_notes' => 'Interview round 1 completed. Candidate demonstrated solid fundamentals.'
            ],
            [
                'job_id' => $jobIds['Lead Product & UI/UX Designer'],
                'candidate_id' => $userIds['sarah.chen@email.com'],
                'resume_path' => 'sample_sarah_chen_resume.pdf',
                'cover_note' => "Hello InnovateLabs team, as a seasoned product designer with deep experience in AI user interfaces and design systems, I'm thrilled to apply for the Lead Product Designer position.",
                'status' => 'under_review',
                'employer_notes' => 'Portfolio looks stellar. Reviewing design system case studies.'
            ],
            [
                'job_id' => $jobIds['Machine Learning & NLP Research Engineer'],
                'candidate_id' => $userIds['david.kim@email.com'],
                'resume_path' => 'sample_david_kim_resume.pdf',
                'cover_note' => "I have published work on transformer fine-tuning and built high-performance RAG pipelines in production. Looking forward to discussing this role!",
                'status' => 'accepted',
                'employer_notes' => 'Offer extended and accepted. Start date confirmed for next month.'
            ]
        ];

        foreach ($applications as $app) {
            dbInsert("
                INSERT INTO applications (job_id, candidate_id, resume_path, cover_note, status, employer_notes)
                VALUES (:job_id, :candidate_id, :resume_path, :cover_note, :status, :employer_notes)
            ", $app);
        }
        $results[] = "Seeded " . count($applications) . " sample applications.";

        // 7. Sample Saved Jobs
        dbInsert("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)", [$userIds['alex.morgan@email.com'], $jobIds['Lead Product & UI/UX Designer']]);
        dbInsert("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)", [$userIds['sarah.chen@email.com'], $jobIds['Senior Full-Stack Web Developer (PHP & Modern JS)']]);
        $results[] = "Seeded sample bookmarks.";

    } catch (Throwable $e) {
        $results[] = "ERROR: " . $e->getMessage();
    }
    return $results;
}

// Run if called directly
$output = runSetup();

// If called via web
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>CareerPulse Database Setup</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-50 text-slate-800 p-8 font-sans">
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md p-6 border border-slate-200">
            <h1 class="text-2xl font-bold text-indigo-600 mb-4 flex items-center gap-2">
                🚀 CareerPulse Database Setup
            </h1>
            <div class="space-y-2 mb-6">
                <?php foreach ($output as $line): ?>
                    <div class="p-3 rounded-lg text-sm font-mono <?= str_starts_with($line, 'ERROR') ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-slate-100 text-slate-700' ?>">
                        <?= htmlspecialchars($line) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="../index.php" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow transition">
                Go to CareerPulse Home &rarr;
            </a>
        </div>
    </body>
    </html>
    <?php
} else {
    foreach ($output as $line) {
        echo $line . PHP_EOL;
    }
}
