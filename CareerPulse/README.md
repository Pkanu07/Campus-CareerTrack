# CareerPulse — Modern Job Board & Career Platform

A full-stack, production-ready web application built with **PHP 8** and **MySQL** (with seamless automatic **SQLite** fallback for zero-configuration testing), styled with **Tailwind CSS** and **Lucide Icons**.

---

## 🌟 Key Features

### 👤 Candidate Experience
- **Smart Job Search & Filters:** Search by title, keywords, category, job type (Full-time, Part-time, Contract, Remote, Internship), experience level, and salary.
- **Detailed Job Postings:** Responsibilities, qualifications, salary breakdown, deadline, and company overview.
- **One-Click Application System:** Upload PDF/DOCX resumes (with MIME type & size verification) and write custom cover notes.
- **Real-Time Application Tracking:** Track statuses (`Pending Review`, `Under Review`, `Shortlisted`, `Interview Scheduled`, `Accepted`, `Declined`) with recruiter feedback notes.
- **Profile & Resume Builder:** Manage headline, experience, skills tags, social links (LinkedIn, GitHub, Portfolio), and default CV.
- **Bookmarks:** Save and pin opportunities for later review.

### 🏢 Employer & Recruiter Center
- **Recruiter Dashboard:** Analytics on active listings, total applicants received, and interview shortlists.
- **Job Creation & Management:** Publish new listings with rich fields, toggle active/paused/closed states, or edit listings.
- **Applicant Review Pipeline:** Review candidate qualifications, inspect skills, download resumes, and update hiring statuses.
- **Company Branding:** Update company bio, size, industry sector, headquarters, and website.

### 🛡️ Administration Console
- **System Metrics:** High-level counts of registered candidates, employers, active postings, and total applications.
- **User Moderation:** Manage registered users and remove abusive accounts.
- **Job Moderation:** Feature top postings and moderate public listings.
- **Category Management:** Create new industry taxonomy categories.

### ⚡ Database & Deployment Flexibility
- **MySQL First (XAMPP Standard):** Connects to MySQL/MariaDB database `careerpulse_db` by default.
- **Automatic SQLite Fallback:** If MySQL is not running yet, CareerPulse automatically initializes and runs on a local SQLite database (`database/careerpulse.sqlite`), allowing instant testing without database configuration errors!
- **One-Click Seeder:** Generates realistic categories, employers, candidates, job listings, and sample applications out of the box.

---

## 🚀 Quick Start (Running the App)

### Option 1: Quick Launch Batch File (Windows)
Double-click `run_server.bat` in the `CareerPulse/` folder.  
It will automatically:
1. Detect PHP from `C:\xampp\php\php.exe` (or system PATH).
2. Check and seed the database.
3. Open `http://localhost:8000` in your browser.

### Option 2: Command Line
Open a terminal in the `CareerPulse` folder and run:

```bash
# Initialize / seed the database
"C:\xampp\php\php.exe" database\setup.php

# Start the PHP development server
"C:\xampp\php\php.exe" -S 127.0.0.1:8000 -t .
```

Then visit **`http://localhost:8000`** in your browser.

### Option 3: Using Apache & MySQL via XAMPP
1. Move or symlink the `CareerPulse` directory to `C:\xampp\htdocs\CareerPulse`.
2. Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin` and import `database/schema.sql`.
4. Open `http://localhost/CareerPulse` in your web browser.

---

## 🔑 Pre-Seeded Demo Accounts

Every pre-seeded account uses the password: **`Password123!`**  
*(You can also use the 1-click demo buttons on the Sign In page for instant access!)*

| Role | Email | Password | Access / Purpose |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin@careerpulse.com` | `Password123!` | Moderation, metrics, users, categories |
| **Recruiter (TechCorp)** | `recruiter@techcorp.com` | `Password123!` | Post jobs, review applicants, update statuses |
| **Recruiter (InnovateLabs)** | `hiring@innovatelabs.io` | `Password123!` | AI & Data engineering openings |
| **Candidate (Engineer)** | `alex.morgan@email.com` | `Password123!` | Active applications & shortlisted interview |
| **Candidate (Designer)** | `sarah.chen@email.com` | `Password123!` | UI/UX applications & portfolio |

---

## 📁 Directory Structure

```
CareerPulse/
├── config/
│   └── database.php            # PDO connection (MySQL + SQLite fallback) & query helpers
├── database/
│   ├── schema.sql              # Standard MySQL schema & seed data for phpMyAdmin
│   └── setup.php               # Universal CLI & browser DB initialization script
├── includes/
│   ├── auth.php                # Session management, authentication, role access control
│   ├── helpers.php             # URL routing, CSRF tokens, formatters, resume uploader
│   ├── header.php              # Global responsive navbar, flash alerts, Lucide CDN
│   └── footer.php              # Global footer, scripts, quick links
├── uploads/
│   ├── resumes/                # Uploaded candidate resumes (with .htaccess protection)
│   └── .htaccess               # Prevents execution of scripts in uploads
├── auth/
│   ├── login.php               # Sign in page with 1-click demo logins
│   ├── register.php            # Candidate & Employer registration
│   └── logout.php              # Session termination
├── candidate/
│   ├── dashboard.php           # Candidate application tracker & metrics
│   ├── profile.php             # Candidate skills, headline, and resume profile
│   └── saved_jobs.php          # Pinned / bookmarked jobs
├── employer/
│   ├── dashboard.php           # Recruiter pipeline metrics & recent applicants
│   ├── post_job.php            # Create or edit job postings
│   ├── manage_jobs.php         # Active/paused/closed jobs management
│   ├── applicants.php          # Applicant pipeline review & status updater
│   └── company_profile.php     # Organization details & branding
├── admin/
│   └── dashboard.php           # User moderation, job features, and category controls
├── index.php                   # Public landing page with search & stats
├── jobs.php                    # Search & filtering catalog
├── job_details.php             # Full job overview, requirements, & apply modal
├── apply.php                   # Application submission handler
├── run_server.bat              # One-click Windows server launcher
└── README.md                   # Documentation & setup guide
```

---

## 🔒 Security Measures Included
- **CSRF Token Validation:** All form submissions are protected with per-session CSRF verification.
- **Bcrypt Password Hashing:** User passwords securely hashed with `password_hash()`.
- **Prepared Statements (PDO):** All database interactions use parameterized queries to prevent SQL Injection.
- **Upload Hardening:** MIME-type and extension validation for resumes, preventing script uploads.
- **XSS Escaping:** All output sanitized using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **Role-Based Access Control:** Strict role-checking middleware preventing unauthorized access between candidates, employers, and administrators.
