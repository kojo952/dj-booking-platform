<?php
/**
 * Admin Panel Header — Sidebar navigation and top bar
 *
 * @var string $adminPageTitle  Current page title
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$adminUsername = getAdminUsername();
$stats         = getDashboardStats();
$currentPage   = basename($_SERVER['PHP_SELF']);
$adminPageTitle = isset($adminPageTitle) ? e($adminPageTitle) . ' | Admin' : 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $adminPageTitle ?> | <?= e(SITE_NAME) ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eecyn6CfDSbb57YbVQRPM/WEg==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- ========== SIDEBAR ========== -->
<aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin navigation">
    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="<?= SITE_URL ?>/admin/index.php" class="sidebar-logo" aria-label="Dashboard">
            <i class="fas fa-headphones-alt" aria-hidden="true"></i>
            <span><?= e(SITE_NAME) ?></span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav" aria-label="Admin sidebar navigation">
        <ul class="sidebar-menu">
            <li class="sidebar-section-label">Main</li>

            <li class="<?= $currentPage === 'index.php' && strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : '' ?>">
                <a href="<?= SITE_URL ?>/admin/index.php">
                    <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-section-label">Bookings</li>

            <li class="<?= $currentPage === 'bookings.php' ? 'active' : '' ?>">
                <a href="<?= SITE_URL ?>/admin/bookings.php">
                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                    <span>Bookings</span>
                    <?php if ($stats['pending_bookings'] > 0): ?>
                    <span class="sidebar-badge"><?= $stats['pending_bookings'] ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="sidebar-section-label">Media</li>

            <li class="<?= $currentPage === 'music.php' || $currentPage === 'upload-music.php' ? 'active' : '' ?>">
                <a href="<?= SITE_URL ?>/admin/music.php">
                    <i class="fas fa-music" aria-hidden="true"></i>
                    <span>Songs</span>
                </a>
            </li>

            <li class="<?= $currentPage === 'categories.php' ? 'active' : '' ?>">
                <a href="<?= SITE_URL ?>/admin/categories.php">
                    <i class="fas fa-tags" aria-hidden="true"></i>
                    <span>Categories</span>
                </a>
            </li>

            <li class="<?= $currentPage === 'videos.php' ? 'active' : '' ?>">
                <a href="<?= SITE_URL ?>/admin/videos.php">
                    <i class="fab fa-youtube" aria-hidden="true"></i>
                    <span>Videos</span>
                </a>
            </li>

            <li class="sidebar-section-label">Settings</li>

            <li class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                <a href="<?= SITE_URL ?>/admin/profile.php">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    <span>DJ Profile</span>
                </a>
            </li>

            <li class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>">
                <a href="<?= SITE_URL ?>/admin/messages.php">
                    <i class="fas fa-envelope" aria-hidden="true"></i>
                    <span>Messages</span>
                    <?php if ($stats['unread_messages'] > 0): ?>
                    <span class="sidebar-badge"><?= $stats['unread_messages'] ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li>
                <a href="<?= SITE_URL ?>/" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                    <span>View Site</span>
                </a>
            </li>

            <li>
                <a href="<?= SITE_URL ?>/logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<!-- ========== MAIN CONTENT AREA ========== -->
<div class="admin-content-wrap">
    <!-- Top Bar -->
    <header class="admin-topbar" role="banner">
        <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="false">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>

        <div class="topbar-title">
            <h1 class="topbar-heading"><?= isset($adminPageTitle) ? e(str_replace(' | Admin', '', $adminPageTitle)) : 'Admin Panel' ?></h1>
        </div>

        <div class="topbar-user">
            <a href="<?= SITE_URL ?>/" target="_blank" rel="noopener noreferrer" class="topbar-btn" title="View Site" aria-label="View public website">
                <i class="fas fa-external-link-alt" aria-hidden="true"></i>
            </a>
            <div class="user-avatar" aria-hidden="true">
                <i class="fas fa-user"></i>
            </div>
            <span class="user-name"><?= e($adminUsername) ?></span>
            <a href="<?= SITE_URL ?>/logout.php" class="topbar-btn logout-btn" title="Logout" aria-label="Logout">
                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            </a>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="flash-message flash-success" role="alert" aria-live="polite">
        <i class="fas fa-check-circle" aria-hidden="true"></i>
        <?= e($_SESSION['flash_success']) ?>
        <button class="flash-close" aria-label="Close notification">&times;</button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
    <div class="flash-message flash-error" role="alert" aria-live="assertive">
        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
        <?= e($_SESSION['flash_error']) ?>
        <button class="flash-close" aria-label="Close notification">&times;</button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Page Content Container -->
    <main class="admin-main" id="admin-main">
