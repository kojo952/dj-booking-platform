<?php
/**
 * Application Configuration
 * Central configuration file for site-wide constants and settings
 */

// Site Information
define('SITE_NAME', 'DJ KoJo');
define('SITE_TAGLINE', 'Your Premier DJ for Every Occasion');
define('SITE_URL', 'http://localhost/dj-booking-platform');
define('ADMIN_EMAIL', 'admin@djkojo.com');

// File Upload Settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MUSIC_UPLOAD_PATH', UPLOAD_PATH . 'music/');
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . 'profile/');
define('MAX_UPLOAD_SIZE', 52428800); // 50MB in bytes

// Allowed file types
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/wav', 'audio/mp3', 'audio/x-wav']);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_AUDIO_EXTENSIONS', ['mp3', 'wav']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 15);

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Booking rate limiting
define('MAX_BOOKINGS_PER_HOUR', 3);

// Application version
define('APP_VERSION', '1.0.0');

// Timezone
date_default_timezone_set('Africa/Accra');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
