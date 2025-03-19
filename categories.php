<?php
$page_title = "Categories";
require_once 'includes/header.php';

// Require login
require_login();

// Get categories with statistics
$stmt = $conn->prepare("
    SELECT c.*, 
           COUNT(DISTINCT t.id) as topic_count,
           COUNT(DISTINCT cm.id) as comment_count,
           MAX(COALESCE(cm.created_at, t.created_at)) as last_activity
    FROM categories c
    LEFT JOIN topics t ON c.id = t.category_id
    LEFT JOIN comments cm ON t.id = cm.topic_id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$stmt->execute();
$categories = $stmt->get_result();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Forum Categories</h4>
                <?php if (is_admin()): ?>
                    <a href="admin/categories.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-cog"></i> Manage Categories
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if ($categories->num_rows > 0): ?>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <a href="category.php?id=<?php echo $category['id']; ?>" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($category['name']); ?></h5>
                                        <p class="mb-1 text-muted">
                                            <?php echo htmlspecialchars($category['description']); ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="h5 mb-0"><?php echo $category['topic_count']; ?> topics</div>
                                        <small class="text-muted">
                                            <?php echo $category['comment_count']; ?> comments
                                            <?php if ($category['last_activity']): ?>
                                                <br>Last activity: <?php echo format_date($category['last_activity']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="list-group-item text-center text-muted">
                            No categories available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 