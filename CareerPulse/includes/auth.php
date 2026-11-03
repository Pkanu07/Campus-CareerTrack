<?php
/**
 * CareerPulse - Authentication & Access Control System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

function startAuthSession(): void {
    ensureSessionStarted();
}

/**
 * Check if a user is currently logged in
 */
function isLoggedIn(): bool {
    startAuthSession();
    return !empty($_SESSION['user_id']);
}

/**
 * Get the currently authenticated user with profile data
 */
function currentUser(): ?array {
    startAuthSession();
    if (!isLoggedIn()) {
        return null;
    }

    static $cachedUser = null;
    if ($cachedUser !== null) {
        return $cachedUser;
    }

    $userId = (int)$_SESSION['user_id'];
    $user = dbFetchOne("SELECT id, name, email, role, phone, avatar, created_at FROM users WHERE id = ?", [$userId]);

    if (!$user) {
        // Session invalid, destroy
        logoutUser();
        return null;
    }

    // Attach role-specific profile if present
    if ($user['role'] === 'employer') {
        $profile = dbFetchOne("SELECT * FROM employer_profiles WHERE user_id = ?", [$userId]);
        $user['employer_profile'] = $profile;
    } elseif ($user['role'] === 'candidate') {
        $profile = dbFetchOne("SELECT * FROM candidate_profiles WHERE user_id = ?", [$userId]);
        $user['candidate_profile'] = $profile;
    }

    $cachedUser = $user;
    return $cachedUser;
}

/**
 * Check if the logged in user matches given role(s)
 */
function hasRole(string|array $roles): bool {
    $user = currentUser();
    if (!$user) {
        return false;
    }

    $allowed = is_array($roles) ? $roles : [$roles];
    return in_array($user['role'], $allowed, true);
}

/**
 * Require authentication or redirect to login
 */
function requireAuth(string|array|null $roles = null): array {
    startAuthSession();

    if (!isLoggedIn()) {
        setFlash('warning', 'Please sign in to access this page.');
        header('Location: ' . baseUrl('auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '')));
        exit;
    }

    $user = currentUser();
    if (!$user) {
        header('Location: ' . baseUrl('auth/login.php'));
        exit;
    }

    if ($roles !== null && !hasRole($roles)) {
        setFlash('error', 'You do not have permission to view that page.');
        header('Location: ' . baseUrl('index.php'));
        exit;
    }

    return $user;
}

/**
 * Authenticate user credentials
 */
function loginUser(string $email, string $password): array {
    startAuthSession();

    $email = trim(strtolower($email));
    $user = dbFetchOne("SELECT * FROM users WHERE LOWER(email) = ?", [$email]);

    if (!$user) {
        return ['success' => false, 'error' => 'No account found with this email address.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'Incorrect password. Please try again.'];
    }

    // Set session
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    return ['success' => true, 'user' => $user];
}

/**
 * Register a new user
 */
function registerUser(string $name, string $email, string $password, string $role, ?string $extraOrg = null): array {
    startAuthSession();

    $name = trim($name);
    $email = trim(strtolower($email));
    $role = in_array($role, ['candidate', 'employer'], true) ? $role : 'candidate';

    if (strlen($name) < 2) {
        return ['success' => false, 'error' => 'Full name must be at least 2 characters.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Please provide a valid email address.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'error' => 'Password must be at least 6 characters long.'];
    }

    // Check if email already registered
    $existing = dbFetchOne("SELECT id FROM users WHERE LOWER(email) = ?", [$email]);
    if ($existing) {
        return ['success' => false, 'error' => 'This email is already registered. Please log in.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $userId = dbInsert(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)",
            [$name, $email, $hash, $role]
        );

        if ($role === 'employer') {
            $companyName = !empty($extraOrg) ? trim($extraOrg) : $name . "'s Company";
            dbInsert(
                "INSERT INTO employer_profiles (user_id, company_name) VALUES (?, ?)",
                [$userId, $companyName]
            );
        } else {
            dbInsert(
                "INSERT INTO candidate_profiles (user_id, headline) VALUES (?, ?)",
                [$userId, 'Job Seeker']
            );
        }

        // Auto login
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role;

        return ['success' => true, 'user_id' => $userId, 'role' => $role];
    } catch (Throwable $e) {
        return ['success' => false, 'error' => 'Registration error: ' . $e->getMessage()];
    }
}

/**
 * Log out user and destroy session
 */
function logoutUser(): void {
    startAuthSession();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
