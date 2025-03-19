<?php
$page_title = "Manage Categories";
require_once '../includes/header.php';

// Require admin access
if (!is_admin()) {
    set_message('danger', 'Access denied. Admin privileges required.');
    header('Location: ../index.php');
    exit();
}

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Invalid request.');
        header('Location: categories.php');
        exit();
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'edit') {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

        // Validate input
        $errors = [];
        if (empty($name)) {
            $errors[] = "Category name is required.";
        }

        if (empty($errors)) {
            if ($action === 'create') {
                $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $description);
                $success_message = "Category created successfully!";
            } else {
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $description, $category_id);
                $success_message = "Category updated successfully!";
            }

            if ($stmt->execute()) {
                set_message('success', $success_message);
                header('Location: categories.php');
                exit();
            } else {
                $errors[] = "Error saving category. Please try again.";
            }
        }
    } elseif ($action === 'delete') {
        $category_id = (int)$_POST['category_id'];
        
        // Check if category has topics
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM topics WHERE category_id = ?");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $topic_count = $stmt->get_result()->fetch_assoc()['count'];

        if ($topic_count > 0) {
            set_message('danger', 'Cannot delete category. It contains topics.');
            header('Location: categories.php');
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        
        if ($stmt->execute()) {
            set_message('success', 'Category deleted successfully!');
        } else {
            set_message('danger', 'Error deleting category.');
        }
        
        header('Location: categories.php');
        exit();
    }
}

// Get categories with stats
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

// Get category for editing if requested
$edit_category = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $category_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $edit_category = $stmt->get_result()->fetch_assoc();
}
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Manage Categories</h2>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#categoryModal">
                <i class="fas fa-plus"></i> Add Category
            </button>
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

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Topics</th>
                    <th>Comments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($categories->num_rows > 0): ?>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a href="../category.php?id=<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td><?php echo $category['topic_count']; ?></td>
                            <td><?php echo $category['comment_count']; ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?php echo $category['id']; ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No categories found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="categories.php">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'create'; ?>">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>

                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $edit_category ? 'Edit Category' : 'Add Category'; ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php 
                            echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; 
                        ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_category ? 'Save Changes' : 'Add Category'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the category "<span id="deleteCategoryName"></span>"?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="categories.php">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="category_id" id="deleteCategoryId">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(categoryId, categoryName) {
    document.getElementById('deleteCategoryId').value = categoryId;
    document.getElementById('deleteCategoryName').textContent = categoryName;
    $('#deleteModal').modal('show');
}

<?php if ($edit_category): ?>
$(document).ready(function() {
    $('#categoryModal').modal('show');
});
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?> 