/* Campus CareerTrack — client-side demonstration.
   Data is stored in localStorage. This is not a production authentication system. */
"use strict";

const STORAGE_KEYS = {
  applications: "careertrack_applications_v1",
  saved: "careertrack_saved_v1",
  profile: "careertrack_profile_v1"
};

const jobs = [
  {id:"j1",company:"NexaSoft",initials:"N",color:"#e9e8ff",ink:"#5d57c9",role:"Frontend Developer Intern",type:"Internship",domain:"Software",location:"Hybrid",pay:"₹15,000 / month",deadline:"2026-11-02",skills:["JavaScript","React","CSS"],description:"Build polished product interfaces with a collaborative engineering team.",recommended:true},
  {id:"j2",company:"DataSpring",initials:"DS",color:"#dff6ee",ink:"#218568",role:"Data Analyst Intern",type:"Internship",domain:"Data",location:"Remote",pay:"₹12,000 / month",deadline:"2026-10-29",skills:["SQL","Python","Excel"],description:"Turn datasets into clear insights, dashboards, and business recommendations.",recommended:true},
  {id:"j3",company:"CloudNova",initials:"C",color:"#e1efff",ink:"#3579c5",role:"Cloud Support Associate",type:"Full-time",domain:"Cloud",location:"On-site",pay:"₹5.5–7 LPA",deadline:"2026-11-05",skills:["Linux","Cloud","Networking"],description:"Help customers troubleshoot cloud services and improve reliability.",recommended:true},
  {id:"j4",company:"SecureLayer",initials:"S",color:"#ffe8e9",ink:"#bd5863",role:"Cybersecurity Trainee",type:"Internship",domain:"Cybersecurity",location:"Hybrid",pay:"₹14,000 / month",deadline:"2026-11-07",skills:["Security","Networking","Linux"],description:"Assist with security monitoring, vulnerability triage, and log analysis.",recommended:true},
  {id:"j5",company:"PixelMint",initials:"P",color:"#fff0dc",ink:"#b67a2e",role:"UI/UX Design Intern",type:"Internship",domain:"Design",location:"Remote",pay:"₹10,000 / month",deadline:"2026-11-10",skills:["Figma","Wireframes","Prototyping"],description:"Design accessible user journeys and prototypes for web products.",recommended:false},
  {id:"j6",company:"BrightByte",initials:"B",color:"#e8edff",ink:"#5368c6",role:"Junior Software Engineer",type:"Full-time",domain:"Software",location:"Hybrid",pay:"₹6–8 LPA",deadline:"2026-11-12",skills:["Java","DSA","SQL"],description:"Contribute to production features, code reviews, and automated tests.",recommended:false},
  {id:"j7",company:"InsightLoop",initials:"I",color:"#e2f5f7",ink:"#25808b",role:"Business Intelligence Intern",type:"Internship",domain:"Data",location:"On-site",pay:"₹13,000 / month",deadline:"2026-11-14",skills:["Power BI","SQL","Python"],description:"Prepare reports and communicate patterns that support decisions.",recommended:false},
  {id:"j8",company:"OrbitStack",initials:"O",color:"#f1e6ff",ink:"#8c5bc0",role:"DevOps Intern",type:"Internship",domain:"Cloud",location:"Remote",pay:"₹16,000 / month",deadline:"2026-11-16",skills:["Git","Docker","CI/CD"],description:"Learn deployment workflows, container tooling, and cloud operations.",recommended:false}
];

const seedApplications = [
  {id:"a1001",jobId:"j1",name:"Priti Kumari Kanu",email:"priti.student@example.com",link:"",reason:"I enjoy building responsive web interfaces and have practiced JavaScript, CSS, and React.",date:"2026-10-14",status:"In Review"},
  {id:"a1002",jobId:"j4",name:"Priti Kumari Kanu",email:"priti.student@example.com",link:"",reason:"I am interested in security monitoring and have studied networking fundamentals and log analysis.",date:"2026-10-18",status:"Interview"},
  {id:"a1003",jobId:"j2",name:"Priti Kumari Kanu",email:"priti.student@example.com",link:"",reason:"I can use SQL and Python for basic data cleaning and analysis and would like to learn from the team.",date:"2026-10-20",status:"Submitted"}
];

const $ = (selector, root=document) => root.querySelector(selector);
const $$ = (selector, root=document) => [...root.querySelectorAll(selector)];
const safeRead = (key, fallback) => {
  try { const raw = localStorage.getItem(key); return raw === null ? fallback : JSON.parse(raw); }
  catch { return fallback; }
};
const store = (key, value) => {
  try { localStorage.setItem(key, JSON.stringify(value)); return true; }
  catch { showToast("Storage unavailable", "Your browser could not save this change. Check storage settings.", "error"); return false; }
};
let applications = safeRead(STORAGE_KEYS.applications, null);
if (!Array.isArray(applications)) { applications = seedApplications; store(STORAGE_KEYS.applications, applications); }
let savedIds = safeRead(STORAGE_KEYS.saved, ["j3","j5"]);
if (!Array.isArray(savedIds)) savedIds = [];
let profile = safeRead(STORAGE_KEYS.profile, {
  name:"Priti Kumari Kanu", email:"priti.student@example.com",
  department:"Computer Science & Engineering", year:"3rd Year", graduation:"2028",
  skills:"JavaScript, React, Java, MySQL",
  objective:"Computer Science student interested in building useful, user-friendly software applications."
});
let activeApplicationStatus = "All";
let lastFocusedElement = null;

const icon = (name, size=16) => `<i data-lucide="${name}" width="${size}" height="${size}"></i>`;
function refreshIcons() {
  if (window.lucide && typeof window.lucide.createIcons === "function") window.lucide.createIcons();
}
function escapeHTML(value) {
  return String(value ?? "").replace(/[&<>"']/g, ch => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[ch]));
}
function formatDate(dateString) {
  const date = new Date(`${dateString}T12:00:00`);
  if (Number.isNaN(date.getTime())) return "—";
  return date.toLocaleDateString("en-IN", {day:"2-digit", month:"short", year:"numeric"});
}
function daysUntil(dateString) {
  const now = new Date(); now.setHours(0,0,0,0);
  const date = new Date(`${dateString}T00:00:00`);
  return Math.ceil((date-now)/86400000);
}
function getJob(id) { return jobs.find(job => job.id === id); }
function getApplicationForJob(jobId) { return applications.find(app => app.jobId === jobId); }
function initialsForName(name) {
  return String(name || "Student").trim().split(/\s+/).slice(0,2).map(part=>part[0]?.toUpperCase() || "").join("");
}
function statusClass(status) {
  return ({"In Review":"status-review","Interview":"status-interview","Offer":"status-offer","Rejected":"status-rejected","Submitted":"status-submitted"})[status] || "status-submitted";
}
function statusBadge(status) { return `<span class="status-pill ${statusClass(status)}">${escapeHTML(status)}</span>`; }
function companyLogo(job) {
  return `<span class="company-logo" style="background:${job.color};color:${job.ink}">${escapeHTML(job.initials)}</span>`;
}
function showToast(title, message, type="success") {
  const region = $("#toastRegion");
  const toast = document.createElement("div");
  toast.className = `toast ${type === "error" ? "error" : ""}`;
  toast.innerHTML = `${icon(type === "error" ? "alert-circle" : "check-circle-2",18)}<div><strong>${escapeHTML(title)}</strong><p>${escapeHTML(message)}</p></div>`;
  region.appendChild(toast); refreshIcons();
  window.setTimeout(() => toast.remove(), 4000);
}
function switchView(viewName) {
  const target = $(`#view-${viewName}`);
  if (!target) return;
  $$(".view").forEach(view => view.classList.toggle("active", view === target));
  $$(".nav-link").forEach(button => button.classList.toggle("active", button.dataset.view === viewName));
  const labels = {overview:"Overview",opportunities:"Opportunities",applications:"My applications",saved:"Saved jobs",profile:"My profile"};
  $("#breadcrumbCurrent").textContent = labels[viewName] || "Overview";
  $("#sidebar").classList.remove("open");
  if (viewName === "applications") renderApplications();
  if (viewName === "saved") renderSavedJobs();
  if (viewName === "opportunities") renderJobs();
  window.scrollTo({top:0,behavior:"smooth"});
}
function jobCard(job) {
  const saved = savedIds.includes(job.id);
  const applied = Boolean(getApplicationForJob(job.id));
  return `<article class="job-card">
    <div class="job-card-top"><div class="job-title-wrap">${companyLogo(job)}<div><h3>${escapeHTML(job.company)}</h3><p>${escapeHTML(job.domain)} · ${escapeHTML(job.location)}</p></div></div>
    <button class="save-button ${saved?"saved":""}" data-save-job="${job.id}" aria-label="${saved?"Remove saved opportunity":"Save opportunity"}" aria-pressed="${saved}">${icon(saved?"bookmark-check":"bookmark",15)}</button></div>
    <p class="job-summary"><strong>${escapeHTML(job.role)}</strong><br>${escapeHTML(job.description)}</p>
    <div class="tag-row"><span class="job-tag">${escapeHTML(job.type)}</span>${job.skills.slice(0,3).map(skill=>`<span class="job-tag">${escapeHTML(skill)}</span>`).join("")}</div>
    <div class="job-card-footer"><span class="job-pay">${escapeHTML(job.pay)}<small>Deadline ${formatDate(job.deadline)}</small></span>
    <button class="job-apply ${applied?"job-applied":""}" data-apply-job="${job.id}" ${applied?"disabled":""}>${applied?"Applied": "View & apply"}</button></div>
  </article>`;
}
function renderJobs() {
  const search = ($("#jobSearch")?.value || "").trim().toLowerCase();
  const type = $("#typeFilter")?.value || "all";
  const location = $("#locationFilter")?.value || "all";
  const domain = $("#domainFilter")?.value || "all";
  const filtered = jobs.filter(job => {
    const searchable = [job.company,job.role,job.domain,job.location,job.type,...job.skills].join(" ").toLowerCase();
    return (!search || searchable.includes(search)) && (type==="all"||job.type===type) && (location==="all"||job.location===location) && (domain==="all"||job.domain===domain);
  });
  $("#allJobs").innerHTML = filtered.map(jobCard).join("");
  $("#resultsCount").textContent = `${filtered.length} ${filtered.length===1?"opportunity":"opportunities"}`;
  $("#jobsEmpty").classList.toggle("hidden", filtered.length !== 0);
  $("#allJobs").classList.toggle("hidden", filtered.length === 0);
  $("#opportunityNavCount").textContent = jobs.length;
  refreshIcons();
}
function renderRecommended() {
  const list = jobs.filter(job=>job.recommended).slice(0,3);
  $("#recommendedJobs").innerHTML = list.map(jobCard).join("");
  refreshIcons();
}
function renderDeadlineList() {
  const upcoming = [...jobs].sort((a,b)=>a.deadline.localeCompare(b.deadline)).slice(0,4);
  $("#deadlineList").innerHTML = upcoming.map(job=>{
    const date = new Date(`${job.deadline}T12:00:00`);
    const soon = daysUntil(job.deadline) <= 5;
    return `<div class="deadline-item"><div class="date-tile"><strong>${date.toLocaleDateString("en-IN",{day:"2-digit"})}</strong><small>${date.toLocaleDateString("en-IN",{month:"short"}).toUpperCase()}</small></div><div class="deadline-info"><strong>${escapeHTML(job.company)}</strong><p>${escapeHTML(job.role)}</p></div><span class="deadline-tag ${soon?"soon":""}">${daysUntil(job.deadline)<0?"Closed":daysUntil(job.deadline)===0?"Today":`${daysUntil(job.deadline)}d left`}</span></div>`;
  }).join("");
}
function renderRecentApplications() {
  const list = [...applications].sort((a,b)=>b.date.localeCompare(a.date)).slice(0,4);
  $("#recentApplications").innerHTML = list.map(app=>{
    const job = getJob(app.jobId); if(!job) return "";
    return `<tr><td><div class="company-cell">${companyLogo(job)}<div class="company-info"><strong>${escapeHTML(job.company)}</strong><small>${escapeHTML(job.role)}</small></div></div></td><td>${formatDate(app.date)}</td><td>${statusBadge(app.status)}</td><td><button class="row-action" data-view="applications" aria-label="View applications">${icon("arrow-up-right",15)}</button></td></tr>`;
  }).join("");
  $("#recentEmpty").classList.toggle("hidden", applications.length !== 0);
}
function renderStats() {
  $("#statApplications").textContent = applications.length;
  $("#statActive").textContent = applications.filter(app=>["In Review","Interview","Submitted"].includes(app.status)).length;
  $("#statSaved").textContent = savedIds.length;
  $("#sidebarName").textContent = profile.name.split(/\s+/).slice(0,2).join(" ");
  $("#welcomeName").textContent = profile.name.trim().split(/\s+/)[0] || "there";
  $$(".avatar").forEach(el=>{el.textContent=initialsForName(profile.name);});
}
function renderApplications() {
  const filtered = [...applications].filter(app=>activeApplicationStatus==="All" || app.status===activeApplicationStatus).sort((a,b)=>b.date.localeCompare(a.date));
  $("#allApplications").innerHTML = filtered.map(app=>{
    const job = getJob(app.jobId); if(!job) return "";
    return `<tr><td><div class="company-cell">${companyLogo(job)}<div class="company-info"><strong>${escapeHTML(job.company)}</strong><small>${escapeHTML(job.role)}</small></div></div></td><td>${escapeHTML(job.type)}</td><td>${formatDate(app.date)}</td><td>${statusBadge(app.status)}</td><td><button class="application-action" data-application-detail="${app.id}">Details</button></td></tr>`;
  }).join("");
  $("#applicationsEmpty").classList.toggle("hidden", filtered.length !== 0);
  $(".applications-full .table-wrap").classList.toggle("hidden", filtered.length === 0);
  $("#tabAllCount").textContent = applications.length;
  $("#tabReviewCount").textContent = applications.filter(a=>a.status==="In Review").length;
  $("#tabInterviewCount").textContent = applications.filter(a=>a.status==="Interview").length;
  $("#tabOfferCount").textContent = applications.filter(a=>a.status==="Offer").length;
}
function renderSavedJobs() {
  const list = jobs.filter(job=>savedIds.includes(job.id));
  $("#savedJobs").innerHTML = list.map(jobCard).join("");
  $("#savedJobs").classList.toggle("hidden",list.length===0);
  $("#savedEmpty").classList.toggle("hidden",list.length!==0);
  $("#statSaved").textContent = savedIds.length;
  refreshIcons();
}
function renderAll() {
  renderStats(); renderRecommended(); renderDeadlineList(); renderRecentApplications(); renderApplications();
  if ($("#view-opportunities").classList.contains("active")) renderJobs();
  if ($("#view-saved").classList.contains("active")) renderSavedJobs();
  refreshIcons();
}
function toggleSaved(jobId) {
  if (!getJob(jobId)) return;
  const isSaved = savedIds.includes(jobId);
  savedIds = isSaved ? savedIds.filter(id=>id!==jobId) : [...savedIds,jobId];
  if (!store(STORAGE_KEYS.saved,savedIds)) return;
  renderAll();
  showToast(isSaved?"Removed from saved":"Opportunity saved",isSaved?"This role was removed from your shortlist.":"You can find this role in Saved opportunities.");
}
function openApplyModal(jobId) {
  const job = getJob(jobId);
  if (!job) return;
  const existing = getApplicationForJob(jobId);
  if (existing) { showToast("Already applied",`Your current status is “${existing.status}”.`); return; }
  lastFocusedElement = document.activeElement;
  $("#applyJobId").value = job.id;
  $("#applyModalTitle").textContent = `Apply: ${job.role}`;
  $("#applyRoleDescription").textContent = `${job.company} · ${job.type} · ${job.location}. Complete the form to record your application.`;
  $("#applyName").value = profile.name || "";
  $("#applyEmail").value = profile.email || "";
  $("#applyLink").value = "";
  $("#applyReason").value = "";
  $("#applyConsent").checked = false;
  clearFormErrors($("#applyForm"));
  $("#applyModal").classList.remove("hidden");
  document.body.style.overflow="hidden";
  window.setTimeout(()=>$("#applyName").focus(),50);
}
function closeApplyModal() {
  $("#applyModal").classList.add("hidden");
  document.body.style.overflow="";
  if (lastFocusedElement && typeof lastFocusedElement.focus==="function") lastFocusedElement.focus();
}
function setFieldError(form, fieldId, message) {
  const field = $(`#${fieldId}`,form);
  const error = $(`[data-error-for="${fieldId}"]`,form);
  if (field) field.classList.toggle("invalid",Boolean(message));
  if (error) error.textContent = message || "";
}
function clearFormErrors(form) {
  $$(".field-error",form).forEach(el=>el.textContent="");
  $$("input,select,textarea",form).forEach(el=>el.classList.remove("invalid"));
}
function validEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);
}
function validHttpUrl(value) {
  if (!value.trim()) return true;
  try { const url = new URL(value); return url.protocol==="https:" || url.protocol==="http:"; }
  catch { return false; }
}
function validateApplication() {
  const form=$("#applyForm"); clearFormErrors(form);
  let valid=true;
  const name=$("#applyName").value.trim(), email=$("#applyEmail").value.trim();
  const link=$("#applyLink").value.trim(), reason=$("#applyReason").value.trim();
  if(name.length<2 || name.length>70 || !/^[\p{L}][\p{L}\p{M} .'-]*$/u.test(name)){setFieldError(form,"applyName","Enter a valid name (2–70 characters).");valid=false;}
  if(!validEmail(email) || email.length>120){setFieldError(form,"applyEmail","Enter a valid email address.");valid=false;}
  if(!validHttpUrl(link) || link.length>250){setFieldError(form,"applyLink","Use a valid http:// or https:// URL.");valid=false;}
  if(reason.length<20 || reason.length>500){setFieldError(form,"applyReason","Please enter 20–500 characters.");valid=false;}
  if(!$("#applyConsent").checked){setFieldError(form,"applyConsent","Please confirm the details before submitting.");valid=false;}
  if(!valid){const first=$(".invalid",form);if(first)first.focus();}
  return valid;
}
function submitApplication(event) {
  event.preventDefault();
  if(!validateApplication()) return;
  const jobId=$("#applyJobId").value;
  if(getApplicationForJob(jobId)){closeApplyModal();showToast("Already applied","An application for this role already exists.","error");return;}
  const app = {
    id:`a${Date.now()}`,jobId,
    name:$("#applyName").value.trim(),email:$("#applyEmail").value.trim(),
    link:$("#applyLink").value.trim(),reason:$("#applyReason").value.trim(),
    date:new Date().toISOString().slice(0,10),status:"Submitted"
  };
  applications=[app,...applications];
  if(!store(STORAGE_KEYS.applications,applications)){applications=applications.filter(item=>item.id!==app.id);return;}
  closeApplyModal();renderAll();
  showToast("Application submitted",`Your application for ${getJob(jobId)?.role || "this role"} has been saved in this browser.`);
}
function showApplicationDetails(appId) {
  const app=applications.find(item=>item.id===appId);
  const job=app && getJob(app.jobId);
  if(!app||!job)return;
  window.alert(`${job.company} — ${job.role}\n\nStatus: ${app.status}\nApplied: ${formatDate(app.date)}\nEmail: ${app.email}\n\nYour response:\n${app.reason}`);
}
function profileToForm() {
  $("#profileName").value=profile.name||"";
  $("#profileEmail").value=profile.email||"";
  $("#profileDepartment").value=profile.department||"Computer Science & Engineering";
  $("#profileYear").value=profile.year||"3rd Year";
  $("#profileGraduation").value=profile.graduation||"2028";
  $("#profileSkills").value=profile.skills||"";
  $("#profileObjective").value=profile.objective||"";
}
function validateProfile() {
  const form=$("#profileForm");clearFormErrors(form);
  let valid=true;
  const name=$("#profileName").value.trim(),email=$("#profileEmail").value.trim();
  if(name.length<2||name.length>70||!/^[\p{L}][\p{L}\p{M} .'-]*$/u.test(name)){setFieldError(form,"profileName","Enter a valid name (2–70 characters).");valid=false;}
  if(!validEmail(email)||email.length>120){setFieldError(form,"profileEmail","Enter a valid email address.");valid=false;}
  ["profileDepartment","profileYear","profileGraduation"].forEach(id=>{if(!$(`#${id}`).value){setFieldError(form,id,"Please select an option.");valid=false;}});
  if(!valid){const first=$(".invalid",form);if(first)first.focus();}
  return valid;
}
function submitProfile(event) {
  event.preventDefault();
  if(!validateProfile())return;
  profile={
    name:$("#profileName").value.trim(),email:$("#profileEmail").value.trim(),
    department:$("#profileDepartment").value,year:$("#profileYear").value,
    graduation:$("#profileGraduation").value,skills:$("#profileSkills").value.trim(),
    objective:$("#profileObjective").value.trim()
  };
  if(!store(STORAGE_KEYS.profile,profile))return;
  $("#profileDisplayName").textContent=profile.name;
  const identity=$("#profileDisplayName").nextElementSibling;
  identity.textContent=`${profile.department} · ${profile.year}`;
  renderStats();
  showToast("Profile updated","Your student profile was saved in this browser.");
}
function resetFilters() {
  $("#jobSearch").value="";$("#typeFilter").value="all";$("#locationFilter").value="all";$("#domainFilter").value="all";renderJobs();
}
function bindEvents() {
  document.addEventListener("click",event=>{
    const viewButton=event.target.closest("[data-view]");
    if(viewButton){switchView(viewButton.dataset.view);return;}
    const saveButton=event.target.closest("[data-save-job]");
    if(saveButton){toggleSaved(saveButton.dataset.saveJob);return;}
    const applyButton=event.target.closest("[data-apply-job]");
    if(applyButton){openApplyModal(applyButton.dataset.applyJob);return;}
    const detailsButton=event.target.closest("[data-application-detail]");
    if(detailsButton){showApplicationDetails(detailsButton.dataset.applicationDetail);return;}
  });
  $("#menuToggle").addEventListener("click",()=>$("#sidebar").classList.toggle("open"));
  $("#sidebarProfile").addEventListener("click",()=>switchView("profile"));
  $("#jobSearch").addEventListener("input",renderJobs);
  ["typeFilter","locationFilter","domainFilter"].forEach(id=>$("#"+id).addEventListener("change",renderJobs));
  $("#resetFilters").addEventListener("click",resetFilters);
  $("#applicationTabs").addEventListener("click",event=>{
    const tab=event.target.closest("[data-status]");if(!tab)return;
    activeApplicationStatus=tab.dataset.status;
    $$(".filter-tab",$("#applicationTabs")).forEach(button=>button.classList.toggle("active",button===tab));
    renderApplications();
  });
  $("#applyForm").addEventListener("submit",submitApplication);
  $("#closeApplyModal").addEventListener("click",closeApplyModal);
  $("#cancelApply").addEventListener("click",closeApplyModal);
  $("#applyModal").addEventListener("click",event=>{if(event.target===$("#applyModal"))closeApplyModal();});
  document.addEventListener("keydown",event=>{if(event.key==="Escape"&&!$("#applyModal").classList.contains("hidden"))closeApplyModal();});
  $("#profileForm").addEventListener("submit",submitProfile);
  $("#profileReset").addEventListener("click",()=>window.setTimeout(()=>{profileToForm();clearFormErrors($("#profileForm"));},0));
  $("#notificationsBtn").addEventListener("click",()=>showToast("You're all caught up","New application updates will appear in this area in a future version."));
}
function init() {
  profileToForm();
  $("#profileDisplayName").textContent=profile.name;
  $("#profileDisplayName").nextElementSibling.textContent=`${profile.department} · ${profile.year}`;
  bindEvents();
  renderAll();
  refreshIcons();
}
document.addEventListener("DOMContentLoaded",init);
