<?php
// Only start session if one doesn't already exist
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Authentication functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function get_user_role() {
    if (!is_logged_in()) {
        return null;
    }
    global $conn;
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ? $user['role'] : null;
}

function is_admin() {
    return get_user_role() === 'admin';
}

function is_moderator() {
    $role = get_user_role();
    return $role === 'admin' || $role === 'moderator';
}

// Database helper functions
function get_user_by_id($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, avatar_url, bio, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_topic_by_id($topic_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT t.*, u.username, c.name as category_name 
        FROM topics t 
        JOIN users u ON t.user_id = u.id 
        JOIN categories c ON t.category_id = c.id 
        WHERE t.id = ?
    ");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Pagination helper
function paginate($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'items_per_page' => $items_per_page
    ];
}

// Time formatting
function format_date($date) {
    $timestamp = strtotime($date);
    return date('F j, Y g:i A', $timestamp);
}

// Error and success message handling
function set_message($type, $message) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $message
    ];
}

function get_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

/**
 * Track a unique view for a topic
 * 
 * This function records a unique view for a topic based on user ID, IP address, and session ID.
 * It ensures that the same user/session/IP combination can only count as one view per day.
 * 
 * @param int $topic_id The ID of the topic being viewed
 * @return bool True if a new view was recorded, false if it was a duplicate view
 */
function track_topic_view($topic_id) {
    global $conn;
    
    // Get user information
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $session_id = session_id();
    $current_date = date('Y-m-d');
    $current_datetime = date('Y-m-d H:i:s');
    
    // Check if this view already exists for today
    $stmt = $conn->prepare("
        SELECT id FROM topic_views 
        WHERE topic_id = ? 
        AND (user_id = ? OR (user_id IS NULL AND ? IS NULL))
        AND ip_address = ? 
        AND session_id = ? 
        AND view_date = ?
    ");
    $stmt->bind_param("iissss", $topic_id, $user_id, $user_id, $ip_address, $session_id, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If view already exists for today, don't count it again
    if ($result->num_rows > 0) {
        return false;
    }
    
    // Insert new view record
    $stmt = $conn->prepare("
        INSERT INTO topic_views (topic_id, user_id, ip_address, session_id, view_date, viewed_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissss", $topic_id, $user_id, $ip_address, $session_id, $current_date, $current_datetime);
    $success = $stmt->execute();
    
    // If successfully inserted, update the topic's view count
    if ($success) {
        // Update the view count in the topics table
        $stmt = $conn->prepare("UPDATE topics SET views = views + 1 WHERE id = ?");
        $stmt->bind_param("i", $topic_id);
        $stmt->execute();
        return true;
    }
    
    return false;
}

/**
 * Get the total view count for a topic
 * 
 * @param int $topic_id The ID of the topic
 * @return int The total number of views
 */
function get_topic_view_count($topic_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT views FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['views'];
    }
    
    return 0;
}

/**
 * Get unique viewer count for a topic
 * 
 * @param int $topic_id The ID of the topic
 * @return int The number of unique viewers (based on user_id or IP if not logged in)
 */
function get_topic_unique_viewers($topic_id) {
    global $conn;
    
    // Count unique users who viewed this topic
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT COALESCE(user_id, CONCAT('ip-', ip_address))) as unique_viewers
        FROM topic_views
        WHERE topic_id = ?
    ");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['unique_viewers'];
    }
    
    return 0;
} 