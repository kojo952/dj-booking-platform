<?php
/**
 * Admin Login Page
 * Secure login with brute-force protection
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

startSecureSession();

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/index.php');
}

$error     = '';
$csrfToken = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (isLockedOut()) {
        $remaining = getLockoutRemaining();
        $error = 'Too many failed attempts. Please wait ' . ceil($remaining / 60) . ' minute(s) before trying again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username/email and password.';
        } elseif (attemptLogin($username, $password)) {
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : SITE_URL . '/admin/index.php';
            // Only allow redirect to our own domain
            if (!str_starts_with($redirect, SITE_URL)) {
                $redirect = SITE_URL . '/admin/index.php';
            }
            redirect($redirect);
        } else {
            if (isLockedOut()) {
                $remaining = getLockoutRemaining();
                $error = 'Too many failed attempts. Please wait ' . ceil($remaining / 60) . ' minute(s).';
            } else {
                $attemptsLeft = MAX_LOGIN_ATTEMPTS - ($_SESSION['login_attempts'] ?? 0);
                $error = 'Invalid username or password.' . ($attemptsLeft < MAX_LOGIN_ATTEMPTS ? " ({$attemptsLeft} attempts remaining)" : '');
            }
        }
    }
    // Regenerate CSRF token after POST
    unset($_SESSION['csrf_token']);
    $csrfToken = generateCSRFToken();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= e(SITE_NAME) ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eecyn6CfDSbb57YbVQRPM/WEg==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">

    <style>
        /* Login-specific styles layered on admin.css */
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg-dark); }
        .login-wrap { width: 100%; max-width: 420px; padding: 1rem; }
        .login-card { background: var(--sidebar-bg); border-radius: 16px; padding: 2.5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        .login-logo { text-align: center; margin-bottom: 2rem; }
        .login-logo i { font-size: 3rem; color: var(--primary); }
        .login-logo h1 { font-size: 1.5rem; color: var(--text-light); margin-top: 0.5rem; }
        .login-logo p { color: var(--text-muted); font-size: 0.875rem; }
        .error-alert { background: rgba(233,69,96,0.15); border: 1px solid var(--accent); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; color: #f88; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
        .login-footer { text-align: center; margin-top: 1.5rem; }
        .login-footer a { color: var(--primary); text-decoration: none; font-size: 0.875rem; }
        .login-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <!-- Logo -->
        <div class="login-logo">
            <i class="fas fa-headphones-alt" aria-hidden="true"></i>
            <h1><?= e(SITE_NAME) ?></h1>
            <p>Admin Panel</p>
        </div>

        <!-- Error -->
        <?php if ($error): ?>
        <div class="error-alert" role="alert">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="" novalidate aria-label="Admin login form">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <div class="form-group">
                <label for="username" class="form-label">Username or Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon" aria-hidden="true"></i>
                    <input type="text" id="username" name="username"
                           class="form-input"
                           value="<?= e($_POST['username'] ?? '') ?>"
                           placeholder="admin"
                           required autocomplete="username"
                           autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                    <input type="password" id="password" name="password"
                           class="form-input"
                           placeholder="••••••••"
                           required autocomplete="current-password">
                    <button type="button" class="toggle-password" id="toggle-password" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="margin-top:1rem;">
                <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Sign In
            </button>
        </form>

        <div class="login-footer">
            <a href="<?= SITE_URL ?>/" aria-label="Return to public website">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Website
            </a>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('toggle-password').addEventListener('click', function () {
    const input = document.getElementById('password');
    const icon  = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
        this.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
        this.setAttribute('aria-label', 'Show password');
    }
});
</script>
</body>
</html>
