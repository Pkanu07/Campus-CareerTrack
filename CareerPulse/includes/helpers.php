<?php
/**
 * CareerPulse - Helper Utilities & Security Functions
 */

declare(strict_types=1);

if (!defined('CAREERPULSE_APP')) {
    define('CAREERPULSE_APP', true);
}

/**
 * Safely check if current HTTP request is POST
 */
function isPost(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Safely ensure session is started with local session path
 */
function ensureSessionStarted(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $sessDir = __DIR__ . '/../sessions';
        if (!is_dir($sessDir)) {
            @mkdir($sessDir, 0777, true);
        }
        if (is_dir($sessDir) && is_writable($sessDir)) {
            @session_save_path($sessDir);
        }
        @session_start();
    }
}

/**
 * Generate a dynamic base URL for links and assets
 */
function baseUrl(string $path = ''): string {
    static $base = null;
    if ($base === null) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $dir = str_replace('\\', '/', dirname($script));
        
        // If document root is CareerPulse, we are at root
        $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
        if (str_ends_with(rtrim($docRoot, '/'), '/CareerPulse') || rtrim($docRoot, '/') === 'CareerPulse') {
            $basePath = '';
        } elseif ($dir === '.' || $dir === '/' || $dir === '') {
            $basePath = '';
        } else {
            // Find root CareerPulse folder if hosted in subfolder (e.g. /xampp/htdocs/CareerPulse)
            $pos = stripos($dir, 'CareerPulse');
            if ($pos !== false) {
                $before = substr($dir, 0, $pos);
                $basePath = rtrim($before, '/') . '/CareerPulse';
                if (!str_starts_with($basePath, '/')) {
                    $basePath = '/' . $basePath;
                }
            } else {
                $basePath = '/' . trim($dir, '/');
                $subfolders = ['/auth', '/candidate', '/employer', '/admin', '/database', '/includes'];
                foreach ($subfolders as $sub) {
                    if (str_ends_with($basePath, $sub)) {
                        $basePath = substr($basePath, 0, -strlen($sub));
                    }
                }
            }
        }

        $basePath = rtrim($basePath, '/');
        $base = "{$scheme}://{$host}" . $basePath;
    }

    $trimmedPath = ltrim($path, '/');
    return $trimmedPath ? "{$base}/{$trimmedPath}" : $base;
}

/**
 * Sanitize string for HTML output
 */
function e(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Format salary ranges nicely
 */
function formatSalary(?int $min, ?int $max, string $currency = 'USD'): string {
    $symbol = match ($currency) {
        'EUR' => '€',
        'GBP' => '£',
        'INR' => '₹',
        default => '$',
    };

    if (!$min && !$max) {
        return 'Competitive / Negotiable';
    }

    if ($min && !$max) {
        return sprintf('%s%s+/yr', $symbol, number_format($min));
    }

    if (!$min && $max) {
        return sprintf('Up to %s%s/yr', $symbol, number_format($max));
    }

    return sprintf('%s%sk - %s%sk/yr', $symbol, round($min / 1000), $symbol, round($max / 1000));
}

/**
 * Relative time ago format (e.g. "2 days ago")
 */
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    if (!$time) return 'Recently';

    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $time);
}

/**
 * Status badge styling
 */
function getStatusBadge(string $status): array {
    return match (strtolower($status)) {
        'pending' => [
            'label' => 'Pending Review',
            'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-400'
        ],
        'under_review' => [
            'label' => 'Under Review',
            'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
            'dot' => 'bg-blue-500'
        ],
        'shortlisted' => [
            'label' => 'Shortlisted',
            'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
            'dot' => 'bg-purple-500'
        ],
        'interview' => [
            'label' => 'Interview Scheduled',
            'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'dot' => 'bg-indigo-500'
        ],
        'accepted' => [
            'label' => 'Accepted',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-500'
        ],
        'rejected' => [
            'label' => 'Declined',
            'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
            'dot' => 'bg-rose-500'
        ],
        'active' => [
            'label' => 'Active',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-500'
        ],
        'paused' => [
            'label' => 'Paused',
            'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-400'
        ],
        'closed' => [
            'label' => 'Closed',
            'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-400'
        ],
        default => [
            'label' => ucfirst($status),
            'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-400'
        ],
    };
}

/**
 * Set flash alert message
 */
function setFlash(string $type, string $message): void {
    ensureSessionStarted();
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message,
    ];
}

/**
 * Retrieve and clear flash message
 */
function getFlash(): ?array {
    ensureSessionStarted();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate CSRF token
 */
function csrfToken(): string {
    ensureSessionStarted();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrf(?string $token): bool {
    ensureSessionStarted();
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Securely handle resume upload
 */
function uploadResume(array $file): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error code: ' . $file['error']];
    }

    // Limit to 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'error' => 'Resume file must be under 5MB.'];
    }

    $allowedExtensions = ['pdf', 'doc', 'docx'];
    $allowedMimeTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/octet-stream' // Some browsers send octet-stream for docx
    ];

    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExtensions, true)) {
        return ['success' => false, 'error' => 'Only PDF, DOC, and DOCX files are allowed.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($ext === 'pdf' && $mime !== 'application/pdf') {
        return ['success' => false, 'error' => 'Invalid PDF file.'];
    }

    $targetDir = __DIR__ . '/../uploads/resumes/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $newFilename = 'resume_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $targetPath = $targetDir . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file to disk.'];
    }

    return ['success' => true, 'filename' => $newFilename];
}
