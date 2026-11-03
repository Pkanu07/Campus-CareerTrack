<?php
/**
 * CareerPulse - Job Application Submission Handler
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireAuth('candidate');

if (!isPost()) {
    header('Location: ' . baseUrl('jobs.php'));
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if (!validateCsrf($csrf)) {
    setFlash('error', 'Invalid security token. Please try again.');
    header('Location: ' . baseUrl('jobs.php'));
    exit;
}

$jobId = (int)($_POST['job_id'] ?? 0);
$job = dbFetchOne("SELECT id, title, status FROM jobs WHERE id = ?", [$jobId]);

if (!$job || $job['status'] !== 'active') {
    setFlash('error', 'This position is no longer accepting applications.');
    header('Location: ' . baseUrl('jobs.php'));
    exit;
}

// Check duplicate application
$existing = dbFetchOne("SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?", [$jobId, $user['id']]);
if ($existing) {
    setFlash('warning', 'You have already submitted an application for this position.');
    header('Location: ' . baseUrl('job_details.php?id=' . $jobId));
    exit;
}

// Upload resume
if (empty($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    setFlash('error', 'Please attach your resume file (PDF or DOCX).');
    header('Location: ' . baseUrl('job_details.php?id=' . $jobId));
    exit;
}

$upload = uploadResume($_FILES['resume']);
if (!$upload['success']) {
    setFlash('error', $upload['error']);
    header('Location: ' . baseUrl('job_details.php?id=' . $jobId));
    exit;
}

$resumeFilename = $upload['filename'];
$coverNote = trim($_POST['cover_note'] ?? '');

try {
    dbInsert("
        INSERT INTO applications (job_id, candidate_id, resume_path, cover_note, status)
        VALUES (?, ?, ?, ?, 'pending')
    ", [$jobId, $user['id'], $resumeFilename, $coverNote]);

    // Also update candidate profile default resume if not set
    dbExecute("
        UPDATE candidate_profiles
        SET resume_url = COALESCE(resume_url, ?)
        WHERE user_id = ?
    ", [$resumeFilename, $user['id']]);

    setFlash('success', 'Your application for "' . $job['title'] . '" was successfully submitted!');
    header('Location: ' . baseUrl('candidate/dashboard.php'));
    exit;
} catch (Throwable $e) {
    setFlash('error', 'Database error: ' . $e->getMessage());
    header('Location: ' . baseUrl('job_details.php?id=' . $jobId));
    exit;
}
