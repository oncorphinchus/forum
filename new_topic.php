<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Create New Topic";
require_once 'includes/header.php';

// Require login
require_login();

// Get categories for the dropdown
$stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Invalid request.');
        header('Location: index.php');
        exit();
    }

    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);
    $category_id = (int)$_POST['category_id'];

    // Validate input
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required.";
    } elseif (strlen($title) > 255) {
        $errors[] = "Title must be less than 255 characters.";
    }

    if (empty($content)) {
        $errors[] = "Content is required.";
    }

    if (empty($category_id)) {
        $errors[] = "Category is required.";
    }

    // If no errors, create the topic
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO topics (title, content, category_id, user_id, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssii", $title, $content, $category_id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $topic_id = $conn->insert_id;
            set_message('success', 'Topic created successfully!');
            header("Location: topic.php?id=" . $topic_id);
            exit();
        } else {
            $errors[] = "Error creating topic. Please try again.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create New Topic</h5>
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

                <form method="POST" action="new_topic.php">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select class="form-control" id="category" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['id']; ?>"
                                    <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                               maxlength="255" required>
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php 
                            echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; 
                        ?></textarea>
                        <small class="text-muted">
                            You can use Markdown formatting in your content.
                        </small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Topic
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'includes/footer.php'; 

// Flush the output buffer
ob_end_flush();
?> 