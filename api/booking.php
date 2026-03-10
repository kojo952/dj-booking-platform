<?php
/**
 * API: Submit Booking Request
 * POST handler for the public booking form
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', ['error' => 'method_not_allowed']);
}

// Start session for CSRF and rate limiting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Validation
$submittedToken = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($submittedToken)) {
    jsonResponse(false, 'Invalid or expired request. Please refresh the page and try again.');
}

// Rate limiting: max 3 submissions per hour per session
$now         = time();
$bookingLog  = $_SESSION['booking_log'] ?? [];
// Filter entries from the last hour
$bookingLog = array_filter($bookingLog, fn($t) => ($now - $t) < 3600);
$_SESSION['booking_log'] = array_values($bookingLog);

if (count($_SESSION['booking_log']) >= MAX_BOOKINGS_PER_HOUR) {
    jsonResponse(false, 'You have submitted too many booking requests. Please wait before trying again.');
}

// Collect and sanitize fields
$fullName      = trim($_POST['full_name']      ?? '');
$email         = trim($_POST['email']          ?? '');
$phone         = trim($_POST['phone']          ?? '');
$eventType     = trim($_POST['event_type']     ?? '');
$eventDate     = trim($_POST['event_date']     ?? '');
$eventTime     = trim($_POST['event_time']     ?? '');
$eventLocation = trim($_POST['event_location'] ?? '');
$notes         = trim($_POST['notes']          ?? '');

// Server-side validation
$validationErrors = [];

if (empty($fullName)) {
    $validationErrors['full_name'] = 'Full name is required.';
} elseif (strlen($fullName) > 150) {
    $validationErrors['full_name'] = 'Full name is too long (max 150 characters).';
}

if (empty($email)) {
    $validationErrors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $validationErrors['email'] = 'Please enter a valid email address.';
}

if (empty($phone)) {
    $validationErrors['phone'] = 'Phone number is required.';
} elseif (!preg_match('/^[+\d\s\-().]{7,30}$/', $phone)) {
    $validationErrors['phone'] = 'Please enter a valid phone number.';
}

$allowedEventTypes = ['Wedding', 'Birthday Party', 'Corporate Event', 'Club Night', 'Festival', 'Other'];
if (empty($eventType) || !in_array($eventType, $allowedEventTypes, true)) {
    $validationErrors['event_type'] = 'Please select a valid event type.';
}

if (empty($eventDate)) {
    $validationErrors['event_date'] = 'Event date is required.';
} else {
    $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
    $today   = new DateTime('today');
    if (!$dateObj || $dateObj <= $today) {
        $validationErrors['event_date'] = 'Event date must be a future date.';
    } elseif (isDateBlocked($eventDate)) {
        $validationErrors['event_date'] = 'The selected date is not available. Please choose another date.';
    }
}

if (empty($eventTime)) {
    $validationErrors['event_time'] = 'Event time is required.';
}

if (!empty($validationErrors)) {
    jsonResponse(false, 'Please fix the errors below.', ['fields' => $validationErrors]);
}

// Insert into database
try {
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO bookings (full_name, email, phone, event_type, event_date, event_time, event_location, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $fullName,
        $email,
        $phone,
        $eventType,
        $eventDate,
        $eventTime,
        $eventLocation,
        $notes,
    ]);

    $bookingId = $pdo->lastInsertId();

    // Record this submission in session for rate limiting
    $_SESSION['booking_log'][] = $now;

    // Send confirmation email (basic PHP mail — upgrade to PHPMailer in production)
    $subject = 'Booking Request Received — ' . SITE_NAME;
    $body    = "Hi {$fullName},\n\nThank you for your booking request!\n\n"
             . "Event Type: {$eventType}\n"
             . "Event Date: {$eventDate}\n"
             . "Event Time: {$eventTime}\n"
             . "Location: " . ($eventLocation ?: 'Not specified') . "\n\n"
             . "We'll review your request and get back to you within 24 hours.\n\n"
             . "Regards,\n" . SITE_NAME;

    $headers  = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    @mail($email, $subject, $body, $headers);

    jsonResponse(true, 'Your booking request has been submitted successfully! We will contact you within 24 hours.', [
        'booking_id' => (int)$bookingId,
    ]);

} catch (Exception $e) {
    error_log('Booking submission error: ' . $e->getMessage());
    jsonResponse(false, 'An error occurred while processing your request. Please try again later.');
}
