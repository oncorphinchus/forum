<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/users.php">
                    <i class="fas fa-users"></i>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/categories.php">
                    <i class="fas fa-folder"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'topics.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/topics.php">
                    <i class="fas fa-comments"></i>
                    Topics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/comments.php">
                    <i class="fas fa-comment"></i>
                    Comments
                </a>
            </li>
            <!-- Comment out Contact Messages section -->
            <!--
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact_messages.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/contact_messages.php">
                    <i class="fas fa-envelope"></i>
                    Contact Messages
                    <?php
                    // Get unread message count
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE is_read = FALSE");
                    $stmt->execute();
                    $unread_count = $stmt->fetchColumn();
                    if ($unread_count > 0): ?>
                        <span class="badge badge-danger badge-counter"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            -->
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'topic_stats.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/topic_stats.php">
                    <i class="fas fa-chart-line"></i>
                    Topic Statistics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/settings.php">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Reports</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' && isset($_GET['type']) && $_GET['type'] == 'activity' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/reports.php?type=activity">
                    <i class="fas fa-chart-line"></i>
                    Activity Report
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' && isset($_GET['type']) && $_GET['type'] == 'users' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/reports.php?type=users">
                    <i class="fas fa-user-chart"></i>
                    User Report
                </a>
            </li>
        </ul>
        
        <div class="d-grid gap-2 px-3 mt-4">
            <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Forum
            </a>
        </div>
    </div>
</nav> 