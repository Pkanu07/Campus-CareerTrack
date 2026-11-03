# PROJECT REPORT
## Campus CareerTrack — Student Placement & Internship Management System

### 1. Introduction
Campus CareerTrack is a responsive client-side web application intended to help students organize placement and internship opportunities. The prototype provides an overview dashboard, searchable opportunity listings, saved roles, an application tracker, and an editable student profile.

### 2. Problem Statement
Students may use multiple sources to discover opportunities and track application progress. Without a central view, it can be harder to compare roles, remember deadlines, and review current statuses. This project demonstrates a single interface for these activities.

### 3. Objectives
- Design a professional and responsive user interface.
- Use HTML5 to structure pages and forms.
- Use CSS3 for layout, visual hierarchy, and responsive behavior.
- Use JavaScript for DOM updates, events, filtering, form validation, and application logic.
- Store demo data locally in the browser.
- Prepare the application structure for a later PHP + MySQL backend.

### 4. Software Requirements
- Operating system: Windows, macOS, or Linux
- Editor: Visual Studio Code (recommended)
- Browser: current Chrome, Edge, Firefox, or Safari
- Optional VS Code extension: Live Server
- Internet connection for CDN-hosted fonts and icons

### 5. Modules
**Overview:** Shows application counts, current statuses, deadlines, and recommended roles.

**Opportunities:** Lists fictional sample roles and filters by search term, type, work location, and domain.

**Saved Opportunities:** Allows students to save and remove opportunities from a shortlist.

**Application Form:** Checks name, email, optional URL, motivation text, and consent. It prevents duplicate applications for the same sample opportunity.

**My Applications:** Displays submitted applications, dates, and statuses. Status tabs help focus on selected application stages.

**My Profile:** Lets a student edit basic academic and career information.

### 6. Implementation
HTML provides the page structure, navigation controls, tables, cards, and forms. CSS creates the sidebar, dashboard cards, status badges, opportunity grid, modal, and responsive layouts. JavaScript uses event listeners to switch views, filter jobs, validate forms, update the DOM, and save data in `localStorage`.

### 7. Validation
The prototype validates name length and allowed characters, email format, optional portfolio URL protocol, motivation text length, required selections, and consent. User-provided text is escaped before it is inserted into generated HTML. Client-side validation is for usability only; a real deployment must validate data again on the server.

### 8. Testing
- Search for a known skill such as React.
- Filter by Internship, Remote, and Data.
- Save and unsave a role.
- Attempt to submit incomplete/invalid application details.
- Submit valid details and confirm the dashboard count changes.
- Attempt a duplicate application.
- Update profile details and test invalid email handling.
- Refresh the page and verify local data persistence.
- Test mobile-width layout.

### 9. Limitations
The application uses fictional sample opportunities and local browser storage. It has no live vacancy feed, server, authentication, centralized database, or real recruiter integration. Data can be cleared by the browser and is not suitable for sensitive information.

### 10. Future Enhancement
Review 02 can introduce PHP and MySQL, password hashing, sessions, role-based access control, prepared SQL statements, CSRF protection, admin opportunity management, and centralized application tracking.

### 11. Conclusion
Campus CareerTrack demonstrates a practical application of HTML, CSS, and JavaScript through an interactive, responsive placement portal. It meets the scope of a client-side prototype and can be extended into a full-stack student placement system.

### 12. References
- MDN Web Docs: HTML, CSS, JavaScript, and Web Storage API
- PHP Manual: https://www.php.net/manual/en/
- MySQL Documentation: https://dev.mysql.com/doc/
- Lucide Icons: https://lucide.dev/
