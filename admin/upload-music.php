<?php
/**
 * Upload Music — Form to upload a new song
 */

$adminPageTitle = 'Upload Song';
require_once __DIR__ . '/includes/admin-header.php';

$pdo        = getDBConnection();
$categories = getCategories();
$errors     = [];
$success    = false;
$csrfToken  = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $title         = trim($_POST['title'] ?? '');
        $artist        = trim($_POST['artist'] ?? '');
        $categoryId    = (int)($_POST['category_id'] ?? 0);
        $allowDownload = isset($_POST['allow_download']) ? 1 : 0;

        // Validate required fields
        if (empty($title)) {
            $errors[] = 'Song title is required.';
        }
        if (!$categoryId) {
            $errors[] = 'Please select a category.';
        }

        // Validate audio file
        if (empty($_FILES['audio_file']['name'])) {
            $errors[] = 'Please select an audio file (MP3 or WAV).';
        } elseif ($_FILES['audio_file']['size'] > MAX_UPLOAD_SIZE) {
            $errors[] = 'Audio file exceeds the maximum allowed size of ' . formatFileSize(MAX_UPLOAD_SIZE) . '.';
        } elseif (!validateFileMime($_FILES['audio_file']['tmp_name'], ALLOWED_AUDIO_TYPES)) {
            $errors[] = 'Invalid audio file type. Only MP3 and WAV files are allowed.';
        }

        // Validate cover image (optional)
        $coverFilename = '';
        if (!empty($_FILES['cover_image']['name'])) {
            if ($_FILES['cover_image']['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Cover image must be under 5MB.';
            } elseif (!validateFileMime($_FILES['cover_image']['tmp_name'], ALLOWED_IMAGE_TYPES)) {
                $errors[] = 'Invalid cover image type. Allowed: JPG, PNG, GIF, WebP.';
            }
        }

        if (empty($errors)) {
            // Save audio file
            $audioExt      = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));
            $audioFilename = generateFilename($audioExt);
            $audioPath     = MUSIC_UPLOAD_PATH . $audioFilename;

            if (!move_uploaded_file($_FILES['audio_file']['tmp_name'], $audioPath)) {
                $errors[] = 'Failed to save audio file. Check server permissions.';
            } else {
                // Save cover image if provided
                if (!empty($_FILES['cover_image']['name']) && empty($errors)) {
                    $imgExt        = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
                    $coverFilename = generateFilename($imgExt);
                    $coverPath     = MUSIC_UPLOAD_PATH . $coverFilename;
                    if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverPath)) {
                        $coverFilename = '';
                    }
                }

                // Insert into DB
                $stmt = $pdo->prepare(
                    'INSERT INTO songs (title, artist, category_id, filename, file_path, cover_image, allow_download)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $title,
                    $artist,
                    $categoryId,
                    $audioFilename,
                    $audioPath,
                    $coverFilename,
                    $allowDownload,
                ]);

                $_SESSION['flash_success'] = 'Song "' . $title . '" uploaded successfully!';
                redirect(SITE_URL . '/admin/music.php');
            }
        }
    }
}
?>

<div class="admin-page-header">
    <h2>Upload New Song</h2>
    <a href="<?= SITE_URL ?>/admin/music.php" class="btn btn-sm btn-outline">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Songs
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" role="alert" aria-live="assertive">
    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
    <ul>
        <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="form-card admin-card">
    <form method="POST" action="" enctype="multipart/form-data" id="upload-form" aria-label="Upload song form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <!-- Title -->
            <div class="form-group">
                <label for="title" class="form-label">
                    Song Title <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="text" id="title" name="title" class="form-input"
                       required placeholder="Enter song title"
                       value="<?= e($_POST['title'] ?? '') ?>">
            </div>

            <!-- Artist -->
            <div class="form-group">
                <label for="artist" class="form-label">Artist Name</label>
                <input type="text" id="artist" name="artist" class="form-input"
                       placeholder="Artist or DJ name"
                       value="<?= e($_POST['artist'] ?? '') ?>">
            </div>

            <!-- Category -->
            <div class="form-group">
                <label for="category_id" class="form-label">
                    Category <span class="required" aria-hidden="true">*</span>
                </label>
                <select id="category_id" name="category_id" class="form-select" required aria-required="true">
                    <option value="">Select category...</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"
                            <?= (int)($_POST['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Allow Download -->
            <div class="form-group">
                <label class="form-label">Options</label>
                <label class="checkbox-label">
                    <input type="checkbox" name="allow_download" value="1"
                           <?= !empty($_POST['allow_download']) ? 'checked' : '' ?>>
                    Allow users to download this song
                </label>
            </div>

            <!-- Audio File -->
            <div class="form-group form-full">
                <label for="audio_file" class="form-label">
                    Audio File (MP3 / WAV) <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="file-upload-area" id="audio-drop-area">
                    <input type="file" id="audio_file" name="audio_file"
                           class="file-input" accept=".mp3,.wav,audio/mpeg,audio/wav"
                           required aria-required="true">
                    <label for="audio_file" class="file-upload-label">
                        <i class="fas fa-file-audio" aria-hidden="true"></i>
                        <span class="file-upload-text">Click to select or drag & drop audio file</span>
                        <span class="file-upload-hint">MP3 or WAV · Max 50MB</span>
                    </label>
                    <p class="file-selected" id="audio-file-name" aria-live="polite"></p>
                </div>
            </div>

            <!-- Cover Image -->
            <div class="form-group form-full">
                <label for="cover_image" class="form-label">Cover Image (optional)</label>
                <div class="file-upload-area">
                    <input type="file" id="cover_image" name="cover_image"
                           class="file-input" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
                    <label for="cover_image" class="file-upload-label">
                        <i class="fas fa-image" aria-hidden="true"></i>
                        <span class="file-upload-text">Click to select cover image</span>
                        <span class="file-upload-hint">JPG, PNG, GIF, WebP · Max 5MB</span>
                    </label>
                    <div class="image-preview-wrap" id="cover-preview-wrap" style="display:none;">
                        <img id="cover-preview" src="" alt="Cover image preview" width="150" height="150" style="border-radius:8px;object-fit:cover;">
                    </div>
                </div>
            </div>
        </div><!-- /.form-grid -->

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg" id="submit-upload">
                <i class="fas fa-upload" aria-hidden="true"></i> Upload Song
            </button>
            <a href="<?= SITE_URL ?>/admin/music.php" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
