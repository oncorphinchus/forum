<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Login";
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Invalid request.');
        header('Location: login.php');
        exit();
    }

    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    // Validate input
    $errors = [];
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        // Get user from database
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Handle remember me
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $stmt = $conn->prepare("
                    INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("isss", $user['id'], $token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                $stmt->execute();

                setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
            }

            set_message('success', 'Welcome back, ' . htmlspecialchars($user['username']) . '!');
            header('Location: index.php');
            exit();
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header text-center bg-primary text-white">
                <h4 class="mb-0">Welcome Back!</h4>
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

                <?php if (!empty($_SESSION['message'])): ?>
                    <div class="alert alert-info">
                        <?php 
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user text-muted"></i> Username
                        </label>
                        <input type="text" class="form-control form-control-lg <?php 
                            echo isset($errors) && empty($username) ? 'is-invalid' : ''; 
                        ?>" id="username" name="username" value="<?php 
                            echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; 
                        ?>" required autofocus>
                        <div class="invalid-feedback">Please enter your username.</div>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock text-muted"></i> Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg <?php 
                                echo isset($errors) && empty($password) ? 'is-invalid' : ''; 
                            ?>" id="password" name="password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="remember_me" 
                                   name="remember_me" <?php echo isset($_POST['remember_me']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="remember_me">Remember me</label>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                
                <!-- Social Login Section -->
                <div class="mt-4">
                    <div class="text-center mb-3">
                        <p class="mb-0">Or sign in with:</p>
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
                    Don't have an account? 
                    <a href="register.php" class="font-weight-bold">Register</a>
                </p>
            </div>
        </div>

        <!-- Password Reset Link -->
        <div class="text-center mt-3">
            <a href="forgot_password.php" class="text-muted">
                <i class="fas fa-key"></i> Forgot your password?
            </a>
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
