# Project Abstract

## Campus CareerTrack – Student Placement & Internship Management System

**Project Type:** Individual Laboratory Project (LWP Review 01)  
**Academic Degree:** Bachelor of Engineering (B.E.)  
**Department:** Computer Science and Engineering  
**Course:** Web Technology Laboratory  
**Technology Stack:** Semantic HTML5, CSS3 Variables & Flexbox/Grid, Vanilla JavaScript (ES6+), Browser LocalStorage API  

---

### Abstract
In higher education institutions, managing campus placement drives, internships, eligibility assessments, and application workflows is often burdened by fragmented channels such as unstructured messaging groups, emailed spreadsheets, and physical notice boards. **Campus CareerTrack** is a centralized, client-side Web application designed to provide university students with a single, modern platform to explore curated opportunities, check personalized eligibility, submit applications, bookmark roles, and monitor progress across recruitment stages. Engineered with pure HTML5, CSS3, and Vanilla JavaScript for Review 01, the system operates entirely client-side using browser `localStorage` for robust state persistence and provides an intuitive, high-performance SaaS interface. The modular architecture is purposefully structured to transition seamlessly to a relational database and backend services (PHP or Java Servlets with MySQL) for Review 02.

---

### Problem Statement
Campus placement cells and graduating students face significant operational hurdles during recruitment seasons:
1. **Scattered Information:** Opportunity descriptions, deadlines, and eligibility benchmarks are dispersed across emails, PDF notices, and instant messaging chats, resulting in missed deadlines.
2. **Eligibility Ambiguity:** Students frequently submit applications without clear visibility into minimum CGPA criteria, allowed backlogs, or eligible disciplines, causing high rejection rates and administrative overhead.
3. **Lack of Transparent Tracking:** After submitting applications, students often have no real-time insight into the status of their candidature (e.g., Shortlisted, Interview Scheduled, Offer Released).
4. **Poor Candidate Experience:** Traditional university portals are outdated, unresponsive on mobile devices, and lack modern usability affordances such as theme customization, instant search, and bookmarking.

---

### Objectives
- Develop a responsive, aesthetically appealing, multi-page client-side web application suitable for college laboratory examination and portfolio presentation.
- Implement a realistic opportunity catalog with multi-parameter search, filtering (by location, work mode, job type, skills), and sorting mechanisms.
- Build an interactive **Eligibility Evaluation Engine** that benchmarks student academic metrics (CGPA, active backlogs, graduation batch, discipline) and core skills against company prerequisites.
- Provide a persistent **Application Tracker** with distinct lifecycle stages (`Applied`, `Shortlisted`, `Interview Scheduled`, `Selected`, `Rejected`) and a demonstration control panel for live faculty evaluations.
- Offer profile management with dynamic completeness scoring, bookmarking, smart notifications, and light/dark theme persistence via browser `localStorage`.

---

### Proposed Solution
Campus CareerTrack solves these challenges by providing a 10-page unified student portal:
- **Landing Page (`index.html`):** Inspiring entrance highlighting platform metrics, benefits, and how it works.
- **Student Dashboard (`dashboard.html`):** Visual snapshot featuring dynamic metric cards, recent application feed, countdown deadlines, skill-matched recommendations, and an SVG application status donut chart.
- **Opportunities Listing (`opportunities.html`):** Filterable catalog of 12+ industry opportunities with keyword search, location, mode, and skill tags.
- **Opportunity Details (`opportunity-details.html`):** Comprehensive role specifications, responsibilities, selection rounds, and one-click application modal.
- **Application Tracker (`applications.html`):** Stage tracking with cancellation modals and an evaluator demonstration state manager.
- **Student Profile (`profile.html`):** Academic credentials, verified skills tag editor, completeness meter, and local resume reference.
- **Saved Opportunities (`saved-opportunities.html`):** Persistent bookmarks manager.
- **Eligibility Checker (`eligibility.html`):** Automated criteria checklist and skill-gap analyzer.
- **Notifications Center (`notifications.html`):** Alerts for upcoming deadlines and status updates.
- **Settings & Demo (`settings.html`):** Theme toggle, notification preferences, and demo data reset/export tools.

---

### Technologies Used
- **Frontend Presentation:** HTML5 Semantic Structure, CSS3 Custom Properties (Variables), Flexbox, CSS Grid.
- **Client-Side Logic:** Vanilla JavaScript (ES6+ classes, modules, DOM APIs, event delegation).
- **Persistence Layer:** Web Storage API (`window.localStorage`) with JSON serialization and schema recovery.
- **Iconography & Typography:** Font Awesome 6 CDN, Google Fonts (Inter & Poppins).

---

### Expected Outcomes
- Students can browse, filter, and inspect campus opportunities with zero latency.
- Instant eligibility checks prevent ineligible submissions and identify skill gaps.
- Applications and bookmarks persist across browser reloads without external server dependencies.
- Faculty evaluators can test application workflows using built-in demo status switchers.

---

### Future Scope (LWP Review 02 Preview)
- Server-side RESTful API backend using PHP 8.x or Java Servlets.
- Relational schema persistence using MySQL for student profiles, job postings, and application stages.
- Role-Based Access Control (RBAC) separating Student and Placement Officer / Admin interfaces.
- Secure session management with password hashing (bcrypt).
- Real PDF resume file storage on server disk.
