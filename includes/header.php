<?php
/**
 * Public Site Header — Navigation and meta tags
 *
 * @var string $pageTitle   Current page title
 * @var string $pageDesc    Meta description for the page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$profile  = getDJProfile();
$djName   = $profile['name'] ?? SITE_NAME;
$pageTitle = isset($pageTitle) ? e($pageTitle) . ' | ' . e($djName) : e($djName) . ' | Official Website';
$pageDesc  = isset($pageDesc)  ? e($pageDesc)  : 'Book ' . e($djName) . ' for your next event. Stream music, watch videos, and submit booking requests online.';
$canonicalUrl = SITE_URL . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= $pageDesc ?>">
    <meta name="keywords" content="DJ, booking, music, afrobeats, gospel, hip-hop, Ghana, events">
    <meta name="author" content="<?= e($djName) ?>">

    <!-- Open Graph / Social Sharing -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= e($canonicalUrl) ?>">
    <meta property="og:title"       content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= $pageDesc ?>">
    <meta property="og:image"       content="<?= SITE_URL ?>/assets/images/og-image.jpg">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= $pageTitle ?>">
    <meta name="twitter:description" content="<?= $pageDesc ?>">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eecyn6CfDSbb57YbVQRPM/WEg==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/player.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/favicon.png">
</head>
<body>

<!-- Navigation -->
<header class="site-header" id="site-header">
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="container">
            <!-- Logo / Brand -->
            <a href="<?= SITE_URL ?>/" class="navbar-brand" aria-label="<?= e($djName) ?> Home">
                <span class="brand-icon"><i class="fas fa-headphones-alt" aria-hidden="true"></i></span>
                <span class="brand-name"><?= e($djName) ?></span>
            </a>

            <!-- Hamburger Toggle (mobile) -->
            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="nav-menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>

            <!-- Navigation Links -->
            <ul class="nav-menu" id="nav-menu" role="menubar">
                <li role="none"><a href="<?= SITE_URL ?>/"              class="nav-link" role="menuitem">Home</a></li>
                <li role="none"><a href="<?= SITE_URL ?>/music.php"     class="nav-link" role="menuitem"><i class="fas fa-music" aria-hidden="true"></i> Music</a></li>
                <li role="none"><a href="<?= SITE_URL ?>/videos.php"    class="nav-link" role="menuitem"><i class="fas fa-video" aria-hidden="true"></i> Videos</a></li>
                <li role="none"><a href="<?= SITE_URL ?>/about.php"     class="nav-link" role="menuitem">About</a></li>
                <li role="none">
                    <a href="<?= SITE_URL ?>/booking.php" class="nav-link nav-cta" role="menuitem">
                        <i class="fas fa-calendar-check" aria-hidden="true"></i> Book Now
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<!-- Main Content -->
<main id="main-content">
