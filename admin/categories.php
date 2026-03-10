<?php
/**
 * Admin Categories Management
 */

$adminPageTitle = 'Categories';
require_once __DIR__ . '/includes/admin-header.php';

$pdo    = getDBConnection();
$errors = [];
$csrfToken = generateCSRFToken();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Invalid request.';
        redirect(SITE_URL . '/admin/categories.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug        = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));

        if (empty($name)) {
            $errors[] = 'Category name is required.';
        } elseif (empty($slug)) {
            $errors[] = 'Invalid category name.';
        } else {
            // Check for duplicate slug
            $check = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
            $check->execute([$slug]);
            if ($check->fetch()) {
                $errors[] = 'A category with this name already exists.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)');
                $stmt->execute([$name, $slug, $description]);
                $_SESSION['flash_success'] = 'Category "' . $name . '" added!';
                redirect(SITE_URL . '/admin/categories.php');
            }
        }
    } elseif ($action === 'delete') {
        $catId = (int)($_POST['category_id'] ?? 0);
        // Check if category has songs
        $check = $pdo->prepare('SELECT COUNT(*) FROM songs WHERE category_id = ?');
        $check->execute([$catId]);
        if ((int)$check->fetchColumn() > 0) {
            $_SESSION['flash_error'] = 'Cannot delete: category has songs. Reassign songs first.';
        } else {
            $del = $pdo->prepare('DELETE FROM categories WHERE id = ?');
            $del->execute([$catId]);
            $_SESSION['flash_success'] = 'Category deleted.';
        }
        redirect(SITE_URL . '/admin/categories.php');
    }
}

// Get categories with song counts
$stmt = $pdo->query(
    'SELECT c.*, COUNT(s.id) AS song_count
     FROM categories c
     LEFT JOIN songs s ON s.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name ASC'
);
$categories = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h2>Categories</h2>
</div>

<div class="categories-layout">

    <!-- Add Category Form -->
    <div class="form-card admin-card">
        <div class="card-header">
            <h3>Add New Category</h3>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($errors as $err): ?>
            <p><?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" aria-label="Add category form">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label for="name" class="form-label">Category Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-input"
                       required placeholder="e.g. Afrobeats"
                       value="<?= e($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-input form-textarea"
                          rows="3" placeholder="Optional category description"><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus" aria-hidden="true"></i> Add Category
            </button>
        </form>
    </div>

    <!-- Categories List -->
    <div class="admin-card">
        <div class="card-header">
            <h3>All Categories (<?= count($categories) ?>)</h3>
        </div>

        <?php if (!empty($categories)): ?>
        <div class="table-responsive">
            <table class="admin-table" aria-label="Categories list">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Songs</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><strong><?= e($cat['name']) ?></strong></td>
                        <td><code><?= e($cat['slug']) ?></code></td>
                        <td><?= e(truncate($cat['description'], 60)) ?: '—' ?></td>
                        <td><?= (int)$cat['song_count'] ?></td>
                        <td>
                            <?php if ((int)$cat['song_count'] === 0): ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                                <button type="button" class="btn btn-sm btn-danger confirm-delete-inline"
                                        data-name="<?= e($cat['name']) ?>"
                                        aria-label="Delete category <?= e($cat['name']) ?>">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted" title="Has songs — cannot delete">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-table">
            <p>No categories yet. Add one above.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
