<?php
/**
 * API Admin: Delete a YouTube Video
 * POST: video_id, csrf_token
 * Auth-protected
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

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

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

$videoId = (int)($_POST['video_id'] ?? 0);
if (!$videoId) {
    echo json_encode(['success' => false, 'message' => 'Invalid video ID.']);
    exit;
}

try {
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare('DELETE FROM videos WHERE id = ?');
    $stmt->execute([$videoId]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Video not found.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Video deleted.', 'video_id' => $videoId]);
    }
} catch (Exception $e) {
    error_log('Delete video error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
