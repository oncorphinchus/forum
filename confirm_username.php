<?php
/**
 * Username Confirmation Page
 * 
 * This page allows new social login users to confirm or change their auto-generated username
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once 'config.php';
require_once 'includes/functions.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    redirect('login.php', 'You must be logged in to access this page.');
}

// Get current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // User not found, clear session and redirect to login
    session_destroy();
    redirect('login.php', 'User not found. Please log in again.');
}

$user = $result->fetch_assoc();

// Process form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_username'])) {
    // Validate username
    $username = trim($_POST['username']);
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be between 3 and 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    } else {
        // Check if username already exists (excluding current user)
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'This username is already taken. Please choose another one.';
        }
    }
    
    // If no errors, update username and redirect to home
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $username, $_SESSION['user_id']);
        $stmt->execute();
        
        redirect('index.php', 'Your username has been updated successfully!');
    }
}

// Set page title and include header
$page_title = "Confirm Username";
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">Welcome to the Forum!</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($_SESSION['message'])): ?>
                        <div class="alert alert-info">
                            <?php 
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <p>Thanks for joining our community! Please confirm or update your username below.</p>
                    
                    <?php if (!empty($user['avatar_url'])): ?>
                        <div class="text-center mb-3">
                            <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Profile avatar" class="img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            <div class="form-text">Your username will be visible to other users. You can change it now or keep the suggested one.</div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="confirm_username" class="btn btn-primary">Confirm Username</button>
                            <a href="index.php" class="btn btn-outline-secondary">Skip for Now</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
// Helper function to redirect with message
function redirect($url, $message = null) {
    if ($message) {
        $_SESSION['message'] = $message;
    }
    header('Location: ' . $url);
    exit;
}
?> 