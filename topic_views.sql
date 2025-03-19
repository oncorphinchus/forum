-- Create topic_views table to track unique views
CREATE TABLE IF NOT EXISTS topic_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL, -- IPv6 can be up to 45 chars
    session_id VARCHAR(255) NOT NULL,
    view_date DATE NOT NULL, -- Store the date separately for easier indexing
    viewed_at DATETIME NOT NULL,
    -- Add foreign key constraints
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    -- Create a unique index to prevent duplicate views from same session/IP/user combination per day
    UNIQUE KEY unique_view (topic_id, user_id, ip_address, session_id, view_date),
    -- Add indexes for performance
    INDEX idx_topic_id (topic_id),
    INDEX idx_user_id (user_id),
    INDEX idx_viewed_at (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 