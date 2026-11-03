# Faculty Review Demonstration Script (3–5 Minutes)

## Campus CareerTrack – Student Placement & Internship Management System
**Presentation Goal:** Deliver a clear, confident, and professional walkthrough of your Web Technology Lab Review 01 project to your faculty evaluator.

---

### Step 1: Introduction & Landing Page (0:00 – 0:45)
**Action:** Open `index.html` in your browser.

**What to Say:**
> "Good morning/afternoon, Sir/Madam. My name is [Your Name], and I am presenting my individual Web Technology Lab project for Review 01: **Campus CareerTrack – Student Placement & Internship Management System**.
>
> In our college, placement notices, eligibility cutoffs, and application updates are often scattered across messaging groups and PDF circulars. Campus CareerTrack solves this by providing a unified, centralized career hub for students.
>
> As per Review 01 requirements, this application is built strictly using **HTML5, CSS3, Vanilla JavaScript, and the browser LocalStorage API**—operating completely client-side without external backend dependencies.
>
> Here on the landing page, we have our platform branding, active placement season statistics, featured company opportunities, key platform benefits, and a step-by-step overview of how the system works."

---

### Step 2: Student Dashboard & Dynamic Metrics (0:45 – 1:30)
**Action:** Click the **"Student Portal"** button in the navbar or **"Get Started"** to navigate to `dashboard.html`.

**What to Say:**
> "Navigating into the **Student Dashboard**, we enter a personalized student workspace. Notice that the topbar and sidebar display the student profile name and initials.
>
> The dashboard displays four dynamic metric cards: Total Applications, Shortlisted Profiles, Upcoming Interviews, and Saved Opportunities. These values are not hardcoded; they are computed dynamically by our storage controller from `localStorage`.
>
> Below, we have our recent application feed, upcoming application deadlines with countdown badges, skill-matched recommendations, and an interactive **SVG Status Distribution Donut Chart** showing the proportion of applications in each recruitment stage.
>
> I can also switch between **Light Mode and Dark Mode** using the theme toggle in the top navigation bar, and this preference persists across page reloads."

---

### Step 3: Opportunities Hub, Search, Filters & Applying (1:30 – 2:30)
**Action:** Click **"Opportunities"** in the sidebar. Show search, filters, bookmark, and apply.

**What to Say:**
> "Next, let's explore the **Opportunities Hub** (`opportunities.html`). Here we have a curated catalog of 12 realistic engineering opportunities across leading tech employers.
>
> Each card displays the company name, logo badge, role title, work mode, stipend or salary range, minimum CGPA requirement, required skills, and deadline countdown.
>
> Let's test the search: If I type *'Google'* or *'React'*, the listing filters instantly. I can also filter by location—such as *'Bangalore'*—work mode, or click any of these quick skill chips like *'Python'* or *'JavaScript'*.
>
> I can bookmark any opportunity with this bookmark button—let's bookmark *AWS Cloud Engineer*. A toast alert confirms the action, and if we check **Saved Jobs** in the sidebar, it appears immediately.
>
> Now, let's apply for an opportunity. I'll click **'Apply'** on the *CRED Mobile App Developer* role. A structured modal opens, pre-filling the student's name, email, phone, and resume document from the profile. I enter an optional statement, check the eligibility certification, and click **'Submit Application'**.
>
> Notice the instant success toast! If I attempt to apply for the same role again, the system prevents duplicate applications and displays a warning."

---

### Step 4: Eligibility Evaluation Engine (2:30 – 3:15)
**Action:** Click **"Eligibility Checker"** in the sidebar (`eligibility.html`).

**What to Say:**
> "One of the standout features of Campus CareerTrack is the **Role Eligibility Checker** (`eligibility.html`).
>
> Instead of students guessing whether they qualify, they can select any company opening—for instance, *Google SDE Intern*. The system displays the company's published benchmarks: minimum CGPA of 8.0, maximum 0 backlogs, and permitted batches.
>
> The candidate's credentials are automatically pre-filled from their profile. When I click **'Run Eligibility Assessment'**, our client-side rule engine evaluates every criterion:
> - CGPA benchmark check (Passed)
> - Active backlogs check (Passed)
> - Graduation batch check (Passed)
> - Department alignment (Passed)
> - Skills assessment: It highlights matched skills in green and identifies missing skills in orange with recommendations on what to learn before the interview.
>
> If I test a 'what-if' scenario by changing the student's CGPA to 7.2 or backlogs to 2, the engine immediately flags the application as **'Does Not Meet Stated Criteria'** and explains the exact shortfall."

---

### Step 5: Application Tracker & Demo State Switcher (3:15 – 4:00)
**Action:** Click **"My Applications"** in the sidebar (`applications.html`).

**What to Say:**
> "Now let's view the **Application Tracker** (`applications.html`). Here students can see their application lifecycle: Applied, Shortlisted, Interview Scheduled, Selected, and Rejected.
>
> To make this evaluation seamless for you, Sir/Madam, I built a dedicated **Demonstration Control Panel** at the top. This allows us to simulate real-time recruiter actions live during the review!
>
> Watch this: Let's select the *Swiggy Data Science Intern* application which is currently in *'Applied'* status. I will switch its status to **'Interview Scheduled'** and click **'Update Status Live'**.
>
> Immediately:
> 1. The status badge in the table turns amber with an interview timestamp.
> 2. A real-time toast notification appears.
> 3. An interview alert is generated in the **Notifications Center** with an updated unread badge in the topbar.
> 4. If we return to the **Dashboard**, the *Upcoming Interviews* metric and donut chart update automatically.
>
> Furthermore, students can withdraw applications with a styled confirmation modal dialog."

---

### Step 6: Profile, Settings, and Future Scope (4:00 – 4:45)
**Action:** Briefly show `profile.html` and `settings.html`.

**What to Say:**
> "In the **Student Profile** page, students can edit their academic credentials, add or remove skills using our interactive tag manager, and observe their profile completeness score update dynamically.
>
> In the **Settings** page, we provide options to switch themes, configure notification preferences, export the entire browser state as a **JSON backup file**, or reset everything back to default demonstration data with a single click.
>
> **Future Scope for Review 02:**
> Because this codebase uses a modular separation between UI views, application controllers, and a centralized data engine (`storage.js`), transitioning to **LWP Review 02** will be straightforward:
> - We will replace `localStorage` calls with `fetch()` requests to a **PHP 8 / Java Servlets** REST backend.
> - We will migrate the data schemas to a normalized **MySQL database** with tables for students, companies, applications, and interview logs.
> - We will add role-based authentication and secure session management.
>
> Thank you, Sir/Madam. I am now open to any questions or specific test cases you would like me to demonstrate."

---

### Quick Answers to Anticipated Faculty Questions:

**Q: Where is the data stored?**  
**A:** "In browser `localStorage` using structured JSON keys. We built a centralized data layer in `js/storage.js` with auto-recovery for missing or corrupted keys."

**Q: Does it work without an internet connection?**  
**A:** "Yes, all application logic, HTML, CSS, JavaScript, and storage run 100% locally. Internet is only used for the Google Fonts and Font Awesome icon CDN links."

**Q: How do you prevent duplicate applications?**  
**A:** "`Storage.hasApplied(oppId)` inspects existing application records in `localStorage` before permitting submission in `App.openApplyModal()`, blocking duplicates with a warning toast."

**Q: How does the application scale to Review 02?**  
**A:** "All data interactions are decoupled through the `Storage` module. In Review 02, the storage methods will simply make asynchronous HTTP requests to PHP scripts or Java Servlets connected to a MySQL database."
