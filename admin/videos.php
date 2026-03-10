<?php
/**
 * Admin YouTube Videos Management
 */

$adminPageTitle = 'Videos';
require_once __DIR__ . '/includes/admin-header.php';

$pdo    = getDBConnection();
$errors = [];
$csrfToken = generateCSRFToken();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Invalid request.';
        redirect(SITE_URL . '/admin/videos.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title       = trim($_POST['title'] ?? '');
        $youtubeUrl  = trim($_POST['youtube_url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;

        if (empty($title)) {
            $errors[] = 'Video title is required.';
        }
        if (empty($youtubeUrl)) {
            $errors[] = 'YouTube URL is required.';
        }

        $youtubeId = extractYouTubeId($youtubeUrl);
        if (!$youtubeId && empty($errors)) {
            $errors[] = 'Invalid YouTube URL. Please enter a valid YouTube video link.';
        }

        if (empty($errors)) {
            $thumbnail = 'https://img.youtube.com/vi/' . $youtubeId . '/hqdefault.jpg';
            $stmt = $pdo->prepare(
                'INSERT INTO videos (title, description, youtube_url, youtube_id, thumbnail, is_featured) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$title, $description, $youtubeUrl, $youtubeId, $thumbnail, $isFeatured]);
            $_SESSION['flash_success'] = 'Video "' . $title . '" added!';
            redirect(SITE_URL . '/admin/videos.php');
        }
    }
}

// Fetch all videos
$stmt   = $pdo->query('SELECT * FROM videos ORDER BY created_at DESC');
$videos = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h2>Videos Management</h2>
</div>

<div class="videos-admin-layout">

    <!-- Add Video Form -->
    <div class="form-card admin-card" id="add-video">
        <div class="card-header">
            <h3><i class="fab fa-youtube" aria-hidden="true"></i> Add YouTube Video</h3>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($errors as $err): ?>
            <p><?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" aria-label="Add video form" id="add-video-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label for="title" class="form-label">Video Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-input"
                       required placeholder="Video title"
                       value="<?= e($_POST['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="youtube_url" class="form-label">YouTube URL <span class="required">*</span></label>
                <input type="url" id="youtube_url" name="youtube_url" class="form-input"
                       required placeholder="https://www.youtube.com/watch?v=..."
                       value="<?= e($_POST['youtube_url'] ?? '') ?>"
                       id="youtube-url-input">
                <!-- Live thumbnail preview -->
                <div class="yt-preview" id="yt-preview" style="display:none; margin-top:0.5rem;">
                    <img id="yt-thumbnail" src="" alt="YouTube video thumbnail" width="200" style="border-radius:6px;">
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-input form-textarea"
                          rows="3" placeholder="Brief description of the video"><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_featured" value="1"
                           <?= !empty($_POST['is_featured']) ? 'checked' : '' ?>>
                    Mark as Featured Video
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus" aria-hidden="true"></i> Add Video
            </button>
        </form>
    </div>

    <!-- Videos List -->
    <div class="admin-card">
        <div class="card-header">
            <h3>All Videos (<?= count($videos) ?>)</h3>
        </div>

        <?php if (!empty($videos)): ?>
        <div class="table-responsive">
            <table class="admin-table" aria-label="Videos list">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>YouTube ID</th>
                        <th>Featured</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                    <tr id="video-row-<?= (int)$video['id'] ?>">
                        <td>
                            <img src="https://img.youtube.com/vi/<?= e($video['youtube_id']) ?>/default.jpg"
                                 alt="<?= e($video['title']) ?> thumbnail"
                                 class="table-thumb"
                                 loading="lazy"
                                 width="80" height="60">
                        </td>
                        <td><?= e($video['title']) ?></td>
                        <td><code><?= e($video['youtube_id']) ?></code></td>
                        <td>
                            <?php if ($video['is_featured']): ?>
                            <span class="badge badge-approved"><i class="fas fa-star" aria-hidden="true"></i> Yes</span>
                            <?php else: ?>
                            <span class="badge badge-pending">No</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e(formatDate($video['created_at'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="https://www.youtube.com/watch?v=<?= e($video['youtube_id']) ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="btn btn-sm btn-outline"
                                   aria-label="View <?= e($video['title']) ?> on YouTube">
                                    <i class="fab fa-youtube" aria-hidden="true"></i>
                                </a>
                                <button class="btn btn-sm btn-danger btn-delete-video"
                                        data-video-id="<?= (int)$video['id'] ?>"
                                        data-video-title="<?= e($video['title']) ?>"
                                        aria-label="Delete <?= e($video['title']) ?>">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-table">
            <i class="fab fa-youtube" aria-hidden="true"></i>
            <p>No videos added yet. Add one using the form.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
