/**
 * Campus CareerTrack - Storage Engine & Data Management Layer
 * LWP Review 01 - Client-Side Architecture
 * 
 * Manages all persistent application data safely in browser localStorage.
 * Handles JSON serialization, edge-case recovery, and data synchronization.
 */

const STORAGE_KEYS = {
  PROFILE: 'cct_student_profile',
  OPPORTUNITIES: 'cct_opportunities',
  APPLICATIONS: 'cct_applications',
  BOOKMARKS: 'cct_saved_bookmarks',
  NOTIFICATIONS: 'cct_notifications',
  THEME: 'cct_theme_preference',
  SETTINGS: 'cct_app_settings',
  INITIALIZED: 'cct_data_initialized_v1'
};

const DEFAULT_PROFILE = {
  fullName: "Priti Kumari",
  email: "priti.kumari@campus.edu",
  phone: "+91 98765 43210",
  registerNo: "710023104042",
  college: "Government College of Technology",
  degree: "B.E.",
  department: "Computer Science and Engineering",
  gradYear: "2026",
  cgpa: 8.45,
  activeBacklogs: 0,
  skills: ["JavaScript", "Python", "Data Structures", "Algorithms", "HTML5", "CSS3", "React", "SQL"],
  preferredRoles: ["Software Engineer", "Full Stack Developer", "Data Scientist"],
  preferredLocation: "Bangalore / Hybrid",
  linkedin: "https://linkedin.com/in/priti-kumari-cse",
  github: "https://github.com/priti-kumari",
  resumeFilename: "Priti_Kumari_CSE_Resume.pdf",
  bio: "Pre-final year Computer Science student passionate about full-stack engineering, cloud architecture, and competitive programming."
};

const DEFAULT_OPPORTUNITIES = [
  {
    id: "opp-1",
    company: "Google",
    logoText: "G",
    logoColor: "#4285F4",
    title: "Software Development Engineer (SDE) Intern",
    department: "Core Engineering & Cloud",
    location: "Bangalore",
    workMode: "Hybrid",
    type: "Internship",
    category: "Software Engineering",
    stipend: "₹1,00,000 / month",
    stipendNumeric: 100000,
    salaryAnnual: "₹28 - ₹34 LPA",
    deadline: "2026-11-20",
    minCGPA: 8.0,
    allowedBatches: ["2026", "2027"],
    allowedBranches: ["Computer Science and Engineering", "Information Technology", "Electronics and Communication"],
    maxBacklogs: 0,
    skills: ["Data Structures", "Algorithms", "C++", "Java", "Python"],
    shortDesc: "Design and implement scalable distributed systems impacting billions of global users.",
    fullDesc: "As an SDE Intern at Google, you will collaborate with seasoned software engineers to design, build, and test next-generation software technologies. You will tackle real-world engineering challenges spanning core search algorithms, developer platforms, distributed cloud storage, and AI infrastructure.",
    responsibilities: [
      "Design, build, test, deploy, and maintain software solutions with strict SLAs.",
      "Collaborate with multidisciplinary engineering, product, and UX teams.",
      "Write clean, modular, and well-tested code following Google's engineering standards.",
      "Analyze and optimize system performance, latency, memory utilization, and throughput."
    ],
    interviewRounds: [
      "Online Coding Assessment (2 algorithmic questions, 90 mins)",
      "Technical Interview 1: Data Structures & Problem Solving",
      "Technical Interview 2: Algorithms, System Design & Clean Code",
      "Googliness & Leadership Fitment Round"
    ]
  },
  {
    id: "opp-2",
    company: "Microsoft",
    logoText: "MS",
    logoColor: "#00A4EF",
    title: "Graduate Technology Associate (Full-Time)",
    department: "Azure Cloud & AI Platform",
    location: "Hyderabad",
    workMode: "Hybrid",
    type: "Full-time",
    category: "Cloud & DevOps",
    stipend: "₹55,000 / month (Probation)",
    stipendNumeric: 55000,
    salaryAnnual: "₹18 - ₹22 LPA",
    deadline: "2026-11-25",
    minCGPA: 7.5,
    allowedBatches: ["2026"],
    allowedBranches: ["Computer Science and Engineering", "Information Technology", "Electronics and Communication", "Electrical and Electronics"],
    maxBacklogs: 0,
    skills: ["Azure", "C#", "Python", "Cloud Computing", "REST APIs"],
    shortDesc: "Empower global organizations by architecting resilient cloud systems and developer tooling.",
    fullDesc: "Join Microsoft's Azure engineering division as a Graduate Technology Associate. You will build and optimize world-class cloud infrastructure, microservices, and enterprise applications that power Fortune 500 businesses.",
    responsibilities: [
      "Develop cloud services and API integrations on Azure Kubernetes Service.",
      "Work closely with customer success teams to remediate complex architectural bottlenecks.",
      "Implement robust automated CI/CD deployment pipelines using Azure DevOps.",
      "Champion security, fault tolerance, and cost optimization best practices."
    ],
    interviewRounds: [
      "Online Technical Screening (Algorithms & OOPS)",
      "Technical Round 1: Cloud Architecture & Problem Solving",
      "Technical Round 2: Low-Level Design & Coding",
      "Executive Leadership & Cultural Interview"
    ]
  },
  {
    id: "opp-3",
    company: "Razorpay",
    logoText: "RZ",
    logoColor: "#0C2340",
    title: "Frontend Web Developer",
    department: "Checkout & Payments Experience",
    location: "Bangalore",
    workMode: "Remote",
    type: "Full-time",
    category: "Web Development",
    stipend: "₹35,000 / month (Internship period)",
    stipendNumeric: 35000,
    salaryAnnual: "₹14 - ₹18 LPA",
    deadline: "2026-12-05",
    minCGPA: 7.0,
    allowedBatches: ["2026", "2027"],
    allowedBranches: ["All Engineering Branches"],
    maxBacklogs: 1,
    skills: ["JavaScript", "HTML5", "CSS3", "React", "TypeScript"],
    shortDesc: "Craft seamless, high-converting checkout experiences processing billions in payments.",
    fullDesc: "At Razorpay, frontend engineers own the payment gateway interface trusted by millions of shoppers and Indian businesses. You will build highly responsive, lightweight, and accessible UI widgets.",
    responsibilities: [
      "Build modular, responsive UI components using modern JavaScript and React.",
      "Ensure cross-browser compatibility and ultra-fast page load times (sub-100ms render).",
      "Collaborate with product designers to create delightful user micro-interactions.",
      "Write comprehensive unit and integration tests using Jest and Cypress."
    ],
    interviewRounds: [
      "Take-Home Frontend Machine Coding Challenge",
      "Technical Interview: JavaScript Internals, DOM, & React Architecture",
      "Web Performance, Security, and State Management Deep-Dive",
      "Culture & Values Interview with VP of Engineering"
    ]
  },
  {
    id: "opp-4",
    company: "Swiggy",
    logoText: "SW",
    logoColor: "#FC8019",
    title: "Data Science & Machine Learning Intern",
    department: "Predictive Logistics & AI",
    location: "Bangalore",
    workMode: "Hybrid",
    type: "Internship",
    category: "Data Science",
    stipend: "₹45,000 / month",
    stipendNumeric: 45000,
    salaryAnnual: "₹16 - ₹20 LPA",
    deadline: "2026-11-18",
    minCGPA: 7.5,
    allowedBatches: ["2026", "2027"],
    allowedBranches: ["Computer Science and Engineering", "Information Technology", "AI/DS", "Mathematics"],
    maxBacklogs: 0,
    skills: ["Python", "Machine Learning", "SQL", "Pandas", "Scikit-Learn"],
    shortDesc: "Optimize delivery dispatch models and predictive hunger intelligence pipelines in real-time.",
    fullDesc: "Swiggy's logistics network generates petabytes of spatial and delivery data every day. In this role, you will analyze order behavior, tune routing algorithms, and improve ETA prediction accuracy.",
    responsibilities: [
      "Build and evaluate statistical and machine learning models for delivery time forecasting.",
      "Perform exploratory data analysis on millions of delivery records to discover optimization opportunities.",
      "Clean, normalize, and transform unstructured real-world datasets using SQL and Pandas.",
      "Deploy models into containerized staging environments for A/B experimentation."
    ],
    interviewRounds: [
      "SQL and Data Analysis Assessment",
      "Technical Interview 1: Mathematics, Probability & Statistics",
      "Technical Interview 2: Machine Learning Algorithms & Applied Case Study",
      "Engineering Manager Discussion"
    ]
  },
  {
    id: "opp-5",
    company: "Amazon Web Services (AWS)",
    logoText: "AWS",
    logoColor: "#FF9900",
    title: "Associate Cloud Engineer",
    department: "Cloud Solutions Architecture",
    location: "Pune",
    workMode: "On-site",
    type: "Full-time",
    category: "Cloud & DevOps",
    stipend: "₹40,000 / month",
    stipendNumeric: 40000,
    salaryAnnual: "₹15 - ₹19 LPA",
    deadline: "2026-12-10",
    minCGPA: 7.2,
    allowedBatches: ["2026"],
    allowedBranches: ["Computer Science and Engineering", "Information Technology", "Electronics and Communication"],
    maxBacklogs: 0,
    skills: ["Linux", "AWS", "Networking", "Python", "Docker"],
    shortDesc: "Architect resilient cloud environments and troubleshoot critical enterprise cloud issues.",
    fullDesc: "Work directly on AWS core infrastructure, helping enterprise clients deploy, scale, and secure mission-critical workloads across compute, containerization, and serverless architectures.",
    responsibilities: [
      "Assist customers with troubleshooting cloud networking, IAM, VPC, and EC2 configurations.",
      "Write infrastructure automation scripts using Bash, Python, and AWS SDK.",
      "Reproduce customer production defects in sandbox staging environments.",
      "Author technical whitepapers and knowledge-base articles on cloud best practices."
    ],
    interviewRounds: [
      "AWS Technical Aptitude & Networking Assessment",
      "Technical Interview 1: Linux Fundamentals, Networking (TCP/IP, DNS), & Shell Scripting",
      "Technical Interview 2: Cloud Troubleshooting Scenarios & Scripting",
      "Amazon Leadership Principles Interview"
    ]
  },
  {
    id: "opp-6",
    company: "Zerodha",
    logoText: "ZD",
    logoColor: "#387ED1",
    title: "Full Stack Engineer Intern",
    department: "Kite Trading Platforms",
    location: "Bangalore",
    workMode: "Remote",
    type: "Internship",
    category: "Web Development",
    stipend: "₹50,000 / month",
    stipendNumeric: 50000,
    salaryAnnual: "₹16 - ₹24 LPA",
    deadline: "2026-11-28",
    minCGPA: 7.0,
    allowedBatches: ["2026", "2027"],
    allowedBranches: ["All Engineering Branches"],
    maxBacklogs: 1,
    skills: ["Go", "JavaScript", "PostgreSQL", "Vue.js", "Redis"],
    shortDesc: "Work on low-latency trading interfaces handling over 15 million orders every day.",
    fullDesc: "Zerodha is India's largest retail stockbroker. We build minimal, fast, and rock-solid financial software using Free and Open Source Software (FOSS). As an intern, you will contribute directly to production systems handling massive concurrent traffic.",
    responsibilities: [
      "Develop ultra-fast backend microservices in Go and high-performance Web APIs.",
      "Optimize frontend charting and order placement flows with Vue.js.",
      "Fine-tune database queries, caching layers, and WebSocket feeds.",
      "Adhere to Unix philosophy: simple, clean, and maintainable software architecture."
    ],
    interviewRounds: [
      "Open Source Project Review & Coding Assignment",
      "Technical Discussion: Go/Concurrency, WebSockets & SQL Query Tuning",
      "System Design for High Concurrency (100k req/sec)",
      "Founders & Engineering Culture Discussion"
    ]
  },
  {
    id: "opp-7",
    company: "Cisco Systems",
    logoText: "CS",
    logoColor: "#1BA0D7",
    title: "Cybersecurity Analyst Trainee",
    department: "Global Security Operations",
    location: "Bangalore",
    workMode: "On-site",
    type: "Full-time",
    category: "Cyber Security",
    stipend: "₹38,000 / month",
    stipendNumeric: 38000,
    salaryAnnual: "₹12 - ₹15 LPA",
    deadline: "2026-12-15",
    minCGPA: 7.0,
    allowedBatches: ["2026"],
    allowedBranches: ["Computer Science and Engineering", "Information Technology", "Electronics and Communication"],
    maxBacklogs: 0,
    skills: ["Network Security", "Wireshark", "Linux", "Python", "Cryptography"],
    shortDesc: "Defend global enterprise networks against emerging cyber threats and attack vectors.",
    fullDesc: "Cisco's security division protects over 80% of global internet traffic. In this role, you will monitor security events, analyze anomalies, and build defenses for next-generation enterprise firewalls.",
    responsibilities: [
      "Monitor SIEM alerts and identify malicious network intrusion attempts.",
      "Perform deep packet inspection and network protocol analysis using Wireshark.",
      "Automate incident response routines with Python and REST APIs.",
      "Conduct vulnerability assessments and assist in security remediation."
    ],
    interviewRounds: [
      "Online Security Fundamentals Assessment",
      "Technical Interview 1: Network Protocols, Cryptography, & Linux Security",
      "Technical Interview 2: Threat Detection & Packet Analysis Practical Test",
      "Behavioral & Team Culture Round"
    ]
  },
  {
    id: "opp-8",
    company: "CRED",
    logoText: "CR",
    logoColor: "#111111",
    title: "Mobile App Developer (Flutter/iOS)",
    department: "Member Experience & Rewards",
    location: "Bangalore",
    workMode: "Hybrid",
    type: "Internship",
    category: "Mobile App Development",
    stipend: "₹60,000 / month",
    stipendNumeric: 60000,
    salaryAnnual: "₹18 - ₹22 LPA",
    deadline: "2026-11-30",
    minCGPA: 7.5,
    allowedBatches: ["2026", "2027"],
    allowedBranches: ["All Engineering Branches"],
    maxBacklogs: 0,
    skills: ["Flutter", "Dart", "Swift", "REST APIs", "UI/UX"],
    shortDesc: "Craft pixel-perfect micro-animations and intuitive financial workflows for credit card members.",
    fullDesc: "CRED is celebrated for its world-class mobile user experience, smooth 60fps animations, and neo-banking elegance. You will build cutting-edge mobile interfaces that delight over 12 million high-trust members.",
    responsibilities: [
      "Create fluid, interactive UI components and custom gestures in Flutter/Dart.",
      "Optimize mobile app cold start time, memory consumption, and battery footprint.",
      "Integrate complex backend APIs with offline caching capabilities.",
      "Collaborate closely with product designers to implement custom physics-based animations."
    ],
    interviewRounds: [
      "Mobile App UI/UX Coding Challenge",
      "Technical Interview 1: State Management, Mobile Architecture, & Performance",
      "Technical Interview 2: Native Bridging & Asynchronous Programming",
      "Design Critique & Culture Interview"
    ]
  },
  {
    id: "opp-9",
    company: "PhonePe",
    logoText: "PP",
    logoColor: "#5F259F",
    title: "QA & SDET Automation Engineer",
    department: "Merchant Payments Quality",
    location: "Pune",
    workMode: "Hybrid",
    type: "Full-time",
    category: "Quality Assurance",
    stipend: "₹32,000 / month",
    stipendNumeric: 32000,
    salaryAnnual: "₹10 - ₹13 LPA",
    deadline: "2026-12-20",
    minCGPA: 6.8,
    allowedBatches: ["2026"],
    allowedBranches: ["All Engineering Branches"],
    maxBacklogs: 1,
    skills: ["Selenium", "Java", "TestNG", "API Testing", "Postman"],
    shortDesc: "Build robust test automation frameworks ensuring 99.99% reliability for UPI transactions.",
    fullDesc: "Join India's leading digital payments platform. As an SDET, you will design automated test harnesses, load test transaction gateways, and prevent critical bugs from reaching 500+ million registered users.",
    responsibilities: [
      "Develop end-to-end regression test suites in Java and Selenium.",
      "Automate REST API verification pipelines with Postman and RestAssured.",
      "Perform stress testing on merchant payment gateways during peak flash sales.",
      "Identify defect root causes and work with development teams for rapid patch deployment."
    ],
    interviewRounds: [
      "Automated Testing & Java Assessment",
      "Technical Interview 1: Core Java, OOPs, & Framework Design",
      "Technical Interview 2: Test Case Design, API Testing, & CI/CD",
      "Managerial & Quality Assurance Mindset Round"
    ]
  },
  {
    id: "opp-10",
    company: "Zomato",
    logoText: "ZM",
    logoColor: "#E23744",
    title: "UI/UX Product Design Intern",
    department: "Consumer Experience Design",
    location: "Gurgaon",
    workMode: "Remote",
    type: "Internship",
    category: "Design",
    stipend: "₹40,000 / month",
    stipendNumeric: 40000,
    salaryAnnual: "₹12 - ₹15 LPA",
    deadline: "2026-12-08",
    minCGPA: 6.5,
    allowedBatches: ["2026", "2027", "2028"],
    allowedBranches: ["All Engineering Branches"],
    maxBacklogs: 2,
    skills: ["Figma", "Design Systems", "Prototyping", "User Research", "Wireframing"],
    shortDesc: "Design mouth-watering food discovery interfaces and gamified dining journeys.",
    fullDesc: "Zomato touches millions of foodies daily. As a design intern, you will conduct user interviews, craft design systems, and prototype gamified features for our consumer apps.",
    responsibilities: [
      "Design wireframes, low and high-fidelity mockups, and interactive prototypes in Figma.",
      "Contribute to Sushi, Zomato's scalable cross-platform design system.",
      "Participate in user research sessions, usability tests, and design reviews.",
      "Work with frontend engineers to ensure design fidelity in production builds."
    ],
    interviewRounds: [
      "Portfolio Review & Case Study Presentation",
      "Design Exercise: Redesigning a Food Delivery Flow in 48 Hours",
      "Design Systems & Usability Deep-Dive",
      "Culture Fit & Team Chat"
    ]
  },
  {
    id: "opp-11",
    company: "NVIDIA",
    logoText: "NV",
    logoColor: "#76B900",
    title: "Systems Software Engineer",
    department: "GPU Computing & CUDA Platform",
    location: "Hyderabad",
    workMode: "On-site",
    type: "Full-time",
    category: "Systems & Hardware",
    stipend: "₹65,000 / month",
    stipendNumeric: 65000,
    salaryAnnual: "₹22 - ₹26 LPA",
    deadline: "2026-11-22",
    minCGPA: 8.2,
    allowedBatches: ["2026"],
    allowedBranches: ["Computer Science and Engineering", "Information Technology", "Electronics and Communication", "Electrical and Electronics"],
    maxBacklogs: 0,
    skills: ["C++", "CUDA", "Linux Kernel", "Operating Systems", "Multithreading"],
    shortDesc: "Optimize deep learning acceleration pipelines on state-of-the-art GPU architectures.",
    fullDesc: "NVIDIA is pioneering modern AI and accelerated computing. In this role, you will write low-level C++ and CUDA software that enables deep neural networks to run at blistering speeds across data centers.",
    responsibilities: [
      "Develop low-level device drivers and runtime libraries for NVIDIA graphics processing units.",
      "Profile and optimize memory bandwidth, cache efficiency, and thread divergence in CUDA kernels.",
      "Collaborate with AI framework teams (PyTorch, TensorFlow) on hardware acceleration.",
      "Debug complex kernel panic, memory corruption, and concurrency deadlock issues."
    ],
    interviewRounds: [
      "Systems Programming Assessment (C/C++, Pointers, OS)",
      "Technical Interview 1: Memory Management, Concurrency, & Architecture",
      "Technical Interview 2: Algorithms, Bitwise Ops, & Low-Level Code Optimization",
      "Director of Systems Engineering Interview"
    ]
  },
  {
    id: "opp-12",
    company: "Flipkart",
    logoText: "FK",
    logoColor: "#2874F0",
    title: "DevOps & Infrastructure Engineer",
    department: "Big Billion Day Platform Infrastructure",
    location: "Bangalore",
    workMode: "Hybrid",
    type: "Full-time",
    category: "Cloud & DevOps",
    stipend: "₹45,000 / month",
    stipendNumeric: 45000,
    salaryAnnual: "₹16 - ₹20 LPA",
    deadline: "2026-12-12",
    minCGPA: 7.2,
    allowedBatches: ["2026"],
    allowedBranches: ["Computer Science and Engineering", "Information Technology", "Electronics and Communication"],
    maxBacklogs: 0,
    skills: ["Kubernetes", "Docker", "Terraform", "CI/CD", "Prometheus"],
    shortDesc: "Manage massive scale infrastructure powering India's largest e-commerce shopping festival.",
    fullDesc: "Join Flipkart's Core Infrastructure platform team. You will automate deployment workflows, manage multi-region Kubernetes clusters, and guarantee zero-downtime during peak traffic spikes.",
    responsibilities: [
      "Manage and scale multi-tenant Kubernetes clusters across hybrid cloud data centers.",
      "Provision cloud infrastructure using Infrastructure as Code (Terraform & Ansible).",
      "Configure automated alerting and real-time observability with Prometheus and Grafana.",
      "Participate in site reliability disaster recovery simulations and chaos engineering."
    ],
    interviewRounds: [
      "DevOps Automation & Shell Scripting Assessment",
      "Technical Interview 1: Containers, Orchestration, & Linux Networking",
      "Technical Interview 2: System Reliability, Monitoring, & Incident Simulation",
      "Engineering Leadership & Collaborative Values"
    ]
  }
];

const DEFAULT_APPLICATIONS = [
  {
    id: "app-101",
    oppId: "opp-1",
    company: "Google",
    logoText: "G",
    logoColor: "#4285F4",
    role: "Software Development Engineer (SDE) Intern",
    appliedDate: "2026-10-18",
    status: "Shortlisted",
    interviewDate: "2026-11-12 10:00 AM",
    applicantName: "Priti Kumari",
    applicantEmail: "priti.kumari@campus.edu",
    applicantPhone: "+91 98765 43210",
    resumeFilename: "Priti_Kumari_CSE_Resume.pdf",
    notes: "Coding assessment cleared with 100% score. Technical Interview Round 1 scheduled with Core Engineering team."
  },
  {
    id: "app-102",
    oppId: "opp-3",
    company: "Razorpay",
    logoText: "RZ",
    logoColor: "#0C2340",
    role: "Frontend Web Developer",
    appliedDate: "2026-10-22",
    status: "Interview Scheduled",
    interviewDate: "2026-11-08 02:30 PM",
    applicantName: "Priti Kumari",
    applicantEmail: "priti.kumari@campus.edu",
    applicantPhone: "+91 98765 43210",
    resumeFilename: "Priti_Kumari_CSE_Resume.pdf",
    notes: "Design challenge approved. Machine coding round on React & DOM manipulation scheduled."
  },
  {
    id: "app-103",
    oppId: "opp-4",
    company: "Swiggy",
    logoText: "SW",
    logoColor: "#FC8019",
    role: "Data Science & Machine Learning Intern",
    appliedDate: "2026-10-25",
    status: "Applied",
    interviewDate: "Pending Scheduling",
    applicantName: "Priti Kumari",
    applicantEmail: "priti.kumari@campus.edu",
    applicantPhone: "+91 98765 43210",
    resumeFilename: "Priti_Kumari_CSE_Resume.pdf",
    notes: "Application submitted. Under initial recruiter screening."
  },
  {
    id: "app-104",
    oppId: "opp-2",
    company: "Microsoft",
    logoText: "MS",
    logoColor: "#00A4EF",
    role: "Graduate Technology Associate (Full-Time)",
    appliedDate: "2026-10-14",
    status: "Selected",
    interviewDate: "Completed (2026-10-28)",
    applicantName: "Priti Kumari",
    applicantEmail: "priti.kumari@campus.edu",
    applicantPhone: "+91 98765 43210",
    resumeFilename: "Priti_Kumari_CSE_Resume.pdf",
    notes: "Offer letter generated! Final onboarding document verification in progress."
  }
];

const DEFAULT_BOOKMARKS = ["opp-5", "opp-6", "opp-8"];

const DEFAULT_NOTIFICATIONS = [
  {
    id: "notif-1",
    title: "Interview Scheduled: Razorpay",
    message: "Machine coding round on React & DOM internals scheduled for Nov 08, 2026 at 02:30 PM.",
    type: "interview",
    timestamp: "2026-10-27T14:30:00Z",
    read: false,
    link: "applications.html"
  },
  {
    id: "notif-2",
    title: "Application Shortlisted: Google",
    message: "Congratulations! Your application for SDE Intern has been shortlisted for technical interviews.",
    type: "status",
    timestamp: "2026-10-26T11:00:00Z",
    read: false,
    link: "applications.html"
  },
  {
    id: "notif-3",
    title: "Upcoming Deadline: Google SDE Intern",
    message: "Only 17 days left to apply for the Google SDE Internship role. Verify your documents.",
    type: "deadline",
    timestamp: "2026-10-25T09:15:00Z",
    read: true,
    link: "opportunity-details.html?id=opp-1"
  },
  {
    id: "notif-4",
    title: "Placement Offer: Microsoft",
    message: "Great news! You have been selected for Graduate Technology Associate role. Check details in Application Tracker.",
    type: "selection",
    timestamp: "2026-10-24T16:45:00Z",
    read: true,
    link: "applications.html"
  },
  {
    id: "notif-5",
    title: "Welcome to Campus CareerTrack",
    message: "Explore 12+ curated opportunities, verify your eligibility, and track every stage of your campus placement.",
    type: "system",
    timestamp: "2026-10-20T08:00:00Z",
    read: true,
    link: "dashboard.html"
  }
];

const DEFAULT_SETTINGS = {
  theme: "light",
  emailAlerts: true,
  deadlineReminders: true,
  interviewAlerts: true,
  compactTables: false
};

/**
 * Storage Controller Object
 */
const Storage = {
  /**
   * Initializes default sample data in localStorage if not already present.
   * Safe against corruption or partial keys.
   */
  init() {
    try {
      if (!localStorage.getItem(STORAGE_KEYS.INITIALIZED)) {
        this.resetToDefaultData(false);
      } else {
        // Validate individual keys exist
        if (!localStorage.getItem(STORAGE_KEYS.PROFILE)) {
          this.set(STORAGE_KEYS.PROFILE, DEFAULT_PROFILE);
        }
        if (!localStorage.getItem(STORAGE_KEYS.OPPORTUNITIES)) {
          this.set(STORAGE_KEYS.OPPORTUNITIES, DEFAULT_OPPORTUNITIES);
        }
        if (!localStorage.getItem(STORAGE_KEYS.APPLICATIONS)) {
          this.set(STORAGE_KEYS.APPLICATIONS, DEFAULT_APPLICATIONS);
        }
        if (!localStorage.getItem(STORAGE_KEYS.BOOKMARKS)) {
          this.set(STORAGE_KEYS.BOOKMARKS, DEFAULT_BOOKMARKS);
        }
        if (!localStorage.getItem(STORAGE_KEYS.NOTIFICATIONS)) {
          this.set(STORAGE_KEYS.NOTIFICATIONS, DEFAULT_NOTIFICATIONS);
        }
        if (!localStorage.getItem(STORAGE_KEYS.SETTINGS)) {
          this.set(STORAGE_KEYS.SETTINGS, DEFAULT_SETTINGS);
        }
      }
    } catch (e) {
      console.warn("Storage.init exception:", e);
    }
  },

  get(key, fallback = null) {
    try {
      const data = localStorage.getItem(key);
      if (data === null || data === undefined) return fallback;
      return JSON.parse(data);
    } catch (e) {
      console.error(`Failed to parse localStorage key '${key}':`, e);
      return fallback;
    }
  },

  set(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
      return true;
    } catch (e) {
      console.error(`Failed to write to localStorage key '${key}':`, e);
      return false;
    }
  },

  // ----------------------------------------------------
  // PROFILE
  // ----------------------------------------------------
  getProfile() {
    return this.get(STORAGE_KEYS.PROFILE, DEFAULT_PROFILE);
  },

  saveProfile(profileData) {
    const current = this.getProfile();
    const updated = { ...current, ...profileData };
    this.set(STORAGE_KEYS.PROFILE, updated);
    return updated;
  },

  // ----------------------------------------------------
  // OPPORTUNITIES
  // ----------------------------------------------------
  getOpportunities() {
    return this.get(STORAGE_KEYS.OPPORTUNITIES, DEFAULT_OPPORTUNITIES);
  },

  getOpportunityById(id) {
    const list = this.getOpportunities();
    return list.find(item => item.id === id) || null;
  },

  // ----------------------------------------------------
  // APPLICATIONS
  // ----------------------------------------------------
  getApplications() {
    return this.get(STORAGE_KEYS.APPLICATIONS, DEFAULT_APPLICATIONS);
  },

  getApplicationById(id) {
    const list = this.getApplications();
    return list.find(app => app.id === id) || null;
  },

  getApplicationByOppId(oppId) {
    const list = this.getApplications();
    return list.find(app => app.oppId === oppId) || null;
  },

  hasApplied(oppId) {
    return !!this.getApplicationByOppId(oppId);
  },

  addApplication(data) {
    if (this.hasApplied(data.oppId)) {
      return { success: false, message: "You have already applied for this opportunity." };
    }

    const applications = this.getApplications();
    const newApp = {
      id: "app-" + Date.now(),
      oppId: data.oppId,
      company: data.company,
      logoText: data.logoText || data.company.substring(0, 2).toUpperCase(),
      logoColor: data.logoColor || "#4F46E5",
      role: data.role,
      appliedDate: new Date().toISOString().split('T')[0],
      status: "Applied",
      interviewDate: "Pending Scheduling",
      applicantName: data.applicantName || "Student",
      applicantEmail: data.applicantEmail || "",
      applicantPhone: data.applicantPhone || "",
      resumeFilename: data.resumeFilename || "Resume.pdf",
      notes: data.notes || "Application submitted via Campus CareerTrack portal."
    };

    applications.unshift(newApp);
    this.set(STORAGE_KEYS.APPLICATIONS, applications);

    // Create a notification for application submission
    this.addNotification({
      title: `Application Submitted: ${data.company}`,
      message: `Your application for ${data.role} has been submitted successfully and is under review.`,
      type: "status",
      link: "applications.html"
    });

    return { success: true, application: newApp };
  },

  updateApplicationStatus(appId, newStatus, customNotes = null) {
    const applications = this.getApplications();
    const index = applications.findIndex(app => app.id === appId);
    if (index === -1) return { success: false, message: "Application not found." };

    const oldStatus = applications[index].status;
    applications[index].status = newStatus;

    if (customNotes) {
      applications[index].notes = customNotes;
    } else {
      if (newStatus === "Shortlisted") {
        applications[index].notes = "Congratulations! Your profile has been shortlisted for further rounds.";
      } else if (newStatus === "Interview Scheduled") {
        applications[index].interviewDate = "2026-11-15 11:30 AM (Demo Time)";
        applications[index].notes = "Technical and HR interview rounds have been scheduled.";
      } else if (newStatus === "Selected") {
        applications[index].notes = "Congratulations! You have received a formal offer.";
      } else if (newStatus === "Rejected") {
        applications[index].notes = "Thank you for participating. We encourage you to apply for future openings.";
      }
    }

    this.set(STORAGE_KEYS.APPLICATIONS, applications);

    // Add notification about status change
    this.addNotification({
      title: `Status Update: ${applications[index].company}`,
      message: `Status changed from '${oldStatus}' to '${newStatus}' for ${applications[index].role}.`,
      type: newStatus === "Selected" ? "selection" : newStatus === "Interview Scheduled" ? "interview" : "status",
      link: "applications.html"
    });

    return { success: true, application: applications[index] };
  },

  removeApplication(appId) {
    const applications = this.getApplications();
    const app = applications.find(a => a.id === appId);
    if (!app) return { success: false, message: "Application not found." };

    const filtered = applications.filter(a => a.id !== appId);
    this.set(STORAGE_KEYS.APPLICATIONS, filtered);

    this.addNotification({
      title: `Application Withdrawn: ${app.company}`,
      message: `You have cancelled your application for ${app.role}.`,
      type: "system",
      link: "applications.html"
    });

    return { success: true };
  },

  // ----------------------------------------------------
  // BOOKMARKS
  // ----------------------------------------------------
  getBookmarks() {
    return this.get(STORAGE_KEYS.BOOKMARKS, DEFAULT_BOOKMARKS);
  },

  isBookmarked(oppId) {
    const bookmarks = this.getBookmarks();
    return bookmarks.includes(oppId);
  },

  toggleBookmark(oppId) {
    let bookmarks = this.getBookmarks();
    let saved = false;
    if (bookmarks.includes(oppId)) {
      bookmarks = bookmarks.filter(id => id !== oppId);
      saved = false;
    } else {
      bookmarks.push(oppId);
      saved = true;
    }
    this.set(STORAGE_KEYS.BOOKMARKS, bookmarks);
    return saved;
  },

  // ----------------------------------------------------
  // NOTIFICATIONS
  // ----------------------------------------------------
  getNotifications() {
    return this.get(STORAGE_KEYS.NOTIFICATIONS, DEFAULT_NOTIFICATIONS);
  },

  getUnreadNotificationsCount() {
    const notifs = this.getNotifications();
    return notifs.filter(n => !n.read).length;
  },

  addNotification(item) {
    const notifications = this.getNotifications();
    const newNotif = {
      id: "notif-" + Date.now(),
      title: item.title || "Notification",
      message: item.message || "",
      type: item.type || "system",
      timestamp: new Date().toISOString(),
      read: false,
      link: item.link || "notifications.html"
    };
    notifications.unshift(newNotif);
    this.set(STORAGE_KEYS.NOTIFICATIONS, notifications);
    return newNotif;
  },

  markNotificationRead(id) {
    const notifications = this.getNotifications();
    const updated = notifications.map(n => n.id === id ? { ...n, read: true } : n);
    this.set(STORAGE_KEYS.NOTIFICATIONS, updated);
  },

  markAllNotificationsRead() {
    const notifications = this.getNotifications();
    const updated = notifications.map(n => ({ ...n, read: true }));
    this.set(STORAGE_KEYS.NOTIFICATIONS, updated);
  },

  clearNotification(id) {
    const notifications = this.getNotifications();
    const updated = notifications.filter(n => n.id !== id);
    this.set(STORAGE_KEYS.NOTIFICATIONS, updated);
  },

  clearAllNotifications() {
    this.set(STORAGE_KEYS.NOTIFICATIONS, []);
  },

  // ----------------------------------------------------
  // SETTINGS & THEME
  // ----------------------------------------------------
  getSettings() {
    return this.get(STORAGE_KEYS.SETTINGS, DEFAULT_SETTINGS);
  },

  saveSettings(newSettings) {
    const current = this.getSettings();
    const updated = { ...current, ...newSettings };
    this.set(STORAGE_KEYS.SETTINGS, updated);
    return updated;
  },

  getTheme() {
    return localStorage.getItem(STORAGE_KEYS.THEME) || 'light';
  },

  setTheme(theme) {
    localStorage.setItem(STORAGE_KEYS.THEME, theme);
    const settings = this.getSettings();
    settings.theme = theme;
    this.set(STORAGE_KEYS.SETTINGS, settings);
  },

  // ----------------------------------------------------
  // DASHBOARD STATISTICS
  // ----------------------------------------------------
  getDashboardStats() {
    const applications = this.getApplications();
    const bookmarks = this.getBookmarks();
    const opportunities = this.getOpportunities();

    const totalApplications = applications.length;
    const shortlistedCount = applications.filter(a => a.status === "Shortlisted").length;
    const interviewCount = applications.filter(a => a.status === "Interview Scheduled").length;
    const selectedCount = applications.filter(a => a.status === "Selected").length;
    const rejectedCount = applications.filter(a => a.status === "Rejected").length;
    const appliedCount = applications.filter(a => a.status === "Applied").length;
    const savedCount = bookmarks.length;
    const totalOpportunities = opportunities.length;

    return {
      totalApplications,
      shortlistedCount,
      interviewCount,
      selectedCount,
      rejectedCount,
      appliedCount,
      savedCount,
      totalOpportunities,
      activeProgress: shortlistedCount + interviewCount
    };
  },

  // ----------------------------------------------------
  // RESET / RECOVERY
  // ----------------------------------------------------
  resetToDefaultData(notifyUser = true) {
    this.set(STORAGE_KEYS.PROFILE, DEFAULT_PROFILE);
    this.set(STORAGE_KEYS.OPPORTUNITIES, DEFAULT_OPPORTUNITIES);
    this.set(STORAGE_KEYS.APPLICATIONS, DEFAULT_APPLICATIONS);
    this.set(STORAGE_KEYS.BOOKMARKS, DEFAULT_BOOKMARKS);
    this.set(STORAGE_KEYS.NOTIFICATIONS, DEFAULT_NOTIFICATIONS);
    this.set(STORAGE_KEYS.SETTINGS, DEFAULT_SETTINGS);
    this.set(STORAGE_KEYS.THEME, "light");
    localStorage.setItem(STORAGE_KEYS.INITIALIZED, "true");

    if (notifyUser && typeof App !== 'undefined' && App.showToast) {
      App.showToast("Application data reset to default demo values.", "info");
    }
  },

  clearAllData() {
    localStorage.removeItem(STORAGE_KEYS.PROFILE);
    localStorage.removeItem(STORAGE_KEYS.OPPORTUNITIES);
    localStorage.removeItem(STORAGE_KEYS.APPLICATIONS);
    localStorage.removeItem(STORAGE_KEYS.BOOKMARKS);
    localStorage.removeItem(STORAGE_KEYS.NOTIFICATIONS);
    localStorage.removeItem(STORAGE_KEYS.INITIALIZED);
    // keep theme
  }
};

// Auto-initialize when file loads
Storage.init();
