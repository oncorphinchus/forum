<?php
// Database initialization script for first launch

// Include database configuration
require_once 'config.php';

// Check if database is already initialized (check for users table)
try {
    $check_query = "SELECT COUNT(*) as table_count FROM information_schema.tables 
                   WHERE table_schema = 'public' AND table_name = 'users'";
    $stmt = $conn->query($check_query);
    $row = $stmt->fetch();

    // If users table doesn't exist, initialize database
    if ($row['table_count'] == 0) {
        echo "Initializing database...\n";
        
        // PostgreSQL uses SERIAL for auto-increment
        $sql[] = "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255),
            avatar_url VARCHAR(255),
            bio TEXT,
            role VARCHAR(10) DEFAULT 'user' CHECK (role IN ('user', 'moderator', 'admin')),
            oauth_provider VARCHAR(10) DEFAULT 'local' CHECK (oauth_provider IN ('local', 'google', 'facebook', 'github', 'apple')),
            oauth_id VARCHAR(255),
            oauth_data TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // Create categories table
        $sql[] = "CREATE TABLE IF NOT EXISTS categories (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // Create topics table
        $sql[] = "CREATE TABLE IF NOT EXISTS topics (
            id SERIAL PRIMARY KEY,
            category_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            views INTEGER DEFAULT 0,
            is_pinned BOOLEAN DEFAULT FALSE,
            is_locked BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";

        // Create comments table
        $sql[] = "CREATE TABLE IF NOT EXISTS comments (
            id SERIAL PRIMARY KEY,
            topic_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";

        // Create user_sessions table
        $sql[] = "CREATE TABLE IF NOT EXISTS user_sessions (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            session_token VARCHAR(255) NOT NULL UNIQUE,
            ip_address VARCHAR(45),
            user_agent TEXT,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";

        // Create topic_views table
        $sql[] = "CREATE TABLE IF NOT EXISTS topic_views (
            id SERIAL PRIMARY KEY,
            topic_id INTEGER NOT NULL,
            user_id INTEGER,
            ip_address VARCHAR(45) NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            view_date DATE NOT NULL,
            viewed_at TIMESTAMP NOT NULL,
            FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE (topic_id, user_id, ip_address, session_id, view_date)
        )";

        // Create contact_messages table
        $sql[] = "CREATE TABLE IF NOT EXISTS contact_messages (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            user_id INTEGER,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_read BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";

        // Insert default admin user (password: admin123)
        $sql[] = "INSERT INTO users (username, email, password, role) 
                VALUES ('admin', 'admin@example.com', 
                        '$2y$10$8tPJXaHtMGXqKzwzN0dQWe4sZeKHj5zKB8cVVZ5hGHB7PuZoNKPJq', 'admin')";

        // Insert default categories
        $sql[] = "INSERT INTO categories (name, description) VALUES
                ('General Discussion', 'General topics and discussions'),
                ('Announcements', 'Important announcements and updates'),
                ('Help & Support', 'Get help and support from the community')";

        // Execute all SQL statements
        $success = true;
        $conn->beginTransaction();
        
        try {
            foreach ($sql as $query) {
                $conn->exec($query);
            }
            $conn->commit();
            echo "Database initialized successfully!\n";
        } catch (PDOException $e) {
            $conn->rollBack();
            echo "Error: " . $e->getMessage() . "\n";
            $success = false;
        }
        
    } else {
        echo "Database already initialized.\n";
        
        // Check if we need to add OAuth columns to existing users table
        $check_oauth_columns = "SELECT COUNT(*) as column_count FROM information_schema.columns 
                             WHERE table_schema = 'public' 
                             AND table_name = 'users' 
                             AND column_name = 'oauth_provider'";
        $stmt = $conn->query($check_oauth_columns);
        $row = $stmt->fetch();
        
        if ($row['column_count'] == 0) {
            echo "Updating users table with OAuth support...\n";
            
            // Add OAuth columns to existing users table
            $update_sql[] = "ALTER TABLE users
                            ADD COLUMN oauth_provider VARCHAR(10) DEFAULT 'local' CHECK (oauth_provider IN ('local', 'google', 'facebook', 'github', 'apple')),
                            ADD COLUMN oauth_id VARCHAR(255),
                            ADD COLUMN oauth_data TEXT";
                            
            // Execute updates
            $success = true;
            $conn->beginTransaction();
            
            try {
                foreach ($update_sql as $query) {
                    $conn->exec($query);
                }
                $conn->commit();
                echo "Users table updated successfully with OAuth support!\n";
            } catch (PDOException $e) {
                $conn->rollBack();
                echo "Error: " . $e->getMessage() . "\n";
                $success = false;
            }
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 