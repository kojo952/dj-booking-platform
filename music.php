<?php
/**
 * Music Catalog Page — Browse and stream songs with category filtering
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Handle category filter
$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : '';
$categoryId   = 0;
$activeCategory = null;

if ($categorySlug) {
    $pdo   = getDBConnection();
    $stmt  = $pdo->prepare('SELECT id, name FROM categories WHERE slug = ? LIMIT 1');
    $stmt->execute([$categorySlug]);
    $activeCategory = $stmt->fetch();
    $categoryId = $activeCategory ? (int)$activeCategory['id'] : 0;
}

// Pagination
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$result      = getSongs($categoryId, $currentPage, ITEMS_PER_PAGE);
$songs       = $result['songs'];
$pagination  = paginate($result['total'], ITEMS_PER_PAGE, $currentPage, SITE_URL . '/music.php' . ($categorySlug ? '?category=' . urlencode($categorySlug) . '&' : '?'));

// All categories for tabs
$categories = getCategories();
$profile    = getDJProfile();
$djName     = $profile['name'] ?? SITE_NAME;

$pageTitle = 'Music';
$pageDesc  = 'Stream the latest Afrobeats, Gospel, Hip-Hop and DJ Mixes from ' . $djName . '. Browse our full music catalog.';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero-content">
            <h1 class="page-title"><i class="fas fa-music" aria-hidden="true"></i> Music Catalog</h1>
            <p class="page-subtitle">Stream and download tracks from <?= e($djName) ?></p>
        </div>
    </div>
</section>

<!-- Music Section -->
<section class="section music-page" aria-label="Music catalog">
    <div class="container">

        <!-- Category Filter Tabs -->
        <nav class="category-tabs" aria-label="Music categories">
            <a href="<?= SITE_URL ?>/music.php"
               class="category-tab <?= empty($categorySlug) ? 'active' : '' ?>"
               data-category="">
                <i class="fas fa-th" aria-hidden="true"></i> All
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= SITE_URL ?>/music.php?category=<?= urlencode($cat['slug']) ?>"
               class="category-tab <?= $categorySlug === $cat['slug'] ? 'active' : '' ?>"
               data-category="<?= e($cat['slug']) ?>">
                <?= e($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Results Info -->
        <div class="results-info" aria-live="polite">
            <?php if ($activeCategory): ?>
            <p>Showing <strong><?= $result['total'] ?></strong> track<?= $result['total'] !== 1 ? 's' : '' ?> in <strong><?= e($activeCategory['name']) ?></strong></p>
            <?php else: ?>
            <p>Showing <strong><?= $result['total'] ?></strong> track<?= $result['total'] !== 1 ? 's' : '' ?></p>
            <?php endif; ?>
        </div>

        <!-- Songs Grid -->
        <?php if (!empty($songs)): ?>
        <div class="songs-grid" id="songs-grid">
            <?php foreach ($songs as $song): ?>
            <article class="song-card glass-card" data-song-id="<?= (int)$song['id'] ?>">
                <!-- Cover art -->
                <div class="song-cover">
                    <?php if (!empty($song['cover_image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/music/<?= e($song['cover_image']) ?>"
                             alt="<?= e($song['title']) ?> album art"
                             loading="lazy"
                             width="200" height="200">
                    <?php else: ?>
                        <div class="song-cover-placeholder" aria-hidden="true">
                            <i class="fas fa-music"></i>
                        </div>
                    <?php endif; ?>
                    <!-- Play button overlay -->
                    <button class="play-overlay"
                            data-song-id="<?= (int)$song['id'] ?>"
                            data-song-title="<?= e($song['title']) ?>"
                            data-song-artist="<?= e($song['artist'] ?: $djName) ?>"
                            data-song-file="<?= SITE_URL ?>/uploads/music/<?= e($song['filename']) ?>"
                            data-song-cover="<?php echo !empty($song['cover_image']) ? SITE_URL . '/uploads/music/' . e($song['cover_image']) : ''; ?>"
                            aria-label="Play <?= e($song['title']) ?>">
                        <i class="fas fa-play" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Song info -->
                <div class="song-info">
                    <span class="song-category"><?= e($song['category_name'] ?? '') ?></span>
                    <h3 class="song-title"><?= e($song['title']) ?></h3>
                    <p class="song-artist"><?= e($song['artist'] ?: $djName) ?></p>
                    <div class="song-meta">
                        <span class="play-count" title="Total plays">
                            <i class="fas fa-headphones" aria-hidden="true"></i>
                            <?= number_format((int)$song['play_count']) ?>
                        </span>
                        <?php if (!empty($song['duration'])): ?>
                        <span class="song-duration">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <?= e($song['duration']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($song['allow_download']): ?>
                        <a href="<?= SITE_URL ?>/uploads/music/<?= e($song['filename']) ?>"
                           download="<?= e($song['title']) ?>"
                           class="song-download btn-icon"
                           aria-label="Download <?= e($song['title']) ?>">
                            <i class="fas fa-download" aria-hidden="true"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="pagination" aria-label="Music pagination">
            <?php if ($pagination['has_prev']): ?>
            <a href="<?= e($pagination['base_url']) ?>page=<?= $pagination['prev_page'] ?>" class="page-btn" aria-label="Previous page">
                <i class="fas fa-chevron-left" aria-hidden="true"></i> Prev
            </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
            <a href="<?= e($pagination['base_url']) ?>page=<?= $i ?>"
               class="page-btn <?= $i === $pagination['current_page'] ? 'active' : '' ?>"
               <?= $i === $pagination['current_page'] ? 'aria-current="page"' : '' ?>>
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($pagination['has_next']): ?>
            <a href="<?= e($pagination['base_url']) ?>page=<?= $pagination['next_page'] ?>" class="page-btn" aria-label="Next page">
                Next <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-music" aria-hidden="true"></i>
            <h3>No tracks found</h3>
            <?php if ($categorySlug): ?>
            <p>No songs found in this category. <a href="<?= SITE_URL ?>/music.php">Browse all music</a></p>
            <?php else: ?>
            <p>The music catalog is empty. Check back soon!</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- Sticky Music Player Bar -->
<div id="music-player-bar" class="music-player-bar" aria-label="Music player" style="display:none;">
    <div class="player-container">
        <div class="player-song-info">
            <div class="player-album-art">
                <img id="player-cover" src="" alt="Now playing album art" width="50" height="50">
                <div class="player-cover-placeholder" id="player-cover-placeholder" aria-hidden="true">
                    <i class="fas fa-music"></i>
                </div>
            </div>
            <div class="player-track">
                <p class="player-title" id="player-title">No track selected</p>
                <p class="player-artist" id="player-artist">—</p>
            </div>
        </div>

        <div class="player-controls">
            <button class="player-btn" id="btn-prev" aria-label="Previous track">
                <i class="fas fa-step-backward" aria-hidden="true"></i>
            </button>
            <button class="player-btn player-btn-main" id="btn-play-pause" aria-label="Play / Pause">
                <i class="fas fa-play" aria-hidden="true" id="play-icon"></i>
            </button>
            <button class="player-btn" id="btn-next" aria-label="Next track">
                <i class="fas fa-step-forward" aria-hidden="true"></i>
            </button>
        </div>

        <div class="player-progress-area">
            <span class="player-time" id="player-current-time">0:00</span>
            <div class="player-progress-bar" id="player-progress-bar" role="progressbar" aria-label="Track progress">
                <div class="player-progress-fill" id="player-progress-fill"></div>
                <div class="player-progress-thumb" id="player-progress-thumb"></div>
            </div>
            <span class="player-time" id="player-duration">0:00</span>
        </div>

        <div class="player-extras">
            <div class="player-volume">
                <button class="player-btn" id="btn-mute" aria-label="Mute/Unmute">
                    <i class="fas fa-volume-up" aria-hidden="true" id="volume-icon"></i>
                </button>
                <input type="range" id="volume-slider" class="volume-slider" min="0" max="1" step="0.05" value="0.8" aria-label="Volume">
            </div>
            <button class="player-btn minimize-btn" id="btn-minimize" aria-label="Minimize player">
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <audio id="audio-element" preload="none"></audio>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
