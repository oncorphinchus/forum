<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

require_once 'includes/header.php';

// Require login
require_login();

// Get topic ID
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$topic_id) {
    set_message('danger', 'Invalid topic ID.');
    header('Location: index.php');
    exit();
}

// Get topic details
$stmt = $conn->prepare("
    SELECT t.*, 
           u.username,
           c.name as category_name,
           c.id as category_id
    FROM topics t
    JOIN users u ON t.user_id = u.id
    JOIN categories c ON t.category_id = c.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$topic = $stmt->get_result()->fetch_assoc();

if (!$topic) {
    set_message('danger', 'Topic not found.');
    header('Location: index.php');
    exit();
}

// Track view using the new system
track_topic_view($topic_id);

// Get comments
$stmt = $conn->prepare("
    SELECT c.*, u.username, u.avatar_url
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.topic_id = ?
    ORDER BY c.created_at ASC
");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$comments = $stmt->get_result();

// Get unique viewers count
$unique_viewers = get_topic_unique_viewers($topic_id);

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Invalid request.');
        header('Location: topic.php?id=' . $topic_id);
        exit();
    }

    $content = sanitize_input($_POST['content']);
    
    if (!empty($content)) {
        $stmt = $conn->prepare("
            INSERT INTO comments (topic_id, user_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param("iis", $topic_id, $_SESSION['user_id'], $content);
        
        if ($stmt->execute()) {
            set_message('success', 'Comment added successfully!');
            header('Location: topic.php?id=' . $topic_id . '#comment-' . $conn->insert_id);
            exit();
        } else {
            set_message('danger', 'Error adding comment. Please try again.');
        }
    }
}

$page_title = $topic['title'];
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item">
                    <a href="category.php?id=<?php echo $topic['category_id']; ?>">
                        <?php echo htmlspecialchars($topic['category_name']); ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($topic['title']); ?>
                </li>
            </ol>
        </nav>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?php echo htmlspecialchars($topic['title']); ?></h4>
                <?php if ($topic['user_id'] === $_SESSION['user_id'] || is_admin()): ?>
                    <div class="btn-group">
                        <a href="edit_topic.php?id=<?php echo $topic_id; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <?php if (is_admin()): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteTopicModal">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="topic-meta mb-3">
                    <small class="text-muted">
                        Posted by <?php echo htmlspecialchars($topic['username']); ?>
                        on <?php echo format_date($topic['created_at']); ?>
                        <?php if ($topic['created_at'] !== $topic['updated_at']): ?>
                            (edited <?php echo format_date($topic['updated_at']); ?>)
                        <?php endif; ?>
                    </small>
                </div>
                <div class="topic-content">
                    <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
                </div>
            </div>
            <div class="card-footer text-muted">
                <i class="far fa-eye"></i> <?php echo $topic['views']; ?> views 
                <span class="text-muted small">(<?php echo $unique_viewers; ?> unique viewers)</span>
                <i class="far fa-comment ml-3"></i> <?php echo $comments->num_rows; ?> comments
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Comments</h5>
            </div>
            <div class="card-body">
                <?php if ($comments->num_rows > 0): ?>
                    <?php while ($comment = $comments->fetch_assoc()): ?>
                        <div class="comment mb-4" id="comment-<?php echo $comment['id']; ?>">
                            <div class="d-flex">
                                <div class="mr-3">
                                    <?php if ($comment['avatar_url']): ?>
                                        <img src="<?php echo htmlspecialchars($comment['avatar_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($comment['username']); ?>" 
                                             class="rounded-circle" width="40" height="40">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="comment-meta mb-2">
                                        <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                        <small class="text-muted ml-2">
                                            <?php echo format_date($comment['created_at']); ?>
                                            <?php if ($comment['created_at'] !== $comment['updated_at']): ?>
                                                (edited)
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="comment-content">
                                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted text-center">No comments yet. Be the first to comment!</p>
                <?php endif; ?>

                <!-- Comment Form -->
                <form method="POST" action="topic.php?id=<?php echo $topic_id; ?>" class="mt-4">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="form-group">
                        <label for="content">Add a Comment</label>
                        <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Post Comment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (is_admin()): ?>
<!-- Delete Topic Modal -->
<div class="modal fade" id="deleteTopicModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Topic</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this topic? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="delete_topic.php" method="POST" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>">
                    <button type="submit" class="btn btn-danger">Delete Topic</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

// Flush the output buffer
ob_end_flush();
?> 