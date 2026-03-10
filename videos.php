<?php
/**
 * Videos Page — YouTube video embeds with subscribe button
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Filter: all or featured only
$filter   = isset($_GET['filter']) && $_GET['filter'] === 'featured' ? 'featured' : 'all';
$videos   = getVideos(0, $filter === 'featured');
$profile  = getDJProfile();
$djName   = $profile['name'] ?? SITE_NAME;

$pageTitle = 'Videos';
$pageDesc  = 'Watch DJ mixes, live sets, and video performances by ' . $djName . ' on YouTube.';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero-content">
            <h1 class="page-title"><i class="fas fa-video" aria-hidden="true"></i> Videos</h1>
            <p class="page-subtitle">Watch DJ mixes, live sets, and performances by <?= e($djName) ?></p>
        </div>
    </div>
</section>

<!-- Videos Section -->
<section class="section videos-page" aria-label="YouTube videos">
    <div class="container">

        <!-- Filter & Subscribe -->
        <div class="videos-header">
            <nav class="filter-tabs" aria-label="Video filter">
                <a href="<?= SITE_URL ?>/videos.php"
                   class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">
                    <i class="fas fa-th" aria-hidden="true"></i> All Videos
                </a>
                <a href="<?= SITE_URL ?>/videos.php?filter=featured"
                   class="filter-tab <?= $filter === 'featured' ? 'active' : '' ?>">
                    <i class="fas fa-star" aria-hidden="true"></i> Featured
                </a>
            </nav>

            <!-- YouTube Subscribe Button -->
            <?php if (!empty($profile['youtube_channel_url'])): ?>
            <div class="youtube-subscribe">
                <a href="<?= e($profile['youtube_channel_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-youtube">
                    <i class="fab fa-youtube" aria-hidden="true"></i> Subscribe on YouTube
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Videos Grid -->
        <?php if (!empty($videos)): ?>
        <div class="videos-grid">
            <?php foreach ($videos as $video): ?>
            <article class="video-card glass-card <?= $video['is_featured'] ? 'featured' : '' ?>">
                <?php if ($video['is_featured']): ?>
                <span class="featured-badge" aria-label="Featured video">
                    <i class="fas fa-star" aria-hidden="true"></i> Featured
                </span>
                <?php endif; ?>

                <!-- Responsive iframe wrapper -->
                <div class="video-embed-wrapper">
                    <iframe
                        src="https://www.youtube.com/embed/<?= e($video['youtube_id']) ?>?rel=0&modestbranding=1&color=white"
                        title="<?= e($video['title']) ?>"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        loading="lazy">
                    </iframe>
                </div>

                <div class="video-info">
                    <h2 class="video-title"><?= e($video['title']) ?></h2>
                    <?php if (!empty($video['description'])): ?>
                    <p class="video-desc"><?= e($video['description']) ?></p>
                    <?php endif; ?>
                    <div class="video-meta">
                        <a href="https://www.youtube.com/watch?v=<?= e($video['youtube_id']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline">
                            <i class="fab fa-youtube" aria-hidden="true"></i> Watch on YouTube
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="empty-state">
            <i class="fab fa-youtube" aria-hidden="true"></i>
            <h3>No videos found</h3>
            <p>Check back soon for new content!</p>
        </div>
        <?php endif; ?>

        <!-- Channel CTA -->
        <?php if (!empty($profile['youtube_channel_url'])): ?>
        <div class="channel-cta">
            <h3>Want to see more?</h3>
            <p>Subscribe to the <?= e($djName) ?> YouTube channel for the latest mixes and performances.</p>
            <a href="<?= e($profile['youtube_channel_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-youtube btn-lg">
                <i class="fab fa-youtube" aria-hidden="true"></i>
                Visit YouTube Channel
            </a>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
