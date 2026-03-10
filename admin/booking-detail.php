<?php
/**
 * Admin Booking Detail — View full booking and update status
 */

$adminPageTitle = 'Booking Detail';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

$bookingId = (int)($_GET['id'] ?? 0);
if (!$bookingId) {
    $_SESSION['flash_error'] = 'Invalid booking ID.';
    redirect(SITE_URL . '/admin/bookings.php');
}

$stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['flash_error'] = 'Booking not found.';
    redirect(SITE_URL . '/admin/bookings.php');
}

// Handle status update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Invalid CSRF token.';
        redirect(SITE_URL . '/admin/booking-detail.php?id=' . $bookingId);
    }

    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, ['pending', 'approved', 'rejected'], true)) {
        $upd = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $upd->execute([$newStatus, $bookingId]);
        $_SESSION['flash_success'] = 'Booking status updated to ' . ucfirst($newStatus) . '.';
    } else {
        $_SESSION['flash_error'] = 'Invalid status value.';
    }
    redirect(SITE_URL . '/admin/booking-detail.php?id=' . $bookingId);
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Invalid CSRF token.';
        redirect(SITE_URL . '/admin/booking-detail.php?id=' . $bookingId);
    }
    $del = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
    $del->execute([$bookingId]);
    $_SESSION['flash_success'] = 'Booking #' . $bookingId . ' deleted.';
    redirect(SITE_URL . '/admin/bookings.php');
}

$csrfToken = generateCSRFToken();
?>

<div class="admin-page-header">
    <h2>Booking #<?= (int)$booking['id'] ?></h2>
    <a href="<?= SITE_URL ?>/admin/bookings.php" class="btn btn-sm btn-outline">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Bookings
    </a>
</div>

<div class="booking-detail-grid">

    <!-- Booking Details -->
    <div class="detail-card admin-card">
        <div class="card-header">
            <h3><i class="fas fa-user" aria-hidden="true"></i> Client Information</h3>
            <?= statusBadge($booking['status']) ?>
        </div>

        <dl class="detail-list">
            <dt>Full Name</dt>
            <dd><?= e($booking['full_name']) ?></dd>

            <dt>Email</dt>
            <dd><a href="mailto:<?= e($booking['email']) ?>"><?= e($booking['email']) ?></a></dd>

            <dt>Phone</dt>
            <dd><a href="tel:<?= e(preg_replace('/\s+/', '', $booking['phone'])) ?>"><?= e($booking['phone']) ?></a></dd>
        </dl>
    </div>

    <div class="detail-card admin-card">
        <div class="card-header">
            <h3><i class="fas fa-calendar" aria-hidden="true"></i> Event Details</h3>
        </div>

        <dl class="detail-list">
            <dt>Event Type</dt>
            <dd><?= e($booking['event_type']) ?></dd>

            <dt>Event Date</dt>
            <dd><?= e(formatDate($booking['event_date'], 'l, F j, Y')) ?></dd>

            <dt>Event Time</dt>
            <dd><?= e(date('g:i A', strtotime($booking['event_time']))) ?></dd>

            <dt>Location</dt>
            <dd><?= e($booking['event_location'] ?: '—') ?></dd>

            <dt>Submitted</dt>
            <dd><?= e(formatDate($booking['created_at'], 'M j, Y \a\t g:i A')) ?></dd>
        </dl>
    </div>

    <!-- Notes -->
    <?php if (!empty($booking['notes'])): ?>
    <div class="detail-card admin-card detail-full">
        <div class="card-header">
            <h3><i class="fas fa-sticky-note" aria-hidden="true"></i> Additional Notes</h3>
        </div>
        <p class="booking-notes"><?= nl2br(e($booking['notes'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Status Update -->
    <div class="detail-card admin-card">
        <div class="card-header">
            <h3><i class="fas fa-edit" aria-hidden="true"></i> Update Status</h3>
        </div>
        <form method="POST" action="" id="status-form">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <div class="form-group">
                <label for="status" class="form-label">Booking Status</label>
                <select name="status" id="status" class="form-select" aria-label="Booking status">
                    <option value="pending"  <?= $booking['status'] === 'pending'  ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $booking['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $booking['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save" aria-hidden="true"></i> Save Status
            </button>
        </form>
    </div>

    <!-- Delete Booking -->
    <div class="detail-card admin-card danger-card">
        <div class="card-header">
            <h3><i class="fas fa-trash" aria-hidden="true"></i> Danger Zone</h3>
        </div>
        <p>Permanently delete this booking. This action cannot be undone.</p>
        <form method="POST" action="" id="delete-booking-form">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <button type="button" class="btn btn-danger confirm-delete" data-target="delete-booking-form" data-name="Booking #<?= (int)$booking['id'] ?>">
                <i class="fas fa-trash" aria-hidden="true"></i> Delete Booking
            </button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
