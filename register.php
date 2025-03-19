<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Register";
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Invalid request.');
        header('Location: register.php');
        exit();
    }

    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    $errors = [];
    
    // Username validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be between 3 and 20 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password) || 
              !preg_match('/[a-z]/', $password) || 
              !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter, one lowercase letter, and one number.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Username is already taken.";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email is already registered.";
    }

    // If no errors, create user
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$username, $email, $hashed_password]);
        
        // Get the new user ID
        $user_id = $conn->lastInsertId();
        
        // Log the user in
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        
        // Create a session token
        $token = bin2hex(random_bytes(32));
        $stmt = $conn->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
        
        // Set message and redirect
        set_message('success', 'Registration successful! Welcome to our forum.');
        header('Location: index.php');
        exit();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header text-center bg-primary text-white">
                <h4 class="mb-0">Create an Account</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user text-muted"></i> Username
                        </label>
                        <input type="text" class="form-control form-control-lg <?php 
                            echo isset($errors) && empty($username) ? 'is-invalid' : ''; 
                        ?>" id="username" name="username" value="<?php 
                            echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; 
                        ?>" required pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="20" autofocus>
                        <small class="form-text text-muted">
                            3-20 characters, letters, numbers, and underscores only
                        </small>
                        <div class="invalid-feedback">
                            Please choose a valid username.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope text-muted"></i> Email
                        </label>
                        <input type="email" class="form-control form-control-lg <?php 
                            echo isset($errors) && empty($email) ? 'is-invalid' : ''; 
                        ?>" id="email" name="email" value="<?php 
                            echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; 
                        ?>" required>
                        <div class="invalid-feedback">
                            Please enter a valid email address.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock text-muted"></i> Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg <?php 
                                echo isset($errors) && empty($password) ? 'is-invalid' : ''; 
                            ?>" id="password" name="password" required minlength="8">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Password must be at least 8 characters long.
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Minimum 8 characters, must include uppercase, lowercase, and numbers
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock text-muted"></i> Confirm Password
                        </label>
                        <input type="password" class="form-control form-control-lg <?php 
                            echo isset($errors) && empty($confirm_password) ? 'is-invalid' : ''; 
                        ?>" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">
                            Passwords must match.
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </div>
                </form>
                
                <!-- Social Login Section -->
                <div class="mt-4">
                    <div class="text-center mb-3">
                        <p class="mb-0">Or create an account with:</p>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <a href="oauth_login.php?provider=google" class="btn btn-outline-danger btn-block mb-2">
                                <i class="fab fa-google"></i> Google
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="oauth_login.php?provider=facebook" class="btn btn-outline-primary btn-block mb-2">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="oauth_login.php?provider=github" class="btn btn-outline-dark btn-block">
                                <i class="fab fa-github"></i> GitHub
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="oauth_login.php?provider=apple" class="btn btn-outline-secondary btn-block">
                                <i class="fab fa-apple"></i> Apple
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center bg-light">
                <p class="mb-0">
                    Already have an account? 
                    <a href="login.php" class="font-weight-bold">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Password match validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password');
    if (this.value !== password.value) {
        this.setCustomValidity("Passwords don't match");
    } else {
        this.setCustomValidity('');
    }
});

// Bootstrap form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php 
require_once 'includes/footer.php'; 

// Flush the output buffer
ob_end_flush();
?>
