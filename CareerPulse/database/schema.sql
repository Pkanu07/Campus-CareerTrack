-- ========================================================
-- CareerPulse - Modern Job Board & Career Platform
-- MySQL Database Schema & Seed Data
-- ========================================================

CREATE DATABASE IF NOT EXISTS `careerpulse_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `careerpulse_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `saved_jobs`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `candidate_profiles`;
DROP TABLE IF EXISTS `employer_profiles`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('candidate', 'employer', 'admin') DEFAULT 'candidate',
    `phone` VARCHAR(30) NULL,
    `avatar` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Employer Profiles Table
CREATE TABLE `employer_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `company_name` VARCHAR(150) NOT NULL,
    `industry` VARCHAR(100) NULL,
    `company_size` VARCHAR(50) NULL,
    `website` VARCHAR(255) NULL,
    `location` VARCHAR(150) NULL,
    `about` TEXT NULL,
    `logo_url` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Candidate Profiles Table
CREATE TABLE `candidate_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `headline` VARCHAR(200) NULL,
    `experience_years` INT DEFAULT 0,
    `education` VARCHAR(255) NULL,
    `skills` TEXT NULL,
    `bio` TEXT NULL,
    `resume_url` VARCHAR(255) NULL,
    `portfolio_url` VARCHAR(255) NULL,
    `linkedin_url` VARCHAR(255) NULL,
    `github_url` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Categories Table
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT 'briefcase',
    `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Jobs Table
CREATE TABLE `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employer_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(220) NULL,
    `job_type` ENUM('full-time', 'part-time', 'contract', 'remote', 'internship') DEFAULT 'full-time',
    `experience_level` ENUM('entry', 'mid', 'senior', 'lead', 'executive') DEFAULT 'entry',
    `location` VARCHAR(150) NOT NULL,
    `is_remote` TINYINT(1) DEFAULT 0,
    `salary_min` INT NULL,
    `salary_max` INT NULL,
    `salary_currency` VARCHAR(10) DEFAULT 'USD',
    `description` MEDIUMTEXT NOT NULL,
    `requirements` MEDIUMTEXT NULL,
    `benefits` TEXT NULL,
    `status` ENUM('active', 'paused', 'closed') DEFAULT 'active',
    `is_featured` TINYINT(1) DEFAULT 0,
    `deadline` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Applications Table
CREATE TABLE `applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `candidate_id` INT NOT NULL,
    `resume_path` VARCHAR(255) NOT NULL,
    `cover_note` TEXT NULL,
    `status` ENUM('pending', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected') DEFAULT 'pending',
    `employer_notes` TEXT NULL,
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_job` (`job_id`, `candidate_id`),
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`candidate_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Saved Jobs Table
CREATE TABLE `saved_jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `saved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_saved` (`user_id`, `job_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- SEED DATA (Default Password for all seeded users: Password123!)
-- --------------------------------------------------------

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
(1, 'Software Engineering', 'software-engineering', 'code', 'Full-stack, backend, frontend, and mobile engineering roles.'),
(2, 'Product & Design', 'product-design', 'palette', 'UI/UX design, product management, user research, and branding.'),
(3, 'Data & AI', 'data-ai', 'cpu', 'Machine learning, data science, analytics, and AI prompt engineering.'),
(4, 'DevOps & Cloud', 'devops-cloud', 'server', 'Cloud infrastructure, CI/CD, Kubernetes, and cybersecurity.'),
(5, 'Marketing & Growth', 'marketing-growth', 'trending-up', 'Performance marketing, content strategy, SEO, and sales.'),
(6, 'Healthcare & Science', 'healthcare-science', 'activity', 'Health informatics, biomedical tech, and clinical analysts.');

-- Users
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`) VALUES
(1, 'System Admin', 'admin@careerpulse.com', '$2y$10$wS2WbT2V6pL0O0E56K.ykeoQ9bN6j2d2u/o0jY7CqS2e9s31bL71a', 'admin', '+1 555-0100'),
(2, 'Sarah Vance (TechCorp Global)', 'recruiter@techcorp.com', '$2y$10$wS2WbT2V6pL0O0E56K.ykeoQ9bN6j2d2u/o0jY7CqS2e9s31bL71a', 'employer', '+1 555-0110'),
(3, 'Marcus Reed (InnovateLabs)', 'hiring@innovatelabs.io', '$2y$10$wS2WbT2V6pL0O0E56K.ykeoQ9bN6j2d2u/o0jY7CqS2e9s31bL71a', 'employer', '+1 555-0120'),
(4, 'Elena Rostova (HealthFirst)', 'talent@healthfirst.org', '$2y$10$wS2WbT2V6pL0O0E56K.ykeoQ9bN6j2d2u/o0jY7CqS2e9s31bL71a', 'employer', '+1 555-0130'),
(5, 'Alex Morgan', 'alex.morgan@email.com', '$2y$10$wS2WbT2V6pL0O0E56K.ykeoQ9bN6j2d2u/o0jY7CqS2e9s31bL71a', 'candidate', '+1 555-0210'),
(6, 'Sarah Chen', 'sarah.chen@email.com', '$2y$10$wS2WbT2V6pL0O0E56K.ykeoQ9bN6j2d2u/o0jY7CqS2e9s31bL71a', 'candidate', '+1 555-0220'),
(7, 'David Kim', 'david.kim@email.com', '$2y$10$wS2WbT2V6pL0O0E56K.ykeoQ9bN6j2d2u/o0jY7CqS2e9s31bL71a', 'candidate', '+1 555-0230');

-- Employer Profiles
INSERT INTO `employer_profiles` (`user_id`, `company_name`, `industry`, `company_size`, `website`, `location`, `about`) VALUES
(2, 'TechCorp Global', 'Enterprise Cloud Software', '500-1000 employees', 'https://techcorp.example.com', 'San Francisco, CA / Remote', 'TechCorp Global is a premier provider of distributed enterprise cloud platforms and developer tooling.'),
(3, 'InnovateLabs AI', 'Artificial Intelligence & ML', '50-200 employees', 'https://innovatelabs.example.com', 'New York, NY / Hybrid', 'Building next-generation generative AI infrastructure and contextual agents for enterprise workflows.'),
(4, 'HealthFirst Diagnostics', 'Digital Healthcare', '1000+ employees', 'https://healthfirst.example.com', 'Boston, MA', 'Transforming patient diagnostic workflows through secure telemedicine and clinical informatics.');

-- Candidate Profiles
INSERT INTO `candidate_profiles` (`user_id`, `headline`, `experience_years`, `education`, `skills`, `bio`, `portfolio_url`, `linkedin_url`, `github_url`) VALUES
(5, 'Senior Full-Stack Engineer | PHP, JavaScript, React, MySQL', 5, 'B.S. in Computer Science, State University', 'PHP, JavaScript, React, Node.js, MySQL, PostgreSQL, Docker, Tailwind CSS, REST APIs', 'Passionate full-stack developer with 5+ years of experience crafting high-performance, accessible web platforms.', 'https://alexmorgan.dev', 'https://linkedin.com/in/alexmorgan', 'https://github.com/alexmorgan'),
(6, 'Lead Product & UI/UX Designer | Design Systems & Prototyping', 4, 'B.A. in Interactive Media & Graphic Design', 'Figma, UI/UX Design, Design Systems, Wireframing, User Testing, HTML/CSS, Tailwind', 'Creative product designer focused on creating intuitive, human-centered SaaS tools and fluid interactions.', 'https://sarahchen.design', 'https://linkedin.com/in/sarahchen', 'https://github.com/sarahchen'),
(7, 'Machine Learning Engineer & Data Scientist | Python, PyTorch, SQL', 3, 'M.S. in Data Science & Applied AI', 'Python, SQL, PyTorch, Scikit-Learn, TensorFlow, Pandas, Docker, Cloud ML', 'Data scientist experienced in fine-tuning transformers, building predictive pipelines, and high-throughput data processing.', 'https://davidkim.ai', 'https://linkedin.com/in/davidkim', 'https://github.com/davidkim');

-- Jobs
INSERT INTO `jobs` (`id`, `employer_id`, `category_id`, `title`, `slug`, `job_type`, `experience_level`, `location`, `is_remote`, `salary_min`, `salary_max`, `salary_currency`, `description`, `requirements`, `benefits`, `status`, `is_featured`, `deadline`) VALUES
(1, 2, 1, 'Senior Full-Stack Web Developer (PHP & Modern JS)', 'senior-full-stack-web-developer', 'full-time', 'senior', 'San Francisco, CA / Remote', 1, 125000, 155000, 'USD', 'We are looking for an experienced Senior Full-Stack Engineer to architect, build, and maintain mission-critical web applications for our enterprise platform.', '• 4+ years of production experience with PHP (8.x) and relational databases (MySQL/PostgreSQL)\n• Strong proficiency in modern JavaScript, CSS/Tailwind, and RESTful APIs\n• Experience with unit testing, git workflows, and CI/CD pipelines\n• Solid understanding of web security best practices (OWASP, CSRF, XSS prevention)', '• Competitive base salary and equity grant\n• 100% remote flexibility and $1,500 home office stipend\n• Comprehensive medical, dental, and vision coverage\n• 401(k) matching up to 5%\n• Unlimited Paid Time Off (PTO)', 'active', 1, DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)),
(2, 3, 3, 'Machine Learning & NLP Research Engineer', 'ml-nlp-research-engineer', 'full-time', 'mid', 'New York, NY / Hybrid', 0, 135000, 170000, 'USD', 'Join InnovateLabs AI as we build state-of-the-art conversational agents and semantic search models.', '• 3+ years experience developing with Python, PyTorch or JAX\n• Deep understanding of Transformer architectures, vector embeddings, and LLM fine-tuning\n• Strong SQL skills and database optimization techniques\n• Degree in Computer Science, Mathematics, or equivalent field', '• Cutting-edge GPU compute clusters (H100 access)\n• Catered daily lunches at our Soho, NY office\n• Generous annual learning & conference travel budget\n• Full health insurance and wellness stipends', 'active', 1, DATE_ADD(CURRENT_DATE, INTERVAL 45 DAY)),
(3, 3, 2, 'Lead Product & UI/UX Designer', 'lead-product-ui-ux-designer', 'full-time', 'lead', 'Remote (US/Canada)', 1, 115000, 145000, 'USD', 'We are looking for a visionary Lead Product Designer to own the user journey across our developer portal and SaaS dashboard.', '• 4+ years of UI/UX design experience for SaaS or web apps\n• Mastery of Figma, auto-layout, interactive component libraries, and design tokens\n• Strong portfolio demonstrating end-to-end design thinking from discovery to delivery\n• Ability to write clean HTML/CSS or collaborate directly with frontend engineers', '• Fully remote role with flexible working hours\n• Latest M3 Max MacBook Pro + 4K dual monitor setup\n• Annual health and wellness retreat\n• 25 days paid vacation + bank holidays', 'active', 1, DATE_ADD(CURRENT_DATE, INTERVAL 20 DAY)),
(4, 2, 4, 'Senior Cloud DevOps & Site Reliability Engineer', 'senior-cloud-devops-sre', 'full-time', 'senior', 'San Francisco, CA', 1, 140000, 180000, 'USD', 'Help us scale high-availability infrastructure supporting over 10 million daily active requests. You will build resilient Kubernetes clusters, automate deployment pipelines, and maintain observability.', '• 5+ years supporting production cloud infrastructure (AWS/GCP)\n• Expertise in Kubernetes, Docker, Terraform, and GitHub Actions\n• Strong knowledge of monitoring tools (Prometheus, Grafana, Datadog)\n• Experience with database replication and disaster recovery planning', '• Top-of-market compensation and equity package\n• Flexible remote work culture\n• Full medical, dental, and vision insurance\n• Dedicated paid professional development time', 'active', 0, DATE_ADD(CURRENT_DATE, INTERVAL 35 DAY)),
(5, 4, 6, 'Health Informatics Data Analyst', 'health-informatics-data-analyst', 'full-time', 'mid', 'Boston, MA', 0, 88000, 115000, 'USD', 'HealthFirst Diagnostics is seeking a Data Analyst to transform clinical and laboratory telemetry into actionable health insights and compliance reports.', '• 2+ years working with healthcare data standards (HL7, FHIR, ICD-10)\n• Strong SQL skills, data cleansing, and Tableau/PowerBI dashboard design\n• Familiarity with HIPAA compliance and clinical security protocols\n• Bachelor\'s degree in Health Informatics, Statistics, or related discipline', '• State pension contribution and 401(a) retirement program\n• Comprehensive healthcare plan with low copays\n• Tuition reimbursement program\n• Generous paid leave policy', 'active', 0, DATE_ADD(CURRENT_DATE, INTERVAL 40 DAY)),
(6, 2, 1, 'Software Engineering Intern (Summer 2027)', 'software-engineering-intern', 'internship', 'entry', 'Remote / US', 1, 45000, 60000, 'USD', 'Jumpstart your engineering career! You will be paired with senior mentors to work on real customer-facing features, participate in code reviews, and learn modern software engineering in a supportive environment.', '• Currently pursuing or recent graduate in Computer Science, Software Engineering, or related field\n• Familiarity with web fundamentals (HTML, CSS, JavaScript, PHP or Python)\n• Passion for writing clean code and eagerness to learn\n• Excellent communication and teamwork skills', '• Competitive hourly pay\n• 1-on-1 mentorship from principal architects\n• Company laptop and tech gear provided\n• High possibility of full-time conversion offer', 'active', 1, DATE_ADD(CURRENT_DATE, INTERVAL 60 DAY));

-- Applications
INSERT INTO `applications` (`job_id`, `candidate_id`, `resume_path`, `cover_note`, `status`, `employer_notes`) VALUES
(1, 5, 'sample_alex_morgan_resume.pdf', 'Hi Hiring Team, I have 5 years of production PHP and modern web experience, building high-scale SaaS products. I would love to contribute to TechCorp Global enterprise platforms!', 'shortlisted', 'Strong background in modern PHP 8 and component-driven frontend. Schedule technical screen.'),
(6, 5, 'sample_alex_morgan_resume.pdf', 'Exploring new mentor opportunities and excited about TechCorp engineering standards.', 'interview', 'Interview round 1 completed. Candidate demonstrated solid fundamentals.'),
(3, 6, 'sample_sarah_chen_resume.pdf', 'Hello InnovateLabs team, as a seasoned product designer with deep experience in AI user interfaces and design systems, I am thrilled to apply for the Lead Product Designer position.', 'under_review', 'Portfolio looks stellar. Reviewing design system case studies.'),
(2, 7, 'sample_david_kim_resume.pdf', 'I have published work on transformer fine-tuning and built high-performance RAG pipelines in production. Looking forward to discussing this role!', 'accepted', 'Offer extended and accepted. Start date confirmed for next month.');

-- Saved Jobs
INSERT INTO `saved_jobs` (`user_id`, `job_id`) VALUES
(5, 3),
(6, 1);
