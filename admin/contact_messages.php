<?php
$page_title = "Contact Messages";
require_once '../includes/header.php';

// Check if user is admin
if (!is_admin()) {
    set_message('danger', 'You do not have permission to access this page.');
    header('Location: ../index.php');
    exit();
}

// Handle message actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    // Verify CSRF token
    if (!verify_csrf_token($_GET['csrf_token'])) {
        set_message('danger', 'Invalid request.');
        header('Location: contact_messages.php');
        exit();
    }
    
    if ($action === 'mark_read') {
        // Mark message as read
        $stmt = $conn->prepare("UPDATE contact_messages SET is_read = TRUE WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            set_message('success', 'Message marked as read.');
        } else {
            set_message('danger', 'Error updating message status.');
        }
    } elseif ($action === 'delete') {
        // Delete message
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            set_message('success', 'Message deleted successfully.');
        } else {
            set_message('danger', 'Error deleting message.');
        }
    }
    
    header('Location: contact_messages.php');
    exit();
}

// Get messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total count
$stmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages");
$stmt->execute();
$total_messages = $stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_messages / $per_page);

// Get messages for current page
$stmt = $conn->prepare("
    SELECT cm.*, u.username 
    FROM contact_messages cm
    LEFT JOIN users u ON cm.user_id = u.id
    ORDER BY cm.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Contact Messages</h1>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2"></i> Messages
                        <?php if ($total_messages > 0): ?>
                            <span class="badge bg-light text-dark ms-2"><?php echo $total_messages; ?></span>
                        <?php endif; ?>
                    </h5>
                    <div>
                        <span class="badge bg-danger">
                            <?php 
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE is_read = FALSE");
                            $stmt->execute();
                            echo $stmt->get_result()->fetch_row()[0] . " unread"; 
                            ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($messages)): ?>
                        <div class="alert alert-info m-3">No messages found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                        <tr class="<?php echo $message['is_read'] ? '' : 'table-active'; ?>">
                                            <td>
                                                <?php if ($message['is_read']): ?>
                                                    <span class="badge bg-secondary"><i class="fas fa-envelope-open"></i> Read</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><i class="fas fa-envelope"></i> Unread</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($message['name']); ?>
                                                <?php if ($message['user_id']): ?>
                                                    <small class="text-muted">
                                                        (<?php echo htmlspecialchars($message['username']); ?>)
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($message['email']); ?></td>
                                            <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewMessageModal<?php echo $message['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if (!$message['is_read']): ?>
                                                        <a href="contact_messages.php?action=mark_read&id=<?php echo $message['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>" 
                                                           class="btn btn-success" 
                                                           title="Mark as Read">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="contact_messages.php?action=delete&id=<?php echo $message['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>" 
                                                       class="btn btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this message?');"
                                                       title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                                
                                                <!-- View Message Modal -->
                                                <div class="modal fade" id="viewMessageModal<?php echo $message['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">
                                                                    <?php echo htmlspecialchars($message['subject']); ?>
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <strong>From:</strong> 
                                                                    <?php echo htmlspecialchars($message['name']); ?> 
                                                                    &lt;<?php echo htmlspecialchars($message['email']); ?>&gt;
                                                                    
                                                                    <?php if ($message['user_id']): ?>
                                                                        <span class="badge bg-primary">Registered User</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <strong>Date:</strong> 
                                                                    <?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?>
                                                                </div>
                                                                
                                                                <div class="card">
                                                                    <div class="card-body bg-light">
                                                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <?php if (!$message['is_read']): ?>
                                                                    <a href="contact_messages.php?action=mark_read&id=<?php echo $message['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>" 
                                                                       class="btn btn-success">
                                                                        <i class="fas fa-check"></i> Mark as Read
                                                                    </a>
                                                                <?php endif; ?>
                                                                
                                                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo htmlspecialchars($message['subject']); ?>" 
                                                                   class="btn btn-primary">
                                                                    <i class="fas fa-reply"></i> Reply by Email
                                                                </a>
                                                                
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Message pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 