<?php
/**
 * About Page — Full DJ profile and background
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$profile = getDJProfile();
$djName  = $profile['name'] ?? SITE_NAME;

$pageTitle = 'About ' . $djName;
$pageDesc  = 'Learn more about ' . $djName . ' — professional DJ, music curator, and entertainer based in ' . ($profile['location'] ?? 'Ghana') . '.';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero-content">
            <h1 class="page-title">About <?= e($djName) ?></h1>
            <p class="page-subtitle">Professional DJ &amp; Music Curator</p>
        </div>
    </div>
</section>

<!-- Profile Section -->
<section class="section about-page" aria-label="DJ Profile">
    <div class="container">
        <div class="about-inner">

            <!-- Profile Image -->
            <div class="about-image-col animate-fade-in">
                <div class="about-image-frame">
                    <?php if (!empty($profile['profile_image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/profile/<?= e($profile['profile_image']) ?>"
                             alt="<?= e($djName) ?> profile photo"
                             class="about-profile-img"
                             loading="lazy"
                             width="500" height="500">
                    <?php else: ?>
                        <div class="profile-img-placeholder large" aria-label="Profile photo placeholder">
                            <i class="fas fa-user-circle" aria-hidden="true"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Social Links -->
                <?php if (!empty($profile['facebook']) || !empty($profile['instagram']) || !empty($profile['twitter']) || !empty($profile['youtube_channel_url'])): ?>
                <div class="about-social" aria-label="Social media links">
                    <?php if (!empty($profile['facebook'])): ?>
                    <a href="<?= e($profile['facebook']) ?>" target="_blank" rel="noopener noreferrer" class="social-link facebook" aria-label="Facebook">
                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($profile['instagram'])): ?>
                    <a href="<?= e($profile['instagram']) ?>" target="_blank" rel="noopener noreferrer" class="social-link instagram" aria-label="Instagram">
                        <i class="fab fa-instagram" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($profile['twitter'])): ?>
                    <a href="<?= e($profile['twitter']) ?>" target="_blank" rel="noopener noreferrer" class="social-link twitter" aria-label="Twitter / X">
                        <i class="fab fa-x-twitter" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($profile['youtube_channel_url'])): ?>
                    <a href="<?= e($profile['youtube_channel_url']) ?>" target="_blank" rel="noopener noreferrer" class="social-link youtube" aria-label="YouTube">
                        <i class="fab fa-youtube" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Profile Content -->
            <div class="about-content-col animate-slide-right">
                <span class="section-label">Get to Know Me</span>
                <h2 class="section-title"><?= e($djName) ?></h2>

                <?php if (!empty($profile['location'])): ?>
                <p class="about-location">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    <?= e($profile['location']) ?>
                </p>
                <?php endif; ?>

                <?php if (!empty($profile['bio'])): ?>
                <div class="about-bio">
                    <?php foreach (explode("\n", $profile['bio']) as $paragraph): ?>
                        <?php if (trim($paragraph)): ?>
                        <p><?= e(trim($paragraph)) ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Contact Info -->
                <div class="about-contact">
                    <?php if (!empty($profile['phone'])): ?>
                    <div class="contact-item">
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        <a href="tel:<?= e(preg_replace('/\s+/', '', $profile['phone'])) ?>"><?= e($profile['phone']) ?></a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['email'])): ?>
                    <div class="contact-item">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        <a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a>
                    </div>
                    <?php endif; ?>
                </div>

                <a href="<?= SITE_URL ?>/booking.php" class="btn btn-primary">
                    <i class="fas fa-calendar-check" aria-hidden="true"></i> Book Me Now
                </a>
            </div>

        </div>
    </div>
</section>

<!-- Why Book Me -->
<section class="section why-book-me" aria-label="Why book DJ KoJo">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Why Choose Me</span>
            <h2 class="section-title">The <span class="gradient-text">Experience</span> You Deserve</h2>
        </div>

        <div class="why-grid">
            <div class="why-card glass-card animate-fade-in">
                <div class="why-icon" aria-hidden="true">
                    <i class="fas fa-award"></i>
                </div>
                <h3>10+ Years Experience</h3>
                <p>Over a decade of performing at events across Ghana and internationally, delivering flawless performances every time.</p>
            </div>

            <div class="why-card glass-card animate-fade-in" style="animation-delay:0.1s">
                <div class="why-icon" aria-hidden="true">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <h3>Professional Equipment</h3>
                <p>State-of-the-art DJ equipment, speakers, lighting, and sound systems to ensure the best audio experience for your guests.</p>
            </div>

            <div class="why-card glass-card animate-fade-in" style="animation-delay:0.2s">
                <div class="why-icon" aria-hidden="true">
                    <i class="fas fa-music"></i>
                </div>
                <h3>Custom Setlists</h3>
                <p>Every event is unique. We work with you to create a custom playlist that perfectly matches the mood and theme of your event.</p>
            </div>

            <div class="why-card glass-card animate-fade-in" style="animation-delay:0.3s">
                <div class="why-icon" aria-hidden="true">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Multi-Genre Expertise</h3>
                <p>From Afrobeats to Gospel, Hip-Hop to custom mixes — versatile enough to cater to any audience and occasion.</p>
            </div>

            <div class="why-card glass-card animate-fade-in" style="animation-delay:0.4s">
                <div class="why-icon" aria-hidden="true">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Reliable &amp; Professional</h3>
                <p>Punctual, professional, and committed to making your event a success. We coordinate with your event team seamlessly.</p>
            </div>

            <div class="why-card glass-card animate-fade-in" style="animation-delay:0.5s">
                <div class="why-icon" aria-hidden="true">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Passion for Music</h3>
                <p>Music is not just a job — it's a passion. That energy and enthusiasm translates into an electrifying experience for your guests.</p>
            </div>
        </div>
    </div>
</section>

<!-- Location / Map Placeholder -->
<section class="section location-section" aria-label="Location">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Location</span>
            <h2 class="section-title">Based in <?= e($profile['location'] ?? 'Accra, Ghana') ?></h2>
        </div>
        <div class="map-placeholder glass-card" aria-label="Map showing location">
            <i class="fas fa-map-marked-alt" aria-hidden="true"></i>
            <p><?= e($profile['location'] ?? 'Accra, Ghana') ?> and surrounding areas</p>
            <a href="https://www.google.com/maps/search/<?= urlencode($profile['location'] ?? 'Accra, Ghana') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline">
                <i class="fas fa-external-link-alt" aria-hidden="true"></i> View on Google Maps
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
