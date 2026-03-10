<?php
/**
 * Admin DJ Profile Editor
 */

$adminPageTitle = 'DJ Profile';
require_once __DIR__ . '/includes/admin-header.php';

$pdo    = getDBConnection();
$errors = [];
$csrfToken = generateCSRFToken();

// Load existing profile
$stmt    = $pdo->query('SELECT * FROM dj_profile LIMIT 1');
$profile = $stmt->fetch();

if (!$profile) {
    // No profile row — create one
    $pdo->exec('INSERT INTO dj_profile (name, bio) VALUES ("", "")');
    $stmt    = $pdo->query('SELECT * FROM dj_profile LIMIT 1');
    $profile = $stmt->fetch();
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Invalid CSRF token.';
        redirect(SITE_URL . '/admin/profile.php');
    }

    $name               = trim($_POST['name'] ?? '');
    $bio                = trim($_POST['bio'] ?? '');
    $location           = trim($_POST['location'] ?? '');
    $phone              = trim($_POST['phone'] ?? '');
    $email              = trim($_POST['email'] ?? '');
    $facebook           = trim($_POST['facebook'] ?? '');
    $instagram          = trim($_POST['instagram'] ?? '');
    $twitter            = trim($_POST['twitter'] ?? '');
    $youtubeChannelUrl  = trim($_POST['youtube_channel_url'] ?? '');

    if (empty($name)) {
        $errors[] = 'DJ name is required.';
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    // Profile image upload
    $profileImage = $profile['profile_image'];
    if (!empty($_FILES['profile_image']['name'])) {
        if ($_FILES['profile_image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Profile image must be under 5MB.';
        } elseif (!validateFileMime($_FILES['profile_image']['tmp_name'], ALLOWED_IMAGE_TYPES)) {
            $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WebP.';
        } else {
            $imgExt       = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $imgFilename  = generateFilename($imgExt);
            $imgPath      = PROFILE_UPLOAD_PATH . $imgFilename;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $imgPath)) {
                // Delete old profile image
                if (!empty($profile['profile_image'])) {
                    $oldPath = PROFILE_UPLOAD_PATH . $profile['profile_image'];
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $profileImage = $imgFilename;
            } else {
                $errors[] = 'Failed to upload profile image.';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'UPDATE dj_profile SET name=?, bio=?, location=?, phone=?, email=?, facebook=?, instagram=?, twitter=?, youtube_channel_url=?, profile_image=? WHERE id=?'
        );
        $stmt->execute([
            $name, $bio, $location, $phone, $email,
            $facebook, $instagram, $twitter, $youtubeChannelUrl,
            $profileImage, $profile['id']
        ]);
        $_SESSION['flash_success'] = 'Profile updated successfully!';
        redirect(SITE_URL . '/admin/profile.php');
    }
}
?>

<div class="admin-page-header">
    <h2>DJ Profile</h2>
    <a href="<?= SITE_URL ?>/about.php" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline">
        <i class="fas fa-external-link-alt" aria-hidden="true"></i> View Public Profile
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" role="alert" aria-live="assertive">
    <ul>
        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="form-card admin-card">
    <form method="POST" action="" enctype="multipart/form-data" aria-label="DJ profile form">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <!-- Profile Image -->
            <div class="form-group form-full profile-image-section">
                <label class="form-label">Profile Photo</label>
                <div class="profile-upload-area">
                    <?php if (!empty($profile['profile_image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/profile/<?= e($profile['profile_image']) ?>"
                             alt="Current profile photo"
                             id="profile-img-preview"
                             class="profile-preview-img"
                             width="150" height="150">
                    <?php else: ?>
                        <div class="profile-preview-placeholder" id="profile-img-preview-placeholder" aria-hidden="true">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <img id="profile-img-preview" src="" alt="Profile photo preview" style="display:none;" width="150" height="150">
                    <?php endif; ?>
                    <div>
                        <input type="file" id="profile_image" name="profile_image"
                               class="file-input" accept="image/*">
                        <label for="profile_image" class="btn btn-outline">
                            <i class="fas fa-camera" aria-hidden="true"></i> Change Photo
                        </label>
                        <p class="form-hint">JPG, PNG, WebP · Max 5MB · Recommended: 500×500px</p>
                    </div>
                </div>
            </div>

            <!-- Name -->
            <div class="form-group">
                <label for="name" class="form-label">DJ Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-input"
                       required value="<?= e($profile['name']) ?>">
            </div>

            <!-- Location -->
            <div class="form-group">
                <label for="location" class="form-label">Location</label>
                <input type="text" id="location" name="location" class="form-input"
                       placeholder="City, Country"
                       value="<?= e($profile['location']) ?>">
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-input"
                       value="<?= e($profile['phone']) ?>">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Contact Email</label>
                <input type="email" id="email" name="email" class="form-input"
                       value="<?= e($profile['email']) ?>">
            </div>

            <!-- Bio -->
            <div class="form-group form-full">
                <label for="bio" class="form-label">Bio / About</label>
                <textarea id="bio" name="bio" class="form-input form-textarea"
                          rows="6" placeholder="Tell visitors about yourself..."><?= e($profile['bio']) ?></textarea>
            </div>

            <!-- Social Links -->
            <div class="form-section-title form-full">
                <h3><i class="fas fa-share-alt" aria-hidden="true"></i> Social Media Links</h3>
            </div>

            <div class="form-group">
                <label for="facebook" class="form-label"><i class="fab fa-facebook" aria-hidden="true"></i> Facebook URL</label>
                <input type="url" id="facebook" name="facebook" class="form-input"
                       placeholder="https://facebook.com/yourpage"
                       value="<?= e($profile['facebook']) ?>">
            </div>

            <div class="form-group">
                <label for="instagram" class="form-label"><i class="fab fa-instagram" aria-hidden="true"></i> Instagram URL</label>
                <input type="url" id="instagram" name="instagram" class="form-input"
                       placeholder="https://instagram.com/yourhandle"
                       value="<?= e($profile['instagram']) ?>">
            </div>

            <div class="form-group">
                <label for="twitter" class="form-label"><i class="fab fa-x-twitter" aria-hidden="true"></i> Twitter / X URL</label>
                <input type="url" id="twitter" name="twitter" class="form-input"
                       placeholder="https://twitter.com/yourhandle"
                       value="<?= e($profile['twitter']) ?>">
            </div>

            <div class="form-group">
                <label for="youtube_channel_url" class="form-label"><i class="fab fa-youtube" aria-hidden="true"></i> YouTube Channel URL</label>
                <input type="url" id="youtube_channel_url" name="youtube_channel_url" class="form-input"
                       placeholder="https://youtube.com/@yourchannel"
                       value="<?= e($profile['youtube_channel_url']) ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save" aria-hidden="true"></i> Save Profile
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
