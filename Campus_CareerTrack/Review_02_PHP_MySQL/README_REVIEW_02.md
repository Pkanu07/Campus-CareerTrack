# Review 02 — PHP + MySQL Backend

This folder is a separate server-backed version of Campus CareerTrack. Keep the original root-level `index.html`, `style.css`, and `script.js` for Review 01. Review 02 uses PHP, MySQL, PDO, sessions and role-based access.

## Requirements
- XAMPP (Apache + PHP + MySQL/MariaDB) or equivalent local PHP/MySQL setup
- PHP 8.0+ with PDO MySQL enabled
- Modern browser

## Install with XAMPP
1. Copy the whole `Campus_CareerTrack` project folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Select **Import**, choose `Review_02_PHP_MySQL/sql/schema.sql`, and click **Import/Go**. The SQL creates the `campus_careertrack` database and sample opportunities.
5. Check `Review_02_PHP_MySQL/config/database.php`. Default XAMPP values are host `127.0.0.1`, database `campus_careertrack`, user `root`, empty password. If your MySQL password differs, update it there.
6. Visit `http://localhost/Campus_CareerTrack/Review_02_PHP_MySQL/` (or `.../index.php`).
7. Register a student account and test applying to an opportunity. Application data is saved in MySQL.

## Create the placement administrator
1. Register a normal account through the app.
2. In phpMyAdmin, select `campus_careertrack` → SQL and run (replace the email with the account you registered):
   ```sql
   UPDATE users SET role='admin' WHERE email='your-admin-email@example.com';
   ```
3. Log out and log in again. Admin accounts can access **Manage roles**, add opportunities, publish/hide roles and update application statuses.

## Features included
- Student registration and login; passwords stored with `password_hash()` and checked using `password_verify()`
- PHP sessions with session ID regeneration at login
- Student dashboard and active opportunities pulled from MySQL
- Application submission with server-side validation and duplicate-application database constraint
- Admin role authorization, opportunity creation/visibility, and application status management
- PDO prepared statements, output escaping, and CSRF tokens on POST forms
- SQL schema and sample seed opportunities

## Important notes
- This is an educational local demo. Before public deployment, add HTTPS, rate limiting, email verification/password reset, audit logs, stronger operational error logging, and deployment-specific security review.
- Do not use real sensitive data in a classroom demo. Do not publish database credentials to GitHub.
- `index.html` is Review 01's front end. The Review 02 PHP version is reached through the PHP URL above; PHP files will not execute by opening them directly with `file://` or Live Server.
- The sample opportunities are fictional and are included for demonstration, not live vacancies.
