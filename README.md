# Campus CareerTrack – Student Placement & Internship Management System

[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![LocalStorage](https://img.shields.io/badge/Storage-LocalStorage-blue?style=for-the-badge)](https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage)

**Project Type:** Individual Laboratory Project (LWP Review 01)  
**Academic Degree:** Bachelor of Engineering (B.E.) in Computer Science and Engineering  
**Course:** Web Technology Laboratory  
**Academic Year:** 2026–2027  

---

## 1. Project Overview
**Campus CareerTrack** is a centralized, client-side web application designed to help college students discover internships, explore campus placement opportunities, assess eligibility against company criteria, submit applications, bookmark roles, and monitor application milestones in real time.

Engineered with a modern SaaS aesthetic, the project replaces fragmented communication channels (WhatsApp chats, emailed spreadsheets, and static PDF circulars) with a single, responsive, and unified career management portal.

---

## 2. Key Features

### 🌟 Landing Page (`index.html`)
- Professional branding, hero call-to-action buttons, and active placement season badge.
- Platform statistics with clearly labelled demonstration metrics (150+ Companies, 850+ Placed, ₹14.5 LPA Average CTC, 94.8% Placement Rate).
- Featured internships and placement preview cards.
- Comprehensive benefits and how-it-works workflow sections.

### 📊 Personalized Student Dashboard (`dashboard.html`)
- Live dynamic metric cards: Total Applications, Shortlisted Profiles, Upcoming Interviews, Saved Roles.
- Recent application activity feed with company logos and status pills.
- Upcoming application deadlines list with urgency countdown badges.
- Skill-matched opportunity recommendations based on student profile.
- Visual SVG Donut Chart displaying proportional application status distribution.

### 💼 Opportunities Catalog (`opportunities.html`)
- Curated catalog of 12 realistic engineering opportunities across top employers (Google, Microsoft, Razorpay, Swiggy, AWS, Zerodha, Cisco, CRED, PhonePe, Zomato, NVIDIA, Flipkart).
- Real-time search by job title, company name, or category.
- Multi-criteria filtering by Location, Work Mode (Remote, Hybrid, On-site), and Job Type (Internship, Full-time).
- Interactive skill chips filter and multi-parameter sorting (Deadline soonest/furthest, Stipend highest, Title A-Z).
- Pagination and Load More functionality with helpful empty states.

### 📄 Opportunity Details View (`opportunity-details.html`)
- Full role specifications, key responsibilities, required technologies, selection rounds, and eligibility benchmarks.
- Instant &quot;Apply Now&quot; modal and bookmarking toggle.
- Status notification banner if the student has already applied for the role.
- Direct link to the Eligibility Checker with pre-loaded opportunity parameters.

### 📋 Application Tracker & Demo State Switcher (`applications.html`)
- Lifecycle tracking across 5 recruitment stages: `Applied`, `Shortlisted`, `Interview Scheduled`, `Selected`, `Rejected`.
- Status filter tabs with dynamic counts and live search.
- Application withdrawal with an accessible confirmation modal.
- **Demo State Switcher Panel:** A dedicated evaluator tool allowing faculty to simulate status transitions live and observe instant synchronization across notifications, tracker tables, and dashboard charts.
- Duplicate application prevention.

### 🎓 Student Profile & Resume Manager (`profile.html`)
- Editable student identity, academic credentials, CGPA, and backlog details.
- Interactive skills tag manager (add/remove skill badges dynamically).
- Real-time Profile Completeness Meter with animated progress bar.
- Client-side mock resume file selector with local file reference.

### 🔖 Saved Opportunities (`saved-opportunities.html`)
- Dedicated bookmarks manager with instant bookmark removal and direct application links.
- Fully persistent across browser sessions and reloads.

### ⚡ Interactive Role Eligibility Checker (`eligibility.html`)
- Pre-fills student credentials from profile and tests against selected company cutoffs.
- Criteria checklist verifying CGPA, backlogs, graduation batch, and engineering discipline.
- Skill gap analysis: identifies matched skills in green and missing skills in orange with interview recommendations.
- Direct &quot;Proceed to Apply&quot; button if eligible.

### 🔔 Notifications Center (`notifications.html`)
- Categorized notification alerts for status updates, interview schedules, deadlines, and submissions.
- Filter by All or Unread with individual/bulk mark-as-read and clear actions.
- Synchronized unread counter badge in top navigation bar.

### ⚙️ Settings & System Demo (`settings.html`)
- Instant Light Mode and Dark Mode theme toggle.
- Notification preference toggles.
- Complete demo state JSON backup export and download.
- One-click &quot;Reset to Default Demo Data&quot; with modal confirmation.

---

## 3. Technology Stack

| Component | Technology | Usage |
|:---|:---|:---|
| **Structure** | Semantic HTML5 | Semantic tags, accessible forms, modals, tables |
| **Styling** | CSS3 (Modern Variables, Flexbox, Grid) | Responsive design, Light/Dark themes, SaaS UI components |
| **Logic** | Vanilla JavaScript (ES6+) | Modular controllers, DOM manipulation, event delegation |
| **Persistence** | Browser `localStorage` API | JSON serialization, error recovery, cross-page synchronization |
| **Icons & Fonts** | Font Awesome 6 & Google Fonts | Vector iconography and Inter/Poppins typography |

> **Technology Restrictions Followed:** For Review 01, this application uses strictly HTML5, CSS3, Vanilla JavaScript, and `localStorage`. No external backend frameworks (React, Angular, Vue, PHP, Node.js) or databases (MySQL) are used in this version.

---

## 4. Folder Structure

```text
campus-careertrack/
│
├── index.html                   # Landing page with hero, stats, benefits & features
├── dashboard.html               # Personalized student dashboard & status chart
├── opportunities.html           # Opportunities hub with multi-filters & search
├── opportunity-details.html     # Single opportunity detail view & specs
├── applications.html            # Application tracker & demo state switcher
├── profile.html                 # Student profile editor & skills tag manager
├── saved-opportunities.html     # Bookmarked opportunities manager
├── eligibility.html             # Interactive role eligibility assessment engine
├── notifications.html           # Notifications log & badge counter
├── settings.html                # Light/Dark theme, preferences & demo data reset
│
├── css/
│   ├── style.css                # Design tokens, typography, buttons, modals, toasts
│   ├── dashboard.css            # Sidebar, topbar, dashboard grids, tables, charts
│   └── responsive.css           # Breakpoint media queries (1200px, 1024px, 768px, 480px)
│
├── js/
│   ├── storage.js               # Centralized data management layer & localStorage engine
│   ├── app.js                   # Layout coordination, theme, toasts, modals & navigation
│   ├── dashboard.js             # Dashboard metrics, recent tables & SVG status chart
│   ├── opportunities.js         # Opportunity search, multi-criteria filters & bookmarks
│   ├── opportunity-details.js   # Single opportunity detailed specs & actions
│   ├── applications.js          # Application tracking & evaluator demo state switcher
│   ├── profile.js               # Profile form validation, tag editor & completeness meter
│   ├── eligibility.js           # Criteria evaluation engine & skill-gap analyzer
│   ├── notifications.js         # Categorized notification log & badge counter
│   └── settings.js              # Theme switcher, demo data reset & JSON export
│
├── assets/
│   └── images/
│       └── logo.svg             # Application vector logo
│
├── docs/
│   ├── project-abstract.md      # Academic abstract, problem statement & objectives
│   ├── project-report.md        # Comprehensive 13-section college lab report
│   ├── presentation-outline.md  # Content structured for exactly 6 presentation slides
│   └── demo-script.md           # 3–5 minute step-by-step faculty presentation script
│
└── README.md                    # Project documentation & instructions
```

---

## 5. How to Run the Project

### Option 1: Direct Browser Opening (Zero Setup)
1. Navigate to the project root folder in File Explorer.
2. Double-click `index.html` (or right-click and choose **Open with Google Chrome / Microsoft Edge / Firefox**).
3. The application will immediately run in your browser.

### Option 2: Using VS Code Live Server (Recommended)
1. Open the project folder in **Visual Studio Code**.
2. If installed, click **Go Live** on the bottom status bar, or right-click `index.html` and select **Open with Live Server**.
3. The application will open at `http://127.0.0.1:5500/index.html`.

### Option 3: Using Python HTTP Server
Open PowerShell or Command Prompt in the project directory and run:
```bash
python -m http.server 8000
```
Then open your browser and navigate to:
```text
http://localhost:8000/index.html
```

---

## 6. Testing Instructions

Follow this testing checklist to verify all features:
1. **Explore Opportunities:** Navigate to `opportunities.html`. Type &quot;Google&quot; in the search box; only Google should display.
2. **Filters & Sorting:** Select &quot;Remote&quot; work mode; only remote jobs (Razorpay, Zerodha, Zomato) appear. Sort by &quot;Stipend (Highest)&quot;.
3. **Save Role:** Click the bookmark icon on any card. Navigate to `saved-opportunities.html` to confirm it is listed. Refresh the page to verify it persists.
4. **Submit Application:** Click &quot;Apply&quot; on any role. Complete the modal and submit. Confirm success toast, application count increment, and notification generation.
5. **Duplicate Prevention:** Click &quot;Apply&quot; on the same role again. Confirm the system blocks submission with a warning.
6. **Live Status Switcher (Demo Feature):** Go to `applications.html`. Select an application in the Demo Control Panel, switch status to &quot;Selected&quot;, and click &quot;Update Status Live&quot;. Check the table status, notification badge, and dashboard donut chart.
7. **Eligibility Check:** Open `eligibility.html`. Select Google SDE Intern. Observe the automated criteria checklist and matched skills. Change CGPA to 6.5 to see failure feedback.
8. **Student Profile:** Open `profile.html`. Add a new skill in the tag manager, edit CGPA, and click &quot;Save Profile&quot;. Refresh to ensure persistence.
9. **Theme Toggle:** Click the Moon/Sun icon in the topbar. Confirm the entire interface transitions between light and dark themes smoothly.
10. **Reset Demo Data:** Open `settings.html`, click &quot;Reset Sample Data&quot;, and confirm. Notice the fresh default dataset is restored.

---

## 7. Current Limitations (Review 01 Prototype)
- **Local Isolation:** Data is stored in browser `localStorage`; changes on one browser or computer do not sync to another device.
- **Simulated Authentication:** The student account (Priti Kumari) is pre-configured for demonstration without server password authentication.
- **Mock File Storage:** Resume attachment records the filename locally rather than uploading binary PDF files to a remote server.
- **Sample Dataset:** Company vacancies and recruitment schedules are demonstration records.

---

## 8. Future Scope (Roadmap for Review 02)
- **Backend Architecture:** Implementation of a RESTful API service using PHP 8.x or Java Servlets.
- **Relational Database:** Normalized MySQL schema with tables: `students`, `companies`, `opportunities`, `applications`, `interviews`.
- **Authentication & RBAC:** Multi-role login (Student, Placement Coordinator, Admin) with bcrypt password hashing and session tokens.
- **Physical Resume Storage:** Server-side file upload handling with PDF preview embedding.
- **Automated Email Notifications:** Integration with PHPMailer or JavaMail for real-time interview invitation emails.
