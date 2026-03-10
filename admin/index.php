<?php
/**
 * Admin Dashboard — Analytics overview
 */

$adminPageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

// Recent bookings
$stmt = $pdo->query(
    'SELECT id, full_name, email, event_type, event_date, status, created_at
     FROM bookings ORDER BY created_at DESC LIMIT 10'
);
$recentBookings = $stmt->fetchAll();

// Recent uploads
$stmt = $pdo->query(
    'SELECT s.id, s.title, s.artist, s.upload_date, c.name AS category_name
     FROM songs s LEFT JOIN categories c ON s.category_id = c.id
     ORDER BY s.upload_date DESC LIMIT 5'
);
$recentSongs = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h2>Dashboard</h2>
    <p class="admin-page-subtitle">Welcome back, <?= e($adminUsername) ?>! Here's an overview of your platform.</p>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card" style="--accent-color:#ff6b35;">
        <div class="stat-icon">
            <i class="fas fa-calendar-check" aria-hidden="true"></i>
        </div>
        <div class="stat-info">
            <h3 class="stat-number"><?= $stats['total_bookings'] ?></h3>
            <p class="stat-label">Total Bookings</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/bookings.php" class="stat-link" aria-label="View all bookings">
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    <div class="stat-card" style="--accent-color:#f59e0b;">
        <div class="stat-icon">
            <i class="fas fa-clock" aria-hidden="true"></i>
        </div>
        <div class="stat-info">
            <h3 class="stat-number"><?= $stats['pending_bookings'] ?></h3>
            <p class="stat-label">Pending Bookings</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/bookings.php?status=pending" class="stat-link" aria-label="View pending bookings">
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    <div class="stat-card" style="--accent-color:#10b981;">
        <div class="stat-icon">
            <i class="fas fa-music" aria-hidden="true"></i>
        </div>
        <div class="stat-info">
            <h3 class="stat-number"><?= $stats['total_songs'] ?></h3>
            <p class="stat-label">Total Songs</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/music.php" class="stat-link" aria-label="View all songs">
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    <div class="stat-card" style="--accent-color:#e94560;">
        <div class="stat-icon">
            <i class="fab fa-youtube" aria-hidden="true"></i>
        </div>
        <div class="stat-info">
            <h3 class="stat-number"><?= $stats['total_videos'] ?></h3>
            <p class="stat-label">Total Videos</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/videos.php" class="stat-link" aria-label="View all videos">
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    <div class="stat-card" style="--accent-color:#6366f1;">
        <div class="stat-icon">
            <i class="fas fa-tags" aria-hidden="true"></i>
        </div>
        <div class="stat-info">
            <h3 class="stat-number"><?= $stats['total_categories'] ?></h3>
            <p class="stat-label">Categories</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/categories.php" class="stat-link" aria-label="View categories">
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    <div class="stat-card" style="--accent-color:#ec4899;">
        <div class="stat-icon">
            <i class="fas fa-envelope" aria-hidden="true"></i>
        </div>
        <div class="stat-info">
            <h3 class="stat-number"><?= $stats['unread_messages'] ?></h3>
            <p class="stat-label">Unread Messages</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/messages.php" class="stat-link" aria-label="View messages">
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="<?= SITE_URL ?>/admin/upload-music.php" class="quick-action-btn">
        <i class="fas fa-upload" aria-hidden="true"></i> Upload Song
    </a>
    <a href="<?= SITE_URL ?>/admin/videos.php#add-video" class="quick-action-btn">
        <i class="fab fa-youtube" aria-hidden="true"></i> Add Video
    </a>
    <a href="<?= SITE_URL ?>/admin/bookings.php?status=pending" class="quick-action-btn">
        <i class="fas fa-clock" aria-hidden="true"></i> Pending Bookings
    </a>
    <a href="<?= SITE_URL ?>/admin/profile.php" class="quick-action-btn">
        <i class="fas fa-user-edit" aria-hidden="true"></i> Edit Profile
    </a>
</div>

<!-- Recent Bookings & Uploads -->
<div class="dashboard-grid">

    <!-- Recent Bookings -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3>Recent Bookings</h3>
            <a href="<?= SITE_URL ?>/admin/bookings.php" class="card-header-link">View All</a>
        </div>
        <?php if (!empty($recentBookings)): ?>
        <div class="table-responsive">
            <table class="admin-table" aria-label="Recent bookings">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Event Type</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $booking): ?>
                    <tr>
                        <td><?= e($booking['full_name']) ?></td>
                        <td><?= e($booking['event_type']) ?></td>
                        <td><?= e(formatDate($booking['event_date'])) ?></td>
                        <td><?= statusBadge($booking['status']) ?></td>
                        <td>
                            <a href="<?= SITE_URL ?>/admin/booking-detail.php?id=<?= (int)$booking['id'] ?>" class="btn btn-sm btn-outline">
                                View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-table">
            <i class="fas fa-calendar-times" aria-hidden="true"></i>
            <p>No bookings yet</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Song Uploads -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3>Recent Uploads</h3>
            <a href="<?= SITE_URL ?>/admin/music.php" class="card-header-link">View All</a>
        </div>
        <?php if (!empty($recentSongs)): ?>
        <div class="table-responsive">
            <table class="admin-table" aria-label="Recent song uploads">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Category</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSongs as $song): ?>
                    <tr>
                        <td><?= e($song['title']) ?></td>
                        <td><?= e($song['artist']) ?></td>
                        <td><?= e($song['category_name'] ?? '—') ?></td>
                        <td><?= e(formatDate($song['upload_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-table">
            <i class="fas fa-music" aria-hidden="true"></i>
            <p>No songs uploaded yet</p>
            <a href="<?= SITE_URL ?>/admin/upload-music.php" class="btn btn-sm btn-primary">Upload Now</a>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /.dashboard-grid -->

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
