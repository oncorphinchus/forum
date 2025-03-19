<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

require_once 'includes/header.php';

// Require login
require_login();

// Get category ID
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$category_id) {
    set_message('danger', 'Invalid category ID.');
    header('Location: index.php');
    exit();
}

// Get category details
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    set_message('danger', 'Category not found.');
    header('Location: index.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 20;

// Get total topics count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM topics WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$total_topics = $stmt->get_result()->fetch_assoc()['total'];

// Calculate pagination
$pagination = paginate($total_topics, $items_per_page, $page);

// Get topics for current page with unique viewer counts
$stmt = $conn->prepare("
    SELECT t.*, 
           u.username,
           COUNT(c.id) as comment_count,
           MAX(c.created_at) as last_comment_date,
           (SELECT COUNT(DISTINCT COALESCE(tv.user_id, CONCAT('ip-', tv.ip_address))) 
            FROM topic_views tv 
            WHERE tv.topic_id = t.id) as unique_viewers
    FROM topics t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN comments c ON t.id = c.topic_id
    WHERE t.category_id = ?
    GROUP BY t.id
    ORDER BY t.is_pinned DESC, 
             COALESCE(MAX(c.created_at), t.created_at) DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $category_id, $pagination['items_per_page'], $pagination['offset']);
$stmt->execute();
$topics = $stmt->get_result();

$page_title = $category['name'];
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($category['name']); ?>
                </li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h4>
                    <small class="text-muted">
                        <?php echo htmlspecialchars($category['description']); ?>
                    </small>
                </div>
                <a href="new_topic.php?category_id=<?php echo $category_id; ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Topic
                </a>
            </div>
            <div class="card-body p-0">
                <div class="topic-list">
                    <?php if ($topics->num_rows > 0): ?>
                        <?php while ($topic = $topics->fetch_assoc()): ?>
                            <div class="topic-item p-3 <?php echo $topic['is_pinned'] ? 'bg-light' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-1">
                                        <?php if ($topic['is_pinned']): ?>
                                            <i class="fas fa-thumbtack text-muted mr-1" title="Pinned Topic"></i>
                                        <?php endif; ?>
                                        <?php if ($topic['is_locked']): ?>
                                            <i class="fas fa-lock text-muted mr-1" title="Locked Topic"></i>
                                        <?php endif; ?>
                                        <a href="topic.php?id=<?php echo $topic['id']; ?>">
                                            <?php echo htmlspecialchars($topic['title']); ?>
                                        </a>
                                    </h5>
                                </div>
                                <div class="topic-meta">
                                    Posted by <?php echo htmlspecialchars($topic['username']); ?>
                                    on <?php echo format_date($topic['created_at']); ?>
                                </div>
                                <div class="topic-stats mt-2">
                                    <i class="far fa-comment"></i> <?php echo $topic['comment_count']; ?> comments
                                    <i class="far fa-eye ml-3"></i> <?php echo $topic['views']; ?> views
                                    <span class="text-muted small">(<?php echo $topic['unique_viewers']; ?> unique)</span>
                                    <?php if ($topic['last_comment_date']): ?>
                                        <span class="ml-3">
                                            Last reply: <?php echo format_date($topic['last_comment_date']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">
                            No topics have been created in this category yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($pagination['total_pages'] > 1): ?>
            <nav aria-label="Topics pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?id=<?php echo $category_id; ?>&page=<?php echo ($page - 1); ?>">
                                Previous
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?id=<?php echo $category_id; ?>&page=<?php echo ($page + 1); ?>">
                                Next
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php 
require_once 'includes/footer.php';

// Flush the output buffer
ob_end_flush();
?> 