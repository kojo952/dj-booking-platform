<?php
/**
 * Home Page — Landing page with hero, featured music, videos, and booking CTA
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch data for sections
$profile      = getDJProfile();
$latestSongs  = getLatestSongs(6);
$latestVideos = getVideos(4);
$djName       = $profile['name'] ?? SITE_NAME;

$pageTitle = 'Home';
$pageDesc  = 'Book ' . ($djName) . ' for your next event. Stream Afrobeats, Gospel, Hip-Hop and more. Submit your booking request online today.';

include __DIR__ . '/includes/header.php';
?>

<!-- ========== HERO SECTION ========== -->
<section class="hero" aria-label="Hero section">
    <div class="hero-overlay" aria-hidden="true"></div>

    <!-- Animated particles background -->
    <div class="hero-particles" aria-hidden="true" id="hero-particles"></div>

    <div class="hero-content container">
        <div class="hero-badge animate-slide-up">
            <i class="fas fa-music" aria-hidden="true"></i>
            <span>Professional DJ & Music Curator</span>
        </div>

        <h1 class="hero-title animate-slide-up" style="animation-delay:0.1s">
            Elevate Your Event<br>
            <span class="gradient-text">with <?= e($djName) ?></span>
        </h1>

        <p class="hero-subtitle animate-slide-up" style="animation-delay:0.2s">
            <?= e(truncate($profile['bio'] ?? 'Professional DJ with 10+ years of experience delivering unforgettable music experiences.', 150)) ?>
        </p>

        <div class="hero-buttons animate-slide-up" style="animation-delay:0.3s">
            <a href="<?= SITE_URL ?>/booking.php" class="btn btn-primary btn-lg">
                <i class="fas fa-calendar-check" aria-hidden="true"></i>
                Book Me Now
            </a>
            <a href="<?= SITE_URL ?>/music.php" class="btn btn-outline btn-lg">
                <i class="fas fa-play-circle" aria-hidden="true"></i>
                Listen Now
            </a>
        </div>

        <!-- Scroll indicator -->
        <div class="hero-scroll" aria-hidden="true">
            <div class="scroll-line"></div>
            <span>Scroll</span>
        </div>
    </div>
</section>

<!-- ========== ABOUT TEASER ========== -->
<section class="section about-teaser" aria-label="About the DJ">
    <div class="container">
        <div class="about-teaser-inner">
            <div class="about-teaser-image animate-fade-in">
                <?php if (!empty($profile['profile_image'])): ?>
                    <img src="<?= SITE_URL ?>/uploads/profile/<?= e($profile['profile_image']) ?>"
                         alt="<?= e($djName) ?> profile photo"
                         class="profile-img"
                         loading="lazy"
                         width="400" height="400">
                <?php else: ?>
                    <div class="profile-img-placeholder" aria-label="DJ profile image placeholder">
                        <i class="fas fa-user-circle" aria-hidden="true"></i>
                    </div>
                <?php endif; ?>
                <div class="profile-badge">
                    <i class="fas fa-star" aria-hidden="true"></i>
                    <span>10+ Years Experience</span>
                </div>
            </div>

            <div class="about-teaser-content animate-slide-right">
                <span class="section-label">About the DJ</span>
                <h2 class="section-title"><?= e($djName) ?></h2>
                <?php if (!empty($profile['location'])): ?>
                <p class="about-location">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    <?= e($profile['location']) ?>
                </p>
                <?php endif; ?>
                <p class="about-bio"><?= e(truncate($profile['bio'] ?? '', 300)) ?></p>

                <div class="about-stats">
                    <div class="stat-item">
                        <span class="stat-number">10+</span>
                        <span class="stat-label">Years Experience</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Events Performed</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">4</span>
                        <span class="stat-label">Music Genres</span>
                    </div>
                </div>

                <a href="<?= SITE_URL ?>/about.php" class="btn btn-primary">
                    Read More <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ========== FEATURED MUSIC ========== -->
<section class="section featured-music" aria-label="Featured music">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Latest Tracks</span>
            <h2 class="section-title">Featured <span class="gradient-text">Music</span></h2>
            <p class="section-subtitle">Stream the hottest tracks from <?= e($djName) ?></p>
        </div>

        <?php if (!empty($latestSongs)): ?>
        <div class="songs-grid" id="home-songs-grid">
            <?php foreach ($latestSongs as $song): ?>
            <article class="song-card glass-card" data-song-id="<?= (int)$song['id'] ?>">
                <!-- Cover art -->
                <div class="song-cover">
                    <?php if (!empty($song['cover_image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/music/<?= e($song['cover_image']) ?>"
                             alt="<?= e($song['title']) ?> cover art"
                             loading="lazy"
                             width="200" height="200">
                    <?php else: ?>
                        <div class="song-cover-placeholder" aria-hidden="true">
                            <i class="fas fa-music"></i>
                        </div>
                    <?php endif; ?>

                    <!-- Play overlay -->
                    <button class="play-overlay"
                            data-song-id="<?= (int)$song['id'] ?>"
                            data-song-title="<?= e($song['title']) ?>"
                            data-song-artist="<?= e($song['artist']) ?>"
                            data-song-file="<?= SITE_URL ?>/uploads/music/<?= e($song['filename']) ?>"
                            data-song-cover="<?php echo !empty($song['cover_image']) ? SITE_URL . '/uploads/music/' . e($song['cover_image']) : ''; ?>"
                            aria-label="Play <?= e($song['title']) ?>">
                        <i class="fas fa-play" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Song info -->
                <div class="song-info">
                    <span class="song-category"><?= e($song['category_name'] ?? 'Uncategorized') ?></span>
                    <h3 class="song-title"><?= e($song['title']) ?></h3>
                    <p class="song-artist"><?= e($song['artist'] ?: $djName) ?></p>
                    <div class="song-meta">
                        <span class="play-count">
                            <i class="fas fa-headphones" aria-hidden="true"></i>
                            <?= number_format((int)$song['play_count']) ?>
                        </span>
                        <?php if ($song['allow_download']): ?>
                        <a href="<?= SITE_URL ?>/uploads/music/<?= e($song['filename']) ?>"
                           download="<?= e($song['title']) ?>"
                           class="song-download"
                           aria-label="Download <?= e($song['title']) ?>">
                            <i class="fas fa-download" aria-hidden="true"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="section-footer">
            <a href="<?= SITE_URL ?>/music.php" class="btn btn-outline">
                View All Music <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-music" aria-hidden="true"></i>
            <p>Music catalog coming soon!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ========== VIDEOS SECTION ========== -->
<?php if (!empty($latestVideos)): ?>
<section class="section videos-section" aria-label="Latest videos">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Watch & Enjoy</span>
            <h2 class="section-title">Latest <span class="gradient-text">Videos</span></h2>
            <p class="section-subtitle">Watch DJ mixes, live sets, and performances</p>
        </div>

        <div class="videos-grid">
            <?php foreach ($latestVideos as $video): ?>
            <article class="video-card glass-card">
                <div class="video-embed-wrapper">
                    <iframe
                        src="https://www.youtube.com/embed/<?= e($video['youtube_id']) ?>?rel=0&modestbranding=1"
                        title="<?= e($video['title']) ?>"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        loading="lazy">
                    </iframe>
                </div>
                <div class="video-info">
                    <h3 class="video-title"><?= e($video['title']) ?></h3>
                    <?php if (!empty($video['description'])): ?>
                    <p class="video-desc"><?= e(truncate($video['description'], 100)) ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="section-footer">
            <a href="<?= SITE_URL ?>/videos.php" class="btn btn-outline">
                View All Videos <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== BOOKING CTA BANNER ========== -->
<section class="section booking-cta" aria-label="Booking call to action">
    <div class="booking-cta-overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="booking-cta-content">
            <div class="cta-icon" aria-hidden="true">
                <i class="fas fa-calendar-star"></i>
            </div>
            <h2 class="cta-title">Ready to Elevate Your Event?</h2>
            <p class="cta-subtitle">From weddings to corporate events, club nights to festivals — <?= e($djName) ?> delivers an unforgettable experience every time.</p>
            <div class="cta-features">
                <div class="cta-feature">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <span>Professional Equipment</span>
                </div>
                <div class="cta-feature">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <span>Custom Setlists</span>
                </div>
                <div class="cta-feature">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <span>Guaranteed Satisfaction</span>
                </div>
            </div>
            <a href="<?= SITE_URL ?>/booking.php" class="btn btn-primary btn-xl">
                <i class="fas fa-calendar-check" aria-hidden="true"></i>
                Book Now
            </a>
        </div>
    </div>
</section>

<!-- Sticky music player bar (rendered by player.js) -->
<div id="music-player-bar" class="music-player-bar" aria-label="Music player" style="display:none;">
    <div class="player-container">
        <!-- Song info -->
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

        <!-- Controls -->
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

        <!-- Progress -->
        <div class="player-progress-area">
            <span class="player-time" id="player-current-time">0:00</span>
            <div class="player-progress-bar" id="player-progress-bar" role="progressbar" aria-label="Track progress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="player-progress-fill" id="player-progress-fill"></div>
                <div class="player-progress-thumb" id="player-progress-thumb"></div>
            </div>
            <span class="player-time" id="player-duration">0:00</span>
        </div>

        <!-- Volume & Minimize -->
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

    <!-- Hidden audio element -->
    <audio id="audio-element" preload="none"></audio>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
