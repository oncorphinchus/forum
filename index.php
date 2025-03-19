<?php
// Check if database needs to be initialized (do this before anything else)
if (file_exists('db_init.php')) {
    include_once 'db_init.php';
}

$page_title = "Home";
require_once 'includes/header.php';

// Require login to view content
require_login();

// Get categories
$stmt = $conn->prepare("
    SELECT c.*, 
           COUNT(DISTINCT t.id) as topic_count,
           COUNT(DISTINCT cm.id) as comment_count
    FROM categories c
    LEFT JOIN topics t ON c.id = t.category_id
    LEFT JOIN comments cm ON t.id = cm.topic_id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$stmt->execute();
$categories = $stmt->get_result();

// Get recent topics with unique viewer counts
$stmt = $conn->prepare("
    SELECT t.*, 
           u.username,
           c.name as category_name,
           COUNT(cm.id) as comment_count,
           MAX(cm.created_at) as last_comment_date,
           (SELECT COUNT(DISTINCT COALESCE(tv.user_id, CONCAT('ip-', tv.ip_address))) 
            FROM topic_views tv 
            WHERE tv.topic_id = t.id) as unique_viewers
    FROM topics t
    JOIN users u ON t.user_id = u.id
    JOIN categories c ON t.category_id = c.id
    LEFT JOIN comments cm ON t.id = cm.topic_id
    GROUP BY t.id
    ORDER BY COALESCE(MAX(cm.created_at), t.created_at) DESC
    LIMIT 10
");
$stmt->execute();
$recent_topics = $stmt->get_result();
?>

<div class="container py-5 main-content">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-gradient">Recent Topics</h5>
                    <?php if (is_logged_in()): ?>
                        <a href="new_topic.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> New Topic
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="topic-list">
                        <?php if ($recent_topics->num_rows > 0): ?>
                            <?php while ($topic = $recent_topics->fetch_assoc()): ?>
                                <div class="topic-item p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="topic-title mb-1">
                                            <a href="topic.php?id=<?php echo $topic['id']; ?>" class="hover-lift">
                                                <?php echo htmlspecialchars($topic['title']); ?>
                                            </a>
                                        </h5>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo htmlspecialchars($topic['category_name']); ?>
                                        </span>
                                    </div>
                                    <div class="topic-meta">
                                        <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($topic['username']); ?>
                                        <i class="fas fa-clock ms-3 me-1"></i> <?php echo format_date($topic['created_at']); ?>
                                    </div>
                                    <div class="topic-stats mt-2">
                                        <span class="me-3"><i class="far fa-comment me-1"></i> <?php echo $topic['comment_count']; ?> comments</span>
                                        <span class="me-3"><i class="far fa-eye me-1"></i> <?php echo $topic['views']; ?> views
                                        <span class="text-muted small">(<?php echo $topic['unique_viewers']; ?> unique)</span></span>
                                        <?php if ($topic['last_comment_date']): ?>
                                            <span>
                                                <i class="fas fa-reply me-1"></i> <?php echo format_date($topic['last_comment_date']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-comment-slash fa-3x mb-3"></i>
                                <p>No topics have been created yet.</p>
                                <a href="new_topic.php" class="btn btn-primary">Create the first topic</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="all_topics.php" class="btn btn-outline-primary btn-sm">View All Topics</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header">
                    <h5 class="mb-0 text-gradient">Categories</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if ($categories->num_rows > 0): ?>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <a href="category.php?id=<?php echo $category['id']; ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 py-3 px-4 hover-lift">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($category['name']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($category['description']); ?>
                                    </small>
                                </div>
                                <div class="text-end ms-2">
                                    <span class="badge bg-light text-dark mb-1"><?php echo $category['topic_count']; ?> topics</span>
                                    <span class="badge bg-light text-dark d-block"><?php echo $category['comment_count']; ?> comments</span>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="list-group-item text-center text-muted border-0 py-4">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <p>No categories available.</p>
                            <?php if (is_admin()): ?>
                                <a href="admin/categories.php" class="btn btn-primary btn-sm">Create Categories</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (is_admin()): ?>
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header">
                        <h5 class="mb-0 text-gradient">Admin Dashboard</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="admin/index.php" class="list-group-item list-group-item-action border-0 py-3 px-4 hover-lift">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="admin/categories.php" class="list-group-item list-group-item-action border-0 py-3 px-4 hover-lift">
                            <i class="fas fa-folder-plus me-2"></i> Manage Categories
                        </a>
                        <a href="admin/users.php" class="list-group-item list-group-item-action border-0 py-3 px-4 hover-lift">
                            <i class="fas fa-users-cog me-2"></i> Manage Users
                        </a>
                        <a href="admin/topic_stats.php" class="list-group-item list-group-item-action border-0 py-3 px-4 hover-lift">
                            <i class="fas fa-chart-line me-2"></i> Topic Statistics
                        </a>
                        <a href="admin/reports.php" class="list-group-item list-group-item-action border-0 py-3 px-4 hover-lift">
                            <i class="fas fa-flag me-2"></i> View Reports
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h5 class="card-title text-gradient">Forum Statistics</h5>
                    <ul class="list-group list-group-flush">
                        <?php
                        // Get total counts
                        $stats = $conn->query("
                            SELECT 
                                (SELECT COUNT(*) FROM users) as user_count,
                                (SELECT COUNT(*) FROM topics) as topic_count,
                                (SELECT COUNT(*) FROM comments) as comment_count,
                                (SELECT COUNT(*) FROM categories) as category_count
                        ")->fetch_assoc();
                        ?>
                        <li class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-users me-2"></i> Users</span>
                            <span class="badge bg-primary rounded-pill"><?php echo $stats['user_count']; ?></span>
                        </li>
                        <li class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-comments me-2"></i> Topics</span>
                            <span class="badge bg-primary rounded-pill"><?php echo $stats['topic_count']; ?></span>
                        </li>
                        <li class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-reply me-2"></i> Comments</span>
                            <span class="badge bg-primary rounded-pill"><?php echo $stats['comment_count']; ?></span>
                        </li>
                        <li class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-folder me-2"></i> Categories</span>
                            <span class="badge bg-primary rounded-pill"><?php echo $stats['category_count']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
