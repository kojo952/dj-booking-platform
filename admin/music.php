<?php
/**
 * Admin Music Management — List, edit, and delete songs
 */

$adminPageTitle = 'Songs';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

$stmt = $pdo->query(
    'SELECT s.*, c.name AS category_name
     FROM songs s
     LEFT JOIN categories c ON s.category_id = c.id
     ORDER BY s.upload_date DESC'
);
$songs = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h2>Songs Management</h2>
    <a href="<?= SITE_URL ?>/admin/upload-music.php" class="btn btn-primary">
        <i class="fas fa-upload" aria-hidden="true"></i> Upload New Song
    </a>
</div>

<?php if (!empty($songs)): ?>
<div class="table-responsive">
    <table class="admin-table sortable-table" aria-label="Songs list">
        <thead>
            <tr>
                <th>Cover</th>
                <th data-sort="title">Title</th>
                <th data-sort="artist">Artist</th>
                <th data-sort="category">Category</th>
                <th data-sort="plays">Plays</th>
                <th>Download</th>
                <th data-sort="date">Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($songs as $song): ?>
            <tr id="song-row-<?= (int)$song['id'] ?>">
                <td>
                    <?php if (!empty($song['cover_image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/music/<?= e($song['cover_image']) ?>"
                             alt="<?= e($song['title']) ?> cover"
                             class="table-thumb"
                             loading="lazy"
                             width="50" height="50">
                    <?php else: ?>
                        <div class="table-thumb-placeholder" aria-hidden="true">
                            <i class="fas fa-music"></i>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= e($song['title']) ?></td>
                <td><?= e($song['artist'] ?: '—') ?></td>
                <td><?= e($song['category_name'] ?? '—') ?></td>
                <td>
                    <span class="play-count-badge">
                        <i class="fas fa-headphones" aria-hidden="true"></i>
                        <?= number_format((int)$song['play_count']) ?>
                    </span>
                </td>
                <td>
                    <?php if ($song['allow_download']): ?>
                        <span class="badge badge-approved">Yes</span>
                    <?php else: ?>
                        <span class="badge badge-rejected">No</span>
                    <?php endif; ?>
                </td>
                <td><?= e(formatDate($song['upload_date'])) ?></td>
                <td>
                    <div class="table-actions">
                        <button class="btn btn-sm btn-danger btn-delete-song"
                                data-song-id="<?= (int)$song['id'] ?>"
                                data-song-title="<?= e($song['title']) ?>"
                                aria-label="Delete <?= e($song['title']) ?>">
                            <i class="fas fa-trash" aria-hidden="true"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php else: ?>
<div class="empty-state-admin">
    <i class="fas fa-music" aria-hidden="true"></i>
    <h3>No songs yet</h3>
    <p>Upload your first song to get started.</p>
    <a href="<?= SITE_URL ?>/admin/upload-music.php" class="btn btn-primary">
        <i class="fas fa-upload" aria-hidden="true"></i> Upload Song
    </a>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
