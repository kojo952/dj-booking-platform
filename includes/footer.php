<?php
/**
 * Public Site Footer
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

$profile = getDJProfile();
$djName  = $profile['name'] ?? SITE_NAME;
$year    = date('Y');
?>
</main><!-- /#main-content -->

<!-- Footer -->
<footer class="site-footer" role="contentinfo">
    <div class="footer-wave" aria-hidden="true">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" fill="currentColor"></path>
            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" fill="currentColor"></path>
            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="currentColor"></path>
        </svg>
    </div>

    <div class="footer-content">
        <div class="container">
            <div class="footer-grid">

                <!-- Brand Column -->
                <div class="footer-col footer-brand">
                    <a href="<?= SITE_URL ?>/" class="footer-logo" aria-label="Home">
                        <i class="fas fa-headphones-alt" aria-hidden="true"></i>
                        <span><?= e($djName) ?></span>
                    </a>
                    <p class="footer-tagline"><?= e(SITE_TAGLINE) ?></p>
                    <!-- Social Links -->
                    <div class="social-links" aria-label="Social media links">
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
                </div>

                <!-- Quick Links -->
                <nav class="footer-col footer-nav" aria-label="Footer navigation">
                    <h3 class="footer-heading">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?= SITE_URL ?>/">Home</a></li>
                        <li><a href="<?= SITE_URL ?>/music.php">Music</a></li>
                        <li><a href="<?= SITE_URL ?>/videos.php">Videos</a></li>
                        <li><a href="<?= SITE_URL ?>/about.php">About</a></li>
                        <li><a href="<?= SITE_URL ?>/booking.php">Book Now</a></li>
                    </ul>
                </nav>

                <!-- Contact Column -->
                <div class="footer-col footer-contact">
                    <h3 class="footer-heading">Contact</h3>
                    <ul class="footer-contact-list">
                        <?php if (!empty($profile['phone'])): ?>
                        <li>
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            <a href="tel:<?= e(preg_replace('/\s+/', '', $profile['phone'])) ?>"><?= e($profile['phone']) ?></a>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($profile['email'])): ?>
                        <li>
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($profile['location'])): ?>
                        <li>
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            <span><?= e($profile['location']) ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

            </div><!-- /.footer-grid -->
        </div><!-- /.container -->
    </div><!-- /.footer-content -->

    <!-- Bottom Bar -->
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= $year ?> <?= e($djName) ?>. All rights reserved.</p>
            <p class="footer-credit">Built with <i class="fas fa-heart" aria-hidden="true" style="color:#e94560;"></i> for music lovers</p>
        </div>
    </div>
</footer>

<!-- Toast Notification Container -->
<div id="toast-container" aria-live="polite" aria-atomic="true"></div>

<!-- JavaScript -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<script src="<?= SITE_URL ?>/assets/js/player.js"></script>
</body>
</html>
