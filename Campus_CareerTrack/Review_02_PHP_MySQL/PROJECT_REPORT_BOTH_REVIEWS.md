# Campus CareerTrack — Combined Project Report (Review 01 + Review 02)

## 1. Title
Campus CareerTrack — Student Placement & Internship Management System

## 2. Problem statement
Students often discover placement and internship information across different channels and have difficulty keeping track of opportunities and application status. The project provides one simple portal to explore roles and monitor applications.

## 3. Objectives
- Provide a clear responsive interface for students.
- Allow searching, saving, and applying to sample opportunities in Review 01.
- Extend the prototype with persistent MySQL storage and PHP server logic in Review 02.
- Demonstrate validation, database connectivity, authentication, and role-based access.

## 4. Technologies
- Review 01: HTML5, CSS3, JavaScript, browser localStorage.
- Review 02: PHP 8+, MySQL/MariaDB, PDO, SQL, PHP sessions.
- Development tools: VS Code, XAMPP, phpMyAdmin, web browser.

## 5. Modules
1. Student registration/login.
2. Opportunity listing.
3. Application submission and duplicate prevention.
4. Student dashboard and application status tracking.
5. Placement administrator opportunity management.
6. MySQL database for users, student profiles, opportunities, and applications.

## 6. Database design
- `users`: account details, password hash, role.
- `student_profiles`: department, year, skills, objective.
- `opportunities`: company, role, location, mode, compensation, description, active flag.
- `applications`: student, opportunity, cover letter, status, applied date; unique student/opportunity pair.

## 7. Security considerations
Passwords are hashed, database statements use PDO prepared queries, server-rendered values are escaped, POST requests use CSRF tokens, and admin pages check the logged-in role. The app is intended for local educational demonstration; production hosting requires HTTPS and further hardening.

## 8. Testing checklist
- Register with valid and invalid values.
- Login with valid and invalid credentials.
- Confirm dashboard lists seeded opportunities.
- Submit an application with a valid statement.
- Attempt duplicate application.
- Log in as admin and create/hide an opportunity.
- Update an application status and confirm the student sees it.
- Verify an ordinary student cannot open admin pages.

## 9. Conclusion
Review 01 demonstrates client-side design and interaction. Review 02 extends the same project concept to a database-backed PHP application with user accounts and administrator workflows.
