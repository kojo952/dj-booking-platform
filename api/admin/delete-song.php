<?php
/**
 * API Admin: Delete a Song
 * POST: song_id, csrf_token
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

$songId = (int)($_POST['song_id'] ?? 0);
if (!$songId) {
    echo json_encode(['success' => false, 'message' => 'Invalid song ID.']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Fetch song file info before deleting
    $stmt = $pdo->prepare('SELECT filename, cover_image FROM songs WHERE id = ?');
    $stmt->execute([$songId]);
    $song = $stmt->fetch();

    if (!$song) {
        echo json_encode(['success' => false, 'message' => 'Song not found.']);
        exit;
    }

    // Delete DB record
    $del = $pdo->prepare('DELETE FROM songs WHERE id = ?');
    $del->execute([$songId]);

    // Delete audio file
    if (!empty($song['filename'])) {
        $audioPath = MUSIC_UPLOAD_PATH . $song['filename'];
        if (file_exists($audioPath)) {
            @unlink($audioPath);
        }
    }

    // Delete cover image
    if (!empty($song['cover_image'])) {
        $coverPath = MUSIC_UPLOAD_PATH . $song['cover_image'];
        if (file_exists($coverPath)) {
            @unlink($coverPath);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Song deleted successfully.', 'song_id' => $songId]);

} catch (Exception $e) {
    error_log('Delete song error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
