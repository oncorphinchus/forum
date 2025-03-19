<?php
// Database initialization script for first launch

// Include database configuration
require_once 'config.php';

// Check if database is already initialized (check for users table)
$check_query = "SELECT COUNT(*) as table_count FROM information_schema.tables 
               WHERE table_schema = '{$dbname}' AND table_name = 'users'";
$result = $conn->query($check_query);
$row = $result->fetch_assoc();

// If users table doesn't exist, initialize database
if ($row['table_count'] == 0) {
    echo "Initializing database...\n";
    
    // Create users table
    $sql[] = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        avatar_url VARCHAR(255),
        bio TEXT,
        role ENUM('user', 'moderator', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    // Create categories table
    $sql[] = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    // Create topics table
    $sql[] = "CREATE TABLE IF NOT EXISTS topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        views INT DEFAULT 0,
        is_pinned BOOLEAN DEFAULT FALSE,
        is_locked BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    // Create comments table
    $sql[] = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        topic_id INT NOT NULL,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    // Create user_sessions table
    $sql[] = "CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_token VARCHAR(255) NOT NULL UNIQUE,
        ip_address VARCHAR(45),
        user_agent TEXT,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    // Create topic_views table
    $sql[] = "CREATE TABLE IF NOT EXISTS topic_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        topic_id INT NOT NULL,
        user_id INT,
        ip_address VARCHAR(45) NOT NULL,
        session_id VARCHAR(255) NOT NULL,
        view_date DATE NOT NULL,
        viewed_at DATETIME NOT NULL,
        FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_view (topic_id, user_id, ip_address, session_id, view_date)
    )";

    // Create contact_messages table
    $sql[] = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";

    // Insert default admin user (password: admin123)
    $sql[] = "INSERT INTO users (username, email, password, role) 
              VALUES ('admin', 'admin@example.com', 
                     '\$2y\$10\$8tPJXaHtMGXqKzwzN0dQWe4sZeKHj5zKB8cVVZ5hGHB7PuZoNKPJq', 'admin')";

    // Insert default categories
    $sql[] = "INSERT INTO categories (name, description) VALUES
              ('General Discussion', 'General topics and discussions'),
              ('Announcements', 'Important announcements and updates'),
              ('Help & Support', 'Get help and support from the community')";

    // Execute all SQL statements
    $success = true;
    foreach ($sql as $query) {
        if (!$conn->query($query)) {
            echo "Error: " . $query . "<br>" . $conn->error . "<br>";
            $success = false;
            break;
        }
    }

    if ($success) {
        echo "Database initialized successfully!\n";
    } else {
        echo "Error initializing database.\n";
    }
} else {
    echo "Database already initialized.\n";
}
?> 