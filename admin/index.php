<?php
$page_title = "Admin Dashboard";
require_once '../includes/header.php';

// Require admin access
if (!is_admin()) {
    set_message('danger', 'Access denied. Admin privileges required.');
    header('Location: ../index.php');
    exit();
}

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $result->fetch_assoc()['count'];

// Total topics
$result = $conn->query("SELECT COUNT(*) as count FROM topics");
$stats['topics'] = $result->fetch_assoc()['count'];

// Total comments
$result = $conn->query("SELECT COUNT(*) as count FROM comments");
$stats['comments'] = $result->fetch_assoc()['count'];

// Total categories
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
$stats['categories'] = $result->fetch_assoc()['count'];

// Commenting out contact message statistics
/*
// Total contact messages
$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
$stats['contact_messages'] = $result->fetch_assoc()['count'];

// Unread contact messages
$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = FALSE");
$stats['unread_messages'] = $result->fetch_assoc()['count'];
*/

// Recent registrations
$stmt = $conn->prepare("
    SELECT id, username, email, role, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_users = $stmt->get_result();

// Recent topics
$stmt = $conn->prepare("
    SELECT t.id, t.title, t.created_at, u.username, c.name as category_name
    FROM topics t
    JOIN users u ON t.user_id = u.id
    JOIN categories c ON t.category_id = c.id
    ORDER BY t.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_topics = $stmt->get_result();

// Commenting out recent contact messages section
/*
// Recent contact messages
$stmt = $conn->prepare("
    SELECT cm.id, cm.name, cm.email, cm.subject, cm.created_at, cm.is_read
    FROM contact_messages cm
    ORDER BY cm.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_messages = $stmt->get_result();
*/
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
            </div>

            <div class="row">
                <!-- Statistics Cards -->
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Users</h6>
                                    <h2 class="mb-0"><?php echo $stats['users']; ?></h2>
                                </div>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a href="users.php" class="text-white">View Details</a>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-success mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Topics</h6>
                                    <h2 class="mb-0"><?php echo $stats['topics']; ?></h2>
                                </div>
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a href="topics.php" class="text-white">View Details</a>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-info mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Comments</h6>
                                    <h2 class="mb-0"><?php echo $stats['comments']; ?></h2>
                                </div>
                                <i class="fas fa-comments fa-2x"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a href="comments.php" class="text-white">View Details</a>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Categories</h6>
                                    <h2 class="mb-0"><?php echo $stats['categories']; ?></h2>
                                </div>
                                <i class="fas fa-folder fa-2x"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a href="categories.php" class="text-white">View Details</a>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Comment out Contact Messages card -->
                <!--
                <div class="col-md-3">
                    <div class="card text-white bg-danger mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Contact Messages</h6>
                                    <h2 class="mb-0"><?php echo $stats['contact_messages']; ?></h2>
                                </div>
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a href="contact_messages.php" class="text-white">
                                View Details
                                <?php if ($stats['unread_messages'] > 0): ?>
                                    <span class="badge bg-light text-danger"><?php echo $stats['unread_messages']; ?> unread</span>
                                <?php endif; ?>
                            </a>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                -->
            </div>

            <div class="row">
                <!-- Recent Users -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-user-plus me-1"></i>
                            Recent Registrations
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = $recent_users->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="users.php" class="btn btn-sm btn-primary">View All Users</a>
                        </div>
                    </div>
                </div>

                <!-- Recent Topics -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-file-alt me-1"></i>
                            Recent Topics
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Author</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($topic = $recent_topics->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($topic['title']); ?></td>
                                                <td><?php echo htmlspecialchars($topic['category_name']); ?></td>
                                                <td><?php echo htmlspecialchars($topic['username']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="topics.php" class="btn btn-sm btn-primary">View All Topics</a>
                        </div>
                    </div>
                </div>
                
                <!-- Comment out Recent Contact Messages section -->
                <!--
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-envelope me-1"></i>
                            Recent Contact Messages
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recent_messages->num_rows === 0): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No messages found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php while ($message = $recent_messages->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($message['is_read']): ?>
                                                            <span class="badge bg-secondary"><i class="fas fa-envelope-open"></i> Read</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger"><i class="fas fa-envelope"></i> Unread</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($message['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                                    <td><?php echo date('M j, Y', strtotime($message['created_at'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="contact_messages.php" class="btn btn-sm btn-primary">View All Messages</a>
                        </div>
                    </div>
                </div>
                -->
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 