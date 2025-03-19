<?php
$page_title = "Topic View Statistics";
require_once '../includes/header.php';

// Check if user is admin
if (!is_admin()) {
    set_message('danger', 'You do not have permission to access this page.');
    header('Location: ../index.php');
    exit();
}

// Get topic ID if provided
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['id'] : null;

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 20;
$offset = ($page - 1) * $items_per_page;

// Get most viewed topics
$query = "
    SELECT t.id, t.title, t.views, 
           COUNT(DISTINCT tv.id) AS confirmed_views,
           COUNT(DISTINCT COALESCE(tv.user_id, CONCAT('ip-', tv.ip_address))) AS unique_viewers,
           COUNT(DISTINCT tv.user_id) AS registered_viewers,
           COUNT(DISTINCT CASE WHEN tv.user_id IS NULL THEN tv.ip_address END) AS guest_viewers,
           MIN(tv.viewed_at) AS first_view,
           MAX(tv.viewed_at) AS last_view
    FROM topics t
    LEFT JOIN topic_views tv ON t.id = tv.topic_id
    WHERE tv.view_date BETWEEN ? AND ?
";

// Add topic filter if provided
if ($topic_id) {
    $query .= " AND t.id = ?";
    $countParams = "ssi";
    $queryParams = [$start_date, $end_date, $topic_id];
} else {
    $countParams = "ss";
    $queryParams = [$start_date, $end_date];
}

$query .= " GROUP BY t.id ORDER BY confirmed_views DESC";

// Get total count for pagination
$countQuery = "SELECT COUNT(DISTINCT t.id) AS total FROM topics t LEFT JOIN topic_views tv ON t.id = tv.topic_id WHERE tv.view_date BETWEEN ? AND ?";
if ($topic_id) {
    $countQuery .= " AND t.id = ?";
}

$stmt = $conn->prepare($countQuery);
$stmt->bind_param($countParams, ...$queryParams);
$stmt->execute();
$total_topics = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_topics / $items_per_page);

// Add pagination to the main query
$query .= " LIMIT ? OFFSET ?";
$queryParams[] = $items_per_page;
$queryParams[] = $offset;

// Execute the main query
$stmt = $conn->prepare($query);
$stmt->bind_param($countParams . "ii", ...$queryParams);
$stmt->execute();
$topics = $stmt->get_result();

// Get overall statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT tv.id) AS total_views,
        COUNT(DISTINCT COALESCE(tv.user_id, CONCAT('ip-', tv.ip_address))) AS total_unique_viewers,
        COUNT(DISTINCT tv.user_id) AS total_registered_viewers,
        COUNT(DISTINCT CASE WHEN tv.user_id IS NULL THEN tv.ip_address END) AS total_guest_viewers,
        COUNT(DISTINCT tv.topic_id) AS topics_viewed,
        AVG(daily_views.view_count) AS avg_daily_views
    FROM topic_views tv
    JOIN (
        SELECT view_date, COUNT(*) AS view_count 
        FROM topic_views 
        WHERE view_date BETWEEN ? AND ?
        GROUP BY view_date
    ) AS daily_views
    WHERE tv.view_date BETWEEN ? AND ?
");
$stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$overall_stats = $stmt->get_result()->fetch_assoc();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Topic View Statistics</h1>
            </div>
            
            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Filter Options</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="topic_stats.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="topic_id" class="form-label">Topic ID (optional)</label>
                            <input type="number" class="form-control" id="topic_id" name="topic_id" 
                                   value="<?php echo $topic_id ? htmlspecialchars($topic_id) : ''; ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="topic_stats.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Overall Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Views</h5>
                            <p class="card-text display-6"><?php echo number_format($overall_stats['total_views']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Unique Viewers</h5>
                            <p class="card-text display-6"><?php echo number_format($overall_stats['total_unique_viewers']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Topics Viewed</h5>
                            <p class="card-text display-6"><?php echo number_format($overall_stats['topics_viewed']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Avg. Daily Views</h5>
                            <p class="card-text display-6"><?php echo number_format($overall_stats['avg_daily_views'], 1); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Topics Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Most Viewed Topics</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Topic</th>
                                    <th>Total Views</th>
                                    <th>Unique Viewers</th>
                                    <th>Registered Users</th>
                                    <th>Guest Viewers</th>
                                    <th>First View</th>
                                    <th>Last View</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($topics->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No data available for the selected period.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($topic = $topics->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <a href="../topic.php?id=<?php echo $topic['id']; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($topic['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo number_format($topic['confirmed_views']); ?></td>
                                            <td><?php echo number_format($topic['unique_viewers']); ?></td>
                                            <td><?php echo number_format($topic['registered_viewers']); ?></td>
                                            <td><?php echo number_format($topic['guest_viewers']); ?></td>
                                            <td><?php echo $topic['first_view'] ? date('M j, Y g:i A', strtotime($topic['first_view'])) : 'N/A'; ?></td>
                                            <td><?php echo $topic['last_view'] ? date('M j, Y g:i A', strtotime($topic['last_view'])) : 'N/A'; ?></td>
                                            <td>
                                                <a href="topic_stats.php?topic_id=<?php echo $topic['id']; ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-chart-line"></i> Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Topic statistics pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?><?php echo $topic_id ? '&topic_id=' . $topic_id : ''; ?>">Previous</a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?><?php echo $topic_id ? '&topic_id=' . $topic_id : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?><?php echo $topic_id ? '&topic_id=' . $topic_id : ''; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($topic_id): ?>
                <!-- Topic View Details -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Detailed View History for Topic #<?php echo $topic_id; ?></h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get detailed view history for specific topic
                        $stmt = $conn->prepare("
                            SELECT tv.*, u.username
                            FROM topic_views tv
                            LEFT JOIN users u ON tv.user_id = u.id
                            WHERE tv.topic_id = ? AND tv.view_date BETWEEN ? AND ?
                            ORDER BY tv.viewed_at DESC
                            LIMIT 100
                        ");
                        $stmt->bind_param("iss", $topic_id, $start_date, $end_date);
                        $stmt->execute();
                        $view_history = $stmt->get_result();
                        ?>
                        
                        <?php if ($view_history->num_rows === 0): ?>
                            <div class="alert alert-info">No detailed view history available for this topic in the selected period.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>User</th>
                                            <th>IP Address</th>
                                            <th>Session ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($view = $view_history->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y g:i:s A', strtotime($view['viewed_at'])); ?></td>
                                                <td>
                                                    <?php if ($view['user_id']): ?>
                                                        <a href="../profile.php?id=<?php echo $view['user_id']; ?>">
                                                            <?php echo htmlspecialchars($view['username']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Guest</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($view['ip_address']); ?></td>
                                                <td>
                                                    <span class="text-muted small"><?php echo substr(htmlspecialchars($view['session_id']), 0, 10); ?>...</span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i> Showing up to 100 most recent views. For performance reasons, the complete history is not displayed.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 