<?php
/**
 * General Helper Functions
 * Reusable utility functions for the application
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Sanitize output to prevent XSS
 *
 * @param string $value
 * @return string
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Get the DJ profile from the database
 *
 * @return array
 */
function getDJProfile(): array
{
    $pdo = getDBConnection();
    $stmt = $pdo->query('SELECT * FROM dj_profile LIMIT 1');
    return $stmt->fetch() ?: [];
}

/**
 * Get all music categories
 *
 * @return array
 */
function getCategories(): array
{
    $pdo = getDBConnection();
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
    return $stmt->fetchAll();
}

/**
 * Get songs with optional category filter and pagination
 *
 * @param int    $categoryId  0 means all categories
 * @param int    $page        Current page number
 * @param int    $perPage     Items per page
 * @return array ['songs' => [...], 'total' => int]
 */
function getSongs(int $categoryId = 0, int $page = 1, int $perPage = ITEMS_PER_PAGE): array
{
    $pdo    = getDBConnection();
    $offset = ($page - 1) * $perPage;

    if ($categoryId > 0) {
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM songs WHERE category_id = ?');
        $countStmt->execute([$categoryId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT s.*, c.name AS category_name, c.slug AS category_slug
             FROM songs s
             LEFT JOIN categories c ON s.category_id = c.id
             WHERE s.category_id = ?
             ORDER BY s.upload_date DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$categoryId, $perPage, $offset]);
    } else {
        $countStmt = $pdo->query('SELECT COUNT(*) FROM songs');
        $total     = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT s.*, c.name AS category_name, c.slug AS category_slug
             FROM songs s
             LEFT JOIN categories c ON s.category_id = c.id
             ORDER BY s.upload_date DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$perPage, $offset]);
    }

    return [
        'songs' => $stmt->fetchAll(),
        'total' => $total,
    ];
}

/**
 * Get latest songs for the home page
 *
 * @param int $limit
 * @return array
 */
function getLatestSongs(int $limit = 6): array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT s.*, c.name AS category_name
         FROM songs s
         LEFT JOIN categories c ON s.category_id = c.id
         ORDER BY s.upload_date DESC
         LIMIT ?'
    );
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get latest YouTube videos
 *
 * @param int  $limit
 * @param bool $featuredOnly
 * @return array
 */
function getVideos(int $limit = 0, bool $featuredOnly = false): array
{
    $pdo = getDBConnection();
    $sql = 'SELECT * FROM videos';

    if ($featuredOnly) {
        $sql .= ' WHERE is_featured = 1';
    }

    $sql .= ' ORDER BY created_at DESC';

    if ($limit > 0) {
        $sql  .= ' LIMIT ?';
        $stmt  = $pdo->prepare($sql);
        $stmt->execute([$limit]);
    } else {
        $stmt = $pdo->query($sql);
    }

    return $stmt->fetchAll();
}

/**
 * Generate a CSRF token and store it in the session
 *
 * @return string
 */
function generateCSRFToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token
 *
 * @param string $token
 * @return bool
 */
function validateCSRFToken(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate a unique UUID-style filename for uploads
 *
 * @param string $extension
 * @return string
 */
function generateFilename(string $extension): string
{
    return sprintf(
        '%s_%s.%s',
        bin2hex(random_bytes(8)),
        time(),
        strtolower($extension)
    );
}

/**
 * Validate uploaded file MIME type
 *
 * @param string $filePath     Temporary file path
 * @param array  $allowedTypes Allowed MIME types
 * @return bool
 */
function validateFileMime(string $filePath, array $allowedTypes): bool
{
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($filePath);
    return in_array($mimeType, $allowedTypes, true);
}

/**
 * Format file size for display
 *
 * @param int $bytes
 * @return string
 */
function formatFileSize(int $bytes): string
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}

/**
 * Format a date/time string for display
 *
 * @param string $dateTime
 * @param string $format
 * @return string
 */
function formatDate(string $dateTime, string $format = 'M j, Y'): string
{
    try {
        $dt = new DateTime($dateTime);
        return $dt->format($format);
    } catch (Exception $e) {
        return $dateTime;
    }
}

/**
 * Truncate a string to a given length
 *
 * @param string $text
 * @param int    $length
 * @param string $suffix
 * @return string
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length - mb_strlen($suffix)) . $suffix;
}

/**
 * Return a JSON response and exit
 *
 * @param bool   $success
 * @param string $message
 * @param array  $data
 * @return never
 */
function jsonResponse(bool $success, string $message = '', array $data = []): never
{
    header('Content-Type: application/json; charset=utf-8');
    $response = array_merge(['success' => $success, 'message' => $message], $data);
    echo json_encode($response);
    exit;
}

/**
 * Redirect to a URL
 *
 * @param string $url
 * @return never
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Get booking status badge HTML
 *
 * @param string $status
 * @return string
 */
function statusBadge(string $status): string
{
    $map = [
        'pending'  => 'badge-pending',
        'approved' => 'badge-approved',
        'rejected' => 'badge-rejected',
    ];
    $class = $map[$status] ?? 'badge-pending';
    return '<span class="badge ' . $class . '">' . e(ucfirst($status)) . '</span>';
}

/**
 * Get dashboard statistics
 *
 * @return array
 */
function getDashboardStats(): array
{
    $pdo = getDBConnection();

    $stats = [];

    $stmt = $pdo->query('SELECT COUNT(*) FROM bookings');
    $stats['total_bookings'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE status = ?');
    $stmt->execute(['pending']);
    $stats['pending_bookings'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) FROM songs');
    $stats['total_songs'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) FROM videos');
    $stats['total_videos'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) FROM categories');
    $stats['total_categories'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0');
    $stats['unread_messages'] = (int)$stmt->fetchColumn();

    return $stats;
}

/**
 * Extract YouTube video ID from a URL
 *
 * @param string $url
 * @return string|null
 */
function extractYouTubeId(string $url): ?string
{
    $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Check if a booking date is blocked
 *
 * @param string $date  (Y-m-d format)
 * @return bool
 */
function isDateBlocked(string $date): bool
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare('SELECT id FROM availability WHERE blocked_date = ?');
    $stmt->execute([$date]);
    return $stmt->fetchColumn() !== false;
}

/**
 * Paginate: return pagination data
 *
 * @param int    $total
 * @param int    $perPage
 * @param int    $currentPage
 * @param string $baseUrl
 * @return array
 */
function paginate(int $total, int $perPage, int $currentPage, string $baseUrl): array
{
    $totalPages = (int)ceil($total / $perPage);
    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'has_prev'     => $currentPage > 1,
        'has_next'     => $currentPage < $totalPages,
        'prev_page'    => $currentPage - 1,
        'next_page'    => $currentPage + 1,
        'base_url'     => $baseUrl,
    ];
}
