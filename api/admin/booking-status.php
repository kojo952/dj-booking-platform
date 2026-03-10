<?php
/**
 * API Admin: Update Booking Status
 * POST: booking_id, status, csrf_token
 * Auth-protected
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Auth check
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// CSRF
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

$bookingId = (int)($_POST['booking_id'] ?? 0);
$newStatus = trim($_POST['status'] ?? '');

if (!$bookingId || !in_array($newStatus, ['pending', 'approved', 'rejected'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

try {
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
    $stmt->execute([$newStatus, $bookingId]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Booking not found.']);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Status updated to ' . ucfirst($newStatus) . '.',
            'status'  => $newStatus,
        ]);
    }
} catch (Exception $e) {
    error_log('Booking status update error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
