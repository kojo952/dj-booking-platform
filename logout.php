<?php
/**
 * Logout Handler
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/config.php';

logout();
header('Location: ' . SITE_URL . '/login.php');
exit;
