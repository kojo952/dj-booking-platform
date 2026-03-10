<?php
/**
 * Authentication Helper Functions
 * Session-based authentication for admin users
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Start a secure session
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false, // Set true in production with HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

/**
 * Check if the current user is logged in as admin
 *
 * @return bool
 */
function isLoggedIn(): bool
{
    startSecureSession();
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require login — redirect to login page if not authenticated
 *
 * @return void
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Attempt to log in an admin user
 *
 * @param string $username
 * @param string $password
 * @return bool
 */
function attemptLogin(string $username, string $password): bool
{
    startSecureSession();

    // Check brute force protection
    if (isLockedOut()) {
        return false;
    }

    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT id, username, email, password_hash FROM admin_users WHERE username = ? OR email = ? LIMIT 1'
    );
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Successful login — regenerate session ID to prevent fixation
        session_regenerate_id(true);

        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_email']    = $user['email'];
        $_SESSION['login_time']     = time();

        // Clear failed attempts
        unset($_SESSION['login_attempts'], $_SESSION['lockout_time']);

        return true;
    }

    // Track failed attempts
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['lockout_time'] = time();
    }

    return false;
}

/**
 * Check if the IP/session is locked out due to too many failed attempts
 *
 * @return bool
 */
function isLockedOut(): bool
{
    if (!isset($_SESSION['lockout_time'])) {
        return false;
    }

    // Check if lockout period has passed
    if ((time() - $_SESSION['lockout_time']) > LOGIN_LOCKOUT_TIME) {
        unset($_SESSION['login_attempts'], $_SESSION['lockout_time']);
        return false;
    }

    return true;
}

/**
 * Get remaining lockout seconds
 *
 * @return int
 */
function getLockoutRemaining(): int
{
    if (!isset($_SESSION['lockout_time'])) {
        return 0;
    }
    $remaining = LOGIN_LOCKOUT_TIME - (time() - $_SESSION['lockout_time']);
    return max(0, $remaining);
}

/**
 * Log out the current admin user
 *
 * @return void
 */
function logout(): void
{
    startSecureSession();
    $_SESSION = [];
    session_destroy();
}

/**
 * Get current admin username
 *
 * @return string
 */
function getAdminUsername(): string
{
    startSecureSession();
    return $_SESSION['admin_username'] ?? 'Admin';
}

/**
 * Get current admin ID
 *
 * @return int
 */
function getAdminId(): int
{
    startSecureSession();
    return (int)($_SESSION['admin_id'] ?? 0);
}
