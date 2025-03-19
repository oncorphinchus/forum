<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Contact Us";
require_once 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Invalid request.');
        header('Location: contact.php');
        exit();
    }

    // Get form data
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);

    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }

    if (empty($message)) {
        $errors[] = "Message is required.";
    }

    // If no errors, save message to database and send email notification
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO contact_messages (name, email, subject, message, created_at, user_id) 
            VALUES (?, ?, ?, ?, NOW(), ?)
        ");
        $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
        $stmt->bind_param("ssssi", $name, $email, $subject, $message, $user_id);

        if ($stmt->execute()) {
            // Send email notification to admin (you would need to implement this)
            // mail('admin@example.com', 'New Contact Form Submission', $message);

            set_message('success', 'Your message has been sent! We will get back to you soon.');
            header('Location: contact.php');
            exit();
        } else {
            $errors[] = "Error sending message. Please try again.";
        }
    }
}

// Pre-fill form if user is logged in
$current_user = null;
if (is_logged_in()) {
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $current_user = $stmt->get_result()->fetch_assoc();
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Contact Us</h4>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="contact-info">
                            <h5><i class="fas fa-map-marker-alt text-primary"></i> Address</h5>
                            <p>[Your Address]</p>
                            
                            <h5><i class="fas fa-phone text-primary"></i> Phone</h5>
                            <p>[Your Phone Number]</p>
                            
                            <h5><i class="fas fa-envelope text-primary"></i> Email</h5>
                            <p>[Your Email]</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="contact-hours">
                            <h5><i class="fas fa-clock text-primary"></i> Business Hours</h5>
                            <ul class="list-unstyled">
                                <li>Monday - Friday: 9:00 AM - 5:00 PM</li>
                                <li>Saturday: 10:00 AM - 2:00 PM</li>
                                <li>Sunday: Closed</li>
                            </ul>
                            
                            <h5><i class="fas fa-info-circle text-primary"></i> Support</h5>
                            <p>For immediate assistance, please check our <a href="faq.php">FAQ</a> page.</p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="contact.php" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user text-muted"></i> Your Name
                        </label>
                        <input type="text" class="form-control <?php 
                            echo isset($errors) && empty($name) ? 'is-invalid' : ''; 
                        ?>" id="name" name="name" value="<?php 
                            echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : 
                                 ($current_user ? htmlspecialchars($current_user['username']) : ''); 
                        ?>" required>
                        <div class="invalid-feedback">Please enter your name.</div>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope text-muted"></i> Email Address
                        </label>
                        <input type="email" class="form-control <?php 
                            echo isset($errors) && empty($email) ? 'is-invalid' : ''; 
                        ?>" id="email" name="email" value="<?php 
                            echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : 
                                 ($current_user ? htmlspecialchars($current_user['email']) : ''); 
                        ?>" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <div class="form-group">
                        <label for="subject">
                            <i class="fas fa-heading text-muted"></i> Subject
                        </label>
                        <input type="text" class="form-control <?php 
                            echo isset($errors) && empty($subject) ? 'is-invalid' : ''; 
                        ?>" id="subject" name="subject" value="<?php 
                            echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; 
                        ?>" required>
                        <div class="invalid-feedback">Please enter a subject.</div>
                    </div>

                    <div class="form-group">
                        <label for="message">
                            <i class="fas fa-comment text-muted"></i> Message
                        </label>
                        <textarea class="form-control <?php 
                            echo isset($errors) && empty($message) ? 'is-invalid' : ''; 
                        ?>" id="message" name="message" rows="5" required><?php 
                            echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; 
                        ?></textarea>
                        <div class="invalid-feedback">Please enter your message.</div>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Map Section -->
        <div class="card mt-4">
            <div class="card-body p-0">
                <div class="embed-responsive embed-responsive-16by9">
                    <!-- Replace with your Google Maps embed code -->
                    <iframe class="embed-responsive-item" 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387193.30596698663!2d-74.25987368715491!3d40.69714941932609!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY!5e0!3m2!1sen!2sus!4v1645564756836!5m2!1sen!2sus" 
                            allowfullscreen="" 
                            loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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