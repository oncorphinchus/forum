<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Profile";
require_once 'includes/header.php';

// Require login
require_login();

// Debug information for admins
if (is_admin() && isset($_GET['debug'])) {
    echo '<div class="alert alert-info">';
    echo '<h4>Debug Information</h4>';
    echo '<p>Upload directory: ' . __DIR__ . '/uploads/avatars/</p>';
    echo '<p>Upload directory exists: ' . (file_exists(__DIR__ . '/uploads/avatars/') ? 'Yes' : 'No') . '</p>';
    echo '<p>Upload directory writable: ' . (is_writable(__DIR__ . '/uploads/avatars/') ? 'Yes' : 'No') . '</p>';
    echo '<p>GD Library installed: ' . (extension_loaded('gd') ? 'Yes' : 'No') . '</p>';
    echo '<p>PHP version: ' . phpversion() . '</p>';
    echo '<p>Max upload size: ' . ini_get('upload_max_filesize') . '</p>';
    echo '<p>Post max size: ' . ini_get('post_max_size') . '</p>';
    echo '</div>';
}

// Get user ID (either from URL or current user)
$profile_user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];

// Get user details
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(DISTINCT t.id) as topic_count,
           COUNT(DISTINCT c.id) as comment_count
    FROM users u
    LEFT JOIN topics t ON u.id = t.user_id
    LEFT JOIN comments c ON u.id = c.user_id
    WHERE u.id = ?
    GROUP BY u.id
");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    set_message('danger', 'User not found.');
    header('Location: index.php');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $profile_user_id === $_SESSION['user_id']) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Invalid request.');
        header('Location: profile.php');
        exit();
    }

    $email = sanitize_input($_POST['email']);
    $bio = sanitize_input($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Check if email is already taken
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $profile_user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email is already taken.";
        }
    }

    // Handle password change if requested
    if (!empty($current_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        } elseif (empty($new_password)) {
            $errors[] = "New password is required.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long.";
        }
    }

    // Handle avatar upload
    $avatar_url = $user['avatar_url'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['avatar']['type'];
        $file_size = $_FILES['avatar']['size'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG and GIF are allowed.";
        } elseif ($file_size > $max_size) {
            $errors[] = "File size exceeds 2MB. Please choose a smaller file.";
        } else {
            // Use absolute path for uploads directory
            $upload_dir = __DIR__ . '/uploads/avatars/';
            
            // Create directory with proper permissions if it doesn't exist
            if (!file_exists($upload_dir)) {
                if (!@mkdir($upload_dir, 0777, true)) {
                    $errors[] = "Failed to create upload directory. Please contact the administrator.";
                }
            }
            
            // Only proceed if no errors
            if (empty($errors)) {
                $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $file_name = uniqid('avatar_') . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;
                
                // For display in HTML, we need a relative path
                $avatar_url_for_db = 'uploads/avatars/' . $file_name;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
                    // Process the image to ensure it's square and properly sized
                    try {
                        list($width, $height) = getimagesize($target_path);
                        
                        // Create a square image
                        $size = min($width, $height);
                        $src_x = ($width - $size) / 2;
                        $src_y = ($height - $size) / 2;
                        
                        // Create new image with proper dimensions
                        $new_image = imagecreatetruecolor(300, 300);
                        
                        // Load source image based on file type
                        switch ($file_type) {
                            case 'image/jpeg':
                                $source = imagecreatefromjpeg($target_path);
                                break;
                            case 'image/png':
                                $source = imagecreatefrompng($target_path);
                                imagealphablending($new_image, false);
                                imagesavealpha($new_image, true);
                                break;
                            case 'image/gif':
                                $source = imagecreatefromgif($target_path);
                                break;
                        }
                        
                        // Resize and crop the image to a square
                        imagecopyresampled(
                            $new_image, $source,
                            0, 0, $src_x, $src_y,
                            300, 300, $size, $size
                        );
                        
                        // Save the processed image
                        switch ($file_type) {
                            case 'image/jpeg':
                                imagejpeg($new_image, $target_path, 90);
                                break;
                            case 'image/png':
                                imagepng($new_image, $target_path, 9);
                                break;
                            case 'image/gif':
                                imagegif($new_image, $target_path);
                                break;
                        }
                        
                        // Free up memory
                        imagedestroy($source);
                        imagedestroy($new_image);
                        
                        $avatar_url = $avatar_url_for_db;
                    } catch (Exception $e) {
                        $errors[] = "Error processing image: " . $e->getMessage();
                    }
                } else {
                    $errors[] = "Error uploading avatar. Please try again.";
                }
            }
        }
    }

    // Update profile if no errors
    if (empty($errors)) {
        if (!empty($new_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                UPDATE users 
                SET email = ?, bio = ?, avatar_url = ?, password = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssssi", $email, $bio, $avatar_url, $password_hash, $profile_user_id);
        } else {
            $stmt = $conn->prepare("
                UPDATE users 
                SET email = ?, bio = ?, avatar_url = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sssi", $email, $bio, $avatar_url, $profile_user_id);
        }

        if ($stmt->execute()) {
            set_message('success', 'Profile updated successfully!');
            header('Location: profile.php');
            exit();
        } else {
            $errors[] = "Error updating profile. Please try again.";
        }
    }
}

// Get recent activity
$stmt = $conn->prepare("
    SELECT 'topic' as type, 
           t.id, 
           t.title, 
           t.created_at,
           c.name as category_name
    FROM topics t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = ?
    UNION ALL
    SELECT 'comment' as type,
           c.topic_id as id,
           t.title,
           c.created_at,
           cat.name as category_name
    FROM comments c
    JOIN topics t ON c.topic_id = t.id
    JOIN categories cat ON t.category_id = cat.id
    WHERE c.user_id = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->bind_param("ii", $profile_user_id, $profile_user_id);
$stmt->execute();
$recent_activity = $stmt->get_result();
?>

<div class="row">
    <div class="col-md-4">
        <div class="card profile-header">
            <div class="card-body text-center">
                <div class="avatar-container mb-4">
                    <?php if ($user['avatar_url']): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" 
                             alt="<?php echo htmlspecialchars($user['username']); ?>'s avatar"
                             onerror="this.onerror=null; this.src='https://via.placeholder.com/150?text=<?php echo strtoupper(substr($user['username'], 0, 1)); ?>';">
                    <?php else: ?>
                        <div class="default-avatar">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                <p class="text-muted">
                    Member since <?php echo format_date($user['created_at']); ?>
                </p>
                <?php if ($user['bio']): ?>
                    <p class="mt-3"><?php echo nl2br(htmlspecialchars($user['bio'] ?? '')); ?></p>
                <?php endif; ?>
                <div class="row mt-4">
                    <div class="col-6">
                        <div class="h4 mb-0"><?php echo $user['topic_count']; ?></div>
                        <small class="text-muted">Topics</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 mb-0"><?php echo $user['comment_count']; ?></div>
                        <small class="text-muted">Comments</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($profile_user_id === $_SESSION['user_id']): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4"><?php 
                                echo htmlspecialchars($user['bio'] ?? ''); 
                            ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="avatar">Avatar</label>
                            <input type="file" class="form-control-file" id="avatar" name="avatar" 
                                   accept="image/jpeg,image/png,image/gif">
                            <small class="text-muted">Max file size: 2MB. JPG, PNG or GIF only.</small>
                            
                            <!-- Avatar preview -->
                            <div class="avatar-preview mt-3" id="avatarPreview">
                                <img id="avatarPreviewImg" src="#" alt="Avatar Preview">
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <small class="text-muted">Required only if changing password</small>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php if ($recent_activity->num_rows > 0): ?>
                    <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-<?php echo $activity['type'] === 'topic' ? 'file-alt' : 'comment'; ?> text-muted mr-2"></i>
                                    <?php if ($activity['type'] === 'topic'): ?>
                                        Created topic 
                                    <?php else: ?>
                                        Commented on 
                                    <?php endif; ?>
                                    <a href="topic.php?id=<?php echo $activity['id']; ?>">
                                        <?php echo htmlspecialchars($activity['title']); ?>
                                    </a>
                                    in <?php echo htmlspecialchars($activity['category_name']); ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo format_date($activity['created_at']); ?>
                                </small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="list-group-item text-center text-muted">
                        No recent activity.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Avatar preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarPreviewImg = document.getElementById('avatarPreviewImg');
    
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size exceeds 2MB. Please choose a smaller file.');
                    this.value = '';
                    avatarPreview.style.display = 'none';
                    return;
                }
                
                // Check file type
                const fileType = file.type;
                const validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validImageTypes.includes(fileType)) {
                    alert('Invalid file type. Only JPG, PNG and GIF are allowed.');
                    this.value = '';
                    avatarPreview.style.display = 'none';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreviewImg.src = e.target.result;
                    avatarPreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                avatarPreview.style.display = 'none';
            }
        });
    }
});
</script>

<?php
// Flush the output buffer
ob_end_flush();
?> 