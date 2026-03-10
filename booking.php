<?php
/**
 * Booking Page — Event booking form with real-time validation
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Generate CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$csrfToken = generateCSRFToken();

$profile = getDJProfile();
$djName  = $profile['name'] ?? SITE_NAME;

$pageTitle = 'Book ' . $djName;
$pageDesc  = 'Submit a booking request for ' . $djName . '. Fill in your event details and we will get back to you shortly.';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero-content">
            <h1 class="page-title"><i class="fas fa-calendar-check" aria-hidden="true"></i> Book <?= e($djName) ?></h1>
            <p class="page-subtitle">Fill in the form below and we'll get back to you within 24 hours</p>
        </div>
    </div>
</section>

<!-- Booking Section -->
<section class="section booking-section" aria-label="Booking form">
    <div class="container">
        <div class="booking-wrapper">

            <!-- Info Sidebar -->
            <aside class="booking-info">
                <div class="info-card glass-card">
                    <h2 class="info-title">
                        <i class="fas fa-info-circle" aria-hidden="true"></i> Booking Info
                    </h2>
                    <ul class="info-list">
                        <li>
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <div>
                                <strong>Response Time</strong>
                                <p>We respond within 24 hours</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                            <div>
                                <strong>Advance Booking</strong>
                                <p>Book at least 2 weeks in advance</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-music" aria-hidden="true"></i>
                            <div>
                                <strong>Music Genres</strong>
                                <p>Afrobeats, Gospel, Hip-Hop, Mixes</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            <div>
                                <strong>Service Area</strong>
                                <p><?= e($profile['location'] ?? 'Accra, Ghana') ?> and surrounding areas</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="info-card glass-card">
                    <h2 class="info-title">
                        <i class="fas fa-headset" aria-hidden="true"></i> Contact Directly
                    </h2>
                    <?php if (!empty($profile['phone'])): ?>
                    <p>
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        <a href="tel:<?= e(preg_replace('/\s+/', '', $profile['phone'])) ?>"><?= e($profile['phone']) ?></a>
                    </p>
                    <?php endif; ?>
                    <?php if (!empty($profile['email'])): ?>
                    <p>
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        <a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a>
                    </p>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Booking Form -->
            <div class="booking-form-wrap">
                <!-- Success Screen (hidden initially) -->
                <div class="booking-success" id="booking-success" style="display:none;" role="alert" aria-live="polite">
                    <div class="success-icon"><i class="fas fa-check-circle" aria-hidden="true"></i></div>
                    <h2>Booking Request Sent!</h2>
                    <p>Thank you for your booking request. We'll review it and get back to you within 24 hours at the email address you provided.</p>
                    <a href="<?= SITE_URL ?>/" class="btn btn-primary">Back to Home</a>
                </div>

                <!-- Error Message -->
                <div class="form-error-banner" id="form-error-banner" style="display:none;" role="alert" aria-live="assertive">
                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                    <span id="form-error-text"></span>
                </div>

                <form id="booking-form" class="booking-form glass-card" novalidate aria-label="Event booking form">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <h2 class="form-title">Event Details</h2>

                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="full_name" class="form-label">
                            Full Name <span class="required" aria-hidden="true">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon" aria-hidden="true"></i>
                            <input type="text" id="full_name" name="full_name" class="form-input"
                                   placeholder="Your full name"
                                   required autocomplete="name"
                                   aria-required="true">
                            <span class="validation-icon" aria-hidden="true"></span>
                        </div>
                        <span class="form-error" id="err-full_name" role="alert"></span>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            Email Address <span class="required" aria-hidden="true">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon" aria-hidden="true"></i>
                            <input type="email" id="email" name="email" class="form-input"
                                   placeholder="your@email.com"
                                   required autocomplete="email"
                                   aria-required="true">
                            <span class="validation-icon" aria-hidden="true"></span>
                        </div>
                        <span class="form-error" id="err-email" role="alert"></span>
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            Phone Number <span class="required" aria-hidden="true">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone input-icon" aria-hidden="true"></i>
                            <input type="tel" id="phone" name="phone" class="form-input"
                                   placeholder="+233 00 000 0000"
                                   required autocomplete="tel"
                                   aria-required="true">
                            <span class="validation-icon" aria-hidden="true"></span>
                        </div>
                        <span class="form-error" id="err-phone" role="alert"></span>
                    </div>

                    <!-- Event Type -->
                    <div class="form-group">
                        <label for="event_type" class="form-label">
                            Event Type <span class="required" aria-hidden="true">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar-star input-icon" aria-hidden="true"></i>
                            <select id="event_type" name="event_type" class="form-input form-select"
                                    required aria-required="true">
                                <option value="">Select event type...</option>
                                <option value="Wedding">Wedding</option>
                                <option value="Birthday Party">Birthday Party</option>
                                <option value="Corporate Event">Corporate Event</option>
                                <option value="Club Night">Club Night</option>
                                <option value="Festival">Festival</option>
                                <option value="Other">Other</option>
                            </select>
                            <span class="validation-icon" aria-hidden="true"></span>
                        </div>
                        <span class="form-error" id="err-event_type" role="alert"></span>
                    </div>

                    <!-- Event Date & Time (side by side) -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_date" class="form-label">
                                Event Date <span class="required" aria-hidden="true">*</span>
                            </label>
                            <div class="input-wrapper">
                                <i class="fas fa-calendar input-icon" aria-hidden="true"></i>
                                <input type="date" id="event_date" name="event_date" class="form-input"
                                       required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                       aria-required="true">
                                <span class="validation-icon" aria-hidden="true"></span>
                            </div>
                            <span class="form-error" id="err-event_date" role="alert"></span>
                        </div>

                        <div class="form-group">
                            <label for="event_time" class="form-label">
                                Event Time <span class="required" aria-hidden="true">*</span>
                            </label>
                            <div class="input-wrapper">
                                <i class="fas fa-clock input-icon" aria-hidden="true"></i>
                                <input type="time" id="event_time" name="event_time" class="form-input"
                                       required aria-required="true">
                                <span class="validation-icon" aria-hidden="true"></span>
                            </div>
                            <span class="form-error" id="err-event_time" role="alert"></span>
                        </div>
                    </div>

                    <!-- Event Location -->
                    <div class="form-group">
                        <label for="event_location" class="form-label">Event Location</label>
                        <div class="input-wrapper">
                            <i class="fas fa-map-marker-alt input-icon" aria-hidden="true"></i>
                            <input type="text" id="event_location" name="event_location" class="form-input"
                                   placeholder="Venue name and address"
                                   autocomplete="street-address">
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="form-group">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea id="notes" name="notes" class="form-input form-textarea"
                                  placeholder="Tell us more about your event, music preferences, special requests..."
                                  rows="4"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary btn-lg btn-full" id="submit-booking">
                            <span class="btn-text">
                                <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                Submit Booking Request
                            </span>
                            <span class="btn-loading" style="display:none;" aria-hidden="true">
                                <i class="fas fa-spinner fa-spin"></i> Sending...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

        </div><!-- /.booking-wrapper -->
    </div>
</section>

<script src="<?= SITE_URL ?>/assets/js/booking.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
