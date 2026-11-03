# Presentation Slides Outline (Exactly 6 Slides)

## Campus CareerTrack – Student Placement & Internship Management System
**Course:** Web Technology Laboratory (LWP Review 01)  
**Department:** Computer Science and Engineering  
**Presenter:** Student Individual Project  

---

### Slide 1: Project Title & Introduction
- **Title:** Campus CareerTrack – Student Placement & Internship Management System
- **Subtitle:** Individual Laboratory Project &bull; LWP Review 01
- **Domain:** Web Technology & Front-End Engineering
- **Student Name:** Priti Kumari (Reg. No: 710023104042)
- **Key Message:** A modern, client-side career hub designed for college students to explore curated internships, check eligibility criteria, apply with one click, and track recruitment progress in real time.
- **Visual:** Campus CareerTrack logo, dual-theme dashboard mockup, and University Placement Season 2026–27 banner.

---

### Slide 2: Problem Statement
- **Fragmented Communication:** Job openings, drive dates, and eligibility criteria are scattered across WhatsApp groups, notice boards, and spreadsheets.
- **Eligibility Confusion:** Students often apply for drives without knowing if their CGPA, branch, or backlog count qualifies, leading to administrative overhead.
- **Zero Real-Time Tracking:** Candidates lack visibility into post-application stages (`Shortlisted`, `Interview Scheduled`, `Selected`, `Rejected`).
- **Outdated User Experience:** Traditional college portals are desktop-only, clunky, and lack mobile responsiveness, dark mode, or bookmarking.

---

### Slide 3: Proposed Solution & Objectives
- **Proposed Solution:** A centralized, responsive SaaS-style web portal featuring 10 dedicated modules for exploring, applying, evaluating, and tracking campus opportunities.
- **Core Objectives:**
  - Build a zero-dependency, high-speed client-side application using HTML5, CSS3, and Vanilla JavaScript.
  - Implement an **Interactive Eligibility Evaluation Engine** for instant pre-application checking.
  - Develop a full **Application Tracker** with a live demonstration state manager for faculty review.
  - Deliver persistent user state (profile, applications, bookmarks, notifications, theme) using `localStorage`.

---

### Slide 4: Technologies & System Modules
- **Technology Stack (Review 01 Restrictions Followed):**
  - **Structure:** Semantic HTML5 (accessible forms, tables, modals)
  - **Styling:** CSS3 Variables, Flexbox, CSS Grid, Light/Dark Modes
  - **Scripting:** Vanilla JavaScript (ES6+ modular controllers, event delegation)
  - **Data Persistence:** Browser `localStorage` with JSON serialization & error recovery
- **Key Modules:**
  1. Landing Page (`index.html`) & Student Dashboard (`dashboard.html`)
  2. Opportunities Catalog & Details View (`opportunities.html`, `opportunity-details.html`)
  3. Real-Time Application Tracker (`applications.html`)
  4. Interactive Eligibility Engine (`eligibility.html`)
  5. Student Profile & Resume Manager (`profile.html`)
  6. Bookmarks & Smart Notifications (`saved-opportunities.html`, `notifications.html`)

---

### Slide 5: Implementation & Verification Highlights
- **Dynamic Application Workflow:** One-click modal application with validation and duplicate submission blocking.
- **Visual Status Distribution:** Custom SVG donut chart dynamically displaying student application proportions.
- **Demo State Switcher:** Purpose-built control panel allowing the faculty evaluator to change application status live (e.g. from `Applied` to `Selected`) and observe instant notification generation and dashboard count updates.
- **Testing Results:** 11/11 formal test cases passed, including search/filter combinations, theme toggles, bookmark persistence, and demo data reset.

---

### Slide 6: Conclusion & Future Scope (Review 02 Roadmap)
- **Review 01 Conclusion:** Successfully delivered an individual, fully functional client-side web application meeting all Web Technology Lab requirements.
- **Roadmap for Review 02 (Full-Stack Backend Integration):**
  - **Backend Layer:** PHP 8.x or Java Servlets (Jakarta EE) RESTful API.
  - **Database Layer:** MySQL relational database schema with normalized tables (`students`, `companies`, `applications`, `interviews`).
  - **Authentication:** Role-Based Access Control (RBAC) with bcrypt password hashing and session tokens.
  - **Physical File Handling:** Server-side PDF resume uploads and storage.
- **Thank You & Q&A:** Open for faculty evaluation and live system walkthrough.
