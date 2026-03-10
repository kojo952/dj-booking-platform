<?php
/**
 * Admin Bookings Management — List, filter, and manage booking requests
 */

$adminPageTitle = 'Bookings';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

// Status filter
$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['pending', 'approved', 'rejected'], true)
    ? $_GET['status']
    : '';

// Search
$search = trim($_GET['search'] ?? '');

// Build query
$params = [];
$where  = [];

if ($statusFilter) {
    $where[]  = 'status = ?';
    $params[] = $statusFilter;
}
if ($search) {
    $where[]  = '(full_name LIKE ? OR email LIKE ? OR event_type LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings $whereSQL");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Pagination
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage     = ADMIN_ITEMS_PER_PAGE;
$offset      = ($currentPage - 1) * $perPage;
$pagination  = paginate($total, $perPage, $currentPage, '?');

$stmt = $pdo->prepare("SELECT * FROM bookings $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $offset]));
$bookings = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h2>Bookings Management</h2>
    <p class="admin-page-subtitle">Manage all event booking requests</p>
</div>

<!-- Filters -->
<div class="filter-bar">
    <form method="GET" action="" class="filter-form" aria-label="Filter bookings">
        <div class="filter-group">
            <label for="status-filter" class="sr-only">Filter by status</label>
            <select name="status" id="status-filter" class="form-select-sm" onchange="this.form.submit()" aria-label="Status filter">
                <option value="">All Statuses</option>
                <option value="pending"  <?= $statusFilter === 'pending'  ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>

        <div class="filter-group search-group">
            <label for="search-input" class="sr-only">Search bookings</label>
            <input type="text" name="search" id="search-input" class="form-input-sm"
                   placeholder="Search name, email, event type..."
                   value="<?= e($search) ?>">
            <button type="submit" class="btn btn-sm btn-primary" aria-label="Search">
                <i class="fas fa-search" aria-hidden="true"></i>
            </button>
        </div>

        <?php if ($statusFilter || $search): ?>
        <a href="<?= SITE_URL ?>/admin/bookings.php" class="btn btn-sm btn-outline">
            <i class="fas fa-times" aria-hidden="true"></i> Clear
        </a>
        <?php endif; ?>
    </form>

    <div class="filter-results">
        <?= $total ?> booking<?= $total !== 1 ? 's' : '' ?> found
    </div>
</div>

<!-- Bookings Table -->
<?php if (!empty($bookings)): ?>
<div class="table-responsive">
    <table class="admin-table sortable-table" aria-label="Bookings list">
        <thead>
            <tr>
                <th data-sort="id">#</th>
                <th data-sort="name">Name</th>
                <th data-sort="email">Email</th>
                <th data-sort="event_type">Event Type</th>
                <th data-sort="event_date">Event Date</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
            <tr>
                <td><?= (int)$booking['id'] ?></td>
                <td><?= e($booking['full_name']) ?></td>
                <td><a href="mailto:<?= e($booking['email']) ?>"><?= e($booking['email']) ?></a></td>
                <td><?= e($booking['event_type']) ?></td>
                <td><?= e(formatDate($booking['event_date'])) ?></td>
                <td><?= statusBadge($booking['status']) ?></td>
                <td><?= e(formatDate($booking['created_at'], 'M j, Y')) ?></td>
                <td>
                    <div class="table-actions">
                        <a href="<?= SITE_URL ?>/admin/booking-detail.php?id=<?= (int)$booking['id'] ?>"
                           class="btn btn-sm btn-outline"
                           aria-label="View booking #<?= (int)$booking['id'] ?>">
                            <i class="fas fa-eye" aria-hidden="true"></i> View
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($pagination['total_pages'] > 1): ?>
<nav class="admin-pagination" aria-label="Bookings pagination">
    <?php
    $queryParts = [];
    if ($statusFilter) $queryParts[] = 'status=' . urlencode($statusFilter);
    if ($search) $queryParts[] = 'search=' . urlencode($search);
    $baseQuery = $queryParts ? implode('&', $queryParts) . '&' : '';
    ?>
    <?php if ($pagination['has_prev']): ?>
    <a href="?<?= $baseQuery ?>page=<?= $pagination['prev_page'] ?>" class="page-btn">&laquo; Prev</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
    <a href="?<?= $baseQuery ?>page=<?= $i ?>"
       class="page-btn <?= $i === $currentPage ? 'active' : '' ?>"
       <?= $i === $currentPage ? 'aria-current="page"' : '' ?>>
        <?= $i ?>
    </a>
    <?php endfor; ?>
    <?php if ($pagination['has_next']): ?>
    <a href="?<?= $baseQuery ?>page=<?= $pagination['next_page'] ?>" class="page-btn">Next &raquo;</a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="empty-state-admin">
    <i class="fas fa-calendar-times" aria-hidden="true"></i>
    <h3>No bookings found</h3>
    <p><?= $search || $statusFilter ? 'Try adjusting your search or filter.' : 'Booking requests will appear here.' ?></p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
