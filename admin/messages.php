<?php
/**
 * Admin Messages — View visitor contact/inquiry messages
 */

$adminPageTitle = 'Messages';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();
$csrfToken = generateCSRFToken();

// Handle mark-as-read / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Invalid request.';
        redirect(SITE_URL . '/admin/messages.php');
    }

    $action    = $_POST['action'] ?? '';
    $messageId = (int)($_POST['message_id'] ?? 0);

    if ($action === 'mark_read' && $messageId) {
        $stmt = $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?');
        $stmt->execute([$messageId]);
        $_SESSION['flash_success'] = 'Message marked as read.';
    } elseif ($action === 'delete' && $messageId) {
        $stmt = $pdo->prepare('DELETE FROM messages WHERE id = ?');
        $stmt->execute([$messageId]);
        $_SESSION['flash_success'] = 'Message deleted.';
    }
    redirect(SITE_URL . '/admin/messages.php');
}

// Filter: unread only
$unreadOnly = isset($_GET['filter']) && $_GET['filter'] === 'unread';
$where      = $unreadOnly ? 'WHERE is_read = 0' : '';

$stmt     = $pdo->query("SELECT * FROM messages $where ORDER BY created_at DESC");
$messages = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h2>Messages & Inquiries</h2>
    <div class="filter-tabs-small">
        <a href="<?= SITE_URL ?>/admin/messages.php" class="filter-tab-sm <?= !$unreadOnly ? 'active' : '' ?>">All</a>
        <a href="<?= SITE_URL ?>/admin/messages.php?filter=unread" class="filter-tab-sm <?= $unreadOnly ? 'active' : '' ?>">Unread</a>
    </div>
</div>

<?php if (!empty($messages)): ?>
<div class="messages-list">
    <?php foreach ($messages as $msg): ?>
    <div class="message-item admin-card <?= !$msg['is_read'] ? 'message-unread' : '' ?>">
        <div class="message-header">
            <div class="message-sender">
                <div class="sender-avatar" aria-hidden="true"><i class="fas fa-user"></i></div>
                <div>
                    <strong><?= e($msg['name']) ?></strong>
                    <a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a>
                </div>
            </div>
            <div class="message-meta">
                <span class="message-date"><?= e(formatDate($msg['created_at'], 'M j, Y g:i A')) ?></span>
                <?php if (!$msg['is_read']): ?>
                <span class="badge badge-pending">Unread</span>
                <?php else: ?>
                <span class="badge badge-approved">Read</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($msg['subject'])): ?>
        <p class="message-subject"><strong><?= e($msg['subject']) ?></strong></p>
        <?php endif; ?>

        <p class="message-body"><?= nl2br(e($msg['message'])) ?></p>

        <div class="message-actions">
            <?php if (!$msg['is_read']): ?>
            <form method="POST" action="" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="mark_read">
                <input type="hidden" name="message_id" value="<?= (int)$msg['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline">
                    <i class="fas fa-check" aria-hidden="true"></i> Mark Read
                </button>
            </form>
            <?php endif; ?>
            <a href="mailto:<?= e($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject'] ?: 'Your inquiry') ?>" class="btn btn-sm btn-outline">
                <i class="fas fa-reply" aria-hidden="true"></i> Reply
            </a>
            <form method="POST" action="" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="message_id" value="<?= (int)$msg['id'] ?>">
                <button type="button" class="btn btn-sm btn-danger confirm-delete-inline"
                        data-name="message from <?= e($msg['name']) ?>">
                    <i class="fas fa-trash" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state-admin">
    <i class="fas fa-envelope-open" aria-hidden="true"></i>
    <h3><?= $unreadOnly ? 'No unread messages' : 'No messages yet' ?></h3>
    <p>Messages from visitors will appear here.</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
