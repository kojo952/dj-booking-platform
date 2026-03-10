<?php
/**
 * API: Music Endpoints
 * GET ?action=list&category=&page= — Returns paginated songs list as JSON
 * POST ?action=increment_play&id=  — Increments play count for a song
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ── List songs (AJAX filtering / pagination) ──────────────────────────
    case 'list':
        $categorySlug = trim($_GET['category'] ?? '');
        $page         = max(1, (int)($_GET['page'] ?? 1));
        $categoryId   = 0;

        if ($categorySlug) {
            $pdo  = getDBConnection();
            $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
            $stmt->execute([$categorySlug]);
            $cat = $stmt->fetch();
            $categoryId = $cat ? (int)$cat['id'] : 0;
        }

        $result = getSongs($categoryId, $page, ITEMS_PER_PAGE);

        // Build safe output — re-map so no raw paths are exposed
        $songs = array_map(function ($song) {
            return [
                'id'            => (int)$song['id'],
                'title'         => $song['title'],
                'artist'        => $song['artist'],
                'category_name' => $song['category_name'] ?? '',
                'category_slug' => $song['category_slug'] ?? '',
                'filename'      => $song['filename'],
                'cover_image'   => $song['cover_image'],
                'duration'      => $song['duration'],
                'play_count'    => (int)$song['play_count'],
                'allow_download'=> (bool)$song['allow_download'],
                'upload_date'   => $song['upload_date'],
            ];
        }, $result['songs']);

        echo json_encode([
            'success'      => true,
            'songs'        => $songs,
            'total'        => $result['total'],
            'current_page' => $page,
            'per_page'     => ITEMS_PER_PAGE,
            'total_pages'  => (int)ceil($result['total'] / ITEMS_PER_PAGE),
        ]);
        break;

    // ── Increment play count ───────────────────────────────────────────────
    case 'increment_play':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            break;
        }

        $songId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if (!$songId) {
            echo json_encode(['success' => false, 'message' => 'Invalid song ID.']);
            break;
        }

        try {
            $pdo  = getDBConnection();
            $stmt = $pdo->prepare('UPDATE songs SET play_count = play_count + 1 WHERE id = ?');
            $stmt->execute([$songId]);

            // Return updated count
            $stmt = $pdo->prepare('SELECT play_count FROM songs WHERE id = ?');
            $stmt->execute([$songId]);
            $count = $stmt->fetchColumn();

            echo json_encode(['success' => true, 'play_count' => (int)$count]);
        } catch (Exception $e) {
            error_log('Play count increment error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
